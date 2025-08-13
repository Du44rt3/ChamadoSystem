@echo off
echo ===================================================================
echo ATIVAR/DESATIVAR HTTPS - SISTEMA ELUS
echo ===================================================================
echo.

echo Status atual do HTTPS:
findstr /C:"# RewriteCond" .htaccess >nul
if %errorlevel%==0 (
    echo ❌ HTTPS DESATIVADO
    set status=off
) else (
    echo ✅ HTTPS ATIVADO
    set status=on
)

echo.
echo O que deseja fazer?
echo [1] Ativar HTTPS
echo [2] Desativar HTTPS
echo [3] Verificar status
echo [4] Sair
echo.

set /p opcao="Escolha uma opcao (1-4): "

if "%opcao%"=="1" goto ativar
if "%opcao%"=="2" goto desativar
if "%opcao%"=="3" goto status
if "%opcao%"=="4" goto sair

:ativar
echo.
echo ===================================================================
echo ATIVANDO HTTPS...
echo ===================================================================

echo [1/4] Fazendo backup...
copy .htaccess .htaccess.backup.%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2% >nul 2>&1

echo [2/4] Ativando redirecionamento HTTPS...
powershell -Command "(Get-Content .htaccess) -replace '# RewriteCond', 'RewriteCond' | Set-Content .htaccess"
powershell -Command "(Get-Content .htaccess) -replace '# RewriteRule', 'RewriteRule' | Set-Content .htaccess"

echo [3/4] Ativando cookies seguros...
powershell -Command "(Get-Content .htaccess) -replace '# php_flag session.cookie_secure On', 'php_flag session.cookie_secure On' | Set-Content .htaccess"
powershell -Command "(Get-Content .htaccess) -replace 'php_flag session.cookie_secure Off', '# php_flag session.cookie_secure Off' | Set-Content .htaccess"

echo [4/4] Ativando headers HSTS...
powershell -Command "(Get-Content .htaccess) -replace '# Header always set Strict-Transport-Security', 'Header always set Strict-Transport-Security' | Set-Content .htaccess"

echo.
echo ✅ HTTPS ATIVADO COM SUCESSO!
echo.
echo Proximos passos:
echo 1. Certifique-se de ter certificado SSL configurado
echo 2. Reinicie Apache se necessario
echo 3. Teste: https://localhost/chamados_system/public/
echo.
pause
goto inicio

:desativar
echo.
echo ===================================================================
echo DESATIVANDO HTTPS...
echo ===================================================================

echo [1/4] Fazendo backup...
copy .htaccess .htaccess.backup.%date:~-4,4%%date:~-10,2%%date:~-7,2%_%time:~0,2%%time:~3,2%%time:~6,2% >nul 2>&1

echo [2/4] Desativando redirecionamento HTTPS...
powershell -Command "(Get-Content .htaccess) -replace 'RewriteCond %%{HTTPS}', '# RewriteCond %%{HTTPS}' | Set-Content .htaccess"
powershell -Command "(Get-Content .htaccess) -replace 'RewriteRule \^(.*)\$ https:', '# RewriteRule ^(.*)$ https:' | Set-Content .htaccess"

echo [3/4] Desativando cookies seguros...
powershell -Command "(Get-Content .htaccess) -replace 'php_flag session.cookie_secure On', '# php_flag session.cookie_secure On' | Set-Content .htaccess"
powershell -Command "(Get-Content .htaccess) -replace '# php_flag session.cookie_secure Off', 'php_flag session.cookie_secure Off' | Set-Content .htaccess"

echo [4/4] Desativando headers HSTS...
powershell -Command "(Get-Content .htaccess) -replace 'Header always set Strict-Transport-Security', '# Header always set Strict-Transport-Security' | Set-Content .htaccess"

echo.
echo ✅ HTTPS DESATIVADO COM SUCESSO!
echo.
echo Agora você pode usar HTTP normalmente:
echo - http://localhost/chamados_system/public/
echo - http://192.168.0.173/chamados_system/public/
echo.
pause
goto inicio

:status
echo.
echo ===================================================================
echo STATUS DO HTTPS
echo ===================================================================
echo.

findstr /C:"# RewriteCond" .htaccess >nul
if %errorlevel%==0 (
    echo Status: ❌ HTTPS DESATIVADO
    echo.
    echo URLs funcionais:
    echo - http://localhost/chamados_system/public/
    echo - http://192.168.0.173/chamados_system/public/
) else (
    echo Status: ✅ HTTPS ATIVADO
    echo.
    echo URLs funcionais:
    echo - https://localhost/chamados_system/public/
    echo - Redirecionamento HTTP -> HTTPS ativo
)

echo.
echo Configuracoes detectadas:
findstr /C:"RewriteCond" .htaccess | findstr /V /C:"#"
findstr /C:"session.cookie_secure" .htaccess | findstr /V /C:"#"
findstr /C:"Strict-Transport-Security" .htaccess | findstr /V /C:"#"

echo.
pause
goto inicio

:inicio
echo.
echo Deseja fazer mais alguma alteracao? (S/N)
set /p continuar="Opcao: "
if /i "%continuar%"=="S" goto inicio
if /i "%continuar%"=="N" goto sair
goto inicio

:sair
echo.
echo Saindo...
exit /b 0
