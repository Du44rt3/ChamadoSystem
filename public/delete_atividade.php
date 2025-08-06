<?php
if (isset($_GET['id']) && isset($_GET['chamado_id'])) {
    include_once '../src/DB.php';
    include_once '../src/ChamadoHistorico.php';
    
    $database = new DB();
    $db = $database->getConnection();
    $historico = new ChamadoHistorico($db);
    
    $atividade_id = $_GET['id'];
    $chamado_id = $_GET['chamado_id'];
    
    if ($historico->deletarAtividade($atividade_id)) {
        header("Location: view.php?id=" . $chamado_id . "&success=3");
    } else {
        header("Location: view.php?id=" . $chamado_id . "&error=4");
    }
} else {
    header("Location: index.php");
}
exit();
?>
