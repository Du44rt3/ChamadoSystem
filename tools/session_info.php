<?php
// Dev-only session info (JSON)
require_once __DIR__ . '/../src/AuthMiddleware.php';
header('Content-Type: application/json');

$data = [
    'session_id' => session_id(),
    'user' => [
        'id' => $_SESSION['user_id'] ?? null,
        'nome' => $_SESSION['user_nome'] ?? null,
        'username' => $_SESSION['user_username'] ?? null,
        'nivel_acesso' => $_SESSION['user_nivel_acesso'] ?? null,
    ],
    'keys' => array_keys($_SESSION ?? []),
];

echo json_encode($data);
