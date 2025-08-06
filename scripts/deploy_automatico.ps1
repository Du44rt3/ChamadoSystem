# SCRIPT DE DEPLOY AUTOMATICO PARA PRODUCAO
# Versao: 1.0 - Sistema de Chamados

Write-Host "========================================" -ForegroundColor Cyan
Write-Host " DEPLOY AUTOMATICO PARA PRODUCAO" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Configuracoes
$SourcePath = $PSScriptRoot + "\.."
$PackageName = "chamados_system_producao_" + (Get-Date -Format "yyyyMMdd_HHmm")
$PackagePath = $SourcePath + "\deploy_packages\" + $PackageName
$ZipPath = $SourcePath + "\deploy_packages\" + $PackageName + ".zip"

Write-Host "[INFO] Iniciando processo de deploy..." -ForegroundColor Green
Write-Host "[INFO] Pasta origem: $SourcePath" -ForegroundColor Gray
Write-Host "[INFO] Pacote destino: $PackageName" -ForegroundColor Gray
Write-Host ""

# Criar diretorio de pacotes se nao existir
$DeployDir = $SourcePath + "\deploy_packages"
if (!(Test-Path $DeployDir)) {
    New-Item -ItemType Directory -Path $DeployDir -Force | Out-Null
    Write-Host "[1/8] Diretorio de deploy criado." -ForegroundColor Green
} else {
    Write-Host "[1/8] Diretorio de deploy ja existe." -ForegroundColor Yellow
}

# Criar diretorio temporario do pacote
if (Test-Path $PackagePath) {
    Remove-Item $PackagePath -Recurse -Force
}
New-Item -ItemType Directory -Path $PackagePath -Force | Out-Null
Write-Host "[2/8] Diretorio temporario criado." -ForegroundColor Green

# Copiar arquivos essenciais
Write-Host "[3/8] Copiando arquivos do sistema..." -ForegroundColor Green

$FoldersToInclude = @("config", "css", "public", "src", "tools")
foreach ($folder in $FoldersToInclude) {
    $sourceFolderPath = Join-Path $SourcePath $folder
    $destFolderPath = Join-Path $PackagePath $folder
    
    if (Test-Path $sourceFolderPath) {
        Copy-Item $sourceFolderPath $destFolderPath -Recurse -Force
        Write-Host "   ✓ $folder copiado" -ForegroundColor Gray
    } else {
        Write-Host "   ⚠ $folder nao encontrado" -ForegroundColor Yellow
    }
}

# Copiar apenas arquivos SQL necessarios
Write-Host "[4/8] Preparando banco de dados..." -ForegroundColor Green
$DatabasePath = Join-Path $PackagePath "database"
New-Item -ItemType Directory -Path $DatabasePath -Force | Out-Null

$SqlSource = Join-Path $SourcePath "database\install_sistema_completo.sql"
if (Test-Path $SqlSource) {
    Copy-Item $SqlSource $DatabasePath -Force
    Write-Host "   ✓ Script SQL principal copiado" -ForegroundColor Gray
} else {
    Write-Host "   ❌ Script SQL nao encontrado!" -ForegroundColor Red
    Write-Host "   Certifique-se que existe: database/install_sistema_completo.sql" -ForegroundColor Red
}

# Criar arquivo .htaccess otimizado
Write-Host "[5/8] Criando configuracoes de seguranca..." -ForegroundColor Green
$HtaccessContent = @"
# ========================================
# CONFIGURACOES DE SEGURANCA - PRODUCAO
# Sistema de Chamados v1.0
# ========================================

# Prevenir listagem de diretorios
Options -Indexes

# Bloquear acesso a arquivos de configuracao
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

<Files "*.env">
    Order allow,deny
    Deny from all
</Files>

# Bloquear acesso a arquivos de banco
<Files "*.sql">
    Order allow,deny
    Deny from all
</Files>

# Bloquear acesso a arquivos sensíveis
<FilesMatch "\.(log|bak|backup|old|tmp|temp)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Configuracoes de seguranca PHP
<IfModule mod_php7.c>
    php_flag display_errors Off
    php_flag log_errors On
    php_value max_execution_time 300
    php_value upload_max_filesize 10M
    php_value post_max_size 10M
