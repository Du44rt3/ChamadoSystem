<?php
/**
 * Register User - DEVELOPERS ONLY
 * ELUS Facilities - Sistema de Registro de Usuários
 * Tema: Dev Area Dark
 */

// Proteção de autenticação
require_once '../src/AuthMiddleware.php';
require_once '../src/SecurityValidator.php';

$validator = new SecurityValidator();

// Verificar se tem acesso de desenvolvedor APENAS
if (!$auth->isDeveloper()) {
    header("Location: index.php?error=access_denied");
    exit();
}

$success_message = '';
$error_message = '';

// Processar registro
if ($_POST) {
    $nome = $validator->sanitizeString($_POST['nome'] ?? '');
    $username = $validator->sanitizeString($_POST['username'] ?? '');
    $email = $validator->sanitizeString($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $nivel_acesso = $_POST['nivel_acesso'] ?? 'usuario';
    
    // Validações
    if (empty($nome) || empty($username) || empty($email) || empty($password)) {
        $error_message = "Todos os campos são obrigatórios!";
    } elseif ($password !== $confirm_password) {
        $error_message = "As senhas não coincidem!";
    } elseif (strlen($password) < 6) {
        $error_message = "A senha deve ter pelo menos 6 caracteres!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Email inválido!";
    } elseif (!in_array($nivel_acesso, ['admin', 'desenvolvedor', 'usuario'])) {
        $error_message = "Nível de acesso inválido!";
    } else {
        // Verificar se username já existe
        $check_query = "SELECT id FROM usuarios WHERE username = :username";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':username', $username);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() > 0) {
            $error_message = "Username já existe!";
        } else {
            // Verificar se email já existe
            $check_email_query = "SELECT id FROM usuarios WHERE email = :email";
            $check_email_stmt = $db->prepare($check_email_query);
            $check_email_stmt->bindParam(':email', $email);
            $check_email_stmt->execute();
            
            if ($check_email_stmt->rowCount() > 0) {
                $error_message = "Email já está em uso!";
            } else {
                // Hash da senha
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Inserir usuário
                $insert_query = "INSERT INTO usuarios (nome, username, email, password, nivel_acesso, ativo) 
                                VALUES (:nome, :username, :email, :password, :nivel_acesso, 1)";
                $insert_stmt = $db->prepare($insert_query);
                $insert_stmt->bindParam(':nome', $nome);
                $insert_stmt->bindParam(':username', $username);
                $insert_stmt->bindParam(':email', $email);
                $insert_stmt->bindParam(':password', $password_hash);
                $insert_stmt->bindParam(':nivel_acesso', $nivel_acesso);
                
                if ($insert_stmt->execute()) {
                    $success_message = "Usuário '$username' registrado com sucesso!";
                    
                    // Log da ação
                    error_log("[REGISTER_USER] Usuário '$username' registrado por '{$current_user['username']}' - IP: {$_SERVER['REMOTE_ADDR']}");
                } else {
                    $error_message = "Erro ao registrar usuário. Tente novamente.";
                }
            }
        }
    }
}

// Obter estatísticas
$stats_query = "SELECT nivel_acesso, COUNT(*) as total FROM usuarios WHERE ativo = 1 GROUP BY nivel_acesso";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetchAll(PDO::FETCH_ASSOC);

$total_query = "SELECT COUNT(*) as total FROM usuarios WHERE ativo = 1";
$total_stmt = $db->prepare($total_query);
$total_stmt->execute();
$total_users = $total_stmt->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register User - Dev Area</title>
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
            max-width: 1200px;
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
            padding: 20px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #00ff41;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #888;
            font-size: 0.9rem;
        }
        
        /* Form Container */
        .form-container {
            background: rgba(0, 0, 0, 0.6);
            border: 1px solid #333;
            border-radius: 8px;
            padding: 40px;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            color: #00ff41;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }
        
        .form-control {
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid #333;
            color: #00ff41;
            padding: 12px 15px;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-control:focus {
            background: rgba(0, 0, 0, 0.9);
            border-color: #00ff41;
            color: #00ff41;
            box-shadow: 0 0 0 0.2rem rgba(0, 255, 65, 0.25);
        }
        
        .form-control::placeholder {
            color: #555;
        }
        
        .form-select {
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid #333;
            color: #00ff41;
            padding: 12px 15px;
            border-radius: 4px;
        }
        
        .form-select:focus {
            background: rgba(0, 0, 0, 0.9);
            border-color: #00ff41;
            color: #00ff41;
            box-shadow: 0 0 0 0.2rem rgba(0, 255, 65, 0.25);
        }
        
        /* Buttons */
        .btn-dev {
            background: #00ff41;
            color: #000;
            border: none;
            padding: 12px 30px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-dev:hover {
            background: #00cc33;
            color: #000;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 255, 65, 0.3);
        }
        
        .btn-secondary-dev {
            background: transparent;
            color: #888;
            border: 1px solid #333;
            padding: 12px 30px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-secondary-dev:hover {
            color: #00ff41;
            border-color: #00ff41;
            text-decoration: none;
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
        
        /* Responsive */
        @media (max-width: 768px) {
            .dev-container {
                padding: 20px 15px;
            }
            
            .form-container {
                padding: 25px;
            }
            
            .page-title h1 {
                font-size: 1.8rem;
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
                <span class="dev-brand-text">Register User</span>
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
            <h1><i class="fas fa-user-plus me-3"></i>Register User</h1>
            <p>Criar nova conta de usuário no sistema - Acesso exclusivo para desenvolvedores</p>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <?php foreach ($stats as $stat): ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stat['total']; ?></div>
                <div class="stat-label"><?php echo ucfirst($stat['nivel_acesso']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Form Container -->
        <div class="form-container">
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

            <!-- Form -->
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user me-2"></i>Nome Completo
                    </label>
                    <input type="text" name="nome" class="form-control" 
                           placeholder="Digite o nome completo" 
                           value="<?php echo isset($_POST['nome']) ? htmlspecialchars($_POST['nome']) : ''; ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-at me-2"></i>Username
                    </label>
                    <input type="text" name="username" class="form-control" 
                           placeholder="Digite o username (único)" 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-envelope me-2"></i>Email
                    </label>
                    <input type="email" name="email" class="form-control" 
                           placeholder="Digite o email" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock me-2"></i>Senha
                    </label>
                    <input type="password" name="password" class="form-control" 
                           placeholder="Mínimo 6 caracteres" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock me-2"></i>Confirmar Senha
                    </label>
                    <input type="password" name="confirm_password" class="form-control" 
                           placeholder="Confirme a senha" required>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-shield-alt me-2"></i>Nível de Acesso
                    </label>
                    <select name="nivel_acesso" class="form-select" required>
                        <option value="usuario" <?php echo (isset($_POST['nivel_acesso']) && $_POST['nivel_acesso'] == 'usuario') ? 'selected' : ''; ?>>
                            Usuário - Acesso básico
                        </option>
                        <option value="admin" <?php echo (isset($_POST['nivel_acesso']) && $_POST['nivel_acesso'] == 'admin') ? 'selected' : ''; ?>>
                            Admin - Acesso completo ao sistema
                        </option>
                        <option value="desenvolvedor" <?php echo (isset($_POST['nivel_acesso']) && $_POST['nivel_acesso'] == 'desenvolvedor') ? 'selected' : ''; ?>>
                            Desenvolvedor - Acesso total + ferramentas
                        </option>
                    </select>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn-dev me-3">
                        <i class="fas fa-user-plus me-2"></i>Registrar Usuário
                    </button>
                    <a href="dev_area.php" class="btn-secondary-dev">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
