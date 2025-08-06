<?php
require_once '../config/config.php';
require_once '../src/DB.php';
require_once '../src/Auth.php';
require_once '../src/SecurityValidator.php';

$database = new DB();
$db = $database->getConnection();
$auth = new Auth($db);

// Se já estiver logado, redirecionar
if ($auth->isLoggedIn()) {
    $redirect_url = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
    unset($_SESSION['redirect_after_login']);
    header("Location: $redirect_url");
    exit();
}

$error_message = '';
$success_message = '';

// Verificar se houve logout
if (isset($_GET['logout']) && $_GET['logout'] == 'success') {
    $success_message = 'Logout realizado com sucesso!';
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        // Verificar CSRF token
        if (!SecurityValidator::validateCSRF($_POST['csrf_token'] ?? '')) {
            $error_message = 'Token de segurança inválido. Tente novamente.';
        } else {
            // Rate limiting
            $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $rateLimit = SecurityValidator::checkRateLimit($clientIP, 5, 300);
            
            if (!$rateLimit['allowed']) {
                $minutes = ceil($rateLimit['retry_after'] / 60);
                $error_message = "Muitas tentativas de login. Tente novamente em {$minutes} minutos.";
            } else {
                $username = SecurityValidator::sanitizeString($_POST['username']);
                $password = $_POST['password'];
                
                // Validação básica
                if (empty($username) || empty($password)) {
                    $error_message = 'Por favor, preencha todos os campos.';
                } elseif (strlen($username) < 3 || strlen($username) > 50) {
                    $error_message = 'Nome de usuário deve ter entre 3 e 50 caracteres.';
                } elseif (strlen($password) < 6) {
                    $error_message = 'Senha deve ter pelo menos 6 caracteres.';
                } else {
                    $result = $auth->login($username, $password);
                    
                    if ($result['success']) {
                        $success_message = $result['message'];
                        
                        // Log de segurança
                        error_log("Login bem-sucedido: {$username} de {$clientIP} em " . date('Y-m-d H:i:s'));
                        
                        // Redirecionar após sucesso
                        $redirect_url = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : 'index.php';
                        unset($_SESSION['redirect_after_login']);
                        
                        echo "<script>
                            setTimeout(function() {
                                window.location.href = '$redirect_url';
                            }, 1500);
                        </script>";
                    } else {
                        // Log de tentativa de login falhada
                        error_log("Tentativa de login falhada: {$username} de {$clientIP} em " . date('Y-m-d H:i:s'));
                        $error_message = $result['message'];
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ELUS Facilities Sistema de Chamados</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="apple-touch-icon" href="images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        body {
            background: url('images/wpp.jpg') center center/cover no-repeat fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                rgba(30, 74, 140, 0.6) 0%, 
                rgba(15, 44, 92, 0.7) 50%, 
                rgba(30, 74, 140, 0.5) 100%);
            z-index: -1;
        }
        
        .login-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            width: 100%;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.96);
            border-radius: 15px;
            box-shadow: 
                0 25px 50px rgba(30, 74, 140, 0.2), 
                0 10px 20px rgba(0, 0, 0, 0.1),
                0 0 0 1px rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            width: 100%;
            max-width: 420px;
            padding: 40px;
            position: relative;
            animation: slideIn 0.6s ease-out;
        }
        
        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--elus-blue), var(--elus-yellow));
            border-radius: 15px 15px 0 0;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .login-logo {
            max-width: 100px;
            margin-bottom: 20px;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }
        
        .login-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--elus-blue);
            margin-bottom: 8px;
            letter-spacing: -0.025em;
        }
        
        .login-subtitle {
            color: var(--gray-600);
            font-size: 0.9rem;
            margin-bottom: 0;
            font-weight: 500;
        }
        
        .system-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, var(--elus-blue), var(--elus-blue-dark));
            color: white;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 15px;
            box-shadow: 0 2px 8px rgba(30, 74, 140, 0.3);
        }
        
        .form-floating {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-floating input {
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid var(--gray-200);
            border-radius: var(--border-radius-lg);
            transition: all 0.3s ease;
            font-size: 0.95rem;
            padding: 12px 16px;
            height: auto;
        }
        
        .form-floating input:focus {
            background: rgba(255, 255, 255, 1);
            border-color: var(--elus-blue);
            box-shadow: 0 0 0 0.2rem rgba(30, 74, 140, 0.15);
            outline: none;
        }
        
        .form-floating label {
            color: var(--gray-600);
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--elus-blue) 0%, var(--elus-blue-dark) 100%);
            border: none;
            border-radius: var(--border-radius-lg);
            padding: 12px 0;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            box-shadow: 0 4px 12px rgba(30, 74, 140, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(30, 74, 140, 0.4);
            color: white;
        }
        
        .btn-login:hover::before {
            left: 100%;
        }
        
        .btn-login:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(30, 74, 140, 0.3);
        }
        
        .alert {
            border-radius: var(--border-radius-lg);
            border: none;
            margin-bottom: 20px;
            padding: 12px 16px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            color: var(--danger-color);
            border-left: 4px solid var(--danger-color);
        }
        
        .alert-success {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: var(--gray-500);
            cursor: pointer;
            z-index: 10;
            transition: color 0.3s ease;
            padding: 4px;
        }
        
        .password-toggle:hover {
            color: var(--elus-blue);
        }
        
        .footer-info {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-200);
        }
        
        .footer-info small {
            color: var(--gray-500);
            font-size: 0.8rem;
            line-height: 1.4;
        }
        
        /* Responsive */
        @media (max-width: 480px) {
            body {
                padding: 15px;
            }
            
            .login-container {
                padding: 30px 25px;
                max-width: 100%;
                margin: 0;
            }
            
            .login-title {
                font-size: 1.4rem;
            }
            
            .login-subtitle {
                font-size: 0.85rem;
            }
            
            .btn-login {
                padding: 14px 0;
                font-size: 1rem;
            }
        }
        
        @media (max-height: 600px) {
            .login-container {
                padding: 25px;
                margin: 10px 0;
            }
            
            .login-header {
                margin-bottom: 25px;
            }
            
            .footer-info {
                margin-top: 20px;
                padding-top: 15px;
            }
        }
        
        /* Loading state */
        .form-loading .btn-login {
            background: var(--gray-400);
            cursor: not-allowed;
            transform: none;
        }
        
        .form-loading .btn-login:hover {
            transform: none;
            box-shadow: 0 4px 12px rgba(30, 74, 140, 0.3);
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <!-- Barra superior colorida (padrão ELUS) -->
            
            <!-- Header com Logo -->
            <div class="login-header">
                <img src="images/logo-eluss.png" alt="ELUS Logo" class="login-logo">
                <h2 class="login-title">Sistema de Chamados</h2>
                <p class="login-subtitle">Grupo Elus - Facilities</p>
                <div class="system-badge">
                    <i class="fas fa-shield-alt"></i>
                    Acesso Seguro
                </div>
            </div>
            
            <!-- Mensagens de Erro/Sucesso -->
            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div><?php echo htmlspecialchars($error_message); ?></div>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <div><?php echo htmlspecialchars($success_message); ?></div>
                </div>
            <?php endif; ?>
            
            <!-- Formulário de Login -->
            <form method="POST" action="login.php" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $auth->generateCSRFToken(); ?>">
                
                <div class="form-floating">
                    <input type="text" class="form-control" id="username" name="username" 
                           placeholder="Usuário" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <label for="username"><i class="fas fa-user me-2"></i>Usuário</label>
                </div>
                
                <div class="form-floating position-relative">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Senha" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Senha</label>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Entrar no Sistema
                    </button>
                </div>
            </form>
            
            <!-- Footer -->
            <div class="footer-info">
                <small>
                    <i class="fas fa-info-circle me-1"></i>
                    Problemas para acessar? Entre em contato com o administrador
                </small>
                <br>
                <small class="mt-2 d-block">
                    <i class="fas fa-copyright me-1"></i>
                    <?php echo date('Y'); ?> Grupo Elus - Todos os direitos reservados
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Inicialização quando a página carrega
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus no primeiro campo
            document.getElementById('username').focus();
            
            // Melhorar feedback visual nos campos
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentElement.classList.remove('focused');
                    }
                });
            });
        });
        
        // Animação de loading no submit
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const form = this;
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            // Adicionar classe de loading
            form.classList.add('form-loading');
            
            // Mudar texto do botão
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Conectando...';
            submitBtn.disabled = true;
            
            // Adicionar feedback visual
            submitBtn.style.background = 'var(--gray-400)';
            
            // Timeout de segurança para reabilitar
            setTimeout(function() {
                if (form.classList.contains('form-loading')) {
                    form.classList.remove('form-loading');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    submitBtn.style.background = '';
                }
            }, 8000);
        });
        
        // Easter egg: duplo clique no logo
        document.querySelector('.login-logo').addEventListener('dblclick', function() {
            this.style.transform = 'rotate(360deg)';
            this.style.transition = 'transform 0.5s ease';
            setTimeout(() => {
                this.style.transform = '';
            }, 500);
        });
        
        // Efeito de paralaxe sutil no fundo
        window.addEventListener('mousemove', function(e) {
            const mouseX = e.clientX / window.innerWidth;
            const mouseY = e.clientY / window.innerHeight;
            
            document.body.style.backgroundPosition = 
                `${50 + mouseX * 2}% ${50 + mouseY * 2}%`;
        });
    </script>
</body>
</html>
