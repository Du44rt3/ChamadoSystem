<?php
require_once '../config/config.php';
require_once '../src/DB.php';
require_once '../src/Chamado.php';
require_once '../src/EmailTemplate.php';

$db = new DB();
$conn = $db->getConnection();
$chamado = new Chamado($conn);
$emailTemplate = new EmailTemplate($conn);

// Buscar um chamado para teste
$stmt = $conn->prepare("SELECT * FROM chamados LIMIT 1");
$stmt->execute();
$chamado_teste = $stmt->fetch(PDO::FETCH_OBJ);

if (!$chamado_teste) {
    // Criar um chamado de exemplo se não houver nenhum
    $chamado_teste = (object) [
        'id' => 1,
        'codigo_chamado' => 'CH001',
        'nome_colaborador' => 'João Silva',
        'email' => 'joao.silva@exemplo.com',
        'setor' => 'Administrativo',
        'descricao_problema' => 'Problema de teste para diagnóstico',
        'gravidade' => 'media',
        'data_abertura' => date('Y-m-d H:i:s'),
        'solucao' => null
    ];
}

$template = $emailTemplate->templateAbertura($chamado_teste);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnóstico Outlook - Sistema de Chamados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .diagnostic-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        .test-result {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .test-success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .test-warning {
            background: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }
        .test-error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .test-info {
            background: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
        .protocol-test {
            margin: 10px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
        }
        .system-info {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            background: #f1f1f1;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-stethoscope me-2"></i>Diagnóstico Outlook</h2>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>

                <!-- Informações do Sistema -->
                <div class="diagnostic-card">
                    <h4><i class="fas fa-info-circle me-2"></i>Informações do Sistema</h4>
                    <div class="system-info" id="systemInfo">
                        <div>Carregando informações do sistema...</div>
                    </div>
                </div>

                <!-- Status dos Testes -->
                <div class="diagnostic-card">
                    <h4><i class="fas fa-clipboard-check me-2"></i>Status dos Testes</h4>
                    <div id="testStatus" class="test-result test-info">
                        <i class="fas fa-spinner fa-spin me-2"></i>Preparando testes...
                    </div>
                </div>

                <!-- Testes de Protocolo -->
                <div class="diagnostic-card">
                    <h4><i class="fas fa-vial me-2"></i>Testes de Protocolo Outlook</h4>
                    <p class="text-muted">Clique em cada teste para verificar qual funciona no seu sistema:</p>
                    
                    <div class="protocol-test">
                        <h5>1. Outlook Moderno (Recomendado)</h5>
                        <p>Protocolo: <code>ms-outlook://</code></p>
                        <button class="btn btn-primary" onclick="testarProtocolo('ms-outlook', 1)">
                            <i class="fas fa-play"></i> Testar Outlook Moderno
                        </button>
                        <div id="result-1" class="test-result" style="display: none;"></div>
                    </div>

                    <div class="protocol-test">
                        <h5>2. Outlook Clássico - MAPI</h5>
                        <p>Protocolo: <code>outlook://</code></p>
                        <button class="btn btn-primary" onclick="testarProtocolo('outlook', 2)">
                            <i class="fas fa-play"></i> Testar MAPI
                        </button>
                        <div id="result-2" class="test-result" style="display: none;"></div>
                    </div>

                    <div class="protocol-test">
                        <h5>3. Outlook Clássico - Shell</h5>
                        <p>Protocolo: <code>shell:sendto</code></p>
                        <button class="btn btn-primary" onclick="testarProtocolo('shell', 3)">
                            <i class="fas fa-play"></i> Testar Shell
                        </button>
                        <div id="result-3" class="test-result" style="display: none;"></div>
                    </div>

                    <div class="protocol-test">
                        <h5>6. Outlook Clássico - Alternativo</h5>
                        <p>Método: <code>Execução via JavaScript avançado</code></p>
                        <button class="btn btn-warning" onclick="testarProtocolo('alternativo', 6)">
                            <i class="fas fa-wrench"></i> Testar Método Alternativo
                        </button>
                        <div id="result-6" class="test-result" style="display: none;"></div>
                    </div>

                    <div class="protocol-test">
                        <h5>7. Outlook Clássico - Força Bruta</h5>
                        <p>Método: <code>Tentativas múltiplas simultâneas</code></p>
                        <button class="btn btn-danger" onclick="testarProtocolo('forcabruta', 7)">
                            <i class="fas fa-hammer"></i> Testar Força Bruta
                        </button>
                        <div id="result-7" class="test-result" style="display: none;"></div>
                    </div>

                    <div class="protocol-test">
                        <h5>4. Outlook Web (Navegador)</h5>
                        <p>Protocolo: <code>https://outlook.live.com/</code></p>
                        <button class="btn btn-primary" onclick="testarProtocolo('web', 4)">
                            <i class="fas fa-play"></i> Testar Outlook Web
                        </button>
                        <div id="result-4" class="test-result" style="display: none;"></div>
                    </div>

                    <div class="protocol-test">
                        <h5>5. Cliente Padrão (Mailto)</h5>
                        <p>Protocolo: <code>mailto:</code></p>
                        <button class="btn btn-primary" onclick="testarProtocolo('mailto', 5)">
                            <i class="fas fa-play"></i> Testar Mailto
                        </button>
                        <div id="result-5" class="test-result" style="display: none;"></div>
                    </div>

                    <div class="protocol-test">
                        <h5>8. ELUS Webmail</h5>
                        <p>Protocolo: <code>https://webmail.elusinstrumentacao.com.br/roundcube/</code></p>
                        <button class="btn btn-warning" onclick="testarProtocolo('kinghost', 8)">
                            <i class="fas fa-crown"></i> Testar ELUS Webmail
                        </button>
                        <div id="result-8" class="test-result" style="display: none;"></div>
                    </div>
                </div>

                <!-- Resultados e Recomendações -->
                <div class="diagnostic-card">
                    <h4><i class="fas fa-chart-line me-2"></i>Resultados e Recomendações</h4>
                    <div id="recommendations" class="test-result test-info">
                        <i class="fas fa-info-circle me-2"></i>Execute os testes acima para receber recomendações personalizadas.
                    </div>
                </div>

                <!-- Dados de Teste -->
                <div class="diagnostic-card">
                    <h4><i class="fas fa-envelope me-2"></i>Dados de Teste</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Para:</strong> <?php echo htmlspecialchars($template['email']); ?><br>
                            <strong>Assunto:</strong> <?php echo htmlspecialchars($template['assunto']); ?>
                        </div>
                        <div class="col-md-6">
                            <strong>Código do Chamado:</strong> <?php echo htmlspecialchars($chamado_teste->codigo_chamado); ?><br>
                            <strong>Colaborador:</strong> <?php echo htmlspecialchars($chamado_teste->nome_colaborador); ?>
                        </div>
                    </div>
                    <div class="mt-3">
                        <strong>Corpo do Email:</strong>
                        <pre class="system-info"><?php echo htmlspecialchars($template['corpo']); ?></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dados do template para teste
        const templateData = {
            email: <?php echo json_encode($template['email']); ?>,
            assunto: <?php echo json_encode($template['assunto']); ?>,
            corpo: <?php echo json_encode($template['corpo']); ?>
        };

        let testResults = {};
        let testCount = 0;

        // Carregar informações do sistema
        function carregarInfoSistema() {
            const info = document.getElementById('systemInfo');
            const userAgent = navigator.userAgent;
            const platform = navigator.platform;
            const language = navigator.language;
            
            let sistemaInfo = `
Navegador: ${navigator.appName} ${navigator.appVersion}
Plataforma: ${platform}
Idioma: ${language}
User Agent: ${userAgent}
Suporte a Protocolos: ${navigator.registerProtocolHandler ? 'Sim' : 'Não'}
Cookies Habilitados: ${navigator.cookieEnabled ? 'Sim' : 'Não'}
`;

            // Verificar se é Windows
            if (platform.includes('Win')) {
                sistemaInfo += `
Sistema: Windows detectado
Outlook Desktop: Provavelmente instalado
Protocolos Recomendados: ms-outlook://, outlook://
`;
            }

            info.innerHTML = `<pre>${sistemaInfo}</pre>`;
        }

        // Testar protocolo específico
        function testarProtocolo(tipo, numero) {
            const resultDiv = document.getElementById(`result-${numero}`);
            const statusDiv = document.getElementById('testStatus');
            
            resultDiv.style.display = 'block';
            resultDiv.className = 'test-result test-warning';
            resultDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testando protocolo...';
            
            statusDiv.className = 'test-result test-info';
            statusDiv.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>Testando ${tipo}...`;

            let url = '';
            let metodo = '';
            
            try {
                switch(tipo) {
                    case 'ms-outlook':
                        url = `ms-outlook://compose?to=${encodeURIComponent(templateData.email)}&subject=${encodeURIComponent(templateData.assunto)}&body=${encodeURIComponent(templateData.corpo)}`;
                        metodo = 'window.location.href';
                        break;
                        
                    case 'outlook':
                        url = `outlook://compose?to=${encodeURIComponent(templateData.email)}&subject=${encodeURIComponent(templateData.assunto)}&body=${encodeURIComponent(templateData.corpo)}`;
                        metodo = 'window.location.href';
                        break;
                        
                    case 'shell':
                        url = `shell:sendto?to=${encodeURIComponent(templateData.email)}&subject=${encodeURIComponent(templateData.assunto)}&body=${encodeURIComponent(templateData.corpo)}`;
                        metodo = 'window.location.href';
                        break;
                        
                    case 'web':
                        url = `https://outlook.live.com/mail/0/deeplink/compose?to=${encodeURIComponent(templateData.email)}&subject=${encodeURIComponent(templateData.assunto)}&body=${encodeURIComponent(templateData.corpo)}`;
                        metodo = 'window.open';
                        break;
                        
                    case 'mailto':
                        url = `mailto:${encodeURIComponent(templateData.email)}?subject=${encodeURIComponent(templateData.assunto)}&body=${encodeURIComponent(templateData.corpo)}`;
                        metodo = 'window.location.href';
                        break;
                        
                    case 'kinghost':
                        url = `https://webmail.elusinstrumentacao.com.br/roundcube/?_task=mail&_mbox=INBOX`;
                        metodo = 'window.open';
                        break;
                        
                    case 'alternativo':
                        // Método alternativo para Outlook Clássico
                        resultDiv.className = 'test-result test-info';
                        resultDiv.innerHTML = '<i class="fas fa-cog fa-spin me-2"></i>Tentando método alternativo...';
                        
                        setTimeout(() => {
                            try {
                                // Criar um elemento temporário para testar
                                const tempLink = document.createElement('a');
                                tempLink.href = `mailto:${encodeURIComponent(templateData.email)}?subject=${encodeURIComponent(templateData.assunto)}&body=${encodeURIComponent(templateData.corpo)}`;
                                tempLink.style.display = 'none';
                                document.body.appendChild(tempLink);
                                
                                // Tentar diferentes métodos
                                if (window.external && window.external.msLaunchUri) {
                                    window.external.msLaunchUri(`outlook://compose?to=${encodeURIComponent(templateData.email)}&subject=${encodeURIComponent(templateData.assunto)}&body=${encodeURIComponent(templateData.corpo)}`);
                                } else if (navigator.msLaunchUri) {
                                    navigator.msLaunchUri(`outlook://compose?to=${encodeURIComponent(templateData.email)}&subject=${encodeURIComponent(templateData.assunto)}&body=${encodeURIComponent(templateData.corpo)}`);
                                } else {
                                    // Fallback para click simulado
                                    tempLink.click();
                                }
                                
                                document.body.removeChild(tempLink);
                                
                                resultDiv.innerHTML = `
                                    <div><i class="fas fa-info-circle me-2"></i>Método alternativo executado!</div>
                                    <div><strong>Técnica:</strong> MS Launch URI + Fallback</div>
                                    <div class="mt-2">
                                        <strong>O Outlook abriu corretamente?</strong><br>
                                        <button class="btn btn-sm btn-success me-2" onclick="marcarResultado(${numero}, true, '${tipo}')">
                                            <i class="fas fa-check"></i> Sim, funcionou!
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="marcarResultado(${numero}, false, '${tipo}')">
                                            <i class="fas fa-times"></i> Não funcionou
                                        </button>
                                    </div>
                                `;
                            } catch (error) {
                                resultDiv.className = 'test-result test-error';
                                resultDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Erro no método alternativo: ${error.message}`;
                            }
                        }, 1000);
                        return;
                        
                    case 'forcabruta':
                        // Método força bruta - tenta todos os protocolos
                        resultDiv.className = 'test-result test-warning';
                        resultDiv.innerHTML = '<i class="fas fa-hammer me-2"></i>Executando força bruta...';
                        
                        setTimeout(() => {
                            const protocolos = [
                                `outlook://compose?to=${encodeURIComponent(templateData.email)}&subject=${encodeURIComponent(templateData.assunto)}&body=${encodeURIComponent(templateData.corpo)}`,
                                `mapi://compose?to=${encodeURIComponent(templateData.email)}&subject=${encodeURIComponent(templateData.assunto)}&body=${encodeURIComponent(templateData.corpo)}`,
                                `shell:sendto?to=${encodeURIComponent(templateData.email)}&subject=${encodeURIComponent(templateData.assunto)}&body=${encodeURIComponent(templateData.corpo)}`,
                                `mailto:${encodeURIComponent(templateData.email)}?subject=${encodeURIComponent(templateData.assunto)}&body=${encodeURIComponent(templateData.corpo)}`
                            ];
                            
                            let tentativas = 0;
                            protocolos.forEach((protocolo, index) => {
                                setTimeout(() => {
                                    try {
                                        window.location.href = protocolo;
                                        tentativas++;
                                    } catch (error) {
                                        console.log(`Protocolo ${index + 1} falhou:`, error);
                                    }
                                }, index * 500);
                            });
                            
                            setTimeout(() => {
                                resultDiv.innerHTML = `
                                    <div><i class="fas fa-info-circle me-2"></i>Força bruta executada!</div>
                                    <div><strong>Tentativas:</strong> ${tentativas} protocolos testados</div>
                                    <div><strong>Protocolos:</strong> outlook://, mapi://, shell:, mailto:</div>
                                    <div class="mt-2">
                                        <strong>Algum Outlook abriu?</strong><br>
                                        <button class="btn btn-sm btn-success me-2" onclick="marcarResultado(${numero}, true, '${tipo}')">
                                            <i class="fas fa-check"></i> Sim, funcionou!
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="marcarResultado(${numero}, false, '${tipo}')">
                                            <i class="fas fa-times"></i> Não funcionou
                                        </button>
                                    </div>
                                `;
                            }, 3000);
                        }, 1000);
                        return;
                }

                // Tentar abrir
                const startTime = Date.now();
                
                if (metodo === 'window.open') {
                    window.open(url, '_blank');
                } else {
                    window.location.href = url;
                }

                // Simular resultado (o usuário precisa confirmar se funcionou)
                setTimeout(() => {
                    const endTime = Date.now();
                    const duration = endTime - startTime;
                    
                    resultDiv.className = 'test-result test-info';
                    resultDiv.innerHTML = `
                        <div><i class="fas fa-info-circle me-2"></i>Protocolo testado!</div>
                        <div><strong>URL:</strong> <code>${url.substring(0, 100)}...</code></div>
                        <div><strong>Tempo:</strong> ${duration}ms</div>
                        <div class="mt-2">
                            <strong>O Outlook abriu corretamente?</strong><br>
                            <button class="btn btn-sm btn-success me-2" onclick="marcarResultado(${numero}, true, '${tipo}')">
                                <i class="fas fa-check"></i> Sim, funcionou!
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="marcarResultado(${numero}, false, '${tipo}')">
                                <i class="fas fa-times"></i> Não funcionou
                            </button>
                        </div>
                    `;
                    
                    statusDiv.className = 'test-result test-info';
                    statusDiv.innerHTML = `<i class="fas fa-clock me-2"></i>Aguardando confirmação do usuário...`;
                }, 1000);
                
            } catch (error) {
                resultDiv.className = 'test-result test-error';
                resultDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i>Erro: ${error.message}`;
                
                testResults[numero] = {
                    tipo: tipo,
                    sucesso: false,
                    erro: error.message
                };
                
                atualizarRecomendacoes();
            }
        }

        // Marcar resultado do teste
        function marcarResultado(numero, sucesso, tipo) {
            const resultDiv = document.getElementById(`result-${numero}`);
            
            testResults[numero] = {
                tipo: tipo,
                sucesso: sucesso,
                testado: true
            };
            
            if (sucesso) {
                resultDiv.className = 'test-result test-success';
                resultDiv.innerHTML = `<i class="fas fa-check-circle me-2"></i>✅ Protocolo <strong>${tipo}</strong> funcionou perfeitamente!`;
            } else {
                resultDiv.className = 'test-result test-error';
                resultDiv.innerHTML = `<i class="fas fa-times-circle me-2"></i>❌ Protocolo <strong>${tipo}</strong> não funcionou.`;
            }
            
            testCount++;
            atualizarRecomendacoes();
        }

        // Atualizar recomendações
        function atualizarRecomendacoes() {
            const recomDiv = document.getElementById('recommendations');
            const statusDiv = document.getElementById('testStatus');
            
            if (testCount === 0) {
                return;
            }
            
            const protocolosFuncionando = Object.values(testResults).filter(r => r.sucesso);
            const protocolosTestados = Object.values(testResults).filter(r => r.testado);
            
            if (protocolosFuncionando.length > 0) {
                const melhorProtocolo = protocolosFuncionando[0];
                
                recomDiv.className = 'test-result test-success';
                recomDiv.innerHTML = `
                    <div><i class="fas fa-thumbs-up me-2"></i><strong>Recomendação:</strong></div>
                    <div>Use o protocolo <strong>${melhorProtocolo.tipo}</strong> - funcionou perfeitamente!</div>
                    <div class="mt-2">
                        <strong>Protocolos que funcionaram:</strong>
                        <ul>
                            ${protocolosFuncionando.map(p => `<li>${p.tipo}</li>`).join('')}
                        </ul>
                    </div>
                    <div class="mt-2">
                        <button class="btn btn-primary" onclick="aplicarConfiguracao('${melhorProtocolo.tipo}')">
                            <i class="fas fa-cog"></i> Aplicar Esta Configuração
                        </button>
                    </div>
                `;
                
                statusDiv.className = 'test-result test-success';
                statusDiv.innerHTML = `<i class="fas fa-check-circle me-2"></i>Diagnóstico concluído! Protocolo recomendado: ${melhorProtocolo.tipo}`;
                
            } else if (protocolosTestados.length > 0) {
                recomDiv.className = 'test-result test-warning';
                recomDiv.innerHTML = `
                    <div><i class="fas fa-exclamation-triangle me-2"></i><strong>Nenhum protocolo funcionou ainda.</strong></div>
                    <div>Continue testando ou verifique se o Outlook está instalado.</div>
                    <div class="mt-2">
                        <strong>Sugestões:</strong>
                        <ul>
                            <li>Verifique se o Microsoft Outlook está instalado</li>
                            <li>Tente o Outlook Web se não tiver o desktop</li>
                            <li>Use o cliente padrão (mailto) como alternativa</li>
                        </ul>
                    </div>
                `;
            }
        }

        // Aplicar configuração
        function aplicarConfiguracao(protocolo) {
            alert(`Configuração aplicada! O sistema usará o protocolo "${protocolo}" por padrão.`);
            // Aqui você pode salvar a configuração no localStorage ou enviar para o servidor
            localStorage.setItem('outlookProtocolo', protocolo);
        }

        // Inicializar
        document.addEventListener('DOMContentLoaded', function() {
            carregarInfoSistema();
            
            const statusDiv = document.getElementById('testStatus');
            statusDiv.className = 'test-result test-info';
            statusDiv.innerHTML = '<i class="fas fa-play me-2"></i>Pronto para testar! Clique nos botões de teste acima.';
        });
    </script>
</body>
</html>
