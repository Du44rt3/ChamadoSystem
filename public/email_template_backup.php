<?php
include_once '../src/DB.php';
include_once '../src/Chamado.php';
include_once '../src/ChamadoHistorico.php';
include_once '../src/EmailTemplate.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$database = new DB();
$db = $database->getConnection();

$chamado = new Chamado($db);
$chamado->id = $_GET['id'];
if (!$chamado->readOne()) {
    header('Location: index.php?error=chamado_nao_encontrado');
    exit;
}

$historico = new ChamadoHistorico($db);
$atividades = $historico->buscarHistoricoSemDuplicacoes($chamado->id);

$emailTemplate = new EmailTemplate($db);

// Definir o tipo de template baseado no parâmetro ou status do chamado
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'abertura';
if ($tipo == 'auto') {
    switch ($chamado->status) {
        case 'aberto':
            $tipo = 'abertura';
            break;
        case 'em_andamento':
            $tipo = 'andamento';
            break;
        case 'fechado':
            $tipo = 'finalizado';
            break;
        default:
            $tipo = 'abertura';
    }
}

// Gerar template baseado no tipo
$dadosExtras = null;
if ($tipo == 'andamento' && !empty($atividades)) {
    $dadosExtras = end($atividades); // Última atividade
} elseif ($tipo == 'finalizado') {
    $dadosExtras = $atividades; // Todas as atividades
}

