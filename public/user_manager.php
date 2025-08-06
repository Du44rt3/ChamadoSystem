<?php
/**
 * User Manager - DEVELOPERS ONLY
 * ELUS Facilities - Sistema de Gerenciamento
 */

// Proteção de autenticação
require_once '../src/AuthMiddleware.php';
require_once '../src/SecurityValidator.php';

$validator = new SecurityValidator();

// Verificar se tem acesso de admin ou desenvolvedor
if (!$auth->isAdmin() && !$auth->isDeveloper()) {
    header("Location: index.php?error=access_denied");
    exit();
}

$success_message = '';
$error_message = '';

// Processar ações
if ($_POST) {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    
    switch ($action) {
        case 'toggle_status':
            $query = "UPDATE usuarios SET ativo = NOT ativo WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $user_id);
            if ($stmt->execute()) {
                $success_message = "Status do usuário alterado com sucesso!";
            } else {
                $error_message = "Erro ao alterar status do usuário.";
            }
            break;
            
        case 'reset_password':
            $new_password = 'user123'; // Senha padrão
            $hashed_password = $auth->hashPassword($new_password);
            
            $query = "UPDATE usuarios SET password = :password WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':id', $user_id);
            
            if ($stmt->execute()) {
                $success_message = "Senha resetada para 'user123'. Usuário deve alterar no primeiro login.";
            } else {
                $error_message = "Erro ao resetar senha.";
            }
            break;
            
        case 'delete_user':
            // Verificar se não é o próprio usuário
            if ($user_id == $current_user['id']) {
                $error_message = "Você não pode deletar sua própria conta!";
            } else {
                $query = "DELETE FROM usuarios WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $user_id);
                
                if ($stmt->execute()) {
                    $success_message = "Usuário deletado com sucesso!";
                } else {
                    $error_message = "Erro ao deletar usuário.";
                }
            }
            break;
            
        case 'edit_user':
            $nome = $validator->sanitizeString($_POST['nome'] ?? '');
            $username = $validator->sanitizeString($_POST['username'] ?? '');
            $email = $validator->sanitizeString($_POST['email'] ?? '');
            $new_level = $_POST['new_level'] ?? '';
            
            if (!empty($nome) && !empty($username) && !empty($email) && in_array($new_level, ['admin', 'desenvolvedor', 'usuario'])) {
                // Verificar se username já existe (exceto para o próprio usuário)
                $check_query = "SELECT id FROM usuarios WHERE username = :username AND id != :id";
                $check_stmt = $db->prepare($check_query);
                $check_stmt->bindParam(':username', $username);
                $check_stmt->bindParam(':id', $user_id);
                $check_stmt->execute();
                
                if ($check_stmt->rowCount() == 0) {
                    $query = "UPDATE usuarios SET nome = :nome, username = :username, email = :email, nivel_acesso = :nivel WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':nome', $nome);
                    $stmt->bindParam(':username', $username);
                    $stmt->bindParam(':email', $email);
                    $stmt->bindParam(':nivel', $new_level);
                    $stmt->bindParam(':id', $user_id);
                    
                    if ($stmt->execute()) {
                        $success_message = "Usuário editado com sucesso!";
                    } else {
                        $error_message = "Erro ao editar usuário.";
                    }
                } else {
                    $error_message = "Username já existe!";
                }
            } else {
                $error_message = "Todos os campos são obrigatórios!";
            }
            break;
    }
}

// Buscar todos os usuários
$query = "SELECT id, nome, username, email, nivel_acesso, ativo, data_criacao, ultimo_login 
          FROM usuarios ORDER BY nivel_acesso DESC, nome ASC";
