<?php
/**
 * Debug Session - DEVELOPERS ONLY
 * ELUS Facilities - Debugging e Informações de Sessão
 * Tema: Dev Area Dark
 */

// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

// Verificar se tem acesso de desenvolvedor APENAS
if (!$auth->isDeveloper()) {
    header("Location: index.php?error=access_denied");
    exit();
}

// Processar ações de debug
$action_result = '';
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'clear_session':
            $vars_before = count($_SESSION);
            session_unset();
            session_regenerate_id(true);
            $action_result = "✓ Sessão limpa! {$vars_before} variáveis removidas.";
            break;
            
        case 'regenerate_id':
            $old_id = session_id();
            session_regenerate_id(true);
            $new_id = session_id();
            $action_result = "✓ ID da sessão regenerado!<br>Antigo: {$old_id}<br>Novo: {$new_id}";
            break;
            
        case 'test_session':
            $_SESSION['debug_test'] = 'Teste realizado em ' . date('Y-m-d H:i:s');
            $_SESSION['debug_counter'] = ($_SESSION['debug_counter'] ?? 0) + 1;
            $action_result = "✓ Variáveis de teste adicionadas à sessão.";
            break;
            
        case 'export_session':
            $export_data = [
                'timestamp' => date('Y-m-d H:i:s'),
                'session_data' => $_SESSION,
                'server_info' => $server_info,
                'session_config' => $session_config,
                'request_info' => $request_info,
                'auth_info' => $auth_info,
                'cookies' => $cookies_info,
                'http_headers' => $http_headers
            ];
            $json_data = json_encode($export_data, JSON_PRETTY_PRINT);
            $filename = '../logs/debug_export_' . date('Y-m-d_H-i-s') . '.json';
            
            // Criar diretório logs se não existir
            if (!is_dir('../logs')) {
                mkdir('../logs', 0755, true);
            }
            
            file_put_contents($filename, $json_data);
            $action_result = "✓ Debug info exportada para: " . basename($filename);
            break;
    }
}

// Coleta de informações do sistema
$server_info = [
    'PHP Version' => PHP_VERSION,
    'OS' => PHP_OS,
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'N/A',
    'Memory Limit' => ini_get('memory_limit'),
    'Max Execution Time' => ini_get('max_execution_time') . 's',
    'Post Max Size' => ini_get('post_max_size'),
    'Upload Max Filesize' => ini_get('upload_max_filesize'),
    'User Agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A'
];

$session_config = [
    'session.name' => ini_get('session.name'),
    'session.save_path' => ini_get('session.save_path'),
    'session.cookie_lifetime' => ini_get('session.cookie_lifetime'),
    'session.cookie_path' => ini_get('session.cookie_path'),
    'session.cookie_domain' => ini_get('session.cookie_domain'),
    'session.cookie_secure' => ini_get('session.cookie_secure') ? 'Yes' : 'No',
    'session.cookie_httponly' => ini_get('session.cookie_httponly') ? 'Yes' : 'No',
    'session.gc_maxlifetime' => ini_get('session.gc_maxlifetime'),
    'session.gc_probability' => ini_get('session.gc_probability'),
    'session.gc_divisor' => ini_get('session.gc_divisor'),
    'session.use_strict_mode' => ini_get('session.use_strict_mode') ? 'Yes' : 'No'
];

// Informações da requisição atual
$request_info = [
    'Method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
    'Protocol' => $_SERVER['SERVER_PROTOCOL'] ?? 'N/A',
    'Host' => $_SERVER['HTTP_HOST'] ?? 'N/A',
    'URI' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'Query String' => $_SERVER['QUERY_STRING'] ?? 'N/A',
    'Remote Address' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
    'Remote Port' => $_SERVER['REMOTE_PORT'] ?? 'N/A',
    'Timestamp' => date('Y-m-d H:i:s')
];