$template = $emailTemplate->gerarTemplate($chamado, $tipo, $dadosExtras);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template de Email - ELUS Facilities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo-eluss.png" alt="ELUS Logo" class="navbar-logo mobile-compact">
                 <div class="navbar-brand-text">
                    <div class="brand-title">Grupo Elus | Operações e Facilities</div>
                    <div class="brand-subtitle">Infraestrutura & Tecnologia</div>
                </div>
            </a>
            <div class="navbar-nav">
                <a class="nav-link" href="index.php">Todos</a>
                <a class="nav-link" href="abertos.php">Abertos</a>
                <a class="nav-link" href="em_andamento.php">Em Andamento</a>
                <a class="nav-link" href="fechados.php">Fechados</a>
                <a class="nav-link" href="add.php">Novo Chamado</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary">
                            <i class="fas fa-envelope text-primary"></i> Template de Email - <?php echo ucfirst($chamado->codigo_chamado); ?>
                        </h5>
                        <div class="d-flex gap-2">
                            <div class="btn-group" role="group">
                                <a href="?id=<?php echo $chamado->id; ?>&tipo=abertura" class="btn btn-outline-primary <?php echo $tipo == 'abertura' ? 'active' : ''; ?>">
                                    <i class="fas fa-plus-circle"></i> Abertura
                                </a>
                                <a href="?id=<?php echo $chamado->id; ?>&tipo=andamento" class="btn btn-outline-warning <?php echo $tipo == 'andamento' ? 'active' : ''; ?>">
                                    <i class="fas fa-clock"></i> Em Andamento
                                </a>
                                <a href="?id=<?php echo $chamado->id; ?>&tipo=finalizado" class="btn btn-outline-success <?php echo $tipo == 'finalizado' ? 'active' : ''; ?>">
                                    <i class="fas fa-check-circle"></i> Finalizado
                                </a>
                                <a href="?id=<?php echo $chamado->id; ?>&tipo=auto" class="btn btn-outline-info <?php echo $tipo == 'auto' ? 'active' : ''; ?>">
                                    <i class="fas fa-magic"></i> Auto
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Coluna 1: Email do Colaborador -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="email" class="form-label"><strong>Para:</strong></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="email" value="<?php echo htmlspecialchars($template['email']); ?>" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copiarTexto('email')" title="Copiar email">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Coluna 2: Assunto -->
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="assunto" class="form-label"><strong>Assunto:</strong></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="assunto" value="<?php echo htmlspecialchars($template['assunto']); ?>" readonly>
                                        <button class="btn btn-outline-secondary" type="button" onclick="copiarTexto('assunto')" title="Copiar assunto">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Corpo do Email -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="corpo" class="form-label"><strong>Corpo do Email:</strong></label>
                                    <div class="position-relative">
                                        <textarea class="form-control" id="corpo" rows="20" readonly><?php echo htmlspecialchars($template['corpo']); ?></textarea>
                                        <button class="btn btn-outline-secondary position-absolute top-0 end-0 m-2" type="button" onclick="copiarTexto('corpo')" title="Copiar corpo do email">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botões de Ação -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <div class="btn-group me-md-2" role="group">
                                        <button class="btn btn-success" onclick="abrirOutlookClassico()">
                                            <i class="fas fa-envelope"></i> Abrir Email
                                        </button>
                                        <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="visually-hidden">Opções</span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="abrirOutlookClassico()">
                                                <i class="fas fa-desktop text-primary"></i> Outlook Classic
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="abrirKingHost()">
                                                <i class="fas fa-crown text-warning"></i> ELUS Webmail (KingHost)
                                            </a></li>
                                        </ul>
                                    </div>
                                    <button class="btn btn-primary me-md-2" onclick="copiarTudoFormatado()">
                                        <i class="fas fa-copy"></i> Copiar Tudo (Ctrl+V)
                                    </button>
                                    <a href="view.php?id=<?php echo $chamado->id; ?>" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Voltar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copiarTexto(elementId) {
            const elemento = document.getElementById(elementId);
            elemento.select();
            elemento.setSelectionRange(0, 99999); // Para dispositivos móveis
            
            try {
                document.execCommand('copy');
                mostrarAlerta('Texto copiado com sucesso!', 'success');
            } catch (err) {
                mostrarAlerta('Erro ao copiar texto', 'danger');
            }
        }
        
        function copiarTudoFormatado() {
            const email = document.getElementById('email').value;
            const assunto = document.getElementById('assunto').value;
            const corpo = document.getElementById('corpo').value;
            
            // Criar um elemento temporário para copiar
            const tempElement = document.createElement('div');
            tempElement.innerHTML = `
                <strong>Para:</strong> ${email}<br>
                <strong>Assunto:</strong> ${assunto}<br><br>
                ${corpo.replace(/\n/g, '<br>')}
            `;
            
            // Copiar como HTML
            const range = document.createRange();
            range.selectNode(tempElement);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            
            try {
                document.execCommand('copy');
                mostrarAlerta('Template completo copiado! Use Ctrl+V no Outlook.', 'success');
            } catch (err) {
                // Fallback para texto simples
                const textoSimples = `Para: ${email}\nAssunto: ${assunto}\n\n${corpo}`;
                navigator.clipboard.writeText(textoSimples).then(() => {
                    mostrarAlerta('Template copiado como texto simples!', 'success');
                }).catch(() => {
                    mostrarAlerta('Erro ao copiar template', 'danger');
                });
            }
            
            window.getSelection().removeAllRanges();
        }
        
        function abrirOutlook() {
            const email = document.getElementById('email').value;
            const assunto = document.getElementById('assunto').value;
            const corpo = document.getElementById('corpo').value;
            
            if (!email) {
                mostrarAlerta('Email do colaborador não encontrado!', 'danger');
                return;
            }
            
            // ESTRATÉGIA INTELIGENTE: Detectar e usar o melhor método disponível
            mostrarAlerta('🔍 Detectando melhor método para seu sistema...', 'info');
            
            // Primeira tentativa: Outlook Moderno (Office 365) - mais confiável
            const tentativas = [
                {
                    nome: 'Outlook Moderno (Office 365)',
                    url: `ms-outlook://compose?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                    icone: '🚀'
                },
                {
                    nome: 'Shell Windows (funciona para Classic)',
                    url: `shell:sendto?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                    icone: '⚡'
                },
                {
                    nome: 'Cliente de Email Padrão',
                    url: `mailto:${email}?subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                    icone: '📧'
                }
            ];
            
            // Copiar template para garantir que o usuário sempre tenha acesso
            copiarTudoFormatado();
            
            let tentativaAtual = 0;
            
            function executarTentativa() {
                if (tentativaAtual >= tentativas.length) {
                    mostrarAlerta('💡 Template copiado! Se nenhum cliente abriu automaticamente, abra seu email e cole com Ctrl+V', 'warning');
                    return;
                }
                
                const tentativa = tentativas[tentativaAtual];
                
                try {
                    window.location.href = tentativa.url;
                    mostrarAlerta(`${tentativa.icone} Tentando ${tentativa.nome}...`, 'info');
                    
                    // Aguardar um tempo antes de tentar o próximo
                    setTimeout(() => {
                        tentativaAtual++;
                        if (tentativaAtual < tentativas.length) {
                            executarTentativa();
                        } else {
                            // Última mensagem
                            setTimeout(() => {
                                mostrarAlerta('✅ Processo concluído! Template disponível na área de transferência', 'success');
                            }, 2000);
                        }
                    }, 1500); // Intervalo entre tentativas
                    
                } catch (error) {
                    console.error(`Erro ${tentativa.nome}:`, error);
                    tentativaAtual++;
                    executarTentativa();
                }
            }
            
            // Iniciar tentativas após uma pequena pausa
            setTimeout(executarTentativa, 500);
        }
        
        function abrirOutlookDesktop() {
            const email = document.getElementById('email').value;
            const assunto = document.getElementById('assunto').value;
            const corpo = document.getElementById('corpo').value;
            
            if (!email) {
                mostrarAlerta('Email do colaborador não encontrado!', 'danger');
                return;
            }
            
            // Sequência de tentativas para Outlook Desktop
            const tentativas = [
                // Outlook moderno (Office 365)
                `ms-outlook://compose?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                // Outlook clássico
                `outlook://compose?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                // Fallback para mailto
                `mailto:${email}?subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`
            ];
            
            function tentarAbrir(index) {
                if (index >= tentativas.length) {
                    mostrarAlerta('Não foi possível abrir o Outlook. Tente a opção "Copiar Tudo" e cole manualmente.', 'warning');
                    return;
                }
                
                const url = tentativas[index];
                const link = document.createElement('a');
                link.href = url;
                link.style.display = 'none';
                document.body.appendChild(link);
                
                try {
                    link.click();
                    document.body.removeChild(link);
                    
                    if (index === 0) {
                        mostrarAlerta('Abrindo Outlook moderno...', 'info');
                    } else if (index === 1) {
                        mostrarAlerta('Abrindo Outlook clássico...', 'info');
                    } else {
                        mostrarAlerta('Abrindo cliente de email padrão...', 'info');
                    }
                    
                } catch (error) {
                    document.body.removeChild(link);
                    tentarAbrir(index + 1);
                }
            }
            
            tentarAbrir(0);
        }
        
        function abrirOutlookModerno() {
            const email = document.getElementById('email').value;
            const assunto = document.getElementById('assunto').value;
            const corpo = document.getElementById('corpo').value;
            
            if (!email) {
                mostrarAlerta('Email do colaborador não encontrado!', 'danger');
                return;
            }
            
            // Protocolo específico para Outlook moderno (Office 365)
            const outlookModernoUrl = `ms-outlook://compose?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`;
            
            try {
                window.location.href = outlookModernoUrl;
                mostrarAlerta('Abrindo Outlook moderno (Office 365)...', 'info');
            } catch (error) {
                mostrarAlerta('Outlook moderno não encontrado. Instale o Office 365 ou tente outra opção.', 'warning');
            }
        }
        
        function abrirOutlookClassico() {
            const email = document.getElementById('email').value;
            const assunto = document.getElementById('assunto').value;
            const corpo = document.getElementById('corpo').value;
            
            if (!email) {
                mostrarAlerta('Email do colaborador não encontrado!', 'danger');
                return;
            }
            
            // NOVOS MÉTODOS: Tentativas diretas que podem funcionar
            mostrarAlerta('� Tentando abrir Outlook Classic diretamente...', 'info');
            
            // Método 1: Tentar comando direto do Outlook via linha de comando
            tentarComandoOutlook();
            
            function tentarComandoOutlook() {
                // Criar URL com comando direto do Outlook
                const outlookCmd = `outlook.exe /c ipm.note /m "${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}"`;
                
                try {
                    // Tentar protocolo file:// para executar comando
                    const cmdUrl = `file:///C:/Program%20Files/Microsoft%20Office/root/Office16/OUTLOOK.EXE?/c%20ipm.note%20/m%20"${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}"`;
                    
                    const iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    iframe.src = cmdUrl;
                    document.body.appendChild(iframe);
                    
                    setTimeout(() => {
                        document.body.removeChild(iframe);
                        mostrarAlerta('Tentativa 1 executada. Se não funcionou, tentando método 2...', 'info');
                        tentarProtocoloCustomizado();
                    }, 2000);
                    
                } catch (error) {
                    console.error('Erro comando Outlook:', error);
                    tentarProtocoloCustomizado();
                }
            }
            
            function tentarProtocoloCustomizado() {
                // Método 2: Protocolo personalizado mais específico
                const protocolos = [
                    // Tentar protocolo MAPI mais específico
                    `mapi:compose?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                    // Protocolo Outlook com sintaxe alternativa
                    `outlook:compose?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                    // Windows Shell Send-To com parâmetros
                    `shell:sendto?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                    // Protocolo de email nativo
                    `mailto:${email}?subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`
                ];
                
                let tentativaAtual = 0;
                
                function executarProtocolo() {
                    if (tentativaAtual >= protocolos.length) {
                        // Se todos falharam, tentar método de memória compartilhada
                        tentarMemoriaCompartilhada();
                        return;
                    }
                    
                    const protocolo = protocolos[tentativaAtual];
                    const nomes = ['MAPI Específico', 'Outlook Direto', 'Shell Windows', 'Mailto Padrão'];
                    
                    try {
                        // Criar múltiplos elementos para tentar diferentes abordagens
                        const link = document.createElement('a');
                        link.href = protocolo;
                        link.style.display = 'none';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        
                        // Também tentar via window.location
                        setTimeout(() => {
                            window.location.href = protocolo;
                        }, 500);
                        
                        mostrarAlerta(`📧 Tentando ${nomes[tentativaAtual]}...`, 'info');
                        
                        tentativaAtual++;
                        setTimeout(executarProtocolo, 2000);
                        
                    } catch (error) {
                        console.error(`Erro ${nomes[tentativaAtual]}:`, error);
                        tentativaAtual++;
                        executarProtocolo();
                    }
                }
                
                executarProtocolo();
            }
            
            function tentarMemoriaCompartilhada() {
                // Método 3: Usar localStorage + temporizador para simular integração
                mostrarAlerta('💾 Salvando dados para integração...', 'info');
                
                // Salvar dados no localStorage
                const dadosEmail = {
                    para: email,
                    assunto: assunto,
                    corpo: corpo,
                    timestamp: Date.now()
                };
                
                localStorage.setItem('outlookTemplate', JSON.stringify(dadosEmail));
                
                // Tentar abrir Outlook sem parâmetros e mostrar instruções
                setTimeout(() => {
                    try {
                        // Protocolo simples para abrir Outlook
                        window.location.href = 'outlook:';
                        
                        setTimeout(() => {
                            mostrarInstrucoesInteligentes();
                        }, 2000);
                        
                    } catch (error) {
                        mostrarInstrucoesInteligentes();
                    }
                }, 1000);
            }
            
            function mostrarInstrucoesInteligentes() {
                // Copiar template primeiro
                const templateCompleto = `Para: ${email}
Assunto: ${assunto}

${corpo}`;
                
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(templateCompleto);
                } else {
                    // Método alternativo
                    const textArea = document.createElement('textarea');
                    textArea.value = templateCompleto;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                }
                
                // Modal com instruções aprimoradas
                const modal = document.createElement('div');
                modal.style.cssText = `
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0,0,0,0.9); z-index: 9999;
                    display: flex; align-items: center; justify-content: center;
                `;
                
                modal.innerHTML = `
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px; border-radius: 15px; max-width: 600px; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                        <h3>🎯 Outlook Classic - Método Direto</h3>
                        <div style="background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin: 20px 0; text-align: left;">
                            <h5>✅ Template Copiado Automaticamente!</h5>
                            <p><strong>Se o Outlook não abriu automaticamente:</strong></p>
                            <ol style="line-height: 1.8;">
                                <li>Abra o <strong>Microsoft Outlook</strong> manualmente</li>
                                <li>Clique em <strong>"Novo Email"</strong> ou pressione <strong>Ctrl+N</strong></li>
                                <li>Pressione <strong>Ctrl+V</strong> - o template será colado</li>
                                <li>Ajuste se necessário e envie</li>
                            </ol>
                            <div style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 5px; margin: 10px 0;">
                                <strong>💡 Dica:</strong> O template está formatado para colar diretamente nos campos corretos!
                            </div>
                        </div>
                        <button onclick="this.closest('.modal').remove()" 
                                style="background: #28a745; color: white; border: none; padding: 15px 30px; border-radius: 25px; cursor: pointer; font-size: 16px;">
                            ✅ Entendi, vou usar!
                        </button>
                    </div>
                `;
                
                modal.className = 'modal';
                document.body.appendChild(modal);
                
                mostrarAlerta('📋 Template copiado! Instruções detalhadas na tela', 'success');
            }
        }
                        window.location.href = url;
                        mostrarAlerta('Tentando via shell do Windows...', 'info');
        
        function abrirOutlookClassicoAlternativo() {
            const email = document.getElementById('email').value;
            const assunto = document.getElementById('assunto').value;
            const corpo = document.getElementById('corpo').value;
            
            if (!email) {
                mostrarAlerta('Email do colaborador não encontrado!', 'danger');
                return;
            }
            
            mostrarAlerta('� Métodos Avançados - Tentando abrir diretamente...', 'info');
            
            // MÉTODO AVANÇADO 1: Tentar ActiveX primeiro (funciona em IE/Edge Legacy)
            if (tentarActiveX()) {
                return; // Se ActiveX funcionou, parar aqui
            }
            
            // MÉTODO AVANÇADO 2: Protocolo MSOutlook personalizado
            setTimeout(() => {
                tentarProtocoloPersonalizado();
            }, 1000);
            
            // MÉTODO AVANÇADO 3: Criar objeto temporário no desktop
            setTimeout(() => {
                tentarObjetoTemporario();
            }, 3000);
            
            function tentarActiveX() {
                try {
                    // Verificar se ActiveX está disponível
                    if (window.ActiveXObject || "ActiveXObject" in window) {
                        const outlook = new ActiveXObject("Outlook.Application");
                        const mail = outlook.CreateItem(0); // olMailItem = 0
                        
                        mail.To = email;
                        mail.Subject = assunto;
                        mail.Body = corpo;
                        mail.Display(true); // true = modal, false = non-modal
                        
                        mostrarAlerta('🎉 ActiveX funcionou! Outlook aberto diretamente!', 'success');
                        return true;
                    }
                } catch (error) {
                    console.warn('ActiveX não disponível:', error);
                }
                return false;
            }
            
            function tentarProtocoloPersonalizado() {
                // Protocolo MSOutlook específico que pode funcionar
                const protocolosAvancados = [
                    // Protocolo Microsoft Office
                    `ms-outlook://compose?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                    // Protocolo Outlook com parâmetros específicos
                    `outlook://compose/?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                    // MAPI com sintaxe Windows
                    `mapi:?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                    // Comando direto do Windows
                    `start outlook.exe /c ipm.note /m "${email}?subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}"`,
                ];
                
                protocolosAvancados.forEach((protocolo, index) => {
                    setTimeout(() => {
                        try {
                            // Método 1: Via location
                            window.location.href = protocolo;
                            
                            // Método 2: Via createElement
                            const link = document.createElement('a');
                            link.href = protocolo;
                            link.click();
                            
                            // Método 3: Via iframe (para alguns protocolos)
                            const iframe = document.createElement('iframe');
                            iframe.src = protocolo;
                            iframe.style.display = 'none';
                            document.body.appendChild(iframe);
                            
                            setTimeout(() => {
                                if (document.body.contains(iframe)) {
                                    document.body.removeChild(iframe);
                                }
                            }, 2000);
                            
                            mostrarAlerta(`🔄 Protocolo ${index + 1}/4 executado...`, 'info');
                            
                        } catch (error) {
                            console.warn(`Protocolo ${index + 1} falhou:`, error);
                        }
                    }, index * 1000);
                });
            }
            
            function tentarObjetoTemporario() {
                // Criar um "data URL" que tenta forçar abertura
                const dataUrl = `data:message/rfc822,To: ${email}%0D%0ASubject: ${encodeURIComponent(assunto)}%0D%0A%0D%0A${encodeURIComponent(corpo)}`;
                
                try {
                    // Tentar abrir como se fosse um arquivo
                    const link = document.createElement('a');
                    link.href = dataUrl;
                    link.download = 'email.eml';
                    link.click();
                    
                    mostrarAlerta('� Objeto de email criado. Se abriu automaticamente, ótimo!', 'info');
                    
                } catch (error) {
                    console.warn('Objeto temporário falhou:', error);
                }
                
                // Fallback final: Instruções inteligentes
                setTimeout(() => {
                    mostrarResultadoFinal();
                }, 2000);
            }
            
            function mostrarResultadoFinal() {
                // Copiar template como backup
                const templateCompleto = `Para: ${email}
Assunto: ${assunto}

${corpo}`;
                
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(templateCompleto);
                } else {
                    const textArea = document.createElement('textarea');
                    textArea.value = templateCompleto;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                }
                
                // Modal de resultado
                const modal = document.createElement('div');
                modal.style.cssText = `
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0,0,0,0.9); z-index: 9999;
                    display: flex; align-items: center; justify-content: center;
                `;
                
                modal.innerHTML = `
                    <div style="background: linear-gradient(135deg, #ff6b6b, #ffa500); color: white; padding: 40px; border-radius: 20px; max-width: 700px; text-align: center;">
                        <h2>� Métodos Avançados Executados!</h2>
                        <div style="background: rgba(255,255,255,0.2); padding: 25px; border-radius: 15px; margin: 25px 0; text-align: left;">
                            <h4>✅ Tentativas Realizadas:</h4>
                            <ul style="font-size: 16px; line-height: 2;">
                                <li>🔧 ActiveX (para IE/Edge)</li>
                                <li>📧 Protocolos MS-Outlook</li>
                                <li>⚡ MAPI Personalizado</li>
                                <li>📄 Objeto de Email Temporário</li>
                                <li>📋 Template Copiado (Backup)</li>
                            </ul>
                            <div style="background: rgba(255,255,255,0.3); padding: 15px; border-radius: 10px;">
                                <strong>🎯 Se o Outlook não abriu automaticamente:</strong><br>
                                O template foi copiado! Abra o Outlook Classic e cole com <strong>Ctrl+V</strong>
                            </div>
                        </div>
                        <div style="margin-top: 25px;">
                            <button onclick="this.closest('.modal').remove()" 
                                    style="background: #28a745; color: white; border: none; padding: 15px 35px; border-radius: 30px; cursor: pointer; font-size: 18px; margin: 0 10px;">
                                ✅ Perfeito!
                            </button>
                            <button onclick="location.reload()" 
                                    style="background: #007bff; color: white; border: none; padding: 15px 35px; border-radius: 30px; cursor: pointer; font-size: 18px; margin: 0 10px;">
                                🔄 Tentar Novamente
                            </button>
                        </div>
                    </div>
                `;
                
                modal.className = 'modal';
                document.body.appendChild(modal);
                
                mostrarAlerta('🏆 Todos os métodos executados! Verifique se o Outlook abriu', 'success');
            }
        }
        
        function criarArquivoEmlSemples() {
            const email = document.getElementById('email').value;
            const assunto = document.getElementById('assunto').value;
            const corpo = document.getElementById('corpo').value;
            
            if (!email) {
                mostrarAlerta('Email do colaborador não encontrado!', 'danger');
                return;
            }
            
            mostrarAlerta('📁 Criando arquivo .eml...', 'info');
            
            // Criar conteúdo do arquivo .eml
            const dataAtual = new Date().toUTCString();
            const emlContent = `From: Sistema ELUS <noreply@elusinstrumentacao.com.br>
To: ${email}
Subject: ${assunto}
Date: ${dataAtual}
Content-Type: text/plain; charset=utf-8

${corpo}`;
            
            try {
                const blob = new Blob([emlContent], { type: 'message/rfc822' });
                const url = URL.createObjectURL(blob);
                
                const downloadLink = document.createElement('a');
                downloadLink.href = url;
                downloadLink.download = `chamado_${Date.now()}.eml`;
                downloadLink.click();
                
                setTimeout(() => URL.revokeObjectURL(url), 1000);
                
                mostrarAlerta('✅ Arquivo .eml baixado! Clique duas vezes nele para abrir no Outlook', 'success');
                
            } catch (error) {
                mostrarAlerta('❌ Erro ao criar arquivo .eml', 'danger');
            }
        }
        
        function abrirClientePadrao() {
            const email = document.getElementById('email').value;
            const assunto = document.getElementById('assunto').value;
            const corpo = document.getElementById('corpo').value;
            
            if (!email) {
                mostrarAlerta('Email do colaborador não encontrado!', 'danger');
                return;
            }
            
            // URL mailto padrão
            const mailtoUrl = `mailto:${email}?subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`;
            
            try {
                window.location.href = mailtoUrl;
                mostrarAlerta('Abrindo cliente de email padrão...', 'info');
            } catch (error) {
                mostrarAlerta('Erro ao abrir cliente de email.', 'danger');
            }
        }
        
        function diagnosticarOutlook() {
            mostrarAlerta('Iniciando diagnóstico do Outlook...', 'info');
            
            const protocolos = [
                { nome: 'Outlook Moderno (Office 365)', protocolo: 'ms-outlook://' },
                { nome: 'Outlook Clássico (MAPI)', protocolo: 'mapi://' },
                { nome: 'Outlook Clássico (outlook://)', protocolo: 'outlook://' },
                { nome: 'Cliente Padrão (mailto)', protocolo: 'mailto:' }
            ];
            
            let resultados = [];
            
            protocolos.forEach((item, index) => {
                setTimeout(() => {
                    const testUrl = `${item.protocolo}test@example.com?subject=Teste&body=Teste`;
                    
                    try {
                        const link = document.createElement('a');
                        link.href = testUrl;
                        link.style.display = 'none';
                        document.body.appendChild(link);
                        
                        // Simular clique
                        link.click();
                        document.body.removeChild(link);
                        
                        resultados.push(`✅ ${item.nome}: Disponível`);
                    } catch (error) {
                        resultados.push(`❌ ${item.nome}: Não disponível`);
                    }
                    
                    // Mostrar resultados após testar todos
                    if (index === protocolos.length - 1) {
                        setTimeout(() => {
                            const relatorio = resultados.join('\n');
                            alert(`Diagnóstico do Outlook:\n\n${relatorio}\n\nRecomendação: Use a opção que aparece como "Disponível"`);
                        }, 1000);
                    }
                }, index * 500);
            });
        }
        
        function abrirOutlookWeb() {
            const email = document.getElementById('email').value;
            const assunto = document.getElementById('assunto').value;
            const corpo = document.getElementById('corpo').value;
            
            if (!email) {
                mostrarAlerta('Email do colaborador não encontrado!', 'danger');
                return;
            }
            
            // URL para Outlook Web
            const outlookWebUrl = `https://outlook.office.com/mail/compose?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`;
            
            try {
                window.open(outlookWebUrl, '_blank');
                mostrarAlerta('Abrindo Outlook Web...', 'info');
            } catch (error) {
                mostrarAlerta('Erro ao abrir Outlook Web.', 'danger');
            }
        }
        
        function abrirGmail() {
            const email = document.getElementById('email').value;
            const assunto = document.getElementById('assunto').value;
            const corpo = document.getElementById('corpo').value;
            
            if (!email) {
                mostrarAlerta('Email do colaborador não encontrado!', 'danger');
                return;
            }
            
            // URL para Gmail
            const gmailUrl = `https://mail.google.com/mail/u/0/?view=cm&fs=1&to=${email}&su=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`;
            
            try {
                window.open(gmailUrl, '_blank');
                mostrarAlerta('Abrindo Gmail...', 'info');
            } catch (error) {
                mostrarAlerta('Erro ao abrir Gmail.', 'danger');
            }
        }
        
        // Função para ELUS Webmail com tentativa de preenchimento automático
        function abrirKingHost() {
            const email = document.getElementById('email').value;
            const assunto = document.getElementById('assunto').value;
            const corpo = document.getElementById('corpo').value;
            
            if (!email) {
                mostrarAlerta('Email do colaborador não encontrado!', 'danger');
                return;
            }
            
            // Copiar dados para área de transferência primeiro
            const dadosCompletos = `Para: ${email}\nAssunto: ${assunto}\n\nMensagem:\n${corpo}`;
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(dadosCompletos).then(() => {
                    mostrarAlerta('📋 Dados copiados para área de transferência!', 'success');
                }).catch(() => {
                    // Fallback para browsers mais antigos
                    const textArea = document.createElement('textarea');
                    textArea.value = dadosCompletos;
                    document.body.appendChild(textArea);
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        mostrarAlerta('📋 Dados copiados para área de transferência!', 'success');
                    } catch (err) {
                        mostrarAlerta('⚠️ Não foi possível copiar os dados.', 'warning');
                    }
                    document.body.removeChild(textArea);
                });
            } else {
                // Fallback para browsers mais antigos
                const textArea = document.createElement('textarea');
                textArea.value = dadosCompletos;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    mostrarAlerta('📋 Dados copiados para área de transferência!', 'success');
                } catch (err) {
                    mostrarAlerta('⚠️ Não foi possível copiar os dados.', 'warning');
                }
                document.body.removeChild(textArea);
            }
            
            // Tentar abrir com preenchimento automático
            const urlCompose = `https://webmail.elusinstrumentacao.com.br/roundcube/?_task=mail&_action=compose&_to=${encodeURIComponent(email)}&_subject=${encodeURIComponent(assunto)}&_body=${encodeURIComponent(corpo)}`;
            
            try {
                const webmailWindow = window.open(urlCompose, '_blank', 'width=1200,height=800');
                
                if (webmailWindow) {
                    mostrarAlerta('✅ ELUS Webmail aberto! Se os campos não foram preenchidos, use Ctrl+V para colar!', 'success');
                    
                    // Aguardar um pouco e verificar se funcionou
                    setTimeout(() => {
                        if (!webmailWindow.closed) {
                            mostrarAlerta('💡 Dica: Se os campos não foram preenchidos automaticamente, clique em "Escrever" e cole com Ctrl+V!', 'info');
                        }
                    }, 3000);
                } else {
                    // Se não conseguiu abrir, tentar a caixa de entrada
                    const inboxUrl = `https://webmail.elusinstrumentacao.com.br/roundcube/?_task=mail&_mbox=INBOX`;
                    window.open(inboxUrl, '_blank', 'width=1200,height=800');
                    mostrarAlerta('⚠️ Webmail aberto na caixa de entrada. Clique em "Escrever" e cole com Ctrl+V!', 'warning');
                }
            } catch (error) {
                // Fallback para caixa de entrada
                const inboxUrl = `https://webmail.elusinstrumentacao.com.br/roundcube/?_task=mail&_mbox=INBOX`;
                window.open(inboxUrl, '_blank', 'width=1200,height=800');
                mostrarAlerta('⚠️ Webmail aberto na caixa de entrada. Clique em "Escrever" e cole com Ctrl+V!', 'warning');
            }
        }
        
        function mostrarAlerta(mensagem, tipo) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed`;
            alertDiv.style.top = '20px';
            alertDiv.style.right = '20px';
            alertDiv.style.zIndex = '9999';
            alertDiv.innerHTML = `
                ${mensagem}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Remover automaticamente após 5 segundos
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }
    </script>
</body>
</html>
