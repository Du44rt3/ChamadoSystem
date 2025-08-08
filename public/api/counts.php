<?php
/**
 * API ultra simples para contadores - apenas dados básicos
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../src/DB.php';
    
    $database = new DB();
    $db = $database->getConnection();
    
    // Query para contar chamados - fechados totais, não apenas hoje
    $sql = "SELECT 
        COUNT(CASE WHEN status = 'aberto' THEN 1 END) as abertos,
        COUNT(CASE WHEN status = 'em_andamento' THEN 1 END) as em_andamento,
        COUNT(CASE WHEN status = 'fechado' THEN 1 END) as fechados,
        COUNT(*) as total
    FROM chamados";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'abertos' => (int)$result['abertos'],
            'em_andamento' => (int)$result['em_andamento'], 
            'fechados' => (int)$result['fechados'],
            'total' => (int)$result['total']
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>