// Informações de autenticação
$auth_info = [
    'Usuario ID' => $current_user['id'] ?? 'N/A',
    'Usuario Nome' => $current_user['nome'] ?? 'N/A',
    'Nivel Acesso' => $current_user['nivel_acesso'] ?? 'N/A',
    'Usuario Ativo' => isset($current_user['ativo']) ? ($current_user['ativo'] ? 'Sim' : 'Não') : 'N/A',
    'Login Time' => isset($_SESSION['login_time']) ? date('Y-m-d H:i:s', $_SESSION['login_time']) : 'N/A',
    'Last Activity' => isset($_SESSION['last_activity']) ? date('Y-m-d H:i:s', $_SESSION['last_activity']) : 'N/A',
    'Session Timeout' => ini_get('session.gc_maxlifetime') . 's'
];

// Informações de cookies
$cookies_info = $_COOKIE ?? [];

// Headers HTTP
$http_headers = [];
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'HTTP_') === 0) {
        $header_name = str_replace('HTTP_', '', $key);
        $header_name = str_replace('_', '-', $header_name);
        $header_name = ucwords(strtolower($header_name), '-');
        $http_headers[$header_name] = $value;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Session - Dev Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Dev Area Dark Theme */
        * {
            box-sizing: border-box;
        }
        
        body {
            background: #0d0d0d;
            color: #00ff41;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        /* Custom Dev Header */
        .dev-header {
            background: linear-gradient(90deg, #000000 0%, #111111 100%);
            border-bottom: 1px solid #333;
            padding: 15px 0;
        }
        
        .dev-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .dev-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #00ff41;
        }
        
        .dev-brand:hover {
            color: #00ff41;
            text-decoration: none;
        }
        
        .dev-logo {
            width: 40px;
            height: 40px;
            margin-right: 15px;
            border-radius: 4px;
        }
        
        .dev-brand-text {
            font-size: 18px;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .dev-badge {
            background: rgba(0, 255, 65, 0.2);
            color: #00ff41;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #00ff41;
        }
        
        .dev-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .dev-user-name {
            color: #888;
            font-size: 14px;
        }
        
        .dev-exit {
            color: #ff4444;
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid #ff4444;
            border-radius: 4px;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        
        .dev-exit:hover {
            background: #ff4444;
            color: white;
            text-decoration: none;
        }
        
        /* Main Container */
        .dev-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        /* Page Title */
        .page-title {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #333;
            border-radius: 8px;
        }
        
        .page-title h1 {
            color: #00ff41;
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .page-title p {
            color: #888;
            margin: 0;
            font-size: 1.1rem;
        }
        
        /* Grid Layout */
        .debug-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        /* Debug Cards */
        .debug-card {
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #333;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .debug-card:hover {
            border-color: #00ff41;
            box-shadow: 0 0 20px rgba(0, 255, 65, 0.1);
        }
        
        .debug-header {
            background: rgba(0, 0, 0, 0.8);
            padding: 15px 20px;
            border-bottom: 1px solid #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .debug-header h3 {
            color: #00ff41;
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .debug-content {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        /* Terminal Style */
        .terminal {
            background: #000;
            border: 1px solid #333;
            border-radius: 4px;
            padding: 15px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.4;
            color: #00ff41;
            white-space: pre-wrap;
            overflow-x: auto;
        }
        
        /* Data Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th,
        .data-table td {
            padding: 8px 12px;
            border-bottom: 1px solid #333;
            text-align: left;
        }
        
        .data-table th {
            background: rgba(0, 0, 0, 0.6);
            color: #00ff41;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .data-table td {
            color: #888;
            font-size: 0.85rem;
        }
        
        .data-table tr:hover {
            background: rgba(0, 255, 65, 0.05);
        }
        
        /* Session Status */
        .session-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid #28a745;
        }
        
        .status-inactive {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        
        .status-warning {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
            border: 1px solid #ffc107;
        }
        
        /* Action Buttons */
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .btn-dev {
            background: #00ff41;
            color: #000;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-dev:hover {
            background: #00cc33;
            color: #000;
            transform: translateY(-2px);
            text-decoration: none;
        }
        
        .btn-warning-dev {
            background: #ffc107;
            color: #000;
        }
        
        .btn-warning-dev:hover {
            background: #e0a800;
            color: #000;
        }
        
        .btn-danger-dev {
            background: #ff4444;
            color: #fff;
        }
        
        .btn-danger-dev:hover {
            background: #cc0000;
            color: #fff;
        }
        
        /* Action Result */
        .action-result {
            background: rgba(0, 255, 65, 0.1);
            border: 1px solid #00ff41;
            color: #00ff41;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 25px;
            font-family: 'Courier New', monospace;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .debug-grid {
                grid-template-columns: 1fr;
            }
            
            .page-title h1 {
                font-size: 1.8rem;
            }
            
            .dev-container {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Custom Dev Header -->
    <div class="dev-header">
        <div class="dev-nav">
            <a href="../public/dev_area.php" class="dev-brand">
                <img src="../public/images/logo-eluss.png" alt="ELUS Logo" class="dev-logo">
                <span class="dev-brand-text">Debug Session</span>
                <span class="dev-badge">DEV</span>
            </a>
            
            <div class="dev-user">
                <span class="dev-user-name"><?php echo htmlspecialchars($current_user['nome']); ?></span>
                <a href="../public/dev_area.php" class="dev-exit">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dev
                </a>
            </div>
        </div>
    </div>
    
    <div class="dev-container">
        <!-- Page Title -->
        <div class="page-title">
            <h1><i class="fas fa-bug me-3"></i>DEBUG SESSION</h1>
            <p>Informações detalhadas de sessão, servidor e debugging para desenvolvedores</p>
        </div>

        <!-- Action Result -->
        <?php if ($action_result): ?>
        <div class="action-result">
            <i class="fas fa-check-circle me-2"></i><?php echo $action_result; ?>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="action-grid">
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="action" value="test_session">
                <button type="submit" class="btn-dev w-100">
                    <i class="fas fa-flask me-2"></i>Test Session
                </button>
            </form>
            
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="action" value="regenerate_id">
                <button type="submit" class="btn-dev btn-warning-dev w-100">
                    <i class="fas fa-sync me-2"></i>Regenerate ID
                </button>
            </form>
            
            <form method="POST" style="margin: 0;" onsubmit="return confirm('Limpar toda a sessão? Esta ação não pode ser desfeita.')">
                <input type="hidden" name="action" value="clear_session">
                <button type="submit" class="btn-dev btn-danger-dev w-100">
                    <i class="fas fa-trash me-2"></i>Clear Session
                </button>
            </form>
            
            <button onclick="location.reload()" class="btn-dev w-100">
                <i class="fas fa-redo me-2"></i>Refresh Page
            </button>
            
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="action" value="export_session">
                <button type="submit" class="btn-dev w-100">
                    <i class="fas fa-download me-2"></i>Export Debug
                </button>
            </form>
        </div>

        <!-- Debug Cards Grid -->
        <div class="debug-grid">
            <!-- Session Status -->
            <div class="debug-card">
                <div class="debug-header">
                    <i class="fas fa-user-clock"></i>
                    <h3>Session Status</h3>
                </div>
                <div class="debug-content">
                    <table class="data-table">
                        <tr>
                            <th>Session ID</th>
                            <td><?php echo session_id(); ?></td>
                        </tr>
                        <tr>
                            <th>Session Name</th>
                            <td><?php echo session_name(); ?></td>
                        </tr>
                        <tr>
                            <th>Status</th>
                            <td>
                                <?php
                                $status = session_status();
                                switch($status) {
                                    case PHP_SESSION_DISABLED:
                                        echo '<span class="session-status status-inactive">DISABLED</span>';
                                        break;
                                    case PHP_SESSION_NONE:
                                        echo '<span class="session-status status-warning">NONE</span>';
                                        break;
                                    case PHP_SESSION_ACTIVE:
                                        echo '<span class="session-status status-active">ACTIVE</span>';
                                        break;
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Variables Count</th>
                            <td><?php echo count($_SESSION); ?></td>
                        </tr>
                        <tr>
                            <th>Started At</th>
                            <td><?php echo date('Y-m-d H:i:s', $_SESSION['login_time'] ?? time()); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Session Variables -->
            <div class="debug-card">
                <div class="debug-header">
                    <i class="fas fa-database"></i>
                    <h3>Session Variables</h3>
                </div>
                <div class="debug-content">
                    <div class="terminal"><?php 
                    if (empty($_SESSION)) {
                        echo "Nenhuma variável de sessão encontrada.";
                    } else {
                        echo "SESSION DATA:\n";
                        echo str_repeat("=", 40) . "\n";
                        foreach ($_SESSION as $key => $value) {
                            echo sprintf("%-20s: %s\n", $key, 
                                is_array($value) || is_object($value) 
                                    ? json_encode($value, JSON_PRETTY_PRINT) 
                                    : $value
                            );
                        }
                    }
                    ?></div>
                </div>
            </div>

            <!-- Session Configuration -->
            <div class="debug-card">
                <div class="debug-header">
                    <i class="fas fa-cogs"></i>
                    <h3>Session Configuration</h3>
                </div>
                <div class="debug-content">
                    <table class="data-table">
                        <?php foreach ($session_config as $key => $value): ?>
                        <tr>
                            <th><?php echo $key; ?></th>
                            <td><?php echo $value; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>

            <!-- Server Information -->
            <div class="debug-card">
                <div class="debug-header">
                    <i class="fas fa-server"></i>
                    <h3>Server Information</h3>
                </div>
                <div class="debug-content">
                    <table class="data-table">
                        <?php foreach ($server_info as $key => $value): ?>
                        <tr>
                            <th><?php echo $key; ?></th>
                            <td><?php echo $value; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>

            <!-- Request Information -->
            <div class="debug-card">
                <div class="debug-header">
                    <i class="fas fa-globe"></i>
                    <h3>Request Information</h3>
                </div>
                <div class="debug-content">
                    <table class="data-table">
                        <?php foreach ($request_info as $key => $value): ?>
                        <tr>
                            <th><?php echo $key; ?></th>
                            <td><?php echo $value; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>

            <!-- Environment Variables -->
            <div class="debug-card">
                <div class="debug-header">
                    <i class="fas fa-list-alt"></i>
                    <h3>Environment Variables</h3>
                </div>
                <div class="debug-content">
                    <div class="terminal"><?php 
                    echo "ENVIRONMENT VARIABLES:\n";
                    echo str_repeat("=", 40) . "\n";
                    $env_vars = ['HTTP_HOST', 'REQUEST_METHOD', 'REQUEST_URI', 'HTTP_USER_AGENT', 'REMOTE_ADDR', 'SERVER_SOFTWARE'];
                    foreach ($env_vars as $var) {
                        $value = $_SERVER[$var] ?? 'N/A';
                        echo sprintf("%-20s: %s\n", $var, $value);
                    }
                    ?></div>
                </div>
            </div>

            <!-- Authentication Info -->
            <div class="debug-card">
                <div class="debug-header">
                    <i class="fas fa-user-shield"></i>
                    <h3>Authentication Info</h3>
                </div>
                <div class="debug-content">
                    <table class="data-table">
                        <?php foreach ($auth_info as $key => $value): ?>
                        <tr>
                            <th><?php echo $key; ?></th>
                            <td><?php echo htmlspecialchars($value); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>

            <!-- HTTP Headers -->
            <div class="debug-card">
                <div class="debug-header">
                    <i class="fas fa-network-wired"></i>
                    <h3>HTTP Headers</h3>
                </div>
                <div class="debug-content">
                    <div class="terminal"><?php 
                    echo "HTTP HEADERS:\n";
                    echo str_repeat("=", 40) . "\n";
                    if (empty($http_headers)) {
                        echo "Nenhum header HTTP encontrado.";
                    } else {
                        foreach ($http_headers as $header => $value) {
                            echo sprintf("%-20s: %s\n", $header, $value);
                        }
                    }
                    ?></div>
                </div>
            </div>

            <!-- Cookies -->
            <div class="debug-card">
                <div class="debug-header">
                    <i class="fas fa-cookie-bite"></i>
                    <h3>Cookies</h3>
                </div>
                <div class="debug-content">
                    <div class="terminal"><?php 
                    echo "COOKIES:\n";
                    echo str_repeat("=", 40) . "\n";
                    if (empty($cookies_info)) {
                        echo "Nenhum cookie encontrado.";
                    } else {
                        foreach ($cookies_info as $name => $value) {
                            echo sprintf("%-20s: %s\n", $name, 
                                strlen($value) > 50 ? substr($value, 0, 50) . '...' : $value
                            );
                        }
                    }
                    ?></div>
                </div>
            </div>

            <!-- PHP Info Summary -->
            <div class="debug-card">
                <div class="debug-header">
                    <i class="fas fa-code"></i>
                    <h3>PHP Configuration</h3>
                </div>
                <div class="debug-content">
                    <table class="data-table">
                        <tr>
                            <th>Loaded Extensions</th>
                            <td><?php echo count(get_loaded_extensions()); ?></td>
                        </tr>
                        <tr>
                            <th>Error Reporting</th>
                            <td><?php echo error_reporting(); ?></td>
                        </tr>
                        <tr>
                            <th>Display Errors</th>
                            <td><?php echo ini_get('display_errors') ? 'On' : 'Off'; ?></td>
                        </tr>
                        <tr>
                            <th>Log Errors</th>
                            <td><?php echo ini_get('log_errors') ? 'On' : 'Off'; ?></td>
                        </tr>
                        <tr>
                            <th>Default Timezone</th>
                            <td><?php echo date_default_timezone_get(); ?></td>
                        </tr>
                        <tr>
                            <th>Include Path</th>
                            <td><?php echo ini_get('include_path'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Advanced Debug Section -->
        <div class="debug-card" style="margin-top: 30px;">
            <div class="debug-header">
                <i class="fas fa-terminal"></i>
                <h3>Advanced Debug Console</h3>
            </div>
            <div class="debug-content">
                <div class="terminal" style="max-height: 300px;"><?php 
                echo "SYSTEM DEBUG CONSOLE:\n";
                echo str_repeat("=", 60) . "\n";
                echo sprintf("PHP Version: %s\n", PHP_VERSION);
                echo sprintf("Memory Usage: %s / %s\n", 
                    number_format(memory_get_usage(true) / 1024 / 1024, 2) . 'MB',
                    ini_get('memory_limit')
                );
                echo sprintf("Peak Memory: %s\n", 
                    number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . 'MB'
                );
                echo sprintf("Execution Time: %s seconds\n", 
                    number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 4)
                );
                echo sprintf("Session Save Path: %s\n", session_save_path());
                echo sprintf("Session Entropy: %s\n", ini_get('session.entropy_length'));
                echo sprintf("Session Hash Function: %s\n", ini_get('session.hash_function'));
                echo sprintf("Session Serialize Handler: %s\n", ini_get('session.serialize_handler'));
                echo sprintf("Autoload Extensions: %s\n", ini_get('extension_dir'));
                echo sprintf("Include Paths: %s\n", get_include_path());
                echo str_repeat("=", 60) . "\n";
                echo "Session Files: ";
                $session_path = session_save_path();
                if (is_dir($session_path)) {
                    $files = glob($session_path . '/sess_*');
                    echo count($files) . " files found\n";
                } else {
                    echo "Path not accessible\n";
                }
                ?></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
