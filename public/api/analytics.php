<?php
/**
 * API Analytics - Endpoint para dados de métricas
 * Sistema ELUS Facilities
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Iniciar sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticação usando o sistema padrão
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../src/DB.php';
require_once __DIR__ . '/../../src/Auth.php';

try {
    // Inicializar autenticação
    $database = new DB();
    $db = $database->getConnection();
    $auth = new Auth($db);

    // Verificar se usuário está logado
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Usuário não autenticado',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    // Carregar Analytics Manager
    require_once __DIR__ . '/../../src/analytics/AnalyticsManager.php';
    
    $analyticsManager = new AnalyticsManager();
    
    // Obter tipo de dados solicitado
    $type = $_GET['type'] ?? 'header';
    $period = $_GET['period'] ?? '30days';
    
    // Validar período
    $validPeriods = ['7days', '30days', '90days', '6months', '1year'];
    if (!in_array($period, $validPeriods)) {
        $period = '30days';
    }
    
    // Obter dados baseado no tipo
    $data = $analyticsManager->getApiData($type, $period);
    
    // Adicionar metadata
    $response = [
        'success' => true,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'period' => $period
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
