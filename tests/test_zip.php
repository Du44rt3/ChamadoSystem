<?php
// Teste para verificar se ZipArchive está disponível
echo "<h2>Teste da classe ZipArchive</h2>";

// Verificar se a classe existe
if (class_exists('ZipArchive')) {
    echo "<p style='color: green;'>✓ Classe ZipArchive está disponível</p>";
    
    // Tentar criar uma instância
    try {
        $zip = new ZipArchive();
        echo "<p style='color: green;'>✓ ZipArchive instanciada com sucesso</p>";
        echo "<p>Versão da biblioteca: " . ZipArchive::LIBZIP_VERSION . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Erro ao instanciar ZipArchive: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Classe ZipArchive não está disponível</p>";
}

// Verificar extensões carregadas
echo "<h3>Extensões relacionadas ao ZIP:</h3>";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if (stripos($ext, 'zip') !== false) {
        echo "<p>- $ext</p>";
    }
}

// Verificar se a função zip_open existe (extensão zip alternativa)
if (function_exists('zip_open')) {
    echo "<p style='color: blue;'>ℹ Função zip_open disponível (extensão zip tradicional)</p>";
}

// Mostrar informações do PHP
echo "<h3>Informações do PHP:</h3>";
echo "<p>Versão PHP: " . phpversion() . "</p>";
echo "<p>SAPI: " . php_sapi_name() . "</p>";
?>
