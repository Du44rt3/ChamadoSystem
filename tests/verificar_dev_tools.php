<?php
/**
 * Verificação das Ferramentas do Dev Area
 * Este script verifica se todas as ferramentas do dev area estão funcionando
 */

echo "<h2>🔧 Verificação das Ferramentas Dev Area</h2>";
echo "<hr>";

// Lista de ferramentas que devem estar funcionando
$tools = [
    'test_connection.php' => 'Teste de Conexão',
    'debug_session.php' => 'Debug de Sessão',
    'diagnostico_conexao.php' => 'Diagnóstico de Conexão',
    'diagnostico_outlook.php' => 'Diagnóstico Outlook',
    'test_funcionalidades.php' => 'Teste de Funcionalidades',
    'test_anexos_system.php' => 'Teste Sistema de Anexos',
    'verificacao_correcoes.php' => 'Verificação de Correções',
    'verificacao_completa_anexos.php' => 'Verificação Completa Anexos',
    'teste_auth.php' => 'Teste de Autenticação'
];

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 5px;'>";

foreach ($tools as $file => $name) {
    $fullPath = __DIR__ . '/' . $file;
    
    echo "<div style='margin: 10px 0; padding: 10px; border-left: 4px solid ";
    
    if (file_exists($fullPath)) {
        // Verificar se o arquivo tem conteúdo válido
        $content = file_get_contents($fullPath);
        
        if (empty($content)) {
            echo "#ffc107; background: #fff3cd;'>";
            echo "⚠️ <strong>$name</strong> - Arquivo vazio";
        } elseif (strpos($content, '<?php') === false) {
            echo "#dc3545; background: #f8d7da;'>";
            echo "❌ <strong>$name</strong> - Não é um arquivo PHP válido";
        } elseif (strpos($content, 'require_once') !== false || strpos($content, 'include') !== false) {
            echo "#28a745; background: #d4edda;'>";
            echo "✅ <strong>$name</strong> - OK (inclui dependências)";
        } else {
            echo "#17a2b8; background: #d1ecf1;'>";
            echo "ℹ️ <strong>$name</strong> - Arquivo válido";
        }
    } else {
        echo "#dc3545; background: #f8d7da;'>";
        echo "❌ <strong>$name</strong> - Arquivo não encontrado";
    }
    
    echo "</div>";
}

echo "</div>";

echo "<h3>📁 Estrutura do diretório tests/</h3>";
$files = scandir(__DIR__);
$phpFiles = array_filter($files, function($file) {
    return pathinfo($file, PATHINFO_EXTENSION) === 'php';
});

echo "<ul>";
foreach ($phpFiles as $file) {
    echo "<li>$file</li>";
}
echo "</ul>";

echo "<hr>";
echo "<p><strong>Total de arquivos PHP encontrados:</strong> " . count($phpFiles) . "</p>";
echo "<p><strong>Data/Hora:</strong> " . date('d/m/Y H:i:s') . "</p>";
?>
