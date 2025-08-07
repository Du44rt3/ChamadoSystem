<?php
// Corrigir timezone para Brasil
date_default_timezone_set('America/Sao_Paulo');

// Iniciar sessão da mesma forma que o Auth.php
if (session_status() == PHP_SESSION_NONE) {
    // Usar as mesmas configurações de sessão do Auth.php
    require_once '../config/config.php';
    ini_set('session.cookie_httponly', defined('SESSION_HTTPONLY') && SESSION_HTTPONLY ? 1 : 0);
    ini_set('session.cookie_secure', defined('SESSION_SECURE') && SESSION_SECURE ? 1 : 0);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', defined('SESSION_SAMESITE') ? SESSION_SAMESITE : 'Strict');
    session_name('ELUS_CHAMADOS_SESSION');
    session_start();
}

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
    $chamado_id = SecurityHelper::validateId(SecurityHelper::getPostValue('chamado_id'));
    $atividade = SecurityHelper::validateText(SecurityHelper::getPostValue('atividade'), 2000, true);
    $usuario = SecurityHelper::validateText(SecurityHelper::getPostValue('usuario'), 100, true);
    $data_atividade = SecurityHelper::validateDateTime(SecurityHelper::getPostValue('data_atividade', ''));
    
    // Evitar duplicidade: checar se já existe atividade igual para o chamado e horário
    if ($historico->existeAtividade($chamado_id, $atividade, $usuario, $data_atividade)) {
        header("Location: view.php?id=" . $chamado_id . "&error=duplicada");
        exit();
    }
    
    if ($historico->adicionarAtividade($chamado_id, $atividade, $usuario, $data_atividade)) {
        header("Location: view.php?id=" . $chamado_id . "&success=1");
    } else {
        header("Location: view.php?id=" . $chamado_id . "&error=1");
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
