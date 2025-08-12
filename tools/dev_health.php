<?php
// Dev-only health check (JSON)
require_once __DIR__ . '/../src/AuthMiddleware.php';
header('Content-Type: application/json');

$result = [
    'success' => true,
    'items' => []
];

// PHP
$result['items']['php_ok'] = true;
$result['items']['php_version'] = PHP_VERSION;

// Zip
$result['items']['zip_available'] = class_exists('ZipArchive');

// Temp dir
$result['items']['temp_dir'] = sys_get_temp_dir();
$result['items']['temp_writable'] = is_writable(sys_get_temp_dir());

// Cache dir
$cacheDir = __DIR__ . '/../cache';
$result['items']['cache_writable'] = is_dir($cacheDir) && is_writable($cacheDir);

// Uploads dir
$uploadsDir = __DIR__ . '/../uploads';
$result['items']['uploads_writable'] = is_dir($uploadsDir) && is_writable($uploadsDir);

// DB check
try {
    require_once __DIR__ . '/../src/DB.php';
    $dbObj = new DB();
    $db = $dbObj->getConnection();
    $result['items']['db_connected'] = (bool)$db;
    if ($db) {
        $stmt = $db->query('SELECT VERSION() as v');
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        $result['items']['db_version'] = $row['v'] ?? null;
    }
} catch (Throwable $e) {
    $result['items']['db_connected'] = false;
    $result['success'] = false;
    $result['items']['db_error'] = $e->getMessage();
}

// mysqldump path (Windows XAMPP default)
$defaultDump = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
$foundDump = file_exists($defaultDump) ? $defaultDump : null;
$result['items']['mysqldump_found'] = (bool)$foundDump;
$result['items']['mysqldump_path'] = $foundDump;

http_response_code(200);
echo json_encode($result);
