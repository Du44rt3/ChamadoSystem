<?php
/**
 * Script para Limpar Sessão e Reiniciar Autenticação
 */

// Iniciar sessão
session_start();

// Limpar todas as variáveis de sessão
$_SESSION = array();

// Destruir cookie de sessão se existir
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir sessão
session_destroy();

// Iniciar nova sessão
session_start();

// Regenerar ID da sessão
session_regenerate_id(true);

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Sessão Reiniciada</title>";
echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
echo "</head>";
echo "<body class='bg-light'>";

echo "<div class='container mt-5'>";
echo "<div class='row justify-content-center'>";
echo "<div class='col-md-6'>";
echo "<div class='card'>";
echo "<div class='card-body text-center'>";
echo "<h3 class='text-success'><i class='fas fa-check-circle'></i> Sessão Reiniciada!</h3>";
echo "<p class='text-muted'>O sistema de autenticação foi reiniciado com sucesso.</p>";
echo "<p>Todas as variáveis de sessão foram limpas e um novo token de segurança foi gerado.</p>";
echo "<div class='mt-4'>";
echo "<a href='login.php' class='btn btn-primary me-2'>Fazer Login</a>";
echo "<a href='debug_token.php' class='btn btn-outline-info'>Testar Token</a>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";
echo "</div>";

echo "<script src='https://kit.fontawesome.com/a076d05399.js'></script>";
echo "</body>";
echo "</html>";
?>
