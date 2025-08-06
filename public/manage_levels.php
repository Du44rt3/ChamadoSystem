<?php
/**
 * Manage Levels - DEVELOPERS ONLY
 * ELUS Facilities - Gerenciamento de Níveis/Cargos
 * Tema: Dev Area Dark
 */

// Proteção de autenticação
require_once '../src/AuthMiddleware.php';
require_once '../src/LevelManager.php';

// Verificar se tem acesso de desenvolvedor APENAS
if (!$auth->isDeveloper()) {
    header("Location: index.php?error=access_denied");
    exit();
}

$levelManager = new LevelManager($db);
$success_message = '';
$error_message = '';

// Processar ações
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    try {
        switch ($action) {
            case 'create':
                $nome = trim($_POST['nome'] ?? '');
                $descricao = trim($_POST['descricao'] ?? '');
                $cor = $_POST['cor'] ?? '#6c757d';
                
                // Construir permissões
                $permissoes = LevelManager::getDefaultPermissions();
                
                // Chamados
                $permissoes['chamados']['criar'] = isset($_POST['perm_chamados_criar']);
                $permissoes['chamados']['editar'] = isset($_POST['perm_chamados_editar']);
                $permissoes['chamados']['excluir'] = isset($_POST['perm_chamados_excluir']);
                $permissoes['chamados']['ver_todos'] = isset($_POST['perm_chamados_ver_todos']);
                
                // Usuários
                $permissoes['usuarios']['criar'] = isset($_POST['perm_usuarios_criar']);
                $permissoes['usuarios']['editar'] = isset($_POST['perm_usuarios_editar']);
                $permissoes['usuarios']['excluir'] = isset($_POST['perm_usuarios_excluir']);
                $permissoes['usuarios']['ver_todos'] = isset($_POST['perm_usuarios_ver_todos']);
                
                // Permissões avançadas (controle total para desenvolvedores)
                $permissoes['backup'] = isset($_POST['perm_backup']);
                $permissoes['logs'] = isset($_POST['perm_logs']);
                $permissoes['debug'] = isset($_POST['perm_debug']);
                $permissoes['security'] = isset($_POST['perm_security']);
                $permissoes['dev_area'] = isset($_POST['perm_dev_area']);
                $permissoes['manage_levels'] = isset($_POST['perm_manage_levels']);
                
                if ($levelManager->createLevel($nome, $descricao, $permissoes, $cor, $current_user['id'])) {
                    $success_message = "Nível '$nome' criado com sucesso!";
                }
                break;
                
            case 'update':
                $id = $_POST['level_id'] ?? 0;
                $nome = trim($_POST['nome'] ?? '');
                $descricao = trim($_POST['descricao'] ?? '');
                $cor = $_POST['cor'] ?? '#6c757d';
                
                // Construir permissões
                $permissoes = LevelManager::getDefaultPermissions();
                
                // Chamados
                $permissoes['chamados']['criar'] = isset($_POST['perm_chamados_criar']);
                $permissoes['chamados']['editar'] = isset($_POST['perm_chamados_editar']);
                $permissoes['chamados']['excluir'] = isset($_POST['perm_chamados_excluir']);
                $permissoes['chamados']['ver_todos'] = isset($_POST['perm_chamados_ver_todos']);
                
                // Usuários
                $permissoes['usuarios']['criar'] = isset($_POST['perm_usuarios_criar']);
                $permissoes['usuarios']['editar'] = isset($_POST['perm_usuarios_editar']);
                $permissoes['usuarios']['excluir'] = isset($_POST['perm_usuarios_excluir']);
                $permissoes['usuarios']['ver_todos'] = isset($_POST['perm_usuarios_ver_todos']);
                
                // Permissões avançadas (controle total para desenvolvedores)
                $permissoes['backup'] = isset($_POST['perm_backup']);
                $permissoes['logs'] = isset($_POST['perm_logs']);
                $permissoes['debug'] = isset($_POST['perm_debug']);
                $permissoes['security'] = isset($_POST['perm_security']);
                $permissoes['dev_area'] = isset($_POST['perm_dev_area']);
                $permissoes['manage_levels'] = isset($_POST['perm_manage_levels']);
                
                if ($levelManager->updateLevel($id, $nome, $descricao, $permissoes, $cor)) {
                    $success_message = "Nível '$nome' atualizado com sucesso!";
                }
                break;
                
            case 'deactivate':
                $id = $_POST['level_id'] ?? 0;
                if ($levelManager->deactivateLevel($id)) {
                    $success_message = "Nível desativado com sucesso!";
                }
                break;
                
            case 'delete_permanent':
                $id = $_POST['level_id'] ?? 0;
                if ($levelManager->deleteLevelPermanent($id)) {
                    $success_message = "Nível excluído permanentemente!";
                } else {
                    $error_message = "Erro ao excluir nível. Verifique se não há usuários vinculados.";
                }
                break;
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Obter dados
$levels = $levelManager->getAllLevels();
$stats = $levelManager->getLevelStats();
$colors = LevelManager::getAvailableColors();

// Level being edited
$editing_level = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    foreach ($levels as $level) {
        if ($level['id'] == $edit_id && $level['nivel_sistema'] === 'customizado') {
            $editing_level = $level;
            $editing_level['permissoes_decoded'] = json_decode($level['permissoes'], true);
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Levels - Dev Area</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Dev Area Dark Theme */
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
            max-width: 1400px;
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
            gap: 15px;
        }
        
        .dev-user-name {
            color: #888;
            font-size: 14px;
        }
        
        .dev-exit {
            color: #ff4444;
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid #ff4444;
            border-radius: 4px;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        
        .dev-exit:hover {
            background: #ff4444;
            color: white;
            text-decoration: none;
        }
        
        /* Main Container */
        .dev-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        /* Page Title */
        .page-title {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #333;
            border-radius: 8px;
        }
        
        .page-title h1 {
            color: #00ff41;
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .page-title p {
            color: #888;
            margin: 0;
            font-size: 1.1rem;
        }
        
        /* Grid Layout */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
            align-items: start;
        }
        
        /* Forms */
        .form-container {
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 30px;
        }
        
        .form-title {
            color: #00ff41;
            font-size: 1.3rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            color: #00ff41;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control, .form-select {
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid #333;
            color: #00ff41;
            padding: 10px 15px;
            border-radius: 4px;
            font-size: 0.95rem;
        }
        
        .form-control:focus, .form-select:focus {
            background: rgba(0, 0, 0, 0.9);
            border-color: #00ff41;
            color: #00ff41;
            box-shadow: 0 0 0 0.2rem rgba(0, 255, 65, 0.25);
        }
        
        .form-control::placeholder {
            color: #555;
        }
        
        /* Permission Groups */
        .permission-group {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }
        
        .permission-group-title {
            color: #00ff41;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .permission-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
        }
        
        .permission-item:last-child {
            margin-bottom: 0;
        }
        
        .form-check-input {
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid #333;
        }
        
        .form-check-input:checked {
            background-color: #00ff41;
            border-color: #00ff41;
        }
        
        .form-check-label {
            color: #888;
            font-size: 0.9rem;
        }
        
        /* Color Picker */
        .color-options {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 10px;
        }
        
        .color-option {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            border: 2px solid #333;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .color-option:hover, .color-option.selected {
            border-color: #00ff41;
            transform: scale(1.1);
        }
        
        /* Buttons */
        .btn-dev {
            background: #00ff41;
            color: #000;
            border: none;
            padding: 10px 25px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-dev:hover {
            background: #00cc33;
            color: #000;
            transform: translateY(-2px);
        }
        
        .btn-secondary-dev {
            background: transparent;
            color: #888;
            border: 1px solid #333;
            padding: 10px 25px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        
        .btn-secondary-dev:hover {
            color: #00ff41;
            border-color: #00ff41;
            text-decoration: none;
        }
        
        /* Levels List */
        .levels-container {
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 20px;
        }
        
        .level-item {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .level-item:hover {
            border-color: #00ff41;
        }
        
        .level-item:last-child {
            margin-bottom: 0;
        }
        
        .level-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .level-name {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .level-badge {
            width: 15px;
            height: 15px;
            border-radius: 3px;
            border: 1px solid #333;
        }
        
        .level-title {
            font-weight: 600;
            color: #00ff41;
        }
        
        .level-type {
            font-size: 0.8rem;
            color: #888;
            background: rgba(0, 0, 0, 0.6);
            padding: 2px 8px;
            border-radius: 3px;
            border: 1px solid #333;
        }
        
        .level-description {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .level-stats {
            display: flex;
            gap: 15px;
            font-size: 0.85rem;
            color: #666;
        }
        
        .level-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-small {
            padding: 5px 10px;
            font-size: 0.8rem;
            border-radius: 3px;
        }
        
        /* Alerts */
        .alert-dev-success {
            background: rgba(0, 255, 65, 0.1);
            border: 1px solid #00ff41;
            color: #00ff41;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 25px;
        }
        
        .alert-dev-error {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid #ff4444;
            color: #ff4444;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 25px;
        }
        
        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: bold;
            color: #00ff41;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #888;
            font-size: 0.85rem;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .dev-container {
                padding: 20px 15px;
            }
        }
        
        @media (max-width: 768px) {
            .page-title h1 {
                font-size: 1.8rem;
            }
            
            .level-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .color-options {
                grid-template-columns: repeat(3, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Custom Dev Header -->
    <div class="dev-header">
        <div class="dev-nav">
            <a href="dev_area.php" class="dev-brand">
                <img src="images/logo-eluss.png" alt="ELUS Logo" class="dev-logo">
                <span class="dev-brand-text">Manage Levels</span>
                <span class="dev-badge">DEV</span>
            </a>
            
            <div class="dev-user">
                <span class="dev-user-name"><?php echo htmlspecialchars($current_user['nome']); ?></span>
                <a href="dev_area.php" class="dev-exit">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dev
                </a>
            </div>
        </div>
    </div>
    
    <div class="dev-container">
        <!-- Page Title -->
        <div class="page-title">
            <h1><i class="fas fa-user-cog me-3"></i>MANAGE LEVELS</h1>
            <p>Gerenciar níveis de acesso e permissões do sistema - Exclusivo para desenvolvedores</p>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($levels); ?></div>
                <div class="stat-label">Total Levels</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($levels, function($l) { return $l['nivel_sistema'] === 'customizado'; })); ?></div>
                <div class="stat-label">Custom Levels</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo array_sum(array_column($stats, 'total_usuarios')); ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo array_sum(array_column($stats, 'usuarios_ativos')); ?></div>
                <div class="stat-label">Active Users</div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($success_message): ?>
        <div class="alert-dev-success">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
        <div class="alert-dev-error">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>

        <!-- Content Grid -->
        <div class="content-grid">
            <!-- Levels List -->
            <div class="levels-container">
                <div class="form-title">
                    <i class="fas fa-list"></i>
                    <span>System Levels</span>
                </div>
                
                <?php foreach ($levels as $level): 
                    $level_stats = array_filter($stats, function($s) use ($level) { 
                        return $s['nome'] === $level['nome']; 
                    });
                    $level_stat = reset($level_stats) ?: ['total_usuarios' => 0, 'usuarios_ativos' => 0];
                ?>
                <div class="level-item">
                    <div class="level-header">
                        <div class="level-name">
                            <div class="level-badge" style="background-color: <?php echo $level['cor']; ?>"></div>
                            <div>
                                <div class="level-title"><?php echo htmlspecialchars($level['nome']); ?></div>
                                <div class="level-type"><?php echo $level['nivel_sistema'] === 'sistema' ? 'Sistema' : 'Customizado'; ?></div>
                            </div>
                        </div>
                        
                        <?php if ($level['nivel_sistema'] === 'customizado'): ?>
                        <div class="level-actions">
                            <a href="?edit=<?php echo $level['id']; ?>" class="btn-secondary-dev btn-small">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Desativar este nível? Usuários com este nível não poderão mais acessar o sistema.')">
                                <input type="hidden" name="action" value="deactivate">
                                <input type="hidden" name="level_id" value="<?php echo $level['id']; ?>">
                                <button type="submit" class="btn-secondary-dev btn-small" style="color: #ffc107; border-color: #ffc107;">
                                    <i class="fas fa-ban"></i> Disable
                                </button>
                            </form>
                            
                            <form method="POST" style="display: inline;" onsubmit="return confirm('ATENÇÃO: Excluir permanentemente este nível? Esta ação não pode ser desfeita! Certifique-se de que não há usuários vinculados.')">
                                <input type="hidden" name="action" value="delete_permanent">
                                <input type="hidden" name="level_id" value="<?php echo $level['id']; ?>">
                                <button type="submit" class="btn-secondary-dev btn-small" style="color: #ff4444; border-color: #ff4444;">
                                    <i class="fas fa-trash-alt"></i> Delete
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="level-description">
                        <?php echo htmlspecialchars($level['descricao']); ?>
                    </div>
                    
                    <div class="level-stats">
                        <span><i class="fas fa-users me-1"></i><?php echo $level_stat['total_usuarios']; ?> usuários</span>
                        <span><i class="fas fa-user-check me-1"></i><?php echo $level_stat['usuarios_ativos']; ?> ativos</span>
                        <span><i class="fas fa-calendar me-1"></i><?php echo date('d/m/Y', strtotime($level['created_at'])); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Create/Edit Form -->
            <div class="form-container">
                <div class="form-title">
                    <i class="fas fa-<?php echo $editing_level ? 'edit' : 'plus'; ?>"></i>
                    <span><?php echo $editing_level ? 'Editar Nível' : 'Criar Novo Nível'; ?></span>
                </div>
                
                <?php if ($editing_level): ?>
                <div class="alert-dev-success">
                    <i class="fas fa-info-circle me-2"></i>Editando: <?php echo htmlspecialchars($editing_level['nome']); ?>
                    <a href="manage_levels.php" style="color: #00ff41; margin-left: 10px;">[Cancelar]</a>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="action" value="<?php echo $editing_level ? 'update' : 'create'; ?>">
                    <?php if ($editing_level): ?>
                    <input type="hidden" name="level_id" value="<?php echo $editing_level['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-tag me-2"></i>Nome do Nível
                        </label>
                        <input type="text" name="nome" class="form-control" 
                               placeholder="Ex: Supervisor, Técnico, etc." 
                               value="<?php echo $editing_level ? htmlspecialchars($editing_level['nome']) : ''; ?>" 
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-align-left me-2"></i>Descrição
                        </label>
                        <textarea name="descricao" class="form-control" rows="3" 
                                  placeholder="Descreva as responsabilidades deste nível..."><?php echo $editing_level ? htmlspecialchars($editing_level['descricao']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-palette me-2"></i>Cor do Badge
                        </label>
                        <div class="color-options">
                            <?php foreach ($colors as $color => $name): ?>
                            <div class="color-option <?php echo ($editing_level && $editing_level['cor'] === $color) || (!$editing_level && $color === '#6c757d') ? 'selected' : ''; ?>" 
                                 style="background-color: <?php echo $color; ?>" 
                                 onclick="selectColor('<?php echo $color; ?>', this)" 
                                 title="<?php echo $name; ?>"></div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="cor" id="selected_color" value="<?php echo $editing_level ? $editing_level['cor'] : '#6c757d'; ?>">
                    </div>

                    <!-- Permissões de Chamados -->
                    <div class="permission-group">
                        <div class="permission-group-title">
                            <i class="fas fa-ticket-alt"></i>
                            <span>Permissões - Chamados</span>
                        </div>
                        
                        <div class="permission-item">
                            <input type="checkbox" class="form-check-input" id="perm_chamados_criar" name="perm_chamados_criar" 
                                   <?php echo ($editing_level && $editing_level['permissoes_decoded']['chamados']['criar']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_chamados_criar">Criar chamados</label>
                        </div>
                        
                        <div class="permission-item">
                            <input type="checkbox" class="form-check-input" id="perm_chamados_editar" name="perm_chamados_editar"
                                   <?php echo ($editing_level && $editing_level['permissoes_decoded']['chamados']['editar']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_chamados_editar">Editar chamados</label>
                        </div>
                        
                        <div class="permission-item">
                            <input type="checkbox" class="form-check-input" id="perm_chamados_excluir" name="perm_chamados_excluir"
                                   <?php echo ($editing_level && $editing_level['permissoes_decoded']['chamados']['excluir']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_chamados_excluir">Excluir chamados</label>
                        </div>
                        
                        <div class="permission-item">
                            <input type="checkbox" class="form-check-input" id="perm_chamados_ver_todos" name="perm_chamados_ver_todos"
                                   <?php echo ($editing_level && $editing_level['permissoes_decoded']['chamados']['ver_todos']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_chamados_ver_todos">Ver todos os chamados</label>
                        </div>
                    </div>

                    <!-- Permissões de Usuários -->
                    <div class="permission-group">
                        <div class="permission-group-title">
                            <i class="fas fa-users"></i>
                            <span>Permissões - Usuários</span>
                        </div>
                        
                        <div class="permission-item">
                            <input type="checkbox" class="form-check-input" id="perm_usuarios_criar" name="perm_usuarios_criar"
                                   <?php echo ($editing_level && $editing_level['permissoes_decoded']['usuarios']['criar']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_usuarios_criar">Criar usuários</label>
                        </div>
                        
                        <div class="permission-item">
                            <input type="checkbox" class="form-check-input" id="perm_usuarios_editar" name="perm_usuarios_editar"
                                   <?php echo ($editing_level && $editing_level['permissoes_decoded']['usuarios']['editar']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_usuarios_editar">Editar usuários</label>
                        </div>
                        
                        <div class="permission-item">
                            <input type="checkbox" class="form-check-input" id="perm_usuarios_excluir" name="perm_usuarios_excluir"
                                   <?php echo ($editing_level && $editing_level['permissoes_decoded']['usuarios']['excluir']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_usuarios_excluir">Excluir usuários</label>
                        </div>
                        
                        <div class="permission-item">
                            <input type="checkbox" class="form-check-input" id="perm_usuarios_ver_todos" name="perm_usuarios_ver_todos"
                                   <?php echo ($editing_level && $editing_level['permissoes_decoded']['usuarios']['ver_todos']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_usuarios_ver_todos">Ver todos os usuários</label>
                        </div>
                    </div>

                    <!-- Permissões Avançadas -->
                    <div class="permission-group">
                        <div class="permission-group-title">
                            <i class="fas fa-cogs"></i>
                            <span>Permissões Avançadas</span>
                            <small style="color: #888; font-weight: normal; margin-left: 10px;">(Use com cuidado)</small>
                        </div>
                        
                        <div class="permission-item">
                            <input type="checkbox" class="form-check-input" id="perm_backup" name="perm_backup"
                                   <?php echo ($editing_level && $editing_level['permissoes_decoded']['backup']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_backup">Sistema de Backup</label>
                        </div>
                        
                        <div class="permission-item">
                            <input type="checkbox" class="form-check-input" id="perm_logs" name="perm_logs"
                                   <?php echo ($editing_level && $editing_level['permissoes_decoded']['logs']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_logs">Visualizar Logs</label>
                        </div>
                        
                        <div class="permission-item">
                            <input type="checkbox" class="form-check-input" id="perm_debug" name="perm_debug"
                                   <?php echo ($editing_level && $editing_level['permissoes_decoded']['debug']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_debug">Ferramentas de Debug</label>
                        </div>
                        
                        <div class="permission-item">
                            <input type="checkbox" class="form-check-input" id="perm_security" name="perm_security"
                                   <?php echo ($editing_level && $editing_level['permissoes_decoded']['security']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_security">Verificação de Segurança</label>
                        </div>
                        
                        <div class="permission-item">
                            <input type="checkbox" class="form-check-input" id="perm_dev_area" name="perm_dev_area"
                                   <?php echo ($editing_level && $editing_level['permissoes_decoded']['dev_area']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_dev_area">Acesso à Dev Area</label>
                        </div>
                        
                        <div class="permission-item">
                            <input type="checkbox" class="form-check-input" id="perm_manage_levels" name="perm_manage_levels"
                                   <?php echo ($editing_level && $editing_level['permissoes_decoded']['manage_levels']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_manage_levels">Gerenciar Níveis/Cargos</label>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn-dev me-3">
                            <i class="fas fa-<?php echo $editing_level ? 'save' : 'plus'; ?> me-2"></i>
                            <?php echo $editing_level ? 'Atualizar Nível' : 'Criar Nível'; ?>
                        </button>
                        <?php if ($editing_level): ?>
                        <a href="manage_levels.php" class="btn-secondary-dev">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectColor(color, element) {
            // Remove selected class from all
            document.querySelectorAll('.color-option').forEach(el => el.classList.remove('selected'));
            
            // Add selected class to clicked
            element.classList.add('selected');
            
            // Update hidden input
            document.getElementById('selected_color').value = color;
        }
    </script>
</body>
</html>
