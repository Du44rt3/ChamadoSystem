<?php
/**
 * Script de Logout
 * Realiza o logout seguro do usuário
 */

require_once '../config/config.php';
require_once '../src/DB.php';
require_once '../src/Auth.php';
// require_once '../src/SystemLogger.php'; // DESATIVADO

// Inicializar autenticação
$database = new DB();
$db = $database->getConnection();
$auth = new Auth($db);
// $logger = new SystemLogger($db); // DESATIVADO

// Obter dados do usuário antes do logout
$current_user = $auth->getLoggedUser();

// Log do logout (DESATIVADO)
if ($current_user) {
    // $logger->logLogout($current_user['id'], [
    //     'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    //     'session_duration' => time() - ($_SESSION['login_time'] ?? time())
    // ]);
    
    // Log simples via error_log
    error_log("Logout realizado: " . $current_user['username'] . " em " . date('Y-m-d H:i:s'));
}

// Realizar logout
$auth->logout();

// Redirecionar para login com mensagem
header("Location: login.php?logout=success");
exit();
?>
