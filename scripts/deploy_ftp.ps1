# CONFIGURACAO DE DEPLOY VIA FTP
# Configure suas credenciais FTP aqui

# ========================================
# CONFIGURACOES FTP - EDITE CONFORME SEU SERVIDOR
# ========================================

# Credenciais do servidor
$FTP_HOST = "ftp.seudominio.com"        # Endereco do servidor FTP
$FTP_USER = "seu_usuario_ftp"           # Usuario FTP
$FTP_PASS = "sua_senha_ftp"             # Senha FTP
$FTP_PATH = "/public_html/chamados/"    # Caminho no servidor (ajuste conforme necessario)

# Configuracoes locais
$LOCAL_PATH = $PSScriptRoot + "\.."     # Pasta local do sistema
$EXCLUDE_FOLDERS = @(".git", "deploy_packages", "docs", "node_modules", ".vscode")
$EXCLUDE_FILES = @("*.md", "*.bat", "*.ps1", "desktop.ini", "Thumbs.db")

Write-Host "========================================" -ForegroundColor Cyan
Write-Host " DEPLOY VIA FTP - CONFIGURACAO" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "⚠️  IMPORTANTE: Este script requer configuracao!" -ForegroundColor Yellow
Write-Host ""
Write-Host "Para usar o deploy via FTP:" -ForegroundColor White
Write-Host "1. Edite este arquivo (deploy_ftp.ps1)" -ForegroundColor Gray
Write-Host "2. Configure suas credenciais FTP" -ForegroundColor Gray
Write-Host "3. Ajuste o caminho de destino no servidor" -ForegroundColor Gray
Write-Host "4. Execute novamente o script" -ForegroundColor Gray
Write-Host ""

# Verificar se configuracoes foram alteradas
if ($FTP_HOST -eq "ftp.seudominio.com" -or $FTP_USER -eq "seu_usuario_ftp") {
    Write-Host "❌ Configuracoes FTP nao foram definidas!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Abra o arquivo deploy_ftp.ps1 e configure:" -ForegroundColor Yellow
    Write-Host "- FTP_HOST: Endereco do seu servidor FTP" -ForegroundColor Gray
    Write-Host "- FTP_USER: Seu usuario FTP" -ForegroundColor Gray
    Write-Host "- FTP_PASS: Sua senha FTP" -ForegroundColor Gray
    Write-Host "- FTP_PATH: Caminho no servidor onde ficara o sistema" -ForegroundColor Gray
    Write-Host ""
    Write-Host "Pressione Enter para sair..."
    Read-Host
    exit
}

# Funcao para upload via FTP (implementacao basica)
function Upload-FilesFTP {
    param(
        [string]$LocalPath,
        [string]$RemotePath,
        [string]$FtpHost,
        [string]$FtpUser,
        [string]$FtpPass
    )
    
    Write-Host "🚀 Iniciando upload via FTP..." -ForegroundColor Green
    Write-Host "📁 Origem: $LocalPath" -ForegroundColor Gray
    Write-Host "🌐 Destino: $FtpHost$RemotePath" -ForegroundColor Gray
    Write-Host ""
    
    # NOTA: Esta e uma implementacao basica
    # Para uso em producao, recomenda-se usar modulos como Posh-FTP ou WinSCP
    
    Write-Host "⚠️  AVISO: Implementacao FTP em desenvolvimento" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Alternativas recomendadas:" -ForegroundColor White
    Write-Host "1. Use o script de criacao de pacote ZIP" -ForegroundColor Gray
    Write-Host "2. Use clientes FTP como FileZilla ou WinSCP" -ForegroundColor Gray
    Write-Host "3. Use ferramentas de deploy do seu painel de controle" -ForegroundColor Gray
    Write-Host ""
}

# Executar upload (se configurado)
if ($FTP_HOST -ne "ftp.seudominio.com") {
    $Confirm = Read-Host "Deseja prosseguir com o upload FTP? (s/n)"
    if ($Confirm.ToLower() -eq 's' -or $Confirm.ToLower() -eq 'sim') {
        Upload-FilesFTP -LocalPath $LOCAL_PATH -RemotePath $FTP_PATH -FtpHost $FTP_HOST -FtpUser $FTP_USER -FtpPass $FTP_PASS
    }
}

Write-Host ""
Write-Host "📋 INSTRUCOES DE USO:" -ForegroundColor Cyan
Write-Host ""
Write-Host "OPCAO 1 - Deploy Manual (Recomendado):" -ForegroundColor Green
Write-Host "1. Execute: scripts\deploy_automatico.ps1" -ForegroundColor Gray
Write-Host "2. Faca upload do ZIP gerado" -ForegroundColor Gray
Write-Host "3. Extraia no servidor e configure" -ForegroundColor Gray
Write-Host ""
Write-Host "OPCAO 2 - Cliente FTP:" -ForegroundColor Green
Write-Host "1. Use FileZilla, WinSCP ou similar" -ForegroundColor Gray
Write-Host "2. Conecte com suas credenciais FTP" -ForegroundColor Gray
Write-Host "3. Copie todos os arquivos do projeto" -ForegroundColor Gray
Write-Host ""
Write-Host "OPCAO 3 - Painel de Controle:" -ForegroundColor Green
Write-Host "1. Acesse cPanel, Plesk ou similar" -ForegroundColor Gray
Write-Host "2. Use o gerenciador de arquivos" -ForegroundColor Gray
Write-Host "3. Faca upload e extraia" -ForegroundColor Gray
Write-Host ""

Write-Host "Pressione Enter para continuar..."
Read-Host
