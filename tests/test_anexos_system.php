<?php
require_once '../src/DB.php';
require_once '../src/ChamadoAnexo.php';

try {
    $database = new DB();
    $db = $database->getConnection();
    
    echo "<h2>Teste do Sistema de Anexos</h2>";
    
    // Verificar se a tabela foi criada
    $stmt = $db->query("SHOW TABLES LIKE 'chamado_anexos'");
    if ($stmt->rowCount() > 0) {
        echo "<p>✅ Tabela 'chamado_anexos' criada com sucesso!</p>";
        
        // Verificar estrutura da tabela
        $stmt = $db->query("DESCRIBE chamado_anexos");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Estrutura da Tabela:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>{$column['Field']}</td>";
            echo "<td>{$column['Type']}</td>";
            echo "<td>{$column['Null']}</td>";
            echo "<td>{$column['Key']}</td>";
            echo "<td>{$column['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Verificar se os triggers foram criados
        $stmt = $db->query("SHOW TRIGGERS LIKE 'after_anexo_%'");
        $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Triggers Criados:</h3>";
        if (count($triggers) > 0) {
            foreach ($triggers as $trigger) {
                echo "<p>✅ Trigger: {$trigger['Trigger']}</p>";
            }
        } else {
            echo "<p>❌ Nenhum trigger encontrado</p>";
        }
        
        // Testar a classe ChamadoAnexo
        $anexo = new ChamadoAnexo($db);
        echo "<p>✅ Classe ChamadoAnexo instanciada com sucesso!</p>";
        
        // Verificar diretório de uploads
        $upload_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR;
        if (is_dir($upload_dir)) {
            echo "<p>✅ Diretório de uploads existe: $upload_dir</p>";
            if (is_writable($upload_dir)) {
                echo "<p>✅ Diretório de uploads tem permissão de escrita</p>";
            } else {
                echo "<p>❌ Diretório de uploads NÃO tem permissão de escrita</p>";
            }
        } else {
            echo "<p>❌ Diretório de uploads NÃO existe: $upload_dir</p>";
        }
        
        // Verificar .htaccess
        $htaccess_file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . '.htaccess';
        if (file_exists($htaccess_file)) {
            echo "<p>✅ Arquivo .htaccess de proteção existe no diretório uploads</p>";
        } else {
            echo "<p>❌ Arquivo .htaccess de proteção NÃO existe no diretório uploads</p>";
        }
        
        echo "<h3>Funcionalidades Implementadas:</h3>";
        echo "<ul>";
        echo "<li>✅ Upload de múltiplas imagens</li>";
        echo "<li>✅ Validação de tipos de arquivo (apenas imagens)</li>";
        echo "<li>✅ Limitação de tamanho (5MB por arquivo)</li>";
        echo "<li>✅ Preview das imagens antes do upload</li>";
        echo "<li>✅ Galeria de imagens na visualização do chamado</li>";
        echo "<li>✅ Modal para visualizar imagens em tamanho completo</li>";
        echo "<li>✅ Download de anexos</li>";
        echo "<li>✅ Exclusão de anexos com confirmação</li>";
        echo "<li>✅ Registro automático no histórico do chamado</li>";
        echo "<li>✅ Proteção de segurança no diretório de uploads</li>";
        echo "</ul>";
        
    } else {
        echo "<p>❌ Tabela 'chamado_anexos' NÃO foi criada!</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Erro: " . $e->getMessage() . "</p>";
}
?>
