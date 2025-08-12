<?php
require_once '../src/header.php';
require_once '../src/Auth.php';
require_once '../src/EmailTemplate.php';

// Verificar se o usuário está logado
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Obter dados do chamado
$chamado_id = $_GET['id'] ?? null;
$email_colaborador = $_GET['email'] ?? '';

if (!$chamado_id) {
    die('ID do chamado não fornecido');
}

// Gerar o template do email
$emailTemplate = new EmailTemplate();
$templateData = $emailTemplate->gerarTemplate($chamado_id);

if (!$templateData) {
    die('Erro ao gerar template de email');
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template de Email - Chamado #<?= htmlspecialchars($chamado_id) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .card-header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
        }
        .template-content {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            font-family: 'Courier New', monospace;
            white-space: pre-wrap;
        }
        .btn-email {
            min-width: 200px;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-lg">
                    <div class="card-header text-center">
                        <h2><i class="fas fa-envelope"></i> Template de Email - Chamado #<?= htmlspecialchars($chamado_id) ?></h2>
                        <p class="mb-0">Sistema simplificado para envio de emails via Outlook Classic e KingHost</p>
                    </div>
                    <div class="card-body">
                        
                        <!-- Formulário do Email -->
                        <div class="row">
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email do Colaborador:</label>
                                <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($email_colaborador) ?>" placeholder="colaborador@elusinstrumentacao.com.br">
                            </div>
                            <div class="col-md-6">
                                <label for="assunto" class="form-label">Assunto:</label>
                                <input type="text" class="form-control" id="assunto" value="<?= htmlspecialchars($templateData['assunto']) ?>">
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <label for="corpo" class="form-label">Corpo do Email:</label>
                            <textarea class="form-control" id="corpo" rows="15"><?= htmlspecialchars($templateData['corpo']) ?></textarea>
                        </div>
                        
                        <!-- Preview do Template -->
                        <div class="mt-4">
                            <h5><i class="fas fa-eye"></i> Preview do Email:</h5>
                            <div class="template-content" id="preview">
                                Para: <span id="preview-email"><?= htmlspecialchars($email_colaborador) ?></span>
Assunto: <span id="preview-assunto"><?= htmlspecialchars($templateData['assunto']) ?></span>

<span id="preview-corpo"><?= htmlspecialchars($templateData['corpo']) ?></span>
                            </div>
                        </div>
                        
                        <!-- Botões de Ação -->
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <div class="btn-group-vertical d-md-none">
                                    <button class="btn btn-primary btn-email" onclick="abrirOutlookClassico()">
                                        <i class="fas fa-desktop"></i> Outlook Classic
                                    </button>
                                    <button class="btn btn-warning btn-email" onclick="abrirKingHost()">
                                        <i class="fas fa-crown"></i> ELUS Webmail (KingHost)
                                    </button>
                                    <button class="btn btn-success btn-email" onclick="copiarTemplate()">
                                        <i class="fas fa-copy"></i> Copiar Template
                                    </button>
                                </div>
                                
                                <div class="d-none d-md-block">
                                    <button class="btn btn-primary btn-email" onclick="abrirOutlookClassico()">
                                        <i class="fas fa-desktop"></i> Outlook Classic
                                    </button>
                                    <button class="btn btn-warning btn-email" onclick="abrirKingHost()">
                                        <i class="fas fa-crown"></i> ELUS Webmail (KingHost)
                                    </button>
                                    <button class="btn btn-success btn-email" onclick="copiarTemplate()">
                                        <i class="fas fa-copy"></i> Copiar Template
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botões de Navegação -->
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <a href="view.php?id=<?= $chamado_id ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Voltar ao Chamado
                                </a>
                                <a href="dashboard.php" class="btn btn-info">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Atualizar preview em tempo real
        document.getElementById('email').addEventListener('input', atualizarPreview);
        document.getElementById('assunto').addEventListener('input', atualizarPreview);
        document.getElementById('corpo').addEventListener('input', atualizarPreview);
        
        function atualizarPreview() {
            const email = document.getElementById('email').value;
            const assunto = document.getElementById('assunto').value;
            const corpo = document.getElementById('corpo').value;
            
            document.getElementById('preview-email').textContent = email || '[Email não informado]';
            document.getElementById('preview-assunto').textContent = assunto;
            document.getElementById('preview-corpo').textContent = corpo;
        }
        
        function obterDadosEmail() {
            const email = document.getElementById('email').value.trim();
            const assunto = document.getElementById('assunto').value.trim();
            const corpo = document.getElementById('corpo').value.trim();
            
            if (!email) {
                mostrarAlerta('Por favor, informe o email do colaborador!', 'danger');
                return null;
            }
            
            return { email, assunto, corpo };
        }
        
        function abrirOutlookClassico() {
            const dados = obterDadosEmail();
            if (!dados) return;
            
            mostrarAlerta('Tentando abrir Outlook Classic...', 'info');
            
            // Método 1: Protocolo ms-outlook
            try {
                const outlookUrl = `ms-outlook://compose?to=${dados.email}&subject=${encodeURIComponent(dados.assunto)}&body=${encodeURIComponent(dados.corpo)}`;
                window.location.href = outlookUrl;
                
                // Se não funcionar, tentar criar arquivo .eml
                setTimeout(() => {
                    criarArquivoEml(dados);
                }, 2000);
                
            } catch (error) {
                criarArquivoEml(dados);
            }
        }
        
        function criarArquivoEml(dados) {
            try {
                const dataAtual = new Date().toUTCString();
                const emlContent = `From: Sistema ELUS <noreply@elusinstrumentacao.com.br>
To: ${dados.email}
Subject: ${dados.assunto}
Date: ${dataAtual}
Content-Type: text/plain; charset=utf-8

${dados.corpo}`;
                
                const blob = new Blob([emlContent], { type: 'message/rfc822' });
                const url = URL.createObjectURL(blob);
                
                const downloadLink = document.createElement('a');
                downloadLink.href = url;
                downloadLink.download = `chamado_${Date.now()}.eml`;
                downloadLink.click();
                
                setTimeout(() => URL.revokeObjectURL(url), 1000);
                
                mostrarAlerta('Arquivo .eml baixado! Clique duas vezes nele para abrir no Outlook Classic.', 'success');
                
            } catch (error) {
                // Se tudo falhar, copiar o texto
                copiarTemplate();
                mostrarAlerta('Não foi possível criar arquivo. Template copiado para colagem manual.', 'warning');
            }
        }
        
        function abrirKingHost() {
            const dados = obterDadosEmail();
            if (!dados) return;
            
            // Copiar template automaticamente
            copiarTemplate();
            
            // Abrir KingHost
            window.open('https://webmail.kinghost.com.br/', '_blank');
            
            mostrarAlerta('KingHost aberto em nova aba. Template copiado - use Ctrl+V para colar!', 'success');
            
            // Mostrar instruções
            setTimeout(() => {
                const modal = document.createElement('div');
                modal.style.cssText = `
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0,0,0,0.8); z-index: 9999;
                    display: flex; align-items: center; justify-content: center;
                `;
                
                modal.innerHTML = `
                    <div style="background: white; padding: 30px; border-radius: 15px; max-width: 500px; text-align: center;">
                        <h3 style="color: #ff6b35;">📧 KingHost Webmail</h3>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0; text-align: left;">
                            <h5>✅ Como usar:</h5>
                            <ol>
                                <li>Faça login no webmail</li>
                                <li>Clique em "Novo Email"</li>
                                <li>Cole o template com <strong>Ctrl+V</strong></li>
                                <li>Envie o email</li>
                            </ol>
                            <div style="background: #e9ecef; padding: 10px; border-radius: 5px; margin-top: 15px;">
                                <small><strong>💡 O template já foi copiado automaticamente!</strong></small>
                            </div>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" 
                                style="background: #28a745; color: white; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer;">
                            ✅ Entendi!
                        </button>
                    </div>
                `;
                
                document.body.appendChild(modal);
            }, 1000);
        }
        
        function copiarTemplate() {
            const dados = obterDadosEmail();
            if (!dados) return;
            
            const templateCompleto = `Para: ${dados.email}
Assunto: ${dados.assunto}

${dados.corpo}`;
            
            // Tentar API moderna primeiro
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(templateCompleto).then(() => {
                    mostrarAlerta('Template copiado com sucesso! Use Ctrl+V para colar.', 'success');
                }).catch(() => {
                    copiarTextoAlternativo(templateCompleto);
                });
            } else {
                copiarTextoAlternativo(templateCompleto);
            }
        }
        
        function copiarTextoAlternativo(texto) {
            const textArea = document.createElement('textarea');
            textArea.value = texto;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            
            try {
                document.execCommand('copy');
                mostrarAlerta('Template copiado com sucesso! Use Ctrl+V para colar.', 'success');
            } catch (err) {
                mostrarAlerta('Erro ao copiar. Por favor, selecione e copie manualmente.', 'danger');
            }
            
            document.body.removeChild(textArea);
        }
        
        function mostrarAlerta(mensagem, tipo) {
            // Remove alertas existentes
            const alertasExistentes = document.querySelectorAll('.alerta-temporario');
            alertasExistentes.forEach(alerta => alerta.remove());
            
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo} alert-dismissible fade show alerta-temporario`;
            alertDiv.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;';
            alertDiv.innerHTML = `
                ${mensagem}
                <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            // Auto-remover após 5 segundos
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 5000);
        }
    </script>
</body>
</html>
