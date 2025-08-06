<?php
/**
 * Middleware de Autenticação
 * Protege páginas que requerem login
 */

// Incluir dependências
require_once '../config/config.php';
require_once '../src/DB.php';
require_once '../src/Auth.php';

// Inicializar conexão e autenticação
$database = new DB();
$db = $database->getConnection();
$auth = new Auth($db);

// Verificar se está logado
if (!$auth->isLoggedIn()) {
    // Salvar a URL atual para redirect após login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    
    // Redirecionar para login
    header("Location: login.php");
    exit();
}

// Disponibilizar dados do usuário globalmente
$current_user = $auth->getLoggedUser();
?>
