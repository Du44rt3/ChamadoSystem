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
            // Permitir override via ENV (se houver) ou usar padrão do XAMPP
            $mysqldump = getenv('MYSQLDUMP_PATH') ?: "C:\\xampp\\mysql\\bin\\mysqldump.exe";
            $mysqldumpEsc = '"' . $mysqldump . '"';
            if (!file_exists($mysqldump)) {
                throw new Exception("mysqldump não encontrado em: $mysqldump. Defina MYSQLDUMP_PATH no ambiente, se necessário.");
            }
            $command = $mysqldumpEsc . " --user=" . DB_USER . " --password=" . DB_PASS . " --host=" . DB_HOST . " --single-transaction --routines --triggers " . DB_NAME;
            
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
                new RecursiveDirectoryIterator('../', FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            $fileCount = 0;
            foreach ($iterator as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    
                    // Verificar se o arquivo existe e é legível
                    if (!$filePath || !is_readable($filePath)) {
                        continue;
                    }
                    
                    $relativePath = substr($filePath, strlen(realpath('../')) + 1);
                    
                    // Pular alguns arquivos desnecessários
                    // Excluir itens desnecessários do backup de arquivos
                    $exclude = (
                        preg_match('/\.(log|tmp|cache)$/i', $relativePath) ||
                        strpos($relativePath, '.git') !== false ||
                        strpos($relativePath, 'node_modules') !== false ||
                        strpos($relativePath, 'cache' . DIRECTORY_SEPARATOR) === 0 ||
                        strpos($relativePath, 'uploads' . DIRECTORY_SEPARATOR) === 0 ||
                        strpos($relativePath, 'logs' . DIRECTORY_SEPARATOR) === 0 ||
                        basename($relativePath) === '.env'
                    );
                    if (!$exclude) {
                        
                        // Tentar adicionar o arquivo ao ZIP
                        if ($zip->addFile($filePath, $relativePath)) {
                            $fileCount++;
                        }
                    }
                }
            }
            
            $zip->close();
            
            // Verificar se o arquivo foi criado corretamente
            if (!file_exists($zipPath)) {
                throw new Exception("Erro ao criar arquivo ZIP.");
            }
            
            // Verificar se o arquivo não está vazio
            if (filesize($zipPath) == 0) {
                throw new Exception("Arquivo ZIP criado está vazio.");
            }
            
            // Limpar qualquer output anterior
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Enviar download com headers corretos
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($zipPath));
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            
            // Desabilitar compressão automática
            if (function_exists('apache_setenv')) {
                apache_setenv('no-gzip', 1);
            }
            
            // Ler e enviar o arquivo
            readfile($zipPath);
            unlink($zipPath);
            exit;
            
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
            $command = "\"C:\\xampp\\mysql\\bin\\mysqldump.exe\" --user=" . DB_USER . " --password=" . DB_PASS . " --host=" . DB_HOST . " --single-transaction --routines --triggers " . DB_NAME;
            $sqlOutput = shell_exec($command);
            
            if ($sqlOutput) {
                $zip->addFromString('database_backup.sql', $sqlOutput);
            }
            
            // 2. Adicionar arquivos do sistema
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator('../', FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            
            foreach ($iterator as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    
                    // Verificar se o arquivo existe e é legível
                    if (!$filePath || !is_readable($filePath)) {
                        continue;
                    }
                    
                    $relativePath = substr($filePath, strlen(realpath('../')) + 1);
                    
                    // Pular alguns arquivos desnecessários
                    $exclude = (
                        preg_match('/\.(log|tmp|cache)$/i', $relativePath) ||
                        strpos($relativePath, '.git') !== false ||
                        strpos($relativePath, 'node_modules') !== false ||
                        strpos($relativePath, 'cache' . DIRECTORY_SEPARATOR) === 0 ||
                        strpos($relativePath, 'uploads' . DIRECTORY_SEPARATOR) === 0 ||
                        strpos($relativePath, 'logs' . DIRECTORY_SEPARATOR) === 0 ||
                        basename($relativePath) === '.env'
                    );
                    if (!$exclude) {
                        
                        // Tentar adicionar o arquivo ao ZIP
                        $zip->addFile($filePath, 'files/' . $relativePath);
                    }
                }
            }
            
            $zip->close();
            
            // Verificar se o arquivo foi criado corretamente
            if (!file_exists($zipPath)) {
                throw new Exception("Erro ao criar arquivo ZIP completo.");
            }
            
            // Verificar se o arquivo não está vazio
            if (filesize($zipPath) == 0) {
                throw new Exception("Arquivo ZIP completo criado está vazio.");
            }
            
            // Limpar qualquer output anterior
            if (ob_get_level()) {
                ob_end_clean();
            }
            
            // Enviar download com headers corretos
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($zipPath));
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            
            // Desabilitar compressão automática
            if (function_exists('apache_setenv')) {
                apache_setenv('no-gzip', 1);
            }
            
            // Ler e enviar o arquivo
            readfile($zipPath);
            unlink($zipPath);
            exit;
            
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Dev Area Clean Dark Theme */
        * {
            box-sizing: border-box;
        }
        
        body {
            background: #0d0d0d;
            color: #00ff41;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        /* Custom Dev Header */
        .dev-header {
            background: linear-gradient(90deg, #000000 0%, #111111 100%);
            border-bottom: 1px solid #333;
            padding: 15px 0;
        }
        
        .dev-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .dev-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #00ff41;
        }
        
        .dev-brand:hover {
            color: #00ff41;
            text-decoration: none;
        }
        
        .dev-logo {
            width: 40px;
            height: 40px;
            margin-right: 15px;
            border-radius: 4px;
        }
        
        .dev-brand-text {
            font-size: 18px;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .dev-badge {
            background: rgba(0, 255, 65, 0.2);
            color: #00ff41;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            border: 1px solid #00ff41;
        }
        
        .dev-user {
            display: flex;
            align-items: center;
            color: #888;
        }
        
        .dev-user-name {
            margin-right: 10px;
            font-size: 14px;
        }
        
        .dev-exit {
            background: transparent;
            border: 1px solid #666;
            color: #888;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .dev-exit:hover {
            border-color: #00ff41;
            color: #00ff41;
            text-decoration: none;
        }
        
        .dev-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        /* Welcome Section */
        .welcome-section {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .welcome-title {
            font-size: 2.2rem;
            font-weight: 300;
            margin-bottom: 10px;
            color: #fff;
        }
        
        .welcome-subtitle {
            color: #888;
            font-size: 1rem;
            margin-bottom: 0;
        }
        
        /* Tools Grid */
        .tools-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .tool-card {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .tool-card:hover {
            border-color: #00ff41;
            transform: translateY(-2px);
        }
        
        .tool-icon {
            font-size: 2.5rem;
            color: #00ff41;
            margin-bottom: 20px;
        }
        
        .tool-title {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .tool-desc {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        
        .btn-dev {
            background: transparent;
            border: 1px solid #00ff41;
            color: #00ff41;
            padding: 12px 25px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-dev:hover {
            background: #00ff41;
            color: #000;
            text-decoration: none;
        }
        
        /* Alert Styles */
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid #dc3545;
            color: #ff6b6b;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border: 1px solid #28a745;
            color: #51cf66;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        /* Info Section */
        .info-section {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 30px;
        }
        
        .info-title {
            color: #fff;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .info-title i {
            margin-right: 10px;
            color: #00ff41;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            color: #888;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .info-value {
            color: #fff;
        }
        
        .text-success {
            color: #51cf66 !important;
        }
        
        .text-danger {
            color: #ff6b6b !important;
        }
        
        /* Back Button */
        .back-section {
            text-align: center;
            margin-top: 40px;
        }
        
        .btn-back {
            background: transparent;
            border: 1px solid #666;
            color: #888;
            padding: 12px 25px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            border-color: #00ff41;
            color: #00ff41;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <!-- Custom Dev Header -->
    <header class="dev-header">
        <nav class="dev-nav">
            <a href="dev_area.php" class="dev-brand">
                <img src="images/logo-eluss.png" alt="ELUS" class="dev-logo">
                <span class="dev-brand-text">ELUS Facilities</span>
                <span class="dev-badge">BACKUP</span>
            </a>
            
            <div class="dev-user">
                <span class="dev-user-name">
                    <i class="fas fa-user-cog"></i> <?php echo $_SESSION['nome'] ?? 'Developer'; ?>
                </span>
                <a href="logout.php" class="dev-exit">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="dev-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="welcome-title">
                <i class="fas fa-download"></i> Gerenciador de Backup
            </h1>
            <p class="welcome-subtitle">Sistema de backup e restore - Acesso restrito para desenvolvedores</p>
        </div>
        
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
        
        <!-- Backup Tools -->
        <div class="tools-grid">
            <!-- Backup do Banco de Dados -->
            <div class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-database"></i>
                </div>
                <h3 class="tool-title">Backup do Banco</h3>
                <p class="tool-desc">Gera um arquivo SQL com todos os dados do banco de dados usando mysqldump</p>
                <a href="?download=database" class="btn-dev">
                    <i class="fas fa-download"></i> Baixar SQL
                </a>
            </div>
            
            <!-- Backup dos Arquivos -->
            <div class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-file-archive"></i>
                </div>
                <h3 class="tool-title">Backup dos Arquivos</h3>
                <p class="tool-desc">Gera um arquivo ZIP com todos os arquivos do sistema, excluindo logs e temporários</p>
                <a href="?download=files" class="btn-dev">
                    <i class="fas fa-download"></i> Baixar ZIP
                </a>
            </div>
            
            <!-- Backup Completo -->
            <div class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-archive"></i>
                </div>
                <h3 class="tool-title">Backup Completo</h3>
                <p class="tool-desc">Gera um arquivo ZIP com banco de dados + arquivos do sistema em um pacote único</p>
                <a href="?download=complete" class="btn-dev">
                    <i class="fas fa-download"></i> Backup Total
                </a>
            </div>
        </div>
        
        <!-- Informações do Sistema -->
        <div class="info-section">
            <h2 class="info-title">
                <i class="fas fa-info-circle"></i> Informações do Sistema
            </h2>
            <div class="info-grid">
                <div>
                    <div class="info-item">
                        <span class="info-label">Versão PHP:</span>
                        <span class="info-value"><?php echo phpversion(); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Sistema:</span>
                        <span class="info-value"><?php echo PHP_OS; ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Extensão ZIP:</span>
                        <span class="info-value <?php echo class_exists('ZipArchive') ? 'text-success' : 'text-danger'; ?>">
                            <?php echo class_exists('ZipArchive') ? 'Disponível' : 'Não disponível'; ?>
                        </span>
                    </div>
                </div>
                <div>
                    <div class="info-item">
                        <span class="info-label">Data/Hora:</span>
                        <span class="info-value"><?php echo date('d/m/Y H:i:s'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Diretório Temp:</span>
                        <span class="info-value"><?php echo sys_get_temp_dir(); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Temp Gravável:</span>
                        <span class="info-value <?php echo is_writable(sys_get_temp_dir()) ? 'text-success' : 'text-danger'; ?>">
                            <?php echo is_writable(sys_get_temp_dir()) ? 'Sim' : 'Não'; ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Back Button -->
        <div class="back-section">
            <a href="dev_area.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Voltar para Dev Area
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
