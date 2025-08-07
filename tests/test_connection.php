<?php
// Teste de conexão com o banco de dados
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../config/config.php';
require_once '../src/DB.php';

echo "<h2>Teste de Conexão - Sistema de Chamados</h2>";
echo "<hr>";

try {
    // Testar conexão com o banco
    $database = new DB();
    $db = $database->getConnection();
    
    if ($db) {
        echo "<p style='color: green;'><strong>✓ Conexão com banco de dados: SUCESSO</strong></p>";
        
        // Testar se as tabelas existem
        $tabelas = ['chamados', 'usuarios', 'chamado_historico', 'niveis_acesso'];
        
        foreach ($tabelas as $tabela) {
            try {
                $stmt = $db->prepare("SELECT COUNT(*) FROM $tabela");
                $stmt->execute();
                $count = $stmt->fetchColumn();
                echo "<p style='color: green;'>✓ Tabela '$tabela': $count registros</p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>✗ Erro na tabela '$tabela': " . $e->getMessage() . "</p>";
            }
        }
        
        // Testar configurações do PHP
        echo "<hr>";
        echo "<h3>Configurações do Sistema:</h3>";
        echo "<p><strong>Versão PHP:</strong> " . phpversion() . "</p>";
        echo "<p><strong>Extensão PDO MySQL:</strong> " . (extension_loaded('pdo_mysql') ? '✓ Ativa' : '✗ Inativa') . "</p>";
        echo "<p><strong>Host do Banco:</strong> " . DB_HOST . "</p>";
        echo "<p><strong>Nome do Banco:</strong> " . DB_NAME . "</p>";
        echo "<p><strong>Charset:</strong> " . DB_CHARSET . "</p>";
        
        // Testar usuários
        echo "<hr>";
        echo "<h3>Usuários no Sistema:</h3>";
        try {
            $stmt = $db->prepare("SELECT id, nome, username, nivel_acesso, ativo FROM usuarios ORDER BY id");
            $stmt->execute();
            $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($usuarios) > 0) {
                echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
                echo "<tr><th>ID</th><th>Nome</th><th>Username</th><th>Nível</th><th>Status</th></tr>";
                foreach ($usuarios as $usuario) {
                    $status = $usuario['ativo'] ? 'Ativo' : 'Inativo';
                    $cor = $usuario['ativo'] ? 'green' : 'red';
                    echo "<tr>";
                    echo "<td>{$usuario['id']}</td>";
                    echo "<td>{$usuario['nome']}</td>";
                    echo "<td>{$usuario['username']}</td>";
                    echo "<td>{$usuario['nivel_acesso']}</td>";
                    echo "<td style='color: $cor;'>$status</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p style='color: orange;'>⚠ Nenhum usuário encontrado no sistema</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Erro ao buscar usuários: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p style='color: red;'><strong>✗ Falha na conexão com banco de dados</strong></p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>✗ Erro geral: " . $e->getMessage() . "</strong></p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
}

echo "<hr>";
echo "<p><a href='../public/index.php'>← Voltar ao Sistema</a></p>";
?>