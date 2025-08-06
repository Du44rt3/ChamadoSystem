@echo off
echo ========================================
echo INSTALACAO DO SISTEMA DE ANEXOS DE IMAGENS
echo ========================================
echo.

echo 1. Verificando diretorio de uploads...
if not exist "uploads\anexos" (
    mkdir "uploads\anexos"
    echo   - Diretorio uploads\anexos criado
) else (
    echo   - Diretorio uploads\anexos ja existe
)

echo.
echo 2. Copiando arquivo de protecao .htaccess...
if not exist "uploads\.htaccess" (
    echo # Proteção do diretório de uploads > "uploads\.htaccess"
    echo # Apenas imagens são permitidas para visualização >> "uploads\.htaccess"
    echo. >> "uploads\.htaccess"
    echo # Negar acesso por padrão >> "uploads\.htaccess"
    echo Order Deny,Allow >> "uploads\.htaccess"
    echo Deny from all >> "uploads\.htaccess"
    echo. >> "uploads\.htaccess"
    echo # Permitir apenas imagens >> "uploads\.htaccess"
    echo ^<FilesMatch "\.(jpg^|jpeg^|png^|gif^|webp^|bmp)$"^> >> "uploads\.htaccess"
    echo     Order Allow,Deny >> "uploads\.htaccess"
    echo     Allow from all >> "uploads\.htaccess"
    echo ^</FilesMatch^> >> "uploads\.htaccess"
    echo. >> "uploads\.htaccess"
    echo # Prevenir execução de scripts >> "uploads\.htaccess"
    echo ^<FilesMatch "\.(php^|php3^|php4^|php5^|phtml^|pl^|py^|jsp^|asp^|sh^|cgi)$"^> >> "uploads\.htaccess"
    echo     Order Deny,Allow >> "uploads\.htaccess"
    echo     Deny from all >> "uploads\.htaccess"
    echo ^</FilesMatch^> >> "uploads\.htaccess"
    echo. >> "uploads\.htaccess"
    echo # Desabilitar listagem de diretório >> "uploads\.htaccess"
    echo Options -Indexes >> "uploads\.htaccess"
    echo   - Arquivo .htaccess criado
) else (
    echo   - Arquivo .htaccess ja existe
)

echo.
echo 3. Aplicando estrutura de banco de dados...
c:\xampp\mysql\bin\mysql.exe -u root chamados_db < "database\anexos_images.sql"
if %ERRORLEVEL% EQU 0 (
    echo   - Tabela chamado_anexos criada com sucesso
    echo   - Triggers de historico configurados
) else (
    echo   - Erro ao criar estrutura de banco de dados
    echo   - Verifique se o MySQL esta rodando e o banco chamados_db existe
)

echo.
echo 4. Definindo permissoes de diretorio...
icacls "uploads\anexos" /grant "IIS_IUSRS:(OI)(CI)(F)" /T >nul 2>&1
icacls "uploads\anexos" /grant "IUSR:(OI)(CI)(F)" /T >nul 2>&1
echo   - Permissoes configuradas para IIS/Apache

echo.
echo ========================================
echo INSTALACAO CONCLUIDA!
echo ========================================
echo.
echo Funcionalidades instaladas:
echo - Upload de multiplas imagens
echo - Galeria responsiva de anexos
echo - Visualizacao em modal
echo - Download e exclusao de anexos
echo - Historico automatico
echo - Protecao de seguranca
echo.
echo Para testar, acesse:
echo http://localhost/chamados_system0/tests/test_anexos_system.php
echo.
pause
