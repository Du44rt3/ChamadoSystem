<?php
// Dev-only DB check (JSON)
require_once __DIR__ . '/../src/AuthMiddleware.php';
header('Content-Type: application/json');

$out = [
    'connected' => false,
];

try {
    require_once __DIR__ . '/../src/DB.php';
    $dbObj = new DB();
    $db = $dbObj->getConnection();
    if ($db) {
        $out['connected'] = true;
        $out['database'] = defined('DB_NAME') ? DB_NAME : null;
        $stmt = $db->query('SELECT VERSION() as v');
        $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
        $out['server_version'] = $row['v'] ?? null;
        $stmt2 = $db->query("SHOW TABLES");
        $out['tables_count'] = $stmt2 ? $stmt2->rowCount() : null;
    }
    echo json_encode($out);
} catch (Throwable $e) {
    $out['error'] = $e->getMessage();
    echo json_encode($out);
}
