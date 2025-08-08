<?php
/**
 * Teste Específico para Outlook Classic
 * Verifica e testa diferentes métodos de abertura
 */

session_start();
require_once '../config/config.php';
require_once '../src/DB.php';
require_once '../src/EmailTemplate.php';

// Dados de teste
$dados_teste = [
    'email' => 'teste@elusinstrumentacao.com.br',
    'assunto' => 'Teste Outlook Classic - ' . date('H:i:s'),
    'corpo' => "Este é um teste para verificar se o Outlook Classic abre corretamente com o template pré-preenchido.\n\nData/Hora: " . date('d/m/Y H:i:s') . "\nTeste realizado pelo sistema ELUS."
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Outlook Classic - Sistema ELUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .test-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .protocol-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }
        .protocol-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .result-box {
            background: #f1f3f4;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            border-left: 4px solid #28a745;
        }
        .info-box {
            background: #e3f2fd;
            border: 1px solid #2196f3;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .btn-test {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .btn-test:hover {
            transform: scale(1.05);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="test-container">
                    <div class="text-center mb-4">
                        <h1><i class="fas fa-envelope text-primary"></i> Teste Outlook Classic</h1>
                        <p class="text-muted">Verificação dos métodos de abertura do Outlook Classic com templates</p>
                    </div>

                    <!-- Informações do Teste -->
                    <div class="info-box">
                        <h5><i class="fas fa-info-circle text-primary"></i> Dados do Teste</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Email:</strong> <?php echo $dados_teste['email']; ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Assunto:</strong> <?php echo $dados_teste['assunto']; ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Status:</strong> <span class="badge bg-success">Pronto</span>
                            </div>
                        </div>
                    </div>

                    <!-- Métodos de Teste -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="protocol-card">
                                <h5><i class="fas fa-desktop text-info"></i> Método 1: MAPI Nativo</h5>
                                <p class="text-muted">Protocolo MAPI do Windows para Outlook Classic</p>
                                <button class="btn btn-test" onclick="testarMAPI()">
                                    <i class="fas fa-play"></i> Testar MAPI
                                </button>
                                <div id="result-mapi" class="result-box" style="display: none;"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="protocol-card">
                                <h5><i class="fas fa-terminal text-warning"></i> Método 2: Shell Windows</h5>
                                <p class="text-muted">Shell nativo do Windows (sendto)</p>
                                <button class="btn btn-test" onclick="testarShell()">
                                    <i class="fas fa-play"></i> Testar Shell
                                </button>
                                <div id="result-shell" class="result-box" style="display: none;"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="protocol-card">
                                <h5><i class="fas fa-mail-bulk text-success"></i> Método 3: Outlook Protocol</h5>
                                <p class="text-muted">Protocolo outlook:// específico</p>
                                <button class="btn btn-test" onclick="testarOutlookProtocol()">
                                    <i class="fas fa-play"></i> Testar Protocol
                                </button>
                                <div id="result-outlook" class="result-box" style="display: none;"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="protocol-card">
                                <h5><i class="fas fa-robot text-danger"></i> Método 4: ActiveX (IE)</h5>
                                <p class="text-muted">ActiveX para Internet Explorer/Edge Legacy</p>
                                <button class="btn btn-test" onclick="testarActiveX()">
                                    <i class="fas fa-play"></i> Testar ActiveX
                                </button>
                                <div id="result-activex" class="result-box" style="display: none;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Teste Completo -->
                    <div class="text-center mt-4">
                        <button class="btn btn-primary btn-lg" onclick="testarTodosMetodos()">
                            <i class="fas fa-rocket"></i> Testar Todos os Métodos
                        </button>
                        <a href="../public/email_template.php?id=1" class="btn btn-secondary btn-lg ms-2">
                            <i class="fas fa-arrow-left"></i> Voltar para Email Template
                        </a>
                    </div>

                    <!-- Resultados Gerais -->
                    <div id="resultados-gerais" class="mt-4" style="display: none;">
                        <h5><i class="fas fa-chart-line"></i> Relatório dos Testes</h5>
                        <div id="relatorio-conteudo"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dados do teste
        const dadosTeste = {
            email: '<?php echo $dados_teste['email']; ?>',
            assunto: '<?php echo $dados_teste['assunto']; ?>',
            corpo: <?php echo json_encode($dados_teste['corpo']); ?>
        };

        let resultados = {};

        function mostrarResultado(metodo, sucesso, mensagem) {
            const resultDiv = document.getElementById(`result-${metodo}`);
            resultDiv.style.display = 'block';
            
            if (sucesso) {
                resultDiv.style.borderLeftColor = '#28a745';
                resultDiv.innerHTML = `<i class="fas fa-check-circle text-success"></i> ${mensagem}`;
            } else {
                resultDiv.style.borderLeftColor = '#dc3545';
                resultDiv.innerHTML = `<i class="fas fa-times-circle text-danger"></i> ${mensagem}`;
            }
            
            resultados[metodo] = { sucesso, mensagem };
        }

        function testarMAPI() {
            const mapiUrl = `mapi://[to=${dadosTeste.email}]&[subject=${encodeURIComponent(dadosTeste.assunto)}]&[body=${encodeURIComponent(dadosTeste.corpo)}]`;
            
            try {
                window.location.href = mapiUrl;
                mostrarResultado('mapi', true, 'Protocolo MAPI executado. Verifique se o Outlook abriu.');
                
                setTimeout(() => {
                    const confirmou = confirm('O Outlook Classic abriu com o template preenchido?');
                    mostrarResultado('mapi', confirmou, 
                        confirmou ? 'MAPI funcionou corretamente!' : 'MAPI não preencheu o template.');
                }, 3000);
                
            } catch (error) {
                mostrarResultado('mapi', false, `Erro MAPI: ${error.message}`);
            }
        }

        function testarShell() {
            const shellUrl = `shell:sendto?to=${dadosTeste.email}&subject=${encodeURIComponent(dadosTeste.assunto)}&body=${encodeURIComponent(dadosTeste.corpo)}`;
            
            try {
                window.location.href = shellUrl;
                mostrarResultado('shell', true, 'Shell do Windows executado. Verifique se abriu corretamente.');
                
                setTimeout(() => {
                    const confirmou = confirm('O Shell abriu algum cliente de email com o template?');
                    mostrarResultado('shell', confirmou, 
                        confirmou ? 'Shell funcionou!' : 'Shell não funcionou como esperado.');
                }, 3000);
                
            } catch (error) {
                mostrarResultado('shell', false, `Erro Shell: ${error.message}`);
            }
        }

        function testarOutlookProtocol() {
            const outlookUrl = `outlook://compose/?to=${dadosTeste.email}&subject=${encodeURIComponent(dadosTeste.assunto)}&body=${encodeURIComponent(dadosTeste.corpo)}`;
            
            try {
                window.location.href = outlookUrl;
                mostrarResultado('outlook', true, 'Protocolo outlook:// executado.');
                
                setTimeout(() => {
                    const confirmou = confirm('O protocolo outlook:// abriu o Outlook com template?');
                    mostrarResultado('outlook', confirmou, 
                        confirmou ? 'Protocolo outlook:// funcional!' : 'Protocolo outlook:// não funcionou.');
                }, 3000);
                
            } catch (error) {
                mostrarResultado('outlook', false, `Erro protocolo outlook: ${error.message}`);
            }
        }

        function testarActiveX() {
            try {
                if (window.ActiveXObject || "ActiveXObject" in window) {
                    const outlook = new ActiveXObject("Outlook.Application");
                    const mail = outlook.CreateItem(0); // olMailItem
                    mail.To = dadosTeste.email;
                    mail.Subject = dadosTeste.assunto;
                    mail.Body = dadosTeste.corpo;
                    mail.Display(true);
                    
                    mostrarResultado('activex', true, 'ActiveX funcionou! Outlook aberto diretamente.');
                } else {
                    mostrarResultado('activex', false, 'ActiveX não disponível (precisa do Internet Explorer).');
                }
            } catch (error) {
                mostrarResultado('activex', false, `Erro ActiveX: ${error.message}`);
            }
        }

        function testarTodosMetodos() {
            const metodos = ['testarMAPI', 'testarShell', 'testarOutlookProtocol', 'testarActiveX'];
            let indice = 0;
            
            function executarProximo() {
                if (indice < metodos.length) {
                    window[metodos[indice]]();
                    indice++;
                    setTimeout(executarProximo, 5000); // 5 segundos entre testes
                } else {
                    setTimeout(gerarRelatorio, 2000);
                }
            }
            
            executarProximo();
        }

        function gerarRelatorio() {
            const resultadosDiv = document.getElementById('resultados-gerais');
            const conteudoDiv = document.getElementById('relatorio-conteudo');
            
            let html = '<div class="row">';
            let sucessos = 0;
            
            Object.keys(resultados).forEach(metodo => {
                const resultado = resultados[metodo];
                if (resultado.sucesso) sucessos++;
                
                html += `
                    <div class="col-md-6 mb-3">
                        <div class="alert ${resultado.sucesso ? 'alert-success' : 'alert-danger'}">
                            <strong>${metodo.toUpperCase()}:</strong> ${resultado.mensagem}
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            html += `<div class="alert alert-info"><strong>Resumo:</strong> ${sucessos} de ${Object.keys(resultados).length} métodos funcionaram.</div>`;
            
            if (sucessos === 0) {
                html += `
                    <div class="alert alert-warning">
                        <strong>Recomendação:</strong> Use a opção "Copiar Tudo" no sistema e cole manualmente no Outlook Classic.
                        O Outlook Classic tem limitações com protocolos modernos web.
                    </div>
                `;
            }
            
            conteudoDiv.innerHTML = html;
            resultadosDiv.style.display = 'block';
        }
    </script>
</body>
</html>
