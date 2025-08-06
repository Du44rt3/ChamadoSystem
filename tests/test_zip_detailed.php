<?php
/**
 * Teste de diagnóstico da ZipArchive
 */

echo "<h2>Diagnóstico da ZipArchive</h2>";

// 1. Verificar se a classe existe
if (class_exists('ZipArchive')) {
    echo "<p style='color: green;'>✓ Classe ZipArchive está disponível</p>";
    
    // 2. Tentar criar uma instância
    try {
        $zip = new ZipArchive();
        echo "<p style='color: green;'>✓ ZipArchive instanciada com sucesso</p>";
        
        // 3. Testar criação de arquivo
        $testFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_zip_' . time() . '.zip';
        echo "<p>Tentando criar arquivo: $testFile</p>";
        
        $result = $zip->open($testFile, ZipArchive::CREATE);
        if ($result === TRUE) {
            echo "<p style='color: green;'>✓ Arquivo ZIP criado com sucesso</p>";
            
            // Adicionar um arquivo de teste
            $zip->addFromString('test.txt', 'Conteúdo de teste');
            $zip->close();
            
            if (file_exists($testFile)) {
                echo "<p style='color: green;'>✓ Arquivo ZIP salvo com sucesso</p>";
                echo "<p>Tamanho: " . filesize($testFile) . " bytes</p>";
                
                // Limpar arquivo de teste
                unlink($testFile);
                echo "<p>Arquivo de teste removido</p>";
            } else {
                echo "<p style='color: red;'>✗ Arquivo ZIP não foi salvo</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ Erro ao criar arquivo ZIP. Código: $result</p>";
            
            // Mostrar códigos de erro possíveis
            $errors = [
                ZipArchive::ER_OK => 'Sem erro',
                ZipArchive::ER_MULTIDISK => 'Multi-disk zip archives not supported',
                ZipArchive::ER_RENAME => 'Renaming temporary file failed',
                ZipArchive::ER_CLOSE => 'Closing zip archive failed',
                ZipArchive::ER_SEEK => 'Seek error',
                ZipArchive::ER_READ => 'Read error',
                ZipArchive::ER_WRITE => 'Write error',
                ZipArchive::ER_CRC => 'CRC error',
                ZipArchive::ER_ZIPCLOSED => 'Containing zip archive was closed',
                ZipArchive::ER_NOENT => 'No such file',
                ZipArchive::ER_EXISTS => 'File already exists',
                ZipArchive::ER_OPEN => 'Can\'t open file',
                ZipArchive::ER_TMPOPEN => 'Failure to create temporary file',
                ZipArchive::ER_ZLIB => 'Zlib error',
                ZipArchive::ER_MEMORY => 'Memory allocation failure',
                ZipArchive::ER_CHANGED => 'Entry has been changed',
                ZipArchive::ER_COMPNOTSUPP => 'Compression method not supported',
                ZipArchive::ER_EOF => 'Premature EOF',
                ZipArchive::ER_INVAL => 'Invalid argument',
                ZipArchive::ER_NOZIP => 'Not a zip archive',
                ZipArchive::ER_INTERNAL => 'Internal error',
                ZipArchive::ER_INCONS => 'Zip archive inconsistent',
                ZipArchive::ER_REMOVE => 'Can\'t remove file',
                ZipArchive::ER_DELETED => 'Entry has been deleted'
            ];
            
            if (isset($errors[$result])) {
                echo "<p>Descrição do erro: " . $errors[$result] . "</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Erro ao instanciar ZipArchive: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Classe ZipArchive não está disponível</p>";
}

// Verificar diretório temporário
echo "<h3>Informações do sistema:</h3>";
echo "<p>Diretório temporário: " . sys_get_temp_dir() . "</p>";
echo "<p>Diretório temporário existe: " . (is_dir(sys_get_temp_dir()) ? 'Sim' : 'Não') . "</p>";
echo "<p>Diretório temporário é gravável: " . (is_writable(sys_get_temp_dir()) ? 'Sim' : 'Não') . "</p>";

// Verificar extensões
echo "<h3>Extensões carregadas:</h3>";
$extensions = get_loaded_extensions();
foreach ($extensions as $ext) {
    if (stripos($ext, 'zip') !== false || stripos($ext, 'zlib') !== false) {
        echo "<p>- $ext</p>";
    }
}

echo "<h3>Configuração do PHP:</h3>";
echo "<p>Versão PHP: " . phpversion() . "</p>";
echo "<p>SAPI: " . php_sapi_name() . "</p>";
echo "<p>Sistema operacional: " . PHP_OS . "</p>";
?>
