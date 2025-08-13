<?php
// Script para atualizar senhas diretamente no banco
require_once '../config/config.php';
require_once '../src/DB.php';

$database = new DB();
$db = $database->getConnection();

if (!$db) {
    die("Erro na conexão com o banco de dados!");
}

// Senhas dos usuários
$usuarios = [
    ['id' => 1, 'username' => 'Renan.duarte', 'nome' => 'Renan Duarte', 'senha' => 'Elu$R3nan#7x!P9kZ'],
    ['id' => 2, 'username' => 'Eduardo.lima', 'nome' => 'Eduardo Lima', 'senha' => 'Duk@2540'],
    ['username' => 'Jorge_gtz', 'nome' => 'Jorge GTZ', 'senha' => 'Elus2214', 'email' => 'jorge@grupopelus.com', 'nivel_acesso' => 'admin']
];

echo "<h2>Atualizando Senhas dos Usuários</h2>";
echo "<hr>";

try {
    foreach ($usuarios as $usuario) {
        $hash = password_hash($usuario['senha'], PASSWORD_DEFAULT);
        
        if (isset($usuario['id'])) {
            // Atualizar usuário existente
            $query = "UPDATE usuarios SET username = ?, nome = ?, password = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            $success = $stmt->execute([$usuario['username'], $usuario['nome'], $hash, $usuario['id']]);
            
            if ($success) {
                echo "✅ Usuário {$usuario['username']} atualizado com sucesso!<br>";
            } else {
                echo "❌ Erro ao atualizar usuário {$usuario['username']}<br>";
            }
        } else {
            // Verificar se o usuário já existe
            $checkQuery = "SELECT id FROM usuarios WHERE username = ?";
            $checkStmt = $db->prepare($checkQuery);
            $checkStmt->execute([$usuario['username']]);
            
            if ($checkStmt->rowCount() > 0) {
                // Atualizar usuário existente
                $query = "UPDATE usuarios SET nome = ?, password = ? WHERE username = ?";
                $stmt = $db->prepare($query);
                $success = $stmt->execute([$usuario['nome'], $hash, $usuario['username']]);
                echo "✅ Usuário {$usuario['username']} atualizado com sucesso!<br>";
            } else {
                // Inserir novo usuário
                $query = "INSERT INTO usuarios (nome, username, password, email, nivel_acesso, ativo) VALUES (?, ?, ?, ?, ?, 1)";
                $stmt = $db->prepare($query);
                $success = $stmt->execute([$usuario['nome'], $usuario['username'], $hash, $usuario['email'], $usuario['nivel_acesso']]);
                
                if ($success) {
                    echo "✅ Usuário {$usuario['username']} criado com sucesso!<br>";
                } else {
                    echo "❌ Erro ao criar usuário {$usuario['username']}<br>";
                }
            }
        }
    }
    
    echo "<hr>";
    echo "<h3>Usuários Atualizados:</h3>";
    
    // Verificar usuários atuais
    $query = "SELECT id, nome, username, email, nivel_acesso, ativo FROM usuarios ORDER BY id";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $usuarios_db = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Nome</th><th>Username</th><th>Email</th><th>Nível Acesso</th><th>Ativo</th></tr>";
    
    foreach ($usuarios_db as $user) {
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['nome']}</td>";
        echo "<td><strong>{$user['username']}</strong></td>";
        echo "<td>{$user['email']}</td>";
        echo "<td><span style='background: " . ($user['nivel_acesso'] == 'desenvolvedor' ? '#dc3545' : ($user['nivel_acesso'] == 'admin' ? '#0dcaf0' : '#6c757d')) . "; color: white; padding: 2px 8px; border-radius: 3px;'>{$user['nivel_acesso']}</span></td>";
        echo "<td>" . ($user['ativo'] ? '✅' : '❌') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<hr>";
    echo "<h3>Credenciais de Login:</h3>";
    echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
    echo "<strong>1. Login:</strong> Renan.duarte | <strong>Senha:</strong> Elu\$R3nan#7x!P9kZ<br>";
    echo "<strong>2. Login:</strong> Eduardo.lima | <strong>Senha:</strong> Duk@2540<br>";
    echo "<strong>3. Login:</strong> Jorge_gtz | <strong>Senha:</strong> Elus2214<br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}
?>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 20px 0; }
th { background: #343a40; color: white; }
tr:nth-child(even) { background: #f8f9fa; }
</style>
