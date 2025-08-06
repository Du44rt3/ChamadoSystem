@echo off
echo ========================================
echo  CRIANDO PACOTE PARA PRODUCAO
echo ========================================
echo.

:: Definir diretórios
set SOURCE_DIR=%~dp0
set PACKAGE_DIR=%SOURCE_DIR%deploy_package
set ZIP_NAME=chamados_system_producao.zip

:: Criar diretório temporário
echo [1/6] Criando diretorio temporario...
if exist "%PACKAGE_DIR%" rmdir /s /q "%PACKAGE_DIR%"
mkdir "%PACKAGE_DIR%"

:: Copiar arquivos essenciais
echo [2/6] Copiando arquivos do sistema...
xcopy "%SOURCE_DIR%config" "%PACKAGE_DIR%\config\" /E /I /Q > nul
xcopy "%SOURCE_DIR%css" "%PACKAGE_DIR%\css\" /E /I /Q > nul
xcopy "%SOURCE_DIR%public" "%PACKAGE_DIR%\public\" /E /I /Q > nul
xcopy "%SOURCE_DIR%src" "%PACKAGE_DIR%\src\" /E /I /Q > nul
xcopy "%SOURCE_DIR%tools" "%PACKAGE_DIR%\tools\" /E /I /Q > nul

:: Copiar apenas o arquivo SQL necessário
echo [3/6] Copiando banco de dados...
mkdir "%PACKAGE_DIR%\database"
copy "%SOURCE_DIR%database\install_sistema_completo.sql" "%PACKAGE_DIR%\database\" > nul

:: Criar arquivo .htaccess de segurança
echo [4/6] Criando configuracoes de seguranca...
(
echo # Configuracoes de Seguranca para Producao
echo.
echo ^<Files "config.php"^>
echo     Order allow,deny
echo     Deny from all
echo ^</Files^>
echo.
echo ^<Files "*.sql"^>
echo     Order allow,deny
echo     Deny from all
echo ^</Files^>
echo.
echo # Prevenir listagem de diretorios
echo Options -Indexes
echo.
echo # Proteger arquivos sensíveis
echo ^<FilesMatch "\.(log|bak|backup|old)$"^>
echo     Order allow,deny
echo     Deny from all
echo ^</FilesMatch^>
) > "%PACKAGE_DIR%\.htaccess"

:: Criar arquivo de configuração de exemplo
echo [5/6] Criando arquivo de configuracao para producao...
(
echo ^<?php
echo // CONFIGURACAO PARA PRODUCAO - EDITE CONFORME SEU SERVIDOR
echo.
echo // Configuracoes do Banco de Dados
echo $host = 'localhost';                    // IP/Host do MySQL do servidor
echo $database = 'chamados_db';              // Nome do banco no servidor
echo $username = 'SEU_USUARIO_MYSQL';        // Usuario MySQL do servidor
echo $password = 'SUA_SENHA_MYSQL';          // Senha MySQL do servidor
echo.
echo // Configuracoes de Seguranca
echo $encryption_key = 'GERE_UMA_CHAVE_ALEATORIA_AQUI';  // Chave de 32 caracteres
echo.
echo // Configuracoes do Sistema
echo $base_url = 'https://seudominio.com/chamados_system/';  // URL do sistema
echo $system_name = 'Sistema de Chamados - Producao';
echo.
echo // Configuracoes de Email ^(opcional^)
echo $smtp_host = 'smtp.seudominio.com';
echo $smtp_user = 'sistema@seudominio.com';
echo $smtp_pass = 'senha_email';
echo $smtp_port = 587;
echo.
echo // Configuracoes de Debug ^(SEMPRE FALSE EM PRODUCAO^)
echo $debug_mode = false;
echo $show_errors = false;
echo.
echo ?^>
) > "%PACKAGE_DIR%\config\config_producao_exemplo.php"

:: Criar arquivo README para instalação
echo [6/6] Criando instrucoes de instalacao...
(
echo # INSTALACAO EM PRODUCAO
echo.
echo ## PASSOS RAPIDOS:
echo.
echo 1. UPLOAD: Copie todos os arquivos para seu servidor
echo 2. BANCO: Importe database/install_sistema_completo.sql
echo 3. CONFIG: Edite config/config.php com dados do servidor
echo 4. TESTE: Acesse e faça login com usuario: Renan.duarte / senha: 123456
echo.
echo ## CREDENCIAIS PADRAO:
echo - Desenvolvedor: Renan.duarte / 123456
echo - Admin: Eduardo.lima / 123456  
echo - Admin: Jorge_gtz / 123456
echo.
echo ## ARQUIVOS IMPORTANTES:
echo - config/config.php - Configuracao principal
echo - database/install_sistema_completo.sql - Banco completo
echo - .htaccess - Seguranca do Apache
echo.
echo ## SUPORTE:
echo - Teste conexao: public/test_connection.php
echo - Diagnostico: tools/security_check.php
echo.
echo SISTEMA PRONTO PARA PRODUCAO!
) > "%PACKAGE_DIR%\README_INSTALACAO.txt"

echo.
echo ========================================
echo  PACOTE CRIADO COM SUCESSO!
echo ========================================
echo.
echo Localizacao: %PACKAGE_DIR%
echo.
echo PROXIMOS PASSOS:
echo 1. Compacte a pasta 'deploy_package' em ZIP
echo 2. Faca upload para seu servidor
echo 3. Extraia e configure conforme README_INSTALACAO.txt
echo.
echo Pressione qualquer tecla para abrir a pasta...
pause > nul
explorer "%PACKAGE_DIR%"
