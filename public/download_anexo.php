<?php
// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

// Verificar se ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

include_once '../src/DB.php';
include_once '../src/ChamadoAnexo.php';

$database = new DB();
$db = $database->getConnection();
$anexo = new ChamadoAnexo($db);

// Buscar anexo
$anexo_data = $anexo->buscarPorId($_GET['id']);

if (!$anexo_data) {
    header('Location: index.php');
    exit;
}

// Caminho completo do arquivo
$caminho_arquivo = dirname(__DIR__) . DIRECTORY_SEPARATOR . $anexo_data['caminho_arquivo'];

// Verificar se arquivo existe
if (!file_exists($caminho_arquivo)) {
    echo "<script>alert('Arquivo não encontrado!'); window.history.back();</script>";
    exit;
}

// Definir headers para download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $anexo_data['nome_original'] . '"');
header('Content-Length: ' . filesize($caminho_arquivo));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Enviar arquivo
readfile($caminho_arquivo);
exit;
?>
