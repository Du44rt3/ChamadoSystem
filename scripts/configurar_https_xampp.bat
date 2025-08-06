@echo off
echo ========================================
echo   CONFIGURADOR AUTOMATICO HTTPS XAMPP
echo   Sistema de Chamados ELUS
echo ========================================
echo.

REM Verificar se está executando como administrador
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo ERRO: Execute este script como Administrador!
    echo.
    echo 1. Clique com botao direito no arquivo
    echo 2. Selecione "Executar como administrador"
    pause
    exit /b 1
)

echo [1/6] Verificando XAMPP...
if not exist "C:\xampp\apache\bin\httpd.exe" (
    echo ERRO: XAMPP nao encontrado em C:\xampp\
    echo Por favor, instale o XAMPP primeiro.
    pause
    exit /b 1
)
echo ✓ XAMPP encontrado

echo.
echo [2/6] Parando Apache...
C:\xampp\apache\bin\httpd.exe -k stop >nul 2>&1
timeout /t 2 /nobreak >nul
echo ✓ Apache parado

echo.
echo [3/6] Configurando httpd.conf...
REM Backup do arquivo original
if not exist "C:\xampp\apache\conf\httpd.conf.backup" (
    copy "C:\xampp\apache\conf\httpd.conf" "C:\xampp\apache\conf\httpd.conf.backup" >nul
    echo ✓ Backup criado
)

REM Habilitar SSL
powershell -Command "(gc 'C:\xampp\apache\conf\httpd.conf') -replace '#LoadModule ssl_module modules/mod_ssl.so', 'LoadModule ssl_module modules/mod_ssl.so' | Out-File -encoding ASCII 'C:\xampp\apache\conf\httpd.conf'"
powershell -Command "(gc 'C:\xampp\apache\conf\httpd.conf') -replace '#Include conf/extra/httpd-ssl.conf', 'Include conf/extra/httpd-ssl.conf' | Out-File -encoding ASCII 'C:\xampp\apache\conf\httpd.conf'"
echo ✓ SSL habilitado no httpd.conf

echo.
echo [4/6] Gerando certificado SSL...
cd /d "C:\xampp\apache"

REM Criar diretórios se não existirem
if not exist "conf\ssl.key" mkdir "conf\ssl.key"
if not exist "conf\ssl.crt" mkdir "conf\ssl.crt"

REM Gerar chave e certificado
bin\openssl.exe genrsa -out conf\ssl.key\server.key 2048 2>nul
echo ✓ Chave privada gerada

REM Criar arquivo de configuração para certificado
echo [req] > temp_ssl.conf
echo distinguished_name = req_distinguished_name >> temp_ssl.conf
echo x509_extensions = v3_req >> temp_ssl.conf
echo prompt = no >> temp_ssl.conf
echo [req_distinguished_name] >> temp_ssl.conf
echo C = BR >> temp_ssl.conf
echo ST = SP >> temp_ssl.conf
echo L = Sao Paulo >> temp_ssl.conf
echo O = Grupo Elus >> temp_ssl.conf
echo OU = Facilities >> temp_ssl.conf
echo CN = localhost >> temp_ssl.conf
echo [v3_req] >> temp_ssl.conf
echo keyUsage = keyEncipherment, dataEncipherment >> temp_ssl.conf
echo extendedKeyUsage = serverAuth >> temp_ssl.conf
echo subjectAltName = @alt_names >> temp_ssl.conf
echo [alt_names] >> temp_ssl.conf
echo DNS.1 = localhost >> temp_ssl.conf
echo DNS.2 = 127.0.0.1 >> temp_ssl.conf
echo IP.1 = 127.0.0.1 >> temp_ssl.conf

bin\openssl.exe req -new -x509 -key conf\ssl.key\server.key -out conf\ssl.crt\server.crt -days 365 -config temp_ssl.conf 2>nul
del temp_ssl.conf
echo ✓ Certificado SSL gerado

echo.
echo [5/6] Configurando httpd-ssl.conf...
REM Backup do SSL config
if not exist "C:\xampp\apache\conf\extra\httpd-ssl.conf.backup" (
    copy "C:\xampp\apache\conf\extra\httpd-ssl.conf" "C:\xampp\apache\conf\extra\httpd-ssl.conf.backup" >nul
)

REM Configurar SSL para localhost
powershell -Command "(gc 'C:\xampp\apache\conf\extra\httpd-ssl.conf') -replace 'ServerName www.example.com:443', 'ServerName localhost:443' | Out-File -encoding ASCII 'C:\xampp\apache\conf\extra\httpd-ssl.conf'"
echo ✓ SSL configurado para localhost

echo.
echo [6/6] Iniciando Apache com SSL...
C:\xampp\apache\bin\httpd.exe -k start
if %errorlevel% equ 0 (
    echo ✓ Apache iniciado com sucesso!
) else (
    echo ✗ Erro ao iniciar Apache. Verifique os logs.
    pause
    exit /b 1
)

echo.
echo ========================================
echo           CONFIGURACAO CONCLUIDA!
echo ========================================
echo.
echo ✓ HTTPS configurado no XAMPP
echo ✓ Certificado SSL gerado
echo ✓ Apache rodando com SSL
echo.
echo PROXIMOS PASSOS:
echo.
echo 1. Acesse: https://localhost/chamados_system/public
echo 2. Aceite o aviso de certificado auto-assinado
echo 3. Configure SESSION_SECURE=true no arquivo .env
echo.
echo OBSERVACAO: O navegador vai mostrar "Nao seguro"
echo porque e um certificado auto-assinado (normal
echo para desenvolvimento local).
echo.
pause
