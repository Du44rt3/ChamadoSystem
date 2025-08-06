@echo off
title Iniciar XAMPP para Sistema de Chamados
echo ========================================
echo  Iniciando XAMPP - Sistema de Chamados
echo ========================================
echo.

echo Verificando se o XAMPP esta instalado...
if not exist "C:\xampp\xampp-control.exe" (
    echo ERRO: XAMPP nao encontrado em C:\xampp\
    echo Por favor, instale o XAMPP ou ajuste o caminho.
    pause
    exit /b 1
)

echo XAMPP encontrado!
echo.

echo Parando servicos existentes (se houver)...
taskkill /f /im httpd.exe >nul 2>&1
taskkill /f /im mysqld.exe >nul 2>&1
echo.

echo Iniciando MySQL...
cd /d "C:\xampp"
start /min "" "C:\xampp\mysql_start.bat"
timeout /t 5 /nobreak >nul

echo Verificando se MySQL iniciou...
netstat -an | findstr :3306 >nul
if %errorlevel% equ 0 (
    echo ✓ MySQL iniciado com sucesso na porta 3306
) else (
    echo ✗ Problema ao iniciar MySQL
    echo Tentando abrir XAMPP Control Panel...
    start "" "C:\xampp\xampp-control.exe"
    echo.
    echo Manual: Clique em 'Start' no modulo MySQL no Control Panel
    pause
    exit /b 1
)

echo.
echo Iniciando Apache...
start /min "" "C:\xampp\apache_start.bat"
timeout /t 3 /nobreak >nul

echo.
echo Verificando Apache...
netstat -an | findstr :80 >nul
if %errorlevel% equ 0 (
    echo ✓ Apache rodando na porta 80
) else (
    echo ! Apache pode estar em conflito na porta 80
    echo Tentando porta alternativa ou verificando conflitos...
)

echo.
echo ========================================
echo  Status dos Servicos
echo ========================================
echo Verificando MySQL (porta 3306):
netstat -an | findstr :3306

echo.
echo Verificando Apache (porta 80):
netstat -an | findstr :80

echo.
echo ========================================
echo  Links Uteis
echo ========================================
echo Sistema de Chamados: http://localhost/chamados_system/
echo Diagnostico: http://localhost/chamados_system/diagnostico_conexao.php
echo phpMyAdmin: http://localhost/phpmyadmin/
echo XAMPP Dashboard: http://localhost/dashboard/
echo.

echo Pressione qualquer tecla para abrir o diagnostico no navegador...
pause >nul

start "" "http://localhost/chamados_system/diagnostico_conexao.php"

echo.
echo Servicos iniciados! Mantenha esta janela aberta.
echo Para parar os servicos, feche esta janela ou use Ctrl+C
pause
