<?php
require_once '../src/header.php';
require_once '../src/Auth.php';

// Verificar se o usuário está logado
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Completo - Integração Outlook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .card-header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
        }
        .btn-test {
            margin: 5px;
            min-width: 200px;
        }
        .resultado {
            margin-top: 15px;
            padding: 15px;
            border-radius: 10px;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
        }
        .protocolo-item {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .method-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            padding: 25px;
            margin: 20px 0;
            border: 2px solid #dee2e6;
        }
        .alert-dismissible .btn-close { margin-left: auto; }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-header text-center">
                        <h2><i class="fas fa-vial"></i> Teste Completo - Integração Outlook</h2>
                        <p class="mb-0">Testador abrangente para todas as funcionalidades de email implementadas</p>
                    </div>
                    <div class="card-body">
                        
                        <!-- Seção de dados do teste -->
                        <div class="method-section">
                            <h4><i class="fas fa-cog"></i> Configurações do Teste</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="email" class="form-label">Email de Destino:</label>
                                    <input type="email" class="form-control" id="email" value="colaborador@elusinstrumentacao.com.br">
                                </div>
                                <div class="col-md-4">
                                    <label for="assunto" class="form-label">Assunto:</label>
                                    <input type="text" class="form-control" id="assunto" value="Teste - Chamado #12345">
                                </div>
                                <div class="col-md-4">
                                    <label for="prioridade" class="form-label">Prioridade:</label>
                                    <select class="form-control" id="prioridade">
                                        <option value="Alta">Alta</option>
                                        <option value="Média" selected>Média</option>
                                        <option value="Baixa">Baixa</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label for="corpo" class="form-label">Corpo do Email:</label>
                                <textarea class="form-control" id="corpo" rows="4">Prezado colaborador,

Foi aberto um novo chamado para sua análise:

Título: Problema com equipamento X
Descrição: Equipamento apresenta falha intermitente
Urgência: Verificar o mais rápido possível

