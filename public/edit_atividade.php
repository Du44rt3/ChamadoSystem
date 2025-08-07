<?php
// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/Sao_Paulo');

// Incluir classes de segurança
include_once '../src/SecurityHelper.php';

try {
    // Verificar se é requisição POST
    SecurityHelper::requirePOST();
    
    // Validar token CSRF
    $csrf_token = SecurityHelper::getPostValue('csrf_token');
    if (!SecurityHelper::validateCSRFToken($csrf_token)) {
        throw new Exception('Token CSRF inválido');
    }
    
    include_once '../src/DB.php';
    include_once '../src/ChamadoHistorico.php';
    
    $database = new DB();
    $db = $database->getConnection();
    $historico = new ChamadoHistorico($db);
    
    // Validar e sanitizar entradas
    $atividade_id = SecurityHelper::validateId(SecurityHelper::getPostValue('atividade_id'));
    $chamado_id = SecurityHelper::validateId(SecurityHelper::getPostValue('chamado_id'));
    $atividade = SecurityHelper::validateText(SecurityHelper::getPostValue('atividade'), 2000, true);
    $usuario = SecurityHelper::validateText(SecurityHelper::getPostValue('usuario'), 100, true);
    $data_atividade = SecurityHelper::validateDateTime(SecurityHelper::getPostValue('data_atividade'));
    
    if ($historico->atualizarAtividade($atividade_id, $atividade, $data_atividade, $usuario)) {
        header("Location: view.php?id=" . $chamado_id . "&success=2");
    } else {
        header("Location: view.php?id=" . $chamado_id . "&error=3");
    }
    
} catch (InvalidArgumentException $e) {
    // Erro de validação
    $chamado_id = SecurityHelper::getPostValue('chamado_id', '1');
    header("Location: view.php?id=" . $chamado_id . "&error=2");
} catch (Exception $e) {
    // Erro de segurança
    header("Location: index.php?error=security");
}
exit();
?>
