<?php
/**
 * Teste das funcionalidades implementadas
 */

require_once '../src/AuthMiddleware.php';
require_once '../src/DB.php';
require_once '../src/Chamado.php';
require_once '../src/ChamadoHistorico.php';

echo "<h2>Teste das Funcionalidades Implementadas</h2>";

$database = new DB();
$db = $database->getConnection();

// 1. Verificar se há chamados para testar
echo "<h3>1. Verificação de Chamados</h3>";
$query = "SELECT COUNT(*) as total FROM chamados";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<p>Total de chamados no sistema: <strong>" . $result['total'] . "</strong></p>";

if ($result['total'] > 0) {
    // Mostrar alguns chamados para teste
    $query = "SELECT id, codigo_chamado, status, data_limite_sla FROM chamados ORDER BY id DESC LIMIT 3";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $chamados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Últimos chamados:</h4>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Código</th><th>Status</th><th>SLA</th><th>Ações</th></tr>";
    
    foreach ($chamados as $chamado) {
        echo "<tr>";
        echo "<td>" . $chamado['id'] . "</td>";
        echo "<td>" . $chamado['codigo_chamado'] . "</td>";
        echo "<td>" . $chamado['status'] . "</td>";
        echo "<td>" . ($chamado['data_limite_sla'] ? date('d/m/Y H:i', strtotime($chamado['data_limite_sla'])) : 'Não definido') . "</td>";
        echo "<td><a href='../public/edit.php?id=" . $chamado['id'] . "' target='_blank'>Editar</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>Nenhum chamado encontrado. <a href='../public/add.php'>Criar um chamado de teste</a></p>";
}

// 2. Verificar histórico de atividades
echo "<h3>2. Verificação do Histórico</h3>";
$query = "SELECT COUNT(*) as total FROM historico_chamados";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<p>Total de atividades registradas: <strong>" . $result['total'] . "</strong></p>";

if ($result['total'] > 0) {
    $query = "SELECT h.*, c.codigo_chamado FROM historico_chamados h 
              LEFT JOIN chamados c ON h.chamado_id = c.id 
              ORDER BY h.data_atividade DESC LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $atividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h4>Últimas atividades:</h4>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Chamado</th><th>Atividade</th><th>Responsável</th><th>Data</th></tr>";
    
    foreach ($atividades as $atividade) {
        echo "<tr>";
        echo "<td>" . $atividade['codigo_chamado'] . "</td>";
        echo "<td>" . $atividade['atividade'] . "</td>";
        echo "<td>" . $atividade['responsavel'] . "</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($atividade['data_atividade'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// 3. Teste da funcionalidade de mudança de status
echo "<h3>3. Teste da Mudança de Status</h3>";
echo "<p>Para testar o registro automático de atividades:</p>";
echo "<ol>";
echo "<li>Acesse um chamado pelo link 'Editar' acima</li>";
echo "<li>Mude o status para 'Em Andamento' e salve</li>";
echo "<li>Verifique se apareceu a atividade 'Chamado colocado em andamento' no viewer</li>";
echo "<li>Mude o status para 'Fechado' e salve</li>";
echo "<li>Verifique se apareceu a atividade 'Chamado fechado' no viewer</li>";
echo "</ol>";

// 4. Teste da funcionalidade de SLA
echo "<h3>4. Teste da Edição de SLA</h3>";
echo "<p>Para testar a edição de SLA:</p>";
echo "<ol>";
echo "<li>Acesse um chamado pelo link 'Editar' acima</li>";
echo "<li>Altere a data/hora no campo 'Prazo SLA'</li>";
echo "<li>Clique no botão 'Atualizar SLA'</li>";
echo "<li>Verifique se aparece a mensagem de sucesso</li>";
echo "<li>Verifique se apareceu a atividade de atualização de SLA no viewer</li>";
echo "</ol>";

echo "<h3>✅ Funcionalidades Implementadas:</h3>";
echo "<ul>";
echo "<li><strong>Registro automático de atividades:</strong> Quando o status muda para 'em_andamento' ou 'fechado'</li>";
echo "<li><strong>Edição de SLA:</strong> Campo para editar o prazo SLA com atualização via AJAX</li>";
echo "<li><strong>Histórico completo:</strong> Todas as mudanças são registradas no histórico</li>";
echo "<li><strong>Interface melhorada:</strong> Botões, alertas e feedback visual</li>";
echo "</ul>";

echo "<p><a href='../public/index.php'>← Voltar para Lista de Chamados</a></p>";
?>
