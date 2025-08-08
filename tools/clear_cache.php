<?php
/**
 * Script para limpar cache manualmente
 * Use quando houver problemas de inconsistência de status
 */

// Verificar se é uma execução via linha de comando ou web com parâmetro especial
$is_cli = php_sapi_name() === 'cli';
$is_web_admin = isset($_GET['admin_clear']) && $_GET['admin_clear'] === 'true';

if (!$is_cli && !$is_web_admin) {
    die('Acesso negado. Use via CLI ou com parâmetro admin_clear=true');
}

// Incluir dependências
require_once '../src/CacheManager.php';

echo $is_cli ? "Limpando cache do sistema...\n" : "<h3>Limpando cache do sistema...</h3>";

try {
    // Instanciar o cache manager
    $cache = new CacheManager('../cache');
    
    // Limpar todo o cache
    $cleared_count = $cache->clear();
    
    echo $is_cli ? "Cache limpo com sucesso!\n" : "<p style='color: green;'>Cache limpo com sucesso!</p>";
    echo $is_cli ? "Arquivos removidos: $cleared_count\n" : "<p>Arquivos removidos: $cleared_count</p>";
    
    // Se for execução web, redirecionar de volta
    if (!$is_cli) {
        echo "<p>Redirecionando em 3 segundos...</p>";
        echo "<script>setTimeout(() => window.history.back(), 3000);</script>";
    }
    
} catch (Exception $e) {
    echo $is_cli ? "Erro ao limpar cache: " . $e->getMessage() . "\n" : "<p style='color: red;'>Erro ao limpar cache: " . $e->getMessage() . "</p>";
    exit(1);
}

echo $is_cli ? "Concluído!\n" : "<p>Concluído!</p>";
?>
