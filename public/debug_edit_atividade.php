<?php
// Versão de debug melhorada
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

echo "<h3>Debug Edit Atividade - Versão Detalhada</h3>";
echo "<p><strong>Método:</strong> " . $_SERVER['REQUEST_METHOD'] . "</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h4>Dados POST Recebidos:</h4>";
    echo "<pre>" . print_r($_POST, true) . "</pre>";
    
    // Verificar se todos os campos necessários estão presentes
    $campos_necessarios = ['atividade_id', 'chamado_id', 'atividade', 'usuario', 'data_atividade'];
    $campos_faltando = [];
    
    foreach ($campos_necessarios as $campo) {
        if (!isset($_POST[$campo]) || empty($_POST[$campo])) {
            $campos_faltando[] = $campo;
        }
    }
    
    if (!empty($campos_faltando)) {
        echo "<p style='color: red;'><strong>Campos faltando:</strong> " . implode(', ', $campos_faltando) . "</p>";
        echo "<p>Verifique se o formulário está enviando todos os dados necessários.</p>";
        exit;
    }
    
    echo "<p style='color: green;'>Todos os campos necessários estão presentes.</p>";
    
    try {
        include_once '../src/DB.php';
        include_once '../src/ChamadoHistorico.php';
        
        $database = new DB();
        $db = $database->getConnection();
        echo "<p style='color: green;'>Conexão com banco estabelecida.</p>";
        
        $historico = new ChamadoHistorico($db);
        echo "<p style='color: green;'>Objeto ChamadoHistorico criado.</p>";
        
        $atividade_id = (int)$_POST['atividade_id'];
        $chamado_id = (int)$_POST['chamado_id'];
        $atividade = trim($_POST['atividade']);
        $usuario = trim($_POST['usuario']);
        $data_atividade = $_POST['data_atividade'];
        
        echo "<h4>Dados Processados:</h4>";
        echo "<ul>";
        echo "<li><strong>ID da Atividade:</strong> $atividade_id</li>";
        echo "<li><strong>ID do Chamado:</strong> $chamado_id</li>";
        echo "<li><strong>Usuário:</strong> $usuario</li>";
        echo "<li><strong>Data/Hora:</strong> $data_atividade</li>";
        echo "<li><strong>Atividade:</strong> " . htmlspecialchars(substr($atividade, 0, 100)) . "...</li>";
        echo "</ul>";
        
        echo "<p>Tentando atualizar atividade...</p>";
        
        $resultado = $historico->atualizarAtividade($atividade_id, $atividade, $data_atividade, $usuario);
        
        if ($resultado) {
            echo "<p style='color: green; font-size: 18px;'><strong>✓ Sucesso! Atividade atualizada com sucesso!</strong></p>";
            echo "<p>Redirecionando em 3 segundos...</p>";
            echo "<script>setTimeout(() => window.location.href = 'view.php?id=$chamado_id&success=2', 3000);</script>";
        } else {
            echo "<p style='color: red; font-size: 18px;'><strong>✗ Erro! Falha ao atualizar atividade!</strong></p>";
            
            // Verificar erros do PDO
            $errorInfo = $db->errorInfo();
            echo "<h4>Informações de Erro do Banco:</h4>";
            echo "<pre>" . print_r($errorInfo, true) . "</pre>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'><strong>Exceção capturada:</strong> " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} else {
    echo "<p style='color: orange;'>Esta página deve ser acessada via POST.</p>";
    echo "<p>Clique no botão 'Editar' de uma atividade para testar.</p>";
}
?>