$stmt = $db->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estatísticas
$total_users = count($usuarios);
$active_users = count(array_filter($usuarios, function($u) { return $u['ativo']; }));
$admin_users = count(array_filter($usuarios, function($u) { return $u['nivel_acesso'] === 'admin'; }));
$dev_users = count(array_filter($usuarios, function($u) { return $u['nivel_acesso'] === 'desenvolvedor'; }));
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - ELUS Facilities</title>
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
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            transition: border-color 0.3s ease;
        }
        
        .stat-card:hover {
            border-color: #00ff41;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 600;
            color: #00ff41;
            margin-bottom: 8px;
        }
        
        .stat-label {
            color: #888;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        /* Users Grid */
        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .user-card {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .user-card:hover {
            border-color: #00ff41;
            transform: translateY(-2px);
        }
        
        .user-header {
            background: rgba(0, 0, 0, 0.6);
            padding: 20px;
            border-bottom: 1px solid #333;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(45deg, #00ff41, #00cc33);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        
        .user-name {
            color: #fff;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .user-username {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .user-body {
            padding: 20px;
        }
        
        .user-info {
            margin-bottom: 15px;
        }
        
        .user-info-label {
            color: #888;
            font-size: 0.8rem;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .user-info-value {
            color: #fff;
            font-size: 0.9rem;
        }
        
        .user-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        /* Badges */
        .badge {
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-admin {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        
        .badge-dev {
            background: rgba(0, 255, 65, 0.2);
            color: #00ff41;
            border: 1px solid #00ff41;
        }
        
        .badge-secondary {
            background: rgba(108, 117, 125, 0.2);
            color: #6c757d;
            border: 1px solid #6c757d;
        }
        
        .badge-active {
            background: rgba(40, 167, 69, 0.2);
            color: #28a745;
            border: 1px solid #28a745;
        }
        
        .badge-inactive {
            background: rgba(108, 117, 125, 0.2);
            color: #6c757d;
            border: 1px solid #6c757d;
        }
        
        /* Buttons */
        .btn-dev {
            background: transparent;
            border: 1px solid #00ff41;
            color: #00ff41;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-dev:hover {
            background: #00ff41;
            color: #000;
            text-decoration: none;
        }
        
        .btn-warning {
            background: transparent;
            border: 1px solid #ffc107;
            color: #ffc107;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-warning:hover {
            background: #ffc107;
            color: #000;
            text-decoration: none;
        }
        
        .btn-danger {
            background: transparent;
            border: 1px solid #dc3545;
            color: #dc3545;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-danger:hover {
            background: #dc3545;
            color: #fff;
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
        
        /* Modal */
        .modal-content {
            background: #1a1a1a;
            border: 1px solid #333;
            color: #fff;
        }
        
        .modal-header {
            background: rgba(0, 0, 0, 0.6);
            border-bottom: 1px solid #333;
        }
        
        .modal-title {
            color: #00ff41;
        }
        
        .form-label {
            color: #888;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .form-control {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid #333;
            color: #fff;
            border-radius: 4px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            background: rgba(0, 0, 0, 0.5);
            border-color: #00ff41;
            color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(0, 255, 65, 0.25);
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
            
            .stats-grid,
            .users-grid {
                grid-template-columns: 1fr;
            }
            
            .user-actions {
                justify-content: space-between;
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
                <a href="register_user.php" class="dev-link">
                    <i class="fas fa-user-plus me-2"></i>Registrar
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
            <h1 class="page-title">Gerenciar Usuários</h1>
            <p class="page-subtitle">Administração completa de usuários do sistema</p>
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

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total de Usuários</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $active_users; ?></div>
                <div class="stat-label">Usuários Ativos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $admin_users; ?></div>
                <div class="stat-label">Administradores</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $dev_users; ?></div>
                <div class="stat-label">Desenvolvedores</div>
            </div>
        </div>

        <!-- Users Grid -->
        <div class="users-grid">
            <?php foreach ($usuarios as $usuario): ?>
            <div class="user-card">
                <div class="user-header">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($usuario['nome'], 0, 2)); ?>
                    </div>
                    <div class="user-name"><?php echo htmlspecialchars($usuario['nome']); ?></div>
                    <div class="user-username">@<?php echo htmlspecialchars($usuario['username']); ?></div>
                    <div>
                        <?php if ($usuario['nivel_acesso'] === 'admin'): ?>
                            <span class="badge badge-admin">ADMIN</span>
                        <?php elseif ($usuario['nivel_acesso'] === 'desenvolvedor'): ?>
                            <span class="badge badge-dev">DEV</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">USER</span>
                        <?php endif; ?>
                        
                        <?php if ($usuario['ativo']): ?>
                            <span class="badge badge-active">Ativo</span>
                        <?php else: ?>
                            <span class="badge badge-inactive">Inativo</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="user-body">
                    <div class="user-info">
                        <div class="user-info-label">Email</div>
                        <div class="user-info-value"><?php echo htmlspecialchars($usuario['email']); ?></div>
                    </div>
                    
                    <div class="user-info">
                        <div class="user-info-label">Criado em</div>
                        <div class="user-info-value"><?php echo date('d/m/Y H:i', strtotime($usuario['data_criacao'])); ?></div>
                    </div>
                    
                    <?php if ($usuario['ultimo_login']): ?>
                    <div class="user-info">
                        <div class="user-info-label">Último Login</div>
                        <div class="user-info-value"><?php echo date('d/m/Y H:i', strtotime($usuario['ultimo_login'])); ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="user-actions">
                        <button class="btn-dev" onclick="editUser(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nome']); ?>', '<?php echo htmlspecialchars($usuario['username']); ?>', '<?php echo htmlspecialchars($usuario['email']); ?>', '<?php echo $usuario['nivel_acesso']; ?>')">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="user_id" value="<?php echo $usuario['id']; ?>">
                            <button type="submit" class="btn-warning" onclick="return confirm('Confirma alteração de status?')">
                                <i class="fas fa-power-off"></i> 
                                <?php echo $usuario['ativo'] ? 'Desativar' : 'Ativar'; ?>
                            </button>
                        </form>
                        
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="action" value="reset_password">
                            <input type="hidden" name="user_id" value="<?php echo $usuario['id']; ?>">
                            <button type="submit" class="btn-warning" onclick="return confirm('Resetar senha para \\'user123\\'?')">
                                <i class="fas fa-key"></i> Reset
                            </button>
                        </form>
                        
                        <?php if ($usuario['id'] != $current_user['id']): ?>
                        <form method="POST" style="display: inline-block;">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="user_id" value="<?php echo $usuario['id']; ?>">
                            <button type="submit" class="btn-danger" onclick="return confirm('ATENÇÃO: Esta ação não pode ser desfeita! Confirma exclusão do usuário?')">
                                <i class="fas fa-trash"></i> Deletar
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        
                        <div class="mb-3">
                            <label for="edit_nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="edit_nome" name="nome" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_level" class="form-label">Nível de Acesso</label>
                            <select class="form-control" id="edit_level" name="new_level" required>
                                <option value="admin">Admin - Administrador</option>
                                <option value="desenvolvedor">Dev - Desenvolvedor</option>
                                <option value="usuario">User - Usuário</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-dev">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(id, nome, username, email, nivel) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_nome').value = nome;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_level').value = nivel;
            
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }

        // Inicializar página
        document.addEventListener('DOMContentLoaded', function() {
            console.log('%c[ELUS DEV] Gerenciador de usuários carregado', 'color: #00ff41; font-weight: bold;');
        });
    </script>
</body>
</html>
