<?php
// Endpoint de ações para a Área DEV (somente desenvolvedores)
header('Content-Type: application/json');

require_once __DIR__ . '/../src/AuthMiddleware.php';

if (!$auth->isDeveloper()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'forbidden']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? null;

try {
    switch ($action) {
        case 'security_scan':
            // Executa security_check.php e captura a saída
            $php = 'c:\\xampp\\php\\php.exe';
            $script = __DIR__ . DIRECTORY_SEPARATOR . 'security_check.php';
            if (!file_exists($php)) {
                throw new RuntimeException('PHP CLI não encontrado em ' . $php);
            }
            if (!file_exists($script)) {
                throw new RuntimeException('Script de verificação não encontrado.');
            }
            $cmd = '"' . $php . '" -d detect_unicode=0 -f ' . escapeshellarg($script) . ' 2>&1';
            $output = shell_exec($cmd);
            echo json_encode(['success' => true, 'output' => $output]);
            break;

        case 'session_info':
            require_once __DIR__ . '/session_info.php';
            // session_info.php já imprime JSON e encerra
            break;

        case 'db_check':
            require_once __DIR__ . '/db_check.php';
            // db_check.php já imprime JSON e encerra
            break;

        case 'cache_clear':
            // Executa limpeza de cache e retorna estatísticas
            require_once __DIR__ . '/../src/CacheManager.php';
            $cache = new CacheManager(__DIR__ . '/../cache');
            $removed = $cache->clear();
            echo json_encode(['success' => true, 'removed' => $removed]);
            break;

        case 'cache_stats':
            require_once __DIR__ . '/../src/CacheManager.php';
            $cache = new CacheManager(__DIR__ . '/../cache');
            echo json_encode(['success' => true, 'stats' => $cache->getStats()]);
            break;

        case 'toggle_debug':
            // Não editamos .env automaticamente. Apenas informamos como proceder.
            echo json_encode([
                'success' => false,
                'message' => 'Altere APP_DEBUG no arquivo .env e recarregue. Edição automática está desabilitada por segurança.'
            ]);
            break;

        case 'check_levels':
            require_once __DIR__ . '/check_levels_table.php';
            // check_levels_table.php já imprime JSON e encerra
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'ação inválida']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
