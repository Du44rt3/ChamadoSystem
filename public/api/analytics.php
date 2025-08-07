<?php
/**
 * API Analytics - Endpoint para dados de métricas
 * Sistema ELUS Facilities
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Verificar se a sessão está ativa e usuário logado
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Acesso não autorizado']);
    exit;
}

require_once '../config/config.php';
require_once '../src/analytics/AnalyticsManager.php';

try {
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
