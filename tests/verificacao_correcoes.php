<?php
/**
 * Script de Verificação das Correções de Estrutura
 * Verifica se todas as referências de banco estão corretas
 */

require_once '../config/config.php';
require_once '../src/DB.php';

$database = new DB();
$db = $database->getConnection();

if (!$db) {
    die('❌ Erro: Não foi possível conectar ao banco de dados');
}

echo "<h2>🔍 Verificação das Correções de Estrutura</h2>";
echo "<hr>";

// 1. Verificar estrutura da tabela usuarios
echo "<h3>1. Estrutura da Tabela 'usuarios'</h3>";
try {
    $stmt = $db->query("SHOW COLUMNS FROM usuarios");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr style='background: #f0f0f0;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    
    $has_created_at = false;
    $has_data_criacao = false;
    $has_password = false;
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
        
        if ($column['Field'] === 'created_at') $has_created_at = true;
        if ($column['Field'] === 'data_criacao') $has_data_criacao = true;
        if ($column['Field'] === 'password') $has_password = true;
    }
    echo "</table>";
    
    echo "<p><strong>Verificações:</strong></p>";
    echo "<ul>";
    echo "<li>Campo 'password': " . ($has_password ? "✅ Existe" : "❌ Não existe") . "</li>";
    echo "<li>Campo 'data_criacao': " . ($has_data_criacao ? "✅ Existe" : "❌ Não existe") . "</li>";
    echo "<li>Campo 'created_at': " . ($has_created_at ? "⚠️ Existe (pode causar conflito)" : "✅ Não existe (correto)") . "</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao verificar tabela usuarios: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// 2. Testar query de inserção corrigido
echo "<h3>2. Teste da Query de Inserção Corrigida</h3>";
try {
    $insert_query = "INSERT INTO usuarios (nome, username, email, password, nivel_acesso, ativo) 
                    VALUES (:nome, :username, :email, :password, :nivel_acesso, 1)";
    
    $insert_stmt = $db->prepare($insert_query);
    echo "<p>✅ Query preparado com sucesso:</p>";
    echo "<code style='background: #f0f0f0; padding: 10px; display: block;'>" . htmlspecialchars($insert_query) . "</code>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao preparar query: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// 3. Verificar estrutura da tabela niveis_acesso
echo "<h3>3. Estrutura da Tabela 'niveis_acesso'</h3>";
try {
    $stmt = $db->query("SHOW COLUMNS FROM niveis_acesso");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $has_created_at_niveis = false;
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'created_at') {
            $has_created_at_niveis = true;
            break;
        }
    }
    
    echo "<p><strong>Campo 'created_at' na tabela niveis_acesso:</strong> " . 
         ($has_created_at_niveis ? "✅ Existe (correto para manage_levels.php)" : "❌ Não existe") . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro ao verificar tabela niveis_acesso: " . $e->getMessage() . "</p>";
}

echo "<hr>";

// 4. Resumo final
echo "<h3>📋 Resumo das Correções</h3>";
echo "<div style='background: #e8f5e8; padding: 15px; border-left: 4px solid #28a745;'>";
echo "<h4>✅ Correções Aplicadas:</h4>";
echo "<ul>";
echo "<li><strong>register_user.php:</strong> Removida referência à coluna 'created_at' inexistente</li>";
echo "<li><strong>teste_auth.php:</strong> Corrigidos caminhos para arquivos movidos</li>";
echo "<li><strong>Estrutura de pastas:</strong> Arquivos organizados em pastas apropriadas</li>";
echo "</ul>";

echo "<h4>🔧 Query Corrigida:</h4>";
echo "<p>Antes: <code>INSERT INTO usuarios (..., created_at) VALUES (..., NOW())</code></p>";
echo "<p>Depois: <code>INSERT INTO usuarios (...) VALUES (...)</code></p>";
echo "<p><em>O campo 'data_criacao' é preenchido automaticamente pelo DEFAULT CURRENT_TIMESTAMP</em></p>";
echo "</div>";

echo "<p style='text-align: center; margin-top: 30px;'>";
echo "<a href='../public/register_user.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>";
echo "🧪 Testar Registro de Usuário";
echo "</a>";
echo "</p>";

echo "<hr>";
echo "<p style='text-align: center; color: #666; font-size: 12px;'>";
echo "Verificação executada em " . date('Y-m-d H:i:s');
echo "</p>";
?>
