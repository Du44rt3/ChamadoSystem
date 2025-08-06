<?php
// Script para atualizar o banco de dados com a tabela de histórico

include_once '../src/DB.php';

try {
    $database = new DB();
    $db = $database->getConnection();
    
    echo "<h2>Atualizando banco de dados...</h2>";
    
    // Ler e executar o script de histórico
    $sql_content = file_get_contents('historico_schema.sql');
    
    // Dividir em comandos individuais
    $commands = explode(';', $sql_content);
    
    foreach ($commands as $command) {
        $command = trim($command);
        if (!empty($command)) {
            try {
                $db->exec($command);
                echo "<p style='color: green;'>✓ Comando executado com sucesso</p>";
            } catch (PDOException $e) {
                // Ignorar erros de "já existe"
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'Duplicate') === false) {
                    echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
                } else {
                    echo "<p style='color: orange;'>⚠ Item já existe (ignorado)</p>";
                }
            }
        }
    }
    
    echo "<h3 style='color: green;'>Atualização concluída!</h3>";
    echo "<p><a href='index.php'>Voltar para o sistema</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Erro durante a atualização:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
