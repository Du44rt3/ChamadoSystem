<?php
// Corrigir timezone para Brasil
date_default_timezone_set('America/Sao_Paulo');
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include_once '../src/DB.php';
    include_once '../src/ChamadoHistorico.php';
    
    $database = new DB();
    $db = $database->getConnection();
    $historico = new ChamadoHistorico($db);
    
    $chamado_id = $_POST['chamado_id'];
    $atividade = trim($_POST['atividade']);
    $usuario = trim($_POST['usuario']);
    $data_atividade = isset($_POST['data_atividade']) ? $_POST['data_atividade'] : null;
    
    if (!empty($atividade) && !empty($usuario) && !empty($chamado_id)) {
        // Se não foi especificada data, usar a atual
        if (empty($data_atividade)) {
            $data_mysql = date('Y-m-d H:i:s');
        } else {
            // Converter data para formato MySQL
            $data_mysql = date('Y-m-d H:i:s', strtotime($data_atividade));
        }
        
        // Evitar duplicidade: checar se já existe atividade igual para o chamado e horário
        if ($historico->existeAtividade($chamado_id, $atividade, $usuario, $data_mysql)) {
            header("Location: view.php?id=" . $chamado_id . "&error=duplicada");
            exit();
        }
        
        if ($historico->adicionarAtividade($chamado_id, $atividade, $usuario, $data_mysql)) {
            header("Location: view.php?id=" . $chamado_id . "&success=1");
        } else {
            header("Location: view.php?id=" . $chamado_id . "&error=1");
        }
    } else {
        header("Location: view.php?id=" . $chamado_id . "&error=2");
    }
} else {
    header("Location: index.php");
}
exit();
?>
