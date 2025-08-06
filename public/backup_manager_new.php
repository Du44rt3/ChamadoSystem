<?php
/**
 * Backup System - DEVELOPERS ONLY
 * ELUS Facilities - Sistema de Gerenciamento
 */

// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

// Verificar se tem acesso de admin ou desenvolvedor
if (!$auth->isAdmin() && !$auth->isDeveloper()) {
    header("Location: index.php?error=access_denied");
    exit();
}

$success_message = '';
$error_message = '';

// Processar download de backup
if (isset($_GET['download'])) {
    if ($_GET['download'] === 'database') {
        try {
            $filename = 'backup_elus_' . date('Y-m-d_H-i-s') . '.sql';
            
            // Configurações do banco
            require_once '../config/config.php';
            
            // Comando mysqldump
            $command = "\"C:\\xampp\\mysql\\bin\\mysqldump.exe\" --user={$config['username']} --password={$config['password']} --host={$config['host']} --single-transaction --routines --triggers {$config['database']}";
            
            // Executar comando
            $output = shell_exec($command);
            
            if ($output) {
                header('Content-Type: application/sql');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                echo $output;
                exit;
            } else {
                $error_message = "Erro ao gerar backup do banco de dados.";
            }
            
        } catch (Exception $e) {
            $error_message = "Erro ao gerar backup: " . $e->getMessage();
        }
    }
    
    elseif ($_GET['download'] === 'files') {
        try {
            // Verificar se a classe ZipArchive está disponível
            if (!class_exists('ZipArchive')) {
                $error_message = "A extensão ZIP não está disponível no PHP. Para resolver este problema:<br>";
                $error_message .= "1. Abra o arquivo C:\\xampp\\php\\php.ini<br>";
                $error_message .= "2. Procure por ';extension=zip'<br>";
                $error_message .= "3. Remova o ponto e vírgula: 'extension=zip'<br>";
                $error_message .= "4. Reinicie o Apache no painel do XAMPP";
                throw new Exception($error_message);
            }
            
            $filename = 'backup_files_elus_' . date('Y-m-d_H-i-s') . '.zip';
            $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
            
            $zip = new ZipArchive();
            $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            
            if ($result !== TRUE) {
                throw new Exception('Não foi possível criar o arquivo ZIP. Código de erro: ' . $result);
            }
            
            // Adicionar arquivos do sistema
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator('../'),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            $fileCount = 0;
            foreach ($iterator as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen(realpath('../')) + 1);
                    
                    // Pular alguns arquivos desnecessários
                    if (!preg_match('/\.(log|tmp|cache)$/i', $relativePath) && 
                        !strpos($relativePath, '.git') &&
                        !strpos($relativePath, 'node_modules') &&
                        !strpos($relativePath, '.env')) {
                        
                        $zip->addFile($filePath, $relativePath);
                        $fileCount++;
                    }
                }
            }
            
            $zip->close();
            
            // Enviar download
            if (file_exists($zipPath)) {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($zipPath));
                readfile($zipPath);
                unlink($zipPath);
                exit;
            } else {
                throw new Exception("Erro ao criar arquivo ZIP.");
            }
            
        } catch (Exception $e) {
            $error_message = "Erro ao criar backup de arquivos: " . $e->getMessage();
        }
    }
    
    elseif ($_GET['download'] === 'complete') {
        try {
            // Verificar se a classe ZipArchive está disponível
            if (!class_exists('ZipArchive')) {
                throw new Exception('A extensão ZIP não está disponível no PHP. Verifique a configuração do servidor.');
            }
            
            $filename = 'backup_completo_elus_' . date('Y-m-d_H-i-s') . '.zip';
            $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;
            
            $zip = new ZipArchive();
            $result = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
            
            if ($result !== TRUE) {
                throw new Exception('Não foi possível criar o arquivo ZIP.');
            }
            
            // 1. Backup do banco de dados
            require_once '../config/config.php';
            $command = "\"C:\\xampp\\mysql\\bin\\mysqldump.exe\" --user={$config['username']} --password={$config['password']} --host={$config['host']} --single-transaction --routines --triggers {$config['database']}";
            $sqlOutput = shell_exec($command);
            
            if ($sqlOutput) {
                $zip->addFromString('database_backup.sql', $sqlOutput);
            }
            
            // 2. Adicionar arquivos do sistema
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator('../'),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($iterator as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen(realpath('../')) + 1);
                    
                    // Pular alguns arquivos desnecessários
                    if (!preg_match('/\.(log|tmp|cache)$/i', $relativePath) && 
                        !strpos($relativePath, '.git') &&
                        !strpos($relativePath, 'node_modules')) {
                        
                        $zip->addFile($filePath, 'files/' . $relativePath);
                    }
                }
            }
            
            $zip->close();
            
            // Enviar download
            if (file_exists($zipPath)) {
                header('Content-Type: application/zip');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($zipPath));
                readfile($zipPath);
                unlink($zipPath);
                exit;
            } else {
                throw new Exception("Erro ao criar arquivo ZIP completo.");
            }
            
        } catch (Exception $e) {
            $error_message = "Erro ao criar backup completo: " . $e->getMessage();
        }
    }
}

// Configurações da página
$page_title = "Gerenciador de Backup";
$page_subtitle = "Backup e restore do sistema";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Manager - ELUS Facilities</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="apple-touch-icon" href="images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body>
    <?php 
    require_once '../src/header.php'; 
    ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h1><i class="fas fa-download"></i> Gerenciador de Backup</h1>
                <p class="text-muted">Sistema de backup e restore - Acesso restrito para desenvolvedores</p>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="row mt-4">
                    <!-- Backup do Banco de Dados -->
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-database fa-3x text-primary mb-3"></i>
                                <h5 class="card-title">Backup do Banco</h5>
                                <p class="card-text">Gera um arquivo SQL com todos os dados do banco de dados</p>
                                <a href="?download=database" class="btn btn-primary">
                                    <i class="fas fa-download"></i> Baixar SQL
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Backup dos Arquivos -->
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-file-archive fa-3x text-success mb-3"></i>
                                <h5 class="card-title">Backup dos Arquivos</h5>
                                <p class="card-text">Gera um arquivo ZIP com todos os arquivos do sistema</p>
                                <a href="?download=files" class="btn btn-success">
                                    <i class="fas fa-download"></i> Baixar ZIP
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Backup Completo -->
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-archive fa-3x text-warning mb-3"></i>
                                <h5 class="card-title">Backup Completo</h5>
                                <p class="card-text">Gera um arquivo ZIP com banco de dados + arquivos</p>
                                <a href="?download=complete" class="btn btn-warning">
                                    <i class="fas fa-download"></i> Backup Total
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Informações do Sistema -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-info-circle"></i> Informações do Sistema</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Versão PHP:</strong> <?php echo phpversion(); ?></p>
                                        <p><strong>Sistema:</strong> <?php echo PHP_OS; ?></p>
                                        <p><strong>Extensão ZIP:</strong> 
                                            <?php echo class_exists('ZipArchive') ? '<span class="text-success">Disponível</span>' : '<span class="text-danger">Não disponível</span>'; ?>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Data/Hora:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
                                        <p><strong>Diretório Temp:</strong> <?php echo sys_get_temp_dir(); ?></p>
                                        <p><strong>Temp Gravável:</strong> 
                                            <?php echo is_writable(sys_get_temp_dir()) ? '<span class="text-success">Sim</span>' : '<span class="text-danger">Não</span>'; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Voltar -->
                <div class="mt-4">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar para Lista
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
