<?php
// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

// Verificar se ID foi fornecido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

// Verificar se chamado_id foi fornecido
if (!isset($_GET['chamado_id']) || empty($_GET['chamado_id'])) {
    header('Location: index.php');
    exit;
}

include_once '../src/DB.php';
include_once '../src/ChamadoAnexo.php';

$database = new DB();
$db = $database->getConnection();
$anexo = new ChamadoAnexo($db);

$anexo_id = $_GET['id'];
$chamado_id = $_GET['chamado_id'];

// Buscar anexo antes de excluir para verificar se existe
$anexo_data = $anexo->buscarPorId($anexo_id);

if (!$anexo_data) {
    header("Location: view.php?id=$chamado_id&error=anexo_nao_encontrado");
    exit;
}

// Verificar se o anexo pertence ao chamado
if ($anexo_data['chamado_id'] != $chamado_id) {
    header("Location: view.php?id=$chamado_id&error=anexo_invalido");
    exit;
}

// Tentar excluir o anexo
if ($anexo->excluir($anexo_id)) {
    header("Location: view.php?id=$chamado_id&success=anexo_excluido");
} else {
    header("Location: view.php?id=$chamado_id&error=erro_excluir_anexo");
}
exit;
?>
