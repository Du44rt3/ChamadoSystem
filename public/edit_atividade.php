<?php
// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include_once '../src/DB.php';
    include_once '../src/ChamadoHistorico.php';
    
    $database = new DB();
    $db = $database->getConnection();
    $historico = new ChamadoHistorico($db);
    
    $atividade_id = $_POST['atividade_id'] ?? '';
    $chamado_id = $_POST['chamado_id'] ?? '';
    $atividade = trim($_POST['atividade'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $data_atividade = $_POST['data_atividade'] ?? '';
    
    // Aceitar tanto formato 'Y-m-d\TH:i' quanto 'Y-m-d H:i:s'
    if (!empty($data_atividade)) {
        if (strpos($data_atividade, 'T') !== false) {
            $data_mysql = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $data_atividade)));
        } else {
            $data_mysql = date('Y-m-d H:i:s', strtotime($data_atividade));
        }
    } else {
        $data_mysql = '';
    }
    
    if (!empty($atividade) && !empty($usuario) && !empty($data_atividade) && $atividade_id != '') {
        if ($historico->atualizarAtividade($atividade_id, $atividade, $data_mysql, $usuario)) {
            header("Location: view.php?id=" . $chamado_id . "&success=2");
        } else {
            header("Location: view.php?id=" . $chamado_id . "&error=3");
        }
    } else {
        header("Location: view.php?id=" . $chamado_id . "&error=2");
    }
} else {
    header("Location: index.php");
}
exit();
?>
