@echo off
echo ===================================================================
echo CONFIGURADOR HTTPS SIMPLIFICADO - SISTEMA ELUS
echo ===================================================================
echo.

echo [PASSO 1] Verificando XAMPP...
if not exist "C:\xampp\apache\conf\httpd.conf" (
    echo ❌ XAMPP nao encontrado em C:\xampp\
    pause
    exit /b 1
)
echo ✅ XAMPP encontrado

echo.
echo [PASSO 2] Fazendo backup...
if not exist "C:\xampp\apache\conf\backup" mkdir "C:\xampp\apache\conf\backup"
copy "C:\xampp\apache\conf\httpd.conf" "C:\xampp\apache\conf\backup\httpd.conf.bak" >nul 2>&1
echo ✅ Backup criado

echo.
echo [PASSO 3] Habilitando SSL...
powershell -ExecutionPolicy Bypass -Command "& {$content = Get-Content 'C:\xampp\apache\conf\httpd.conf'; $content = $content -replace '#LoadModule ssl_module', 'LoadModule ssl_module'; $content = $content -replace '#Include conf/extra/httpd-ssl.conf', 'Include conf/extra/httpd-ssl.conf'; Set-Content 'C:\xampp\apache\conf\httpd.conf' $content}"
echo ✅ SSL habilitado

echo.
echo [PASSO 4] Criando certificado...
if not exist "C:\xampp\apache\conf\ssl.crt" mkdir "C:\xampp\apache\conf\ssl.crt"
if not exist "C:\xampp\apache\conf\ssl.key" mkdir "C:\xampp\apache\conf\ssl.key"

cd /d "C:\xampp\apache\bin"
if exist "openssl.exe" (
    openssl.exe req -x509 -nodes -days 365 -newkey rsa:2048 -keyout ../conf/ssl.key/server.key -out ../conf/ssl.crt/server.crt -subj "/C=BR/ST=SP/L=SaoPaulo/O=ELUS/CN=localhost" >nul 2>&1
    if exist "../conf/ssl.crt/server.crt" (
        echo ✅ Certificado SSL criado
    ) else (
        echo ⚠️  Erro ao criar certificado
    )
) else (
    echo ⚠️  OpenSSL nao encontrado
)

echo.
echo ===================================================================
echo ✅ CONFIGURACAO CONCLUIDA!
echo ===================================================================
echo.
echo PROXIMOS PASSOS:
echo 1. Reinicie o Apache no XAMPP Control Panel
echo 2. Acesse: https://localhost/chamados_system/public/
echo 3. Aceite o certificado autoassinado no navegador
echo.
echo URLS DISPONIVEIS:
echo - HTTP:  http://localhost/chamados_system/public/
echo - HTTPS: https://localhost/chamados_system/public/
echo.
pause
