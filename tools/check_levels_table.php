<?php
// Script de verificação para tabela niveis_acesso
// Quando executado via web, usa AuthMiddleware; via CLI executa direto
$is_cli = php_sapi_name() === 'cli';

if (!$is_cli) {
    require_once __DIR__ . '/../src/AuthMiddleware.php';
    if (!$auth->isDeveloper()) {
        http_response_code(403);
        echo json_encode(['error' => 'forbidden']);
        exit;
    }
    header('Content-Type: application/json');
} else {
    // Para CLI, carregamos diretamente as dependências
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../src/DB.php';
}

try {
    if (!$is_cli) {
        require_once __DIR__ . '/../src/DB.php';
    }
    $dbObj = new DB();
    $db = $dbObj->getConnection();
    
    $result = [
        'table_exists' => false,
        'levels_count' => 0,
        'levels' => [],
        'error' => null
    ];
    
    // Verificar se a tabela existe
    $stmt = $db->query("SHOW TABLES LIKE 'niveis_acesso'");
    if ($stmt->rowCount() > 0) {
        $result['table_exists'] = true;
        
        // Contar níveis
        $stmt = $db->query("SELECT COUNT(*) as count FROM niveis_acesso");
        $count = $stmt->fetch(PDO::FETCH_ASSOC);
        $result['levels_count'] = $count['count'];
        
        // Listar níveis
        $stmt = $db->query("SELECT nome, descricao, nivel_sistema, ativo FROM niveis_acesso ORDER BY nivel_sistema DESC, nome ASC");
        $result['levels'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } else {
        $result['error'] = 'Tabela niveis_acesso não existe';
    }
    
    if ($is_cli) {
        echo "Tabela niveis_acesso: " . ($result['table_exists'] ? 'EXISTS' : 'NOT EXISTS') . "\n";
        echo "Níveis cadastrados: " . $result['levels_count'] . "\n";
        if (!empty($result['levels'])) {
            foreach ($result['levels'] as $level) {
                echo "- {$level['nome']} ({$level['nivel_sistema']}) - " . ($level['ativo'] ? 'Ativo' : 'Inativo') . "\n";
            }
        }
    } else {
        echo json_encode($result);
    }
    
} catch (Exception $e) {
    if ($is_cli) {
        echo "ERRO: " . $e->getMessage() . "\n";
    } else {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
