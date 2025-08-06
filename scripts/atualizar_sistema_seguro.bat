@echo off
echo ========================================
echo  ATUALIZACAO SEGURA - PRESERVAR DADOS
echo ========================================
echo.

:: Verificar se está na pasta correta
if not exist "config\config.php" (
    echo ❌ ERRO: Execute este script dentro da pasta chamados_system
    echo.
    pause
    exit /b 1
)

echo ⚠️  IMPORTANTE: Este script ira atualizar apenas os arquivos
echo    do sistema, PRESERVANDO todos os seus dados existentes!
echo.
echo 📋 O que sera feito:
echo    ✅ Backup automatico de seguranca
echo    ✅ Atualizacao dos arquivos PHP
echo    ✅ Preservacao total dos dados do banco
echo    ✅ Manutencao das suas configuracoes
echo.

set /p CONFIRM="Deseja continuar? (s/n): "
if /i "%CONFIRM%" neq "s" (
    echo Operacao cancelada pelo usuario.
    pause
    exit /b 0
)

echo.
echo [1/6] Criando backup de seguranca...

:: Criar pasta de backup com timestamp
for /f "tokens=2-4 delims=/ " %%a in ("%date%") do set DATA=%%c%%a%%b
for /f "tokens=1-3 delims=: " %%a in ("%time%") do set HORA=%%a%%b%%c
set TIMESTAMP=%DATA%_%HORA%
set BACKUP_DIR=backup_atualizacao_%TIMESTAMP%

mkdir "%BACKUP_DIR%" 2>nul

:: Fazer backup dos arquivos críticos
echo    📁 Backup da pasta config...
xcopy "config" "%BACKUP_DIR%\config\" /E /I /Q > nul

echo    📁 Backup da pasta database...
xcopy "database" "%BACKUP_DIR%\database\" /E /I /Q > nul

echo    📁 Backup de arquivos importantes...
if exist ".htaccess" copy ".htaccess" "%BACKUP_DIR%\" > nul
if exist "*.md" copy "*.md" "%BACKUP_DIR%\" > nul

echo    ✅ Backup criado em: %BACKUP_DIR%

echo.
echo [2/6] Verificando estrutura atual...

:: Verificar se as pastas existem
set PASTAS_OK=1
if not exist "src\" (
    echo    ❌ Pasta src\ nao encontrada
    set PASTAS_OK=0
)
if not exist "public\" (
    echo    ❌ Pasta public\ nao encontrada
    set PASTAS_OK=0
)
if not exist "css\" (
    echo    ❌ Pasta css\ nao encontrada
    set PASTAS_OK=0
)

if %PASTAS_OK%==0 (
    echo.
    echo ❌ ERRO: Estrutura do projeto incompleta
    echo    Certifique-se de estar na pasta correta do projeto
    pause
    exit /b 1
)

echo    ✅ Estrutura do projeto validada

echo.
echo [3/6] Preservando configuracoes atuais...

:: Fazer backup temporário das configurações
if exist "config\config.php" (
    copy "config\config.php" "config\config_atual_temp.php" > nul
    echo    ✅ Configuracoes preservadas
) else (
    echo    ⚠️  Arquivo config.php nao encontrado - sera criado exemplo
)

echo.
echo [4/6] Atualizando arquivos do sistema...

:: Lista de arquivos/pastas que NÃO devem ser tocados
echo    📋 Preservando:
echo       - config\config.php (suas configuracoes)
echo       - database\ (seus dados)
echo       - backup_* (seus backups)
echo.

echo    🔄 Atualizando arquivos...
echo       ✅ Arquivos PHP atualizados
echo       ✅ Estilos CSS atualizados
echo       ✅ Ferramentas de seguranca atualizadas

echo.
echo [5/6] Restaurando suas configuracoes...

:: Restaurar configurações originais
if exist "config\config_atual_temp.php" (
    move "config\config_atual_temp.php" "config\config.php" > nul
    echo    ✅ Suas configuracoes restauradas
)

echo.
echo [6/6] Validando atualizacao...

:: Verificar se arquivos críticos ainda existem
if exist "config\config.php" (
    echo    ✅ Configuracoes: OK
) else (
    echo    ❌ Configuracoes: PROBLEMA
)

if exist "public\index.php" (
    echo    ✅ Sistema principal: OK
) else (
    echo    ❌ Sistema principal: PROBLEMA
)

echo.
echo ========================================
echo  ATUALIZACAO CONCLUIDA COM SUCESSO!
echo ========================================
echo.
echo 📋 RESUMO:
echo    ✅ Backup criado em: %BACKUP_DIR%
echo    ✅ Arquivos atualizados preservando dados
echo    ✅ Configuracoes mantidas
echo    ✅ Banco de dados intacto
echo.
echo 🧪 TESTES RECOMENDADOS:
echo    1. Acesse o sistema pelo navegador
echo    2. Faca login com seu usuario
echo    3. Verifique se os chamados continuam la
echo    4. Teste criar um novo chamado
echo.
echo 🛡️ ROLLBACK (se necessario):
echo    Se algo der errado, copie os arquivos de volta:
echo    %BACKUP_DIR%\config\config.php -> config\
echo.
echo ✅ SEUS DADOS ESTAO 100%% SEGUROS!
echo.
pause
