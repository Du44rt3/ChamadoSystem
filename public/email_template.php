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
                                        <button class="btn btn-success" onclick="abrirOutlook()">
                                            <i class="fas fa-external-link-alt"></i> Abrir no Outlook
                                        </button>
                                        <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="visually-hidden">Opções</span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="abrirOutlookModerno()">
                                                <i class="fas fa-rocket text-success"></i> Outlook Moderno (Office 365) <span class="badge bg-success">Recomendado</span>
                                            </a></li>
                                            <li><a class="dropdown-item text-muted" href="#" onclick="abrirOutlookClassico()">
                                                <i class="fas fa-desktop text-secondary"></i> Outlook Clássico (MAPI) <span class="badge bg-warning">Redireciona</span>
                                            </a></li>
                                            <li><a class="dropdown-item text-muted" href="#" onclick="abrirOutlookClassicoAlternativo()">
                                                <i class="fas fa-cog text-secondary"></i> Outlook Clássico (Alternativo) <span class="badge bg-warning">Redireciona</span>
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#" onclick="abrirOutlookWeb()">
                                                <i class="fas fa-globe text-info"></i> Outlook Web
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="abrirGmail()">
                                                <i class="fas fa-envelope text-danger"></i> Gmail
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="abrirKingHost()">
                                                <i class="fas fa-crown text-warning"></i> ELUS Webmail
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="#" onclick="abrirClientePadrao()">
                                                <i class="fas fa-mail-bulk"></i> Cliente de Email Padrão
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="diagnostico_outlook.php" target="_blank">
                                                <i class="fas fa-stethoscope text-warning"></i> Diagnosticar Outlook
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
            
            // Verificar se há email
            if (!email) {
                mostrarAlerta('Email do colaborador não encontrado!', 'danger');
                return;
            }
            
            // Sequência de tentativas: Outlook moderno primeiro, depois clássico, depois padrão
            const tentativas = [
                {
                    url: `ms-outlook://compose?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                    nome: 'Outlook Moderno (Office 365)'
                },
                {
                    url: `mailto:${email}?subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                    nome: 'Cliente de Email Padrão'
                }
            ];
            
            function tentarSequencia(index) {
                if (index >= tentativas.length) {
                    mostrarAlerta('Não foi possível abrir nenhum cliente de email. Use a opção "Copiar Tudo".', 'warning');
                    return;
                }
                
                const tentativa = tentativas[index];
                
                try {
                    window.location.href = tentativa.url;
                    mostrarAlerta(`Tentando abrir ${tentativa.nome}...`, 'info');
                    
                    // Se chegou no último item (mailto), não precisa de fallback
                    if (index === tentativas.length - 1) {
                        return;
                    }
                    
                    // Aguardar um pouco e tentar a próxima opção se esta não funcionar
                    setTimeout(() => {
                        tentarSequencia(index + 1);
                    }, 2000);
                    
                } catch (error) {
                    tentarSequencia(index + 1);
                }
            }
            
            tentarSequencia(0);
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
            
            // ATUALIZADO: Priorizar Outlook Moderno (funciona melhor no sistema atual)
            // Como o clássico não funciona, vamos redirecionar para o moderno
            mostrarAlerta('Redirecionando para Outlook Moderno (recomendado)...', 'info');
            
            setTimeout(() => {
                abrirOutlookModerno();
            }, 1000);
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
            
            // Método alternativo: criar um iframe invisível para tentar diferentes protocolos
            const iframeContainer = document.createElement('div');
            iframeContainer.style.display = 'none';
            document.body.appendChild(iframeContainer);
            
            const tentativas = [
                // Diferentes sintaxes para Outlook clássico
                `outlook.exe /c ipm.note /m "${email}?subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}"`,
                `outlook:compose?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                `ms-outlook:compose?to=${email}&subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`,
                `mailto:${email}?subject=${encodeURIComponent(assunto)}&body=${encodeURIComponent(corpo)}`
            ];
            
            function tentarIframe(index) {
                if (index >= tentativas.length) {
                    document.body.removeChild(iframeContainer);
                    mostrarAlerta('Não foi possível abrir o Outlook clássico. Tente a opção "Cliente de Email Padrão".', 'warning');
                    return;
                }
                
                const iframe = document.createElement('iframe');
                iframe.style.display = 'none';
                iframe.src = tentativas[index];
                iframeContainer.appendChild(iframe);
                
                mostrarAlerta(`Tentativa ${index + 1} de ${tentativas.length} para Outlook clássico...`, 'info');
                
                setTimeout(() => {
                    iframeContainer.removeChild(iframe);
                    if (index === tentativas.length - 1) {
                        // Última tentativa (mailto)
                        window.location.href = tentativas[index];
                        document.body.removeChild(iframeContainer);
                        mostrarAlerta('Abrindo cliente de email padrão...', 'info');
                    } else {
                        tentarIframe(index + 1);
                    }
                }, 1000);
            }
            
            tentarIframe(0);
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
