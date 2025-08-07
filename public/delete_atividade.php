<?php
// Incluir classes de segurança
include_once '../src/SecurityHelper.php';

try {
    // Validar e sanitizar entradas
    $atividade_id = SecurityHelper::validateId(SecurityHelper::getGetValue('id'));
    $chamado_id = SecurityHelper::validateId(SecurityHelper::getGetValue('chamado_id'));
    
    include_once '../src/DB.php';
    include_once '../src/ChamadoHistorico.php';
    
    $database = new DB();
    $db = $database->getConnection();
    $historico = new ChamadoHistorico($db);
    
    if ($historico->deletarAtividade($atividade_id)) {
        header("Location: view.php?id=" . $chamado_id . "&success=3");
    } else {
        header("Location: view.php?id=" . $chamado_id . "&error=4");
    }
    
} catch (InvalidArgumentException $e) {
    // Erro de validação - redirecionar para index
    header("Location: index.php?error=invalid_id");
} catch (Exception $e) {
    // Erro geral
    header("Location: index.php?error=system");
}
exit();
?>
