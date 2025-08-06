# Configurador HTTPS para XAMPP - Sistema de Chamados ELUS
# Execute como Administrador: PowerShell -ExecutionPolicy Bypass -File configurar_https_xampp.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "   CONFIGURADOR HTTPS XAMPP - ELUS" -ForegroundColor Yellow
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Verificar se está executando como administrador
if (-NOT ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Host "ERRO: Execute este script como Administrador!" -ForegroundColor Red
    Write-Host ""
    Write-Host "1. Abra PowerShell como Administrador"
    Write-Host "2. Execute: PowerShell -ExecutionPolicy Bypass -File configurar_https_xampp.ps1"
    Read-Host "Pressione Enter para sair"
    exit 1
}

$xamppPath = "C:\xampp"
$apachePath = "$xamppPath\apache"

# Verificar XAMPP
Write-Host "[1/7] Verificando XAMPP..." -ForegroundColor Green
if (!(Test-Path "$apachePath\bin\httpd.exe")) {
    Write-Host "ERRO: XAMPP não encontrado em $xamppPath" -ForegroundColor Red
    Write-Host "Por favor, instale o XAMPP primeiro." -ForegroundColor Yellow
    Read-Host "Pressione Enter para sair"
    exit 1
}
Write-Host "✓ XAMPP encontrado" -ForegroundColor Green

# Parar Apache
Write-Host ""
Write-Host "[2/7] Parando Apache..." -ForegroundColor Green
try {
    & "$apachePath\bin\httpd.exe" -k stop 2>$null
    Start-Sleep -Seconds 3
    Write-Host "✓ Apache parado" -ForegroundColor Green
} catch {
    Write-Host "⚠ Apache pode não estar rodando" -ForegroundColor Yellow
}

# Backup httpd.conf
Write-Host ""
Write-Host "[3/7] Configurando httpd.conf..." -ForegroundColor Green
$httpdConf = "$apachePath\conf\httpd.conf"
$httpdBackup = "$apachePath\conf\httpd.conf.backup"

if (!(Test-Path $httpdBackup)) {
    Copy-Item $httpdConf $httpdBackup
    Write-Host "✓ Backup do httpd.conf criado" -ForegroundColor Green
}

# Habilitar SSL no httpd.conf
$content = Get-Content $httpdConf
$content = $content -replace '#LoadModule ssl_module modules/mod_ssl.so', 'LoadModule ssl_module modules/mod_ssl.so'
$content = $content -replace '#Include conf/extra/httpd-ssl.conf', 'Include conf/extra/httpd-ssl.conf'
$content | Out-File -FilePath $httpdConf -Encoding ASCII
Write-Host "✓ SSL habilitado no httpd.conf" -ForegroundColor Green

# Criar diretórios SSL
Write-Host ""
Write-Host "[4/7] Preparando diretórios SSL..." -ForegroundColor Green
$sslKeyDir = "$apachePath\conf\ssl.key"
$sslCrtDir = "$apachePath\conf\ssl.crt"

if (!(Test-Path $sslKeyDir)) { New-Item -ItemType Directory -Path $sslKeyDir -Force | Out-Null }
if (!(Test-Path $sslCrtDir)) { New-Item -ItemType Directory -Path $sslCrtDir -Force | Out-Null }
Write-Host "✓ Diretórios SSL preparados" -ForegroundColor Green

# Gerar certificado SSL
Write-Host ""
Write-Host "[5/7] Gerando certificado SSL..." -ForegroundColor Green
Set-Location $apachePath

# Gerar chave privada
& "bin\openssl.exe" genrsa -out "conf\ssl.key\server.key" 2048 2>$null
Write-Host "✓ Chave privada gerada" -ForegroundColor Green

# Criar configuração do certificado
$sslConfig = @"
[req]
distinguished_name = req_distinguished_name
x509_extensions = v3_req
prompt = no

[req_distinguished_name]
C = BR
ST = SP
L = Sao Paulo
O = Grupo Elus
OU = Facilities - Sistema de Chamados
CN = localhost

[v3_req]
keyUsage = digitalSignature, keyEncipherment
extendedKeyUsage = serverAuth
subjectAltName = @alt_names

[alt_names]
DNS.1 = localhost
DNS.2 = *.localhost
IP.1 = 127.0.0.1
"@

$sslConfig | Out-File -FilePath "temp_ssl.conf" -Encoding ASCII

# Gerar certificado
& "bin\openssl.exe" req -new -x509 -key "conf\ssl.key\server.key" -out "conf\ssl.crt\server.crt" -days 365 -config "temp_ssl.conf" 2>$null
Remove-Item "temp_ssl.conf" -Force
Write-Host "✓ Certificado SSL gerado (válido por 365 dias)" -ForegroundColor Green

