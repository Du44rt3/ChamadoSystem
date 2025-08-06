<?php
require_once '../config/config.php';
require_once '../src/DB.php';

$database = new DB();
$db = $database->getConnection();

if (!$db) {
    die('Erro: Não foi possível conectar ao banco de dados');
}

echo "<h3>Estrutura da tabela usuarios:</h3>";

try {
    $stmt = $db->query("DESCRIBE usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>
