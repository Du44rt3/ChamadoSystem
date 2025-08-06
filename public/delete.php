<?php
// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

$id = isset($_GET['id']) ? $_GET['id'] : die('ID não especificado.');

include_once '../src/DB.php';
include_once '../src/Chamado.php';

$database = new DB();
$db = $database->getConnection();
$chamado = new Chamado($db);

$chamado->id = $id;

if($chamado->delete()){
    header('Location: index.php?message=deleted');
} else {
    header('Location: index.php?error=delete_failed');
}
?>

