<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

try {
    // Verificar se é requisição POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    // Verificar campos obrigatórios
    $campos_obrigatorios = ['atividade_id', 'chamado_id', 'atividade', 'usuario', 'data_atividade'];
    foreach ($campos_obrigatorios as $campo) {
        if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
            throw new Exception("Campo obrigatório ausente: $campo");
        }
    }
    
    include_once '../src/DB.php';
    include_once '../src/ChamadoHistorico.php';
    
    $database = new DB();
    $db = $database->getConnection();
    $historico = new ChamadoHistorico($db);
    
    // Processar dados
    $atividade_id = (int)$_POST['atividade_id'];
    $chamado_id = (int)$_POST['chamado_id'];
    $atividade = trim($_POST['atividade']);
    $usuario = trim($_POST['usuario']);
    $data_atividade = $_POST['data_atividade'];
    
    // Validações básicas
    if ($atividade_id <= 0 || $chamado_id <= 0) {
        throw new Exception('IDs inválidos');
    }
    
    if (strlen($atividade) < 1 || strlen($atividade) > 2000) {
        throw new Exception('Atividade deve ter entre 1 e 2000 caracteres');
    }
    
    if (strlen($usuario) < 1 || strlen($usuario) > 100) {
        throw new Exception('Usuário deve ter entre 1 e 100 caracteres');
    }
    
    // Tentar atualizar
    if ($historico->atualizarAtividade($atividade_id, $atividade, $data_atividade, $usuario)) {
        header("Location: view.php?id=" . $chamado_id . "&success=2");
    } else {
        header("Location: view.php?id=" . $chamado_id . "&error=3");
    }
    
} catch (Exception $e) {
    // Em caso de erro, tentar voltar para o chamado ou para o index
    $chamado_id = isset($_POST['chamado_id']) ? (int)$_POST['chamado_id'] : 1;
    header("Location: view.php?id=" . $chamado_id . "&error=2");
}
exit();
?>