</IfModule>

# Forcear HTTPS (descomente se disponivel)
# RewriteEngine On
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Headers de seguranca
<IfModule mod_headers.c>
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>
"@

$HtaccessPath = Join-Path $PackagePath ".htaccess"
Set-Content -Path $HtaccessPath -Value $HtaccessContent -Encoding UTF8
Write-Host "   ✓ Arquivo .htaccess criado" -ForegroundColor Gray

# Criar arquivo de configuracao de exemplo
Write-Host "[6/8] Criando template de configuracao..." -ForegroundColor Green
$ConfigContent = @"
<?php
/**
 * CONFIGURACAO PARA PRODUCAO
 * Sistema de Chamados v1.0
 * 
 * IMPORTANTE: Edite este arquivo com os dados do seu servidor!
 */

// ========================================
// CONFIGURACOES DO BANCO DE DADOS
// ========================================
`$host = 'localhost';                     // IP ou hostname do MySQL
`$database = 'chamados_db';               // Nome do banco de dados
`$username = 'SEU_USUARIO_MYSQL';         // Usuario do MySQL
`$password = 'SUA_SENHA_MYSQL_FORTE';     // Senha do MySQL

// ========================================
// CONFIGURACOES DE SEGURANCA
// ========================================
`$encryption_key = 'GERE_UMA_CHAVE_DE_32_CARACTERES_AQUI';  // Chave unica de seguranca

// ========================================
// CONFIGURACOES DO SISTEMA
// ========================================
`$base_url = 'https://seudominio.com/chamados_system/';  // URL completa do sistema
`$system_name = 'Sistema de Chamados - Producao';
`$system_email = 'sistema@seudominio.com';

// ========================================
// CONFIGURACOES DE EMAIL (OPCIONAL)
// ========================================
`$smtp_host = 'smtp.seudominio.com';      // Servidor SMTP
`$smtp_user = 'sistema@seudominio.com';   // Usuario SMTP
`$smtp_pass = 'senha_email_forte';        // Senha SMTP
`$smtp_port = 587;                        // Porta SMTP (587 ou 465)
`$smtp_secure = 'tls';                    // Seguranca (tls ou ssl)

// ========================================
// CONFIGURACOES DE DEBUG
// ========================================
// ATENCAO: Sempre manter FALSE em producao!
`$debug_mode = false;
`$show_errors = false;
`$log_errors = true;

