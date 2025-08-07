<?php
/**
 * Teste de criação de ZIP para debug
 */

echo "<h2>Teste de Criação de ZIP</h2>";

// Verificar se ZipArchive está disponível
if (!class_exists('ZipArchive')) {
    echo "<p style='color: red;'>ZipArchive não está disponível!</p>";
    exit;
}

// Criar um arquivo ZIP de teste
$testZipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test_backup_' . time() . '.zip';
echo "<p>Criando arquivo teste: $testZipPath</p>";

$zip = new ZipArchive();
$result = $zip->open($testZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

if ($result !== TRUE) {
    echo "<p style='color: red;'>Erro ao criar ZIP: $result</p>";
    exit;
}

// Adicionar alguns arquivos de teste
$testFiles = [
    '../config/config.php',
    '../public/index.php',
    '../src/DB.php'
];

$addedFiles = 0;
foreach ($testFiles as $file) {
    if (file_exists($file) && is_readable($file)) {
        $relativePath = basename($file);
        if ($zip->addFile(realpath($file), $relativePath)) {
            echo "<p style='color: green;'>✓ Adicionado: $relativePath</p>";
            $addedFiles++;
        } else {
            echo "<p style='color: orange;'>⚠ Falha ao adicionar: $relativePath</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Arquivo não encontrado: $file</p>";
    }
}

// Adicionar conteúdo direto
$zip->addFromString('test.txt', 'Conteúdo de teste criado em ' . date('Y-m-d H:i:s'));
$addedFiles++;

echo "<p>Total de arquivos adicionados: $addedFiles</p>";

// Fechar o ZIP
$zip->close();

// Verificar o arquivo criado
if (file_exists($testZipPath)) {
    $fileSize = filesize($testZipPath);
    echo "<p style='color: green;'>✓ Arquivo ZIP criado com sucesso!</p>";
    echo "<p>Tamanho: $fileSize bytes</p>";
    
    // Tentar abrir o ZIP para verificar integridade
    $testZip = new ZipArchive();
    if ($testZip->open($testZipPath) === TRUE) {
        echo "<p style='color: green;'>✓ Arquivo ZIP é válido!</p>";
        echo "<p>Número de arquivos no ZIP: " . $testZip->numFiles . "</p>";
        
        // Listar arquivos
        echo "<h3>Arquivos no ZIP:</h3><ul>";
        for ($i = 0; $i < $testZip->numFiles; $i++) {
            $filename = $testZip->getNameIndex($i);
            echo "<li>$filename</li>";
        }
        echo "</ul>";
        
        $testZip->close();
    } else {
        echo "<p style='color: red;'>✗ Arquivo ZIP está corrompido!</p>";
    }
    
    // Oferecer download para teste
    echo "<p><a href='?download_test=" . urlencode(basename($testZipPath)) . "' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;'>Baixar arquivo de teste</a></p>";
    
} else {
    echo "<p style='color: red;'>✗ Falha ao criar arquivo ZIP!</p>";
}

// Processar download de teste
if (isset($_GET['download_test'])) {
    $testFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $_GET['download_test'];
    if (file_exists($testFile)) {
        // Limpar output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        header('Content-Type: application/octet-stream');
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="' . basename($testFile) . '"');
        header('Content-Length: ' . filesize($testFile));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        
        readfile($testFile);
        unlink($testFile);
        exit;
    }
}

echo "<p><a href='../public/backup_manager.php'>Voltar para Backup Manager</a></p>";
?>
