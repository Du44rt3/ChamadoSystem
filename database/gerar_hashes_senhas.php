<?php
/**
 * Script para gerar hashes das senhas dos usuários
 * Execute este script para gerar os hashes corretos
 */

// Senhas dos usuários
$usuarios = [
    'Renan.duarte' => 'Elu$R3nan#7x!P9kZ',
    'Eduardo.lima' => 'Duk@2540',
    'Jorge_gtz' => 'Elus2214'
];

echo "<h2>Geração de Hashes de Senha</h2>";
echo "<hr>";

foreach ($usuarios as $username => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "<h3>Usuário: {$username}</h3>";
    echo "Senha: <code>{$password}</code><br>";
    echo "Hash: <code>{$hash}</code><br>";
    echo "<hr>";
}

// Gerar script SQL atualizado
echo "<h2>Script SQL Atualizado</h2>";
echo "<textarea style='width: 100%; height: 300px;'>";
echo "-- Script para atualizar usuários com novas credenciais\n";
echo "-- Data: " . date('d/m/Y H:i:s') . "\n\n";
echo "USE chamados_db;\n\n";

$id = 1;
foreach ($usuarios as $username => $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $nome = str_replace(['_', '.'], [' ', ' '], ucwords($username, '_.'));
    
    if ($username === 'Renan.duarte') {
        echo "-- Atualizar usuário Renan\n";
        echo "UPDATE usuarios \n";
        echo "SET username = '{$username}',\n";
        echo "    nome = 'Renan Duarte',\n";
        echo "    password = '{$hash}'\n";
        echo "WHERE id = 1;\n\n";
    } elseif ($username === 'Eduardo.lima') {
        echo "-- Atualizar usuário Eduardo\n";
        echo "UPDATE usuarios \n";
        echo "SET username = '{$username}',\n";
        echo "    nome = 'Eduardo Lima',\n";
        echo "    password = '{$hash}'\n";
        echo "WHERE id = 2;\n\n";
    } elseif ($username === 'Jorge_gtz') {
        echo "-- Inserir/Atualizar usuário Jorge_gtz\n";
        echo "INSERT INTO usuarios (nome, username, password, email, nivel_acesso, ativo)\n";
        echo "VALUES ('Jorge GTZ', '{$username}', '{$hash}', 'jorge@grupopelus.com', 'admin', 1)\n";
        echo "ON DUPLICATE KEY UPDATE\n";
        echo "    password = '{$hash}',\n";
        echo "    nome = 'Jorge GTZ';\n\n";
    }
}

echo "-- Verificar os usuários atualizados\n";
echo "SELECT id, nome, username, email, nivel_acesso, ativo FROM usuarios ORDER BY id;";
echo "</textarea>";

?>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
code { background: #f4f4f4; padding: 2px 4px; border-radius: 3px; }
textarea { font-family: monospace; }
</style>
