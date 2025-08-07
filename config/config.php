<?php
/**
 * Configurações do Sistema - VERSÃO SEGURA
 * Use variáveis de ambiente para configurações sensíveis
 */

// Carregar variáveis de ambiente
require_once __DIR__ . '/../src/EnvLoader.php';

// Carregar arquivo .env
try {
    EnvLoader::load(__DIR__ . '/../.env');
} catch (Exception $e) {
    die("ERRO: Não foi possível carregar as configurações. Verifique se o arquivo .env existe e está configurado corretamente.");
}

// Configurações do banco de dados (usando variáveis de ambiente)
define('DB_HOST', EnvLoader::get('DB_HOST', 'localhost'));
define('DB_NAME', EnvLoader::get('DB_NAME', 'chamados_db'));
define('DB_USER', EnvLoader::get('DB_USER', 'root'));
define('DB_PASS', EnvLoader::get('DB_PASS', ''));

// Configurações gerais
define('APP_NAME', 'Sistema de Chamados');
define('APP_VERSION', '1.0.1');
define('APP_ENV', EnvLoader::get('APP_ENV', 'development'));
define('APP_DEBUG', EnvLoader::get('APP_DEBUG', 'false') === 'true');
define('APP_URL', EnvLoader::get('APP_URL', 'http://localhost/chamados_system'));

// Configurações de segurança
define('JWT_SECRET', EnvLoader::get('JWT_SECRET', 'change_this_in_production'));
define('SESSION_SECRET', EnvLoader::get('SESSION_SECRET', 'change_this_too'));

// Configurações de email
define('MAIL_HOST', EnvLoader::get('MAIL_HOST', 'smtp.gmail.com'));
define('MAIL_PORT', EnvLoader::get('MAIL_PORT', '587'));
define('MAIL_USER', EnvLoader::get('MAIL_USER', ''));
define('MAIL_PASS', EnvLoader::get('MAIL_PASS', ''));
define('MAIL_FROM', EnvLoader::get('MAIL_FROM', 'noreply@elusinstrumentacao.com.br'));

// Configurações de sessão
define('SESSION_SECURE', EnvLoader::get('SESSION_SECURE', 'false') === 'true');
define('SESSION_HTTPONLY', EnvLoader::get('SESSION_HTTPONLY', 'true') === 'true');
define('SESSION_SAMESITE', EnvLoader::get('SESSION_SAMESITE', 'Strict'));

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de erro (apenas em desenvolvimento)
if (APP_DEBUG && APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>