# Configurar httpd-ssl.conf
Write-Host ""
Write-Host "[6/7] Configurando httpd-ssl.conf..." -ForegroundColor Green
$httpdSslConf = "$apachePath\conf\extra\httpd-ssl.conf"
$httpdSslBackup = "$apachePath\conf\extra\httpd-ssl.conf.backup"

if (!(Test-Path $httpdSslBackup)) {
    Copy-Item $httpdSslConf $httpdSslBackup
}

$sslContent = Get-Content $httpdSslConf
$sslContent = $sslContent -replace 'ServerName www.example.com:443', 'ServerName localhost:443'
$sslContent = $sslContent -replace 'DocumentRoot ".*?"', 'DocumentRoot "C:/xampp/htdocs"'
$sslContent | Out-File -FilePath $httpdSslConf -Encoding ASCII
Write-Host "✓ httpd-ssl.conf configurado" -ForegroundColor Green

# Iniciar Apache
Write-Host ""
Write-Host "[7/7] Iniciando Apache com SSL..." -ForegroundColor Green
try {
    & "$apachePath\bin\httpd.exe" -k start
    Start-Sleep -Seconds 2
    
    # Verificar se Apache está rodando
    $process = Get-Process -Name "httpd" -ErrorAction SilentlyContinue
    if ($process) {
        Write-Host "✓ Apache iniciado com sucesso!" -ForegroundColor Green
    } else {
        throw "Apache não está rodando"
    }
} catch {
    Write-Host "✗ Erro ao iniciar Apache. Verifique os logs em: $apachePath\logs\error.log" -ForegroundColor Red
    Read-Host "Pressione Enter para sair"
    exit 1
}

# Configurar .env
Write-Host ""
Write-Host "Atualizando arquivo .env..." -ForegroundColor Green
$envPath = "C:\xampp\htdocs\chamados_system\.env"
if (Test-Path $envPath) {
    $envContent = Get-Content $envPath
    $envContent = $envContent -replace 'SESSION_SECURE=false', 'SESSION_SECURE=true'
    $envContent = $envContent -replace 'APP_URL=http://localhost', 'APP_URL=https://localhost'
    $envContent | Out-File -FilePath $envPath -Encoding ASCII
    Write-Host "✓ Arquivo .env atualizado" -ForegroundColor Green
} else {
    Write-Host "⚠ Arquivo .env não encontrado. Configure manualmente:" -ForegroundColor Yellow
    Write-Host "  SESSION_SECURE=true" -ForegroundColor Yellow
    Write-Host "  APP_URL=https://localhost/chamados_system" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "        CONFIGURAÇÃO CONCLUÍDA!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "✓ HTTPS configurado no XAMPP" -ForegroundColor Green
Write-Host "✓ Certificado SSL gerado" -ForegroundColor Green
Write-Host "✓ Apache rodando com SSL" -ForegroundColor Green
Write-Host "✓ Arquivo .env atualizado" -ForegroundColor Green
Write-Host ""
Write-Host "PRÓXIMOS PASSOS:" -ForegroundColor Yellow
Write-Host ""
Write-Host "1. Acesse: https://localhost/chamados_system/public" -ForegroundColor White
Write-Host "2. Aceite o aviso de certificado auto-assinado" -ForegroundColor White
Write-Host "3. Verifique se o cadeado aparece na barra de endereços" -ForegroundColor White
Write-Host ""
Write-Host "OBSERVAÇÃO:" -ForegroundColor Yellow
Write-Host "O navegador mostrará 'Não seguro' porque é um" -ForegroundColor Gray
Write-Host "certificado auto-assinado (normal para desenvolvimento)." -ForegroundColor Gray
Write-Host ""
Write-Host "Para aceitar o certificado:" -ForegroundColor Yellow
Write-Host "Chrome/Edge: Clique em 'Avançado' > 'Prosseguir para localhost'" -ForegroundColor Gray
Write-Host "Firefox: Clique em 'Avançado' > 'Aceitar o risco e continuar'" -ForegroundColor Gray
Write-Host ""

# Tentar abrir no navegador
Write-Host "Deseja abrir o sistema no navegador agora? (S/N): " -ForegroundColor Yellow -NoNewline
$response = Read-Host
if ($response -eq "S" -or $response -eq "s" -or $response -eq "sim") {
    Start-Process "https://localhost/chamados_system/public"
    Write-Host "✓ Navegador aberto" -ForegroundColor Green
}

Write-Host ""
Read-Host "Pressione Enter para finalizar"
