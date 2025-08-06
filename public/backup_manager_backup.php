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
            
            // Tentar backup via PHP
            $backup = "-- Backup ELUS Facilities Database\n";
            $backup .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
            $backup .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
            
            // Obter todas as tabelas
            $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                // Estrutura da tabela
                $createTable = $db->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
                $backup .= "DROP TABLE IF EXISTS `$table`;\n";
                $backup .= $createTable['Create Table'] . ";\n\n";
                
                // Dados da tabela
                $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($rows)) {
                    $columns = array_keys($rows[0]);
                    $backup .= "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES\n";
                    
                    $values = [];
                    foreach ($rows as $row) {
                        $rowValues = [];
                        foreach ($row as $value) {
                            if (is_null($value)) {
                                $rowValues[] = 'NULL';
                            } else {
                                $rowValues[] = "'" . addslashes($value) . "'";
                            }
                        }
                        $values[] = "(" . implode(', ', $rowValues) . ")";
                    }
                    $backup .= implode(",\n", $values) . ";\n\n";
                }
            }
            
            $backup .= "SET FOREIGN_KEY_CHECKS = 1;\n";
            
            // Enviar download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . strlen($backup));
            echo $backup;
            exit;
            
        } catch (Exception $e) {
            $error_message = "Erro ao gerar backup: " . $e->getMessage();
        }
    }
    
    elseif ($_GET['download'] === 'files') {
        try {
            // Verificar se a classe ZipArchive está disponível
            if (!class_exists('ZipArchive')) {
                throw new Exception('A extensão ZIP não está disponível no PHP. Verifique se a extensão php_zip está habilitada no php.ini e reinicie o Apache.');
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
            
            foreach ($iterator as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen(realpath('../')) + 1);
                    
                    // Pular alguns arquivos desnecessários
                    if (!preg_match('/\.(log|tmp|cache)$/i', $relativePath) && 
                        !strpos($relativePath, '.git') &&
                        !strpos($relativePath, 'node_modules')) {
                        $zip->addFile($filePath, $relativePath);
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
}

// Obter informações do sistema
try {
    $db_size_query = "SELECT 
        ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'db_size_mb'
        FROM information_schema.tables 
        WHERE table_schema = :db_name";
    $stmt = $db->prepare($db_size_query);
    $stmt->bindValue(':db_name', DB_NAME);
    $stmt->execute();
    $db_info = $stmt->fetch(PDO::FETCH_ASSOC);
    $db_size = $db_info['db_size_mb'] ?? 'N/A';
    
    // Contar tabelas
    $tables_count = count($db->query("SHOW TABLES")->fetchAll());
    
    // Contar registros principais
    $users_count = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $chamados_count = $db->query("SELECT COUNT(*) FROM chamados")->fetchColumn();
    
} catch (Exception $e) {
    $db_size = 'N/A';
    $tables_count = 'N/A';
    $users_count = 'N/A';
    $chamados_count = 'N/A';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Backup - ELUS Facilities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Dev Pages Clean Dark Theme */
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
        
        .dev-nav-links {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .dev-link {
            background: transparent;
            border: 1px solid #666;
            color: #888;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .dev-link:hover {
            border-color: #00ff41;
            color: #00ff41;
            text-decoration: none;
        }
        
        .dev-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 300;
            color: #fff;
            margin-bottom: 10px;
        }
        
        .page-subtitle {
            color: #888;
            font-size: 1rem;
        }
        
        /* Cards */
        .info-card {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .info-card h5 {
            color: #00ff41;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .backup-card {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .backup-card:hover {
            border-color: #00ff41;
            transform: translateY(-2px);
        }
        
        .backup-icon {
            font-size: 3rem;
            color: #00ff41;
            margin-bottom: 20px;
        }
        
        .backup-title {
            color: #fff;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .backup-desc {
            color: #888;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        /* Buttons */
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
            display: inline-block;
        }
        
        .btn-dev:hover {
            background: #00ff41;
            color: #000;
            text-decoration: none;
        }
        
        .btn-secondary {
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
        
        .btn-secondary:hover {
            border-color: #888;
            color: #fff;
            text-decoration: none;
        }
        
        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            text-align: center;
            padding: 20px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
            border-radius: 8px;
        }
        
        .info-number {
            font-size: 1.8rem;
            font-weight: 600;
            color: #00ff41;
            margin-bottom: 8px;
        }
        
        .info-label {
            color: #888;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Alerts */
        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid #28a745;
            color: #28a745;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid #dc3545;
            color: #dc3545;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .alert-warning {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid #ffc107;
            color: #ffc107;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .dev-nav {
                flex-direction: column;
                gap: 15px;
            }
            
            .dev-nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .dev-container {
                padding: 20px 15px;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .backup-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Custom Dev Header -->
    <div class="dev-header">
        <div class="dev-nav">
            <a href="index.php" class="dev-brand">
                <img src="images/logo-eluss.png" alt="ELUS Logo" class="dev-logo">
                <span class="dev-brand-text">ELUS Facilities</span>
                <span class="dev-badge">DEV</span>
            </a>
            
            <div class="dev-nav-links">
                <a href="dev_area.php" class="dev-link">
                    <i class="fas fa-arrow-left me-2"></i>Área Dev
                </a>
                <a href="user_manager.php" class="dev-link">
                    <i class="fas fa-users me-2"></i>Usuários
                </a>
                <a href="index.php" class="dev-link">
                    <i class="fas fa-home me-2"></i>Início
                </a>
            </div>
        </div>
    </div>
    
    <div class="dev-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">Sistema de Backup</h1>
            <p class="page-subtitle">Backup e recuperação de dados do sistema ELUS</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success_message): ?>
        <div class="alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo $success_message; ?>
        </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div class="alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo $error_message; ?>
        </div>
        <?php endif; ?>

        <!-- System Information -->
        <div class="info-card">
            <h5><i class="fas fa-info-circle me-2"></i>Informações do Sistema</h5>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-number"><?php echo $db_size; ?> MB</div>
                    <div class="info-label">Tamanho do BD</div>
                </div>
                <div class="info-item">
                    <div class="info-number"><?php echo $tables_count; ?></div>
                    <div class="info-label">Tabelas</div>
                </div>
                <div class="info-item">
                    <div class="info-number"><?php echo $users_count; ?></div>
                    <div class="info-label">Usuários</div>
                </div>
                <div class="info-item">
                    <div class="info-number"><?php echo $chamados_count; ?></div>
                    <div class="info-label">Chamados</div>
                </div>
            </div>
        </div>

        <!-- Warning -->
        <div class="alert-warning">
            <h6><i class="fas fa-exclamation-triangle me-2"></i>Importante:</h6>
            <ul class="mb-0">
                <li>Realize backups regularmente para garantir a segurança dos dados</li>
                <li>Mantenha os backups em local seguro e separado do servidor</li>
                <li>Teste a restauração dos backups periodicamente</li>
                <li>O backup de arquivos pode demorar alguns minutos dependendo do tamanho</li>
            </ul>
        </div>

        <!-- Backup Options -->
        <div class="row">
            <div class="col-md-6">
                <div class="backup-card">
                    <i class="fas fa-database backup-icon"></i>
                    <div class="backup-title">Backup do Banco de Dados</div>
                    <div class="backup-desc">
                        Faça o download completo da estrutura e dados do banco de dados MySQL.
                        Inclui todas as tabelas, relacionamentos e dados.
                    </div>
                    <a href="?download=database" class="btn-dev" onclick="return confirm('Iniciar download do backup do banco de dados?')">
                        <i class="fas fa-download me-2"></i>Baixar Backup BD
                    </a>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="backup-card">
                    <i class="fas fa-file-archive backup-icon"></i>
                    <div class="backup-title">Backup dos Arquivos</div>
                    <div class="backup-desc">
                        Faça o download de todos os arquivos do sistema incluindo código fonte,
                        configurações e recursos.
                    </div>
                    <a href="?download=files" class="btn-dev" onclick="return confirm('Iniciar download do backup dos arquivos? Isso pode demorar alguns minutos.')">
                        <i class="fas fa-download me-2"></i>Baixar Backup Arquivos
                    </a>
                </div>
            </div>
        </div>

        <!-- Additional Info -->
        <div class="info-card">
            <h5><i class="fas fa-lightbulb me-2"></i>Recomendações de Backup</h5>
            <div class="row">
                <div class="col-md-6">
                    <h6 style="color: #fff; margin-bottom: 15px;">Frequência Recomendada:</h6>
                    <ul style="color: #888; line-height: 1.8;">
                        <li><strong>Banco de Dados:</strong> Diário (automático) + Manual quando necessário</li>
                        <li><strong>Arquivos:</strong> Semanal ou após grandes alterações</li>
                        <li><strong>Configurações:</strong> Sempre antes de mudanças</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 style="color: #fff; margin-bottom: 15px;">Armazenamento:</h6>
                    <ul style="color: #888; line-height: 1.8;">
                        <li>Cloud storage (Google Drive, OneDrive, etc.)</li>
                        <li>Servidor de backup externo</li>
                        <li>Múltiplas cópias em locais diferentes</li>
                        <li>Versionamento com data/hora</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inicializar página
        document.addEventListener('DOMContentLoaded', function() {
            console.log('%c[ELUS DEV] Sistema de backup carregado', 'color: #00ff41; font-weight: bold;');
        });
    </script>
</body>
</html>
