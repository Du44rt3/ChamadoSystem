<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Simples - Outlook Classic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding: 20px; background: #f8f9fa; }
        .test-card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="test-card">
                    <h2 class="text-center mb-4">🧪 Teste Outlook Classic</h2>
                    
                    <div class="alert alert-info">
                        <strong>Objetivo:</strong> Testar se as novas soluções funcionam para o Outlook Classic
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h5>📋 Método 1: Copiar + Instruções</h5>
                            <p>Copia template e mostra instruções visuais</p>
                            <button class="btn btn-primary w-100" onclick="testarMetodo1()">
                                Testar Método 1
                            </button>
                        </div>
                        
                        <div class="col-md-6">
                            <h5>📁 Método 2: Arquivo .eml</h5>
                            <p>Cria arquivo .eml para download</p>
                            <button class="btn btn-success w-100" onclick="testarMetodo2()">
                                Testar Método 2
                            </button>
                        </div>
                    </div>
                    
                    <div id="resultado" class="mt-4" style="display: none;">
                        <h5>Resultado:</h5>
                        <div id="resultado-conteudo"></div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="../public/email_template.php?id=1" class="btn btn-secondary">
                            ← Voltar para Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const dadosTeste = {
            email: 'teste@elusinstrumentacao.com.br',
            assunto: 'Teste Outlook Classic - ' + new Date().toLocaleTimeString(),
            corpo: 'Este é um teste das novas funcionalidades para Outlook Classic.\n\nSe você está vendo este texto no Outlook, significa que funcionou!'
        };

        function mostrarResultado(metodo, sucesso, mensagem) {
            document.getElementById('resultado').style.display = 'block';
            document.getElementById('resultado-conteudo').innerHTML = `
                <div class="alert ${sucesso ? 'alert-success' : 'alert-danger'}">
                    <strong>${metodo}:</strong> ${mensagem}
                </div>
            `;
        }

        function testarMetodo1() {
            // Método 1: Copiar + Instruções (igual ao implementado)
            const templateCompleto = `Para: ${dadosTeste.email}
Assunto: ${dadosTeste.assunto}

${dadosTeste.corpo}`;
            
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(templateCompleto).then(() => {
                    mostrarResultado('Método 1', true, 'Template copiado! Agora abra o Outlook Classic e cole com Ctrl+V');
                    mostrarInstrucoes();
                }).catch(() => {
                    usarMetodoAlternativo();
                });
            } else {
                usarMetodoAlternativo();
            }
            
            function usarMetodoAlternativo() {
                const textArea = document.createElement('textarea');
                textArea.value = templateCompleto;
                textArea.style.position = 'fixed';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.select();
                
                try {
                    document.execCommand('copy');
                    mostrarResultado('Método 1', true, 'Template copiado com método alternativo!');
                    mostrarInstrucoes();
                } catch (err) {
                    mostrarResultado('Método 1', false, 'Erro ao copiar template');
                } finally {
                    document.body.removeChild(textArea);
                }
            }
        }

        function testarMetodo2() {
            // Método 2: Arquivo .eml
            const dataAtual = new Date().toUTCString();
            const emlContent = `From: Sistema ELUS <noreply@elusinstrumentacao.com.br>
To: ${dadosTeste.email}
Subject: ${dadosTeste.assunto}
Date: ${dataAtual}
Content-Type: text/plain; charset=utf-8

${dadosTeste.corpo}`;
            
            try {
                const blob = new Blob([emlContent], { type: 'message/rfc822' });
                const url = URL.createObjectURL(blob);
                
                const downloadLink = document.createElement('a');
                downloadLink.href = url;
                downloadLink.download = `teste_outlook_${Date.now()}.eml`;
                downloadLink.style.display = 'none';
                
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
                
                setTimeout(() => URL.revokeObjectURL(url), 1000);
                
                mostrarResultado('Método 2', true, 'Arquivo .eml criado e baixado! Vá para Downloads e clique duas vezes no arquivo');
                
            } catch (error) {
                mostrarResultado('Método 2', false, 'Erro ao criar arquivo .eml: ' + error.message);
            }
        }

        function mostrarInstrucoes() {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.8); z-index: 9999;
                display: flex; align-items: center; justify-content: center;
            `;
            
            modal.innerHTML = `
                <div style="background: white; padding: 30px; border-radius: 10px; max-width: 500px; text-align: center;">
                    <h4>📧 Como usar no Outlook Classic</h4>
                    <div style="text-align: left; margin: 20px 0;">
                        <p><strong>1.</strong> Abra o Microsoft Outlook</p>
                        <p><strong>2.</strong> Clique em "Novo Email"</p>
                        <p><strong>3.</strong> Pressione Ctrl+V</p>
                        <p><strong>4.</strong> O template será colado automaticamente</p>
                    </div>
                    <button onclick="this.remove()" 
                            style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px;">
                        Entendi
                    </button>
                </div>
            `;
            
            document.body.appendChild(modal);
            setTimeout(() => modal.remove(), 10000);
        }
    </script>
</body>
</html>
