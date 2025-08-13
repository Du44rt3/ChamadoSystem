@echo off
echo ===================================================================
echo PREPARAR SISTEMA PARA PRODUCAO HTTPS - ELUS
echo ===================================================================
echo.

echo [INFO] Este script prepara o sistema para HTTPS em producao
echo [INFO] Certifique-se de ter um certificado SSL valido!
echo.

echo Opcoes disponiveis:
echo.
echo [1] CloudFlare (Recomendado - Mais facil)
echo [2] Let's Encrypt (Gratuito - Requer SSH)
echo [3] Certificado comercial (Pago - Maximo suporte)
echo [4] Preparar arquivos para upload
echo [5] Sair
echo.

set /p opcao="Escolha uma opcao (1-5): "

if "%opcao%"=="1" goto cloudflare
if "%opcao%"=="2" goto letsencrypt
if "%opcao%"=="3" goto comercial
if "%opcao%"=="4" goto preparar
if "%opcao%"=="5" goto sair

:cloudflare
echo.
echo ===================================================================
echo CONFIGURACAO CLOUDFLARE
echo ===================================================================
echo.
echo Passos para configurar CloudFlare:
echo.
echo 1. Acesse: https://cloudflare.com
echo 2. Crie uma conta gratuita
echo 3. Adicione seu dominio (ex: elus.com.br)
echo 4. Altere os nameservers do seu dominio para:
echo    - NS1: nome1.cloudflare.com
echo    - NS2: nome2.cloudflare.com
echo 5. No painel CloudFlare:
echo    - SSL/TLS ^> Edge Certificates ^> Always Use HTTPS: ON
echo    - SSL Mode: "Full (strict)"
echo    - HSTS: Enable
echo.
echo VANTAGENS:
echo - SSL automatico e gratuito
echo - CDN global (site mais rapido)
echo - Protecao contra ataques DDoS
echo - Cache inteligente
echo.
pause
goto menu

:letsencrypt
echo.
echo ===================================================================
echo CONFIGURACAO LET'S ENCRYPT
echo ===================================================================
echo.
echo Comandos para servidor Linux:
echo.
echo # Ubuntu/Debian:
echo sudo apt update
echo sudo apt install certbot python3-certbot-apache
echo.
echo # Gerar certificado:
echo sudo certbot --apache -d seudominio.com.br
echo.
echo # Renovacao automatica:
echo sudo crontab -e
echo # Adicione: 0 2 * * * /usr/bin/certbot renew --quiet
echo.
echo REQUISITOS:
echo - Servidor Linux com SSH
echo - Dominio apontando para o servidor
echo - Apache/Nginx configurado
echo.
pause
goto menu

:comercial
echo.
echo ===================================================================
echo CERTIFICADO COMERCIAL
echo ===================================================================
echo.
echo Fornecedores recomendados:
echo.
echo 1. SSL.com - A partir de R$ 50/ano
echo 2. DigiCert - A partir de R$ 200/ano
echo 3. Sectigo - A partir de R$ 80/ano
echo 4. GoDaddy - A partir de R$ 100/ano
echo.
echo Tipos de certificado:
echo - DV (Domain Validation): Basico, validacao automatica
echo - OV (Organization Validation): Validacao da empresa
echo - EV (Extended Validation): Barra verde, maxima confianca
echo.
echo PROCESSO:
echo 1. Compre o certificado
echo 2. Gere CSR no seu servidor
echo 3. Valide o dominio/empresa
echo 4. Baixe e instale o certificado
echo.
pause
goto menu

:preparar
echo.
echo ===================================================================
echo PREPARAR ARQUIVOS PARA PRODUCAO
echo ===================================================================
echo.

echo [1/5] Criando versao de producao...
if not exist "producao" mkdir "producao"

echo [2/5] Copiando arquivos essenciais...
xcopy "public" "producao\public" /E /I /Y >nul 2>&1
xcopy "src" "producao\src" /E /I /Y >nul 2>&1
xcopy "config" "producao\config" /E /I /Y >nul 2>&1
xcopy "css" "producao\css" /E /I /Y >nul 2>&1
xcopy "assets" "producao\assets" /E /I /Y >nul 2>&1
xcopy ".htaccess" "producao\.htaccess" /Y >nul 2>&1
xcopy ".env.example" "producao\.env.example" /Y >nul 2>&1

echo [3/5] Criando estrutura de logs...
if not exist "producao\logs" mkdir "producao\logs"
echo. > "producao\logs\.gitkeep"

echo [4/5] Criando pasta de uploads...
if not exist "producao\uploads" mkdir "producao\uploads"
echo. > "producao\uploads\.gitkeep"

echo [5/5] Criando arquivo de configuracao...
echo # CONFIGURACAO DE PRODUCAO > "producao\LEIA-ME-PRODUCAO.txt"
echo. >> "producao\LEIA-ME-PRODUCAO.txt"
echo 1. Configure o arquivo .env com dados reais >> "producao\LEIA-ME-PRODUCAO.txt"
echo 2. Ajuste permissoes das pastas: >> "producao\LEIA-ME-PRODUCAO.txt"
echo    - logs/ deve ter permissao de escrita >> "producao\LEIA-ME-PRODUCAO.txt"
echo    - uploads/ deve ter permissao de escrita >> "producao\LEIA-ME-PRODUCAO.txt"
echo 3. Configure certificado SSL >> "producao\LEIA-ME-PRODUCAO.txt"
echo 4. Teste todas as funcionalidades >> "producao\LEIA-ME-PRODUCAO.txt"
echo. >> "producao\LEIA-ME-PRODUCAO.txt"
echo URLs IMPORTANTES: >> "producao\LEIA-ME-PRODUCAO.txt"
echo - Sistema: https://seudominio.com.br/public/ >> "producao\LEIA-ME-PRODUCAO.txt"
echo - Teste: https://seudominio.com.br/tools/debug/https_test.php >> "producao\LEIA-ME-PRODUCAO.txt"

echo.
echo ✅ Arquivos preparados na pasta 'producao/'
echo ✅ Leia o arquivo LEIA-ME-PRODUCAO.txt
echo.
echo Proximos passos:
echo 1. Configure certificado SSL no servidor
echo 2. Faca upload da pasta 'producao/' para o servidor
echo 3. Configure permissoes e .env
echo 4. Teste o sistema
echo.
pause
goto menu

:menu
echo.
goto inicio

:sair
echo.
echo Saindo...
exit /b 0

:inicio
echo.
echo Deseja fazer mais alguma configuracao? (S/N)
set /p continuar="Opcao: "
if /i "%continuar%"=="S" goto inicio
if /i "%continuar%"=="N" goto sair
goto inicio
