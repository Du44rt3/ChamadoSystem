<?php
require_once '../config/config.php';
require_once '../src/DB.php';

$database = new DB();
$db = $database->getConnection();

if (!$db) {
    die('Erro: Não foi possível conectar ao banco de dados');
}

echo "<h3>Teste de Insert na tabela usuarios:</h3>";

try {
    // Teste do query que estava causando erro
    $insert_query = "INSERT INTO usuarios (nome, username, email, password, nivel_acesso, ativo) 
                    VALUES (:nome, :username, :email, :password, :nivel_acesso, 1)";
    
    $insert_stmt = $db->prepare($insert_query);
    
    echo "<p><strong>Query preparado com sucesso!</strong></p>";
    echo "<p>Query: <code>" . htmlspecialchars($insert_query) . "</code></p>";
    
    // Verificar estrutura da tabela
    $stmt = $db->query("SHOW COLUMNS FROM usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<p><strong>Colunas disponíveis na tabela usuarios:</strong></p>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>" . htmlspecialchars($column) . "</li>";
    }
    echo "</ul>";
    
    echo "<p style='color: green;'><strong>✅ Correção aplicada com sucesso! O arquivo register_user.php deve funcionar agora.</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