Atenciosamente,
Sistema ELUS</textarea>
                            </div>
                        </div>

                        <!-- 1. Métodos Outlook Classic -->
                        <div class="method-section">
                            <h4><i class="fas fa-desktop"></i> Métodos Outlook Classic</h4>
                            <p class="text-muted">Testadores específicos para Outlook Classic (versões desktop antigas)</p>
                            
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary btn-test" onclick="testarOutlookClassicoDireto()">
                                        <i class="fas fa-rocket"></i><br>Abertura Direta
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-warning btn-test" onclick="testarOutlookClassicoAvancado()">
                                        <i class="fas fa-cogs"></i><br>Métodos Avançados
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-success btn-test" onclick="testarArquivoEml()">
                                        <i class="fas fa-file-download"></i><br>Arquivo .EML
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-info btn-test" onclick="testarCopiarTexto()">
                                        <i class="fas fa-copy"></i><br>Copiar Texto
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Métodos Web -->
                        <div class="method-section">
                            <h4><i class="fas fa-globe"></i> Métodos Web</h4>
                            <p class="text-muted">Testadores para clientes de email baseados em web</p>
                            
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary btn-test" onclick="testarOutlookWeb()">
                                        <i class="fab fa-microsoft"></i><br>Outlook Web
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-danger btn-test" onclick="testarGmail()">
                                        <i class="fab fa-google"></i><br>Gmail
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-warning btn-test" onclick="testarWebmail()">
                                        <i class="fas fa-crown"></i><br>ELUS Webmail
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-secondary btn-test" onclick="testarMailto()">
                                        <i class="fas fa-mail-bulk"></i><br>Cliente Padrão
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Protocolos Avançados -->
                        <div class="method-section">
                            <h4><i class="fas fa-network-wired"></i> Testes de Protocolos</h4>
                            <p class="text-muted">Testadores individuais para cada protocolo implementado</p>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="protocolo-item">
                                        <h6><i class="fas fa-link"></i> Protocolo ms-outlook://</h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="testarProtocolo('ms-outlook')">
                                            Testar ms-outlook://
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="protocolo-item">
                                        <h6><i class="fas fa-link"></i> Protocolo outlook://</h6>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="testarProtocolo('outlook')">
                                            Testar outlook://
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="protocolo-item">
                                        <h6><i class="fas fa-link"></i> Protocolo mapi://</h6>
                                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="testarProtocolo('mapi')">
                                            Testar mapi://
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="protocolo-item">
                                        <h6><i class="fas fa-robot"></i> ActiveX (IE/Edge)</h6>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="testarActiveX()">
                                            Testar ActiveX
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Diagnóstico -->
                        <div class="method-section">
                            <h4><i class="fas fa-stethoscope"></i> Ferramentas de Diagnóstico</h4>
                            <p class="text-muted">Ferramentas para diagnosticar problemas e verificar compatibilidade</p>
                            
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-info btn-test" onclick="diagnosticarSistema()">
                                        <i class="fas fa-search"></i><br>Diagnóstico Sistema
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-warning btn-test" onclick="verificarNavegador()">
                                        <i class="fas fa-browser"></i><br>Verificar Navegador
                                    </button>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-secondary btn-test" onclick="limparTestes()">
                                        <i class="fas fa-broom"></i><br>Limpar Resultados
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Área de Resultados -->
                        <div class="method-section">
                            <h4><i class="fas fa-chart-line"></i> Resultados dos Testes</h4>
                            <div id="area-resultados">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Clique nos botões acima para executar os testes. Os resultados aparecerão aqui.
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
        let contadorTestes = 0;
        
        function adicionarResultado(titulo, resultado, tipo = 'info') {
            contadorTestes++;
            const area = document.getElementById('area-resultados');
            
            if (contadorTestes === 1) {
                area.innerHTML = '';
            }
            
            const div = document.createElement('div');
            div.className = `alert alert-${tipo} alert-dismissible fade show`;
            div.innerHTML = `
                <strong>[${new Date().toLocaleTimeString()}] ${titulo}:</strong><br>
                ${resultado}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            area.appendChild(div);
            area.scrollTop = area.scrollHeight;
        }

        function obterDadosFormulario() {
            return {
                email: document.getElementById('email').value,
                assunto: document.getElementById('assunto').value,
                corpo: document.getElementById('corpo').value,
                prioridade: document.getElementById('prioridade').value
            };
        }

        // Testes Outlook Classic
        function testarOutlookClassicoDireto() {
            const dados = obterDadosFormulario();
            adicionarResultado('Outlook Classic - Abertura Direta', 'Tentando abrir Outlook Classic com protocolos padrão...', 'primary');
            
            const protocolos = [
                `ms-outlook://compose?to=${dados.email}&subject=${encodeURIComponent(dados.assunto)}&body=${encodeURIComponent(dados.corpo)}`,
                `outlook:/compose/?to=${dados.email}&subject=${encodeURIComponent(dados.assunto)}&body=${encodeURIComponent(dados.corpo)}`
            ];
            
            protocolos.forEach((protocolo, index) => {
                setTimeout(() => {
                    try {
                        window.location.href = protocolo;
                        adicionarResultado(`Protocolo ${index + 1}`, `Executado: ${protocolo.substring(0, 50)}...`, 'success');
                    } catch (error) {
                        adicionarResultado(`Protocolo ${index + 1}`, `Erro: ${error.message}`, 'danger');
                    }
                }, index * 500);
            });
        }

        function testarOutlookClassicoAvancado() {
            adicionarResultado('Outlook Classic - Métodos Avançados', 'Iniciando sequência de métodos avançados...', 'warning');
            
            // Tentar ActiveX
            try {
                const outlook = new ActiveXObject('Outlook.Application');
                adicionarResultado('ActiveX', 'ActiveX disponível! Tentando criar email...', 'success');
                
                const mail = outlook.CreateItem(0);
                const dados = obterDadosFormulario();
                mail.To = dados.email;
                mail.Subject = dados.assunto;
                mail.Body = dados.corpo;
                mail.Display();
                
                adicionarResultado('ActiveX', 'Email criado via ActiveX com sucesso!', 'success');
            } catch (error) {
                adicionarResultado('ActiveX', `ActiveX não disponível: ${error.message}`, 'warning');
                
                // Fallback para outros métodos
                testarProtocolosAlternativos();
            }
        }

        function testarProtocolosAlternativos() {
            const dados = obterDadosFormulario();
            const protocolosAvancados = [
                `mapi:?to=${dados.email}&subject=${encodeURIComponent(dados.assunto)}&body=${encodeURIComponent(dados.corpo)}`,
                `ms-outlook://compose?to=${dados.email}&subject=${encodeURIComponent(dados.assunto)}&body=${encodeURIComponent(dados.corpo)}`,
            ];
            
            protocolosAvancados.forEach((protocolo, index) => {
                setTimeout(() => {
                    try {
                        const link = document.createElement('a');
                        link.href = protocolo;
                        link.click();
                        adicionarResultado(`Protocolo Avançado ${index + 1}`, `Testado: ${protocolo.split(':')[0]}://`, 'info');
                    } catch (error) {
                        adicionarResultado(`Protocolo Avançado ${index + 1}`, `Falhou: ${error.message}`, 'danger');
                    }
                }, index * 1000);
            });
        }

        function testarArquivoEml() {
            const dados = obterDadosFormulario();
            adicionarResultado('Arquivo .EML', 'Criando arquivo .eml para download...', 'success');
            
            const dataAtual = new Date().toUTCString();
            const emlContent = `From: Sistema ELUS <noreply@elusinstrumentacao.com.br>
To: ${dados.email}
Subject: ${dados.assunto}
Date: ${dataAtual}
Content-Type: text/plain; charset=utf-8

${dados.corpo}`;
            
            try {
                const blob = new Blob([emlContent], { type: 'message/rfc822' });
                const url = URL.createObjectURL(blob);
                
                const downloadLink = document.createElement('a');
                downloadLink.href = url;
                downloadLink.download = `teste_chamado_${Date.now()}.eml`;
                downloadLink.click();
                
                setTimeout(() => URL.revokeObjectURL(url), 1000);
                
                adicionarResultado('Arquivo .EML', 'Arquivo .eml baixado com sucesso! Clique duas vezes nele para abrir no Outlook.', 'success');
                
            } catch (error) {
                adicionarResultado('Arquivo .EML', `Erro ao criar arquivo: ${error.message}`, 'danger');
            }
        }

        function testarCopiarTexto() {
            const dados = obterDadosFormulario();
            const templateCompleto = `Para: ${dados.email}
Assunto: ${dados.assunto}

${dados.corpo}`;
            
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(templateCompleto).then(() => {
                    adicionarResultado('Copiar Texto', 'Template copiado com sucesso! Use Ctrl+V no Outlook.', 'success');
                });
            } else {
                const textArea = document.createElement('textarea');
                textArea.value = templateCompleto;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                adicionarResultado('Copiar Texto', 'Template copiado (método alternativo). Use Ctrl+V no Outlook.', 'success');
            }
        }

        // Testes Web
        function testarOutlookWeb() {
            const dados = obterDadosFormulario();
            const url = `https://outlook.live.com/mail/0/deeplink/compose?to=${dados.email}&subject=${encodeURIComponent(dados.assunto)}&body=${encodeURIComponent(dados.corpo)}`;
            window.open(url, '_blank');
            adicionarResultado('Outlook Web', 'Abrindo Outlook Web em nova aba...', 'primary');
        }

        function testarGmail() {
            const dados = obterDadosFormulario();
            const url = `https://mail.google.com/mail/?view=cm&fs=1&to=${dados.email}&su=${encodeURIComponent(dados.assunto)}&body=${encodeURIComponent(dados.corpo)}`;
            window.open(url, '_blank');
            adicionarResultado('Gmail', 'Abrindo Gmail em nova aba...', 'danger');
        }

        function testarWebmail() {
            const dados = obterDadosFormulario();
            const url = `https://webmail.kinghost.com.br/`;
            window.open(url, '_blank');
            adicionarResultado('ELUS Webmail', 'Abrindo webmail ELUS (KingHost) em nova aba...', 'warning');
        }

        function testarMailto() {
            const dados = obterDadosFormulario();
            const mailtoUrl = `mailto:${dados.email}?subject=${encodeURIComponent(dados.assunto)}&body=${encodeURIComponent(dados.corpo)}`;
            try {
                window.location.href = mailtoUrl;
                adicionarResultado('Cliente Padrão', 'Tentando abrir cliente de email padrão...', 'secondary');
            } catch (error) {
                adicionarResultado('Cliente Padrão', `Erro: ${error.message}`, 'danger');
            }
        }

        // Testes de Protocolos
        function testarProtocolo(tipo) {
            const dados = obterDadosFormulario();
            let protocolo;
            
            switch(tipo) {
                case 'ms-outlook':
                    protocolo = `ms-outlook://compose?to=${dados.email}&subject=${encodeURIComponent(dados.assunto)}&body=${encodeURIComponent(dados.corpo)}`;
                    break;
                case 'outlook':
                    protocolo = `outlook://compose/?to=${dados.email}&subject=${encodeURIComponent(dados.assunto)}&body=${encodeURIComponent(dados.corpo)}`;
                    break;
                case 'mapi':
                    protocolo = `mapi:?to=${dados.email}&subject=${encodeURIComponent(dados.assunto)}&body=${encodeURIComponent(dados.corpo)}`;
                    break;
            }
            
            try {
                window.location.href = protocolo;
                adicionarResultado(`Protocolo ${tipo}://`, `Executado: ${protocolo}`, 'info');
            } catch (error) {
                adicionarResultado(`Protocolo ${tipo}://`, `Erro: ${error.message}`, 'danger');
            }
        }

        function testarActiveX() {
            try {
                const outlook = new ActiveXObject('Outlook.Application');
                adicionarResultado('ActiveX', 'ActiveX disponível e funcional!', 'success');
                
                const dados = obterDadosFormulario();
                const mail = outlook.CreateItem(0);
                mail.To = dados.email;
                mail.Subject = dados.assunto;
                mail.Body = dados.corpo;
                mail.Display();
                
                adicionarResultado('ActiveX', 'Email criado e exibido via ActiveX!', 'success');
            } catch (error) {
                adicionarResultado('ActiveX', `ActiveX não disponível ou falhou: ${error.message}`, 'warning');
            }
        }

        // Diagnósticos
        function diagnosticarSistema() {
            const info = {
                navegador: navigator.userAgent,
                plataforma: navigator.platform,
                idioma: navigator.language,
                cookiesHabilitados: navigator.cookieEnabled,
                javaHabilitado: navigator.javaEnabled(),
                temActiveX: typeof ActiveXObject !== 'undefined',
                temClipboard: !!navigator.clipboard,
                protocolosSuportados: []
            };
            
            adicionarResultado('Diagnóstico Sistema', `
                <strong>Navegador:</strong> ${info.navegador}<br>
                <strong>Plataforma:</strong> ${info.plataforma}<br>
                <strong>Idioma:</strong> ${info.idioma}<br>
                <strong>Cookies:</strong> ${info.cookiesHabilitados ? 'Habilitados' : 'Desabilitados'}<br>
                <strong>Java:</strong> ${info.javaHabilitado ? 'Habilitado' : 'Desabilitado'}<br>
                <strong>ActiveX:</strong> ${info.temActiveX ? 'Disponível' : 'Não disponível'}<br>
                <strong>Clipboard API:</strong> ${info.temClipboard ? 'Disponível' : 'Não disponível'}
            `, 'info');
        }

        function verificarNavegador() {
            const isIE = navigator.userAgent.indexOf('MSIE') !== -1 || navigator.appVersion.indexOf('Trident/') > -1;
            const isEdge = navigator.userAgent.indexOf('Edg') !== -1;
            const isChrome = navigator.userAgent.indexOf('Chrome') !== -1;
            const isFirefox = navigator.userAgent.indexOf('Firefox') !== -1;
            
            let recomendacao = '';
            if (isIE) {
                recomendacao = 'Internet Explorer detectado. ActiveX deve funcionar.';
            } else if (isEdge) {
                recomendacao = 'Microsoft Edge detectado. Protocolos ms-outlook:// podem funcionar.';
            } else if (isChrome) {
                recomendacao = 'Google Chrome detectado. Use métodos alternativos (arquivo .eml ou cópia).';
            } else if (isFirefox) {
                recomendacao = 'Mozilla Firefox detectado. Use métodos alternativos (arquivo .eml ou cópia).';
            } else {
                recomendacao = 'Navegador não identificado. Teste diferentes métodos.';
            }
            
            adicionarResultado('Verificação Navegador', recomendacao, 'warning');
        }

        function limparTestes() {
            document.getElementById('area-resultados').innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Resultados limpos. Execute novos testes para ver os resultados aqui.
                </div>
            `;
            contadorTestes = 0;
            adicionarResultado('Sistema', 'Área de resultados limpa.', 'secondary');
        }
    </script>
</body>
</html>