// ========================================
// CONFIGURACOES DE SESSAO
// ========================================
`$session_timeout = 3600;                // 1 hora em segundos
`$max_login_attempts = 5;                 // Tentativas maximas de login
`$lockout_time = 900;                     // Tempo de bloqueio (15 minutos)

// ========================================
// TIMEZONE
// ========================================
date_default_timezone_set('America/Sao_Paulo');

?>
"@

$ConfigPath = Join-Path $PackagePath "config\config_producao_template.php"
Set-Content -Path $ConfigPath -Value $ConfigContent -Encoding UTF8
Write-Host "   ✓ Template de configuracao criado" -ForegroundColor Gray

# Criar guia de instalacao detalhado
Write-Host "[7/8] Criando guia de instalacao..." -ForegroundColor Green
$ReadmeContent = @"
# GUIA DE INSTALACAO - SISTEMA DE CHAMADOS

## 🚀 INSTALACAO RAPIDA (5 MINUTOS)

### 1. UPLOAD DOS ARQUIVOS
- Descompacte este arquivo no seu servidor web
- Copie todos os arquivos para a pasta raiz do seu dominio ou subpasta

### 2. CONFIGURACAO DO BANCO DE DADOS
- Acesse o phpMyAdmin do seu servidor
- Crie um banco chamado 'chamados_db' (ou nome de sua preferencia)
- Importe o arquivo: database/install_sistema_completo.sql

### 3. CONFIGURACAO DO SISTEMA
- Renomeie: config/config_producao_template.php -> config/config.php
- Edite config/config.php com os dados do seu servidor:
  * Host do MySQL (geralmente 'localhost')
  * Nome do banco de dados
  * Usuario e senha do MySQL
  * URL do sistema

### 4. TESTE DE FUNCIONAMENTO
- Acesse: http://seudominio.com/chamados_system/
- Teste a conexao: http://seudominio.com/chamados_system/public/test_connection.php

## 👥 USUARIOS PADRAO

### Desenvolvedor (Acesso Total):
- Usuario: Renan.duarte
- Senha: 123456

### Administradores:
- Usuario: Eduardo.lima / Senha: 123456
- Usuario: Jorge_gtz / Senha: 123456

## 🔧 SOLUCAO DE PROBLEMAS

### Erro de Conexao com Banco:
1. Verifique as credenciais em config/config.php
2. Confirme se o banco foi criado e importado
3. Teste com public/test_connection.php

### Pagina em Branco:
1. Verifique os logs de erro do servidor
2. Confirme as permissoes dos arquivos (755 para pastas, 644 para arquivos)
3. Verifique se o PHP está funcionando

### CSS nao Carrega:
1. Confirme que a pasta css/ foi copiada
2. Verifique as permissoes dos arquivos
3. Confirme a URL base em config/config.php

## 🔒 SEGURANCA

### Configuracoes Aplicadas:
- Arquivos .htaccess com protecoes
- Bloqueio de arquivos sensíveis
- Headers de seguranca configurados
- Debug desabilitado para producao

### Recomendacoes:
- Use sempre HTTPS em producao
- Mude as senhas padrao apos a instalacao
- Configure backups automaticos
- Monitore os logs de acesso

## 📞 SUPORTE

### Ferramentas de Diagnostico:
- tools/security_check.php - Verificacao de seguranca
- public/test_connection.php - Teste de conexao

### Logs do Sistema:
- Verifique os logs do servidor web
- Monitore logs de erro do PHP
- Use ferramentas do painel de controle

## ✅ CHECKLIST POS-INSTALACAO

- [ ] Sistema acessivel via navegador
- [ ] Login funcionando
- [ ] Banco de dados conectado
- [ ] Usuarios conseguem criar chamados
- [ ] Email funcionando (se configurado)
- [ ] Backups configurados
- [ ] Monitoramento ativo

SUCESSO! Seu sistema está pronto para producao! 🎉
"@

$ReadmePath = Join-Path $PackagePath "README_INSTALACAO.md"
Set-Content -Path $ReadmePath -Value $ReadmeContent -Encoding UTF8
Write-Host "   ✓ Guia de instalacao criado" -ForegroundColor Gray

# Criar arquivo ZIP
Write-Host "[8/8] Criando arquivo ZIP..." -ForegroundColor Green
try {
    Compress-Archive -Path "$PackagePath\*" -DestinationPath $ZipPath -CompressionLevel Optimal -Force
    Write-Host "   ✓ Arquivo ZIP criado: $PackageName.zip" -ForegroundColor Gray
    
    # Remover pasta temporaria
    Remove-Item $PackagePath -Recurse -Force
    Write-Host "   ✓ Limpeza concluida" -ForegroundColor Gray
} catch {
    Write-Host "   ❌ Erro ao criar ZIP: $($_.Exception.Message)" -ForegroundColor Red
}

# Resultados finais
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host " DEPLOY CONCLUIDO COM SUCESSO!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "📦 Pacote criado: $PackageName.zip" -ForegroundColor Yellow
Write-Host "📁 Localizacao: $DeployDir" -ForegroundColor Yellow
Write-Host ""
Write-Host "🚀 PROXIMOS PASSOS:" -ForegroundColor Green
Write-Host "1. Faca upload do arquivo ZIP para seu servidor" -ForegroundColor White
Write-Host "2. Extraia o conteudo na pasta web" -ForegroundColor White
Write-Host "3. Configure o banco e config.php" -ForegroundColor White
Write-Host "4. Acesse e teste o sistema" -ForegroundColor White
Write-Host ""
Write-Host "📋 Todos os detalhes estao no README_INSTALACAO.md" -ForegroundColor Cyan
Write-Host ""

# Abrir pasta do resultado
$OpenFolder = Read-Host "Deseja abrir a pasta com o pacote? (s/n)"
if ($OpenFolder.ToLower() -eq 's' -or $OpenFolder.ToLower() -eq 'sim') {
    Start-Process explorer.exe $DeployDir
}

Write-Host "Deploy finalizado! Pressione Enter para sair..."
Read-Host
