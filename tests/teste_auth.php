<?php
/**
 * Teste Completo do Sistema de Autenticação
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Carregar configurações
require_once '../config/config.php';
require_once '../src/DB.php';
require_once '../src/Auth.php';
require_once '../src/SecurityValidator.php';

// Inicializar sistema
$database = new DB();
$db = $database->getConnection();

if (!$db) {
    die('❌ Erro: Não foi possível conectar ao banco de dados');
}

$auth = new Auth($db);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste de Autenticação - Sistema de Chamados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-4">
                    <i class="fas fa-shield-alt text-primary"></i>
                    Teste de Autenticação - Sistema de Chamados
                </h1>
            </div>
        </div>

        <div class="row">
            <!-- Status do Sistema -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-cogs"></i> Status do Sistema</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        echo "<p><strong>Conexão DB:</strong> <span class='text-success'>✅ Conectado</span></p>";
                        echo "<p><strong>Sessão:</strong> <span class='text-success'>✅ Ativa (ID: " . session_id() . ")</span></p>";
                        echo "<p><strong>PHP:</strong> " . phpversion() . "</p>";
                        echo "<p><strong>Ambiente:</strong> " . (defined('APP_ENV') ? APP_ENV : 'undefined') . "</p>";
                        echo "<p><strong>Debug:</strong> " . (defined('APP_DEBUG') && APP_DEBUG ? 'Ativo' : 'Inativo') . "</p>";
                        ?>
                    </div>
                </div>
            </div>

            <!-- Token CSRF -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-key"></i> Token CSRF</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $csrf_token = $auth->generateCSRFToken();
                            echo "<p><strong>Token:</strong> <code>" . substr($csrf_token, 0, 20) . "...</code></p>";
                            echo "<p><strong>Status:</strong> <span class='text-success'>✅ Gerado com sucesso</span></p>";
                            echo "<p><strong>Método:</strong> Auth::generateCSRFToken()</p>";
                        } catch (Exception $e) {
                            echo "<p><strong>Status:</strong> <span class='text-danger'>❌ Erro: " . $e->getMessage() . "</span></p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Teste -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-vial"></i> Teste de Login (Simulação)</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Processar teste se enviado
                        if (isset($_POST['test_login'])) {
                            echo "<div class='alert alert-info'>";
                            echo "<h6>Resultado do Teste:</h6>";
                            
                            $submitted_token = $_POST['csrf_token'] ?? '';
                            $test_username = $_POST['test_username'] ?? '';
                            
                            echo "<p><strong>Token enviado:</strong> " . substr($submitted_token, 0, 20) . "...</p>";
                            echo "<p><strong>Token na sessão:</strong> " . substr($_SESSION['csrf_token'] ?? 'NENHUM', 0, 20) . "...</p>";
                            
                            // Validar token
                            $token_valid = SecurityValidator::validateCSRF($submitted_token);
                            
                            if ($token_valid) {
                                echo "<p><strong>Validação CSRF:</strong> <span class='text-success'>✅ VÁLIDO</span></p>";
                                echo "<div class='alert alert-success mt-3'>";
                                echo "<strong>🎉 Sucesso!</strong> O token CSRF está funcionando corretamente.";
                                echo "</div>";
                            } else {
                                echo "<p><strong>Validação CSRF:</strong> <span class='text-danger'>❌ INVÁLIDO</span></p>";
                                echo "<div class='alert alert-warning mt-3'>";
                                echo "<strong>⚠️ Problema detectado!</strong> O token CSRF não está funcionando.";
                                echo "</div>";
                            }
                            echo "</div>";
                        }
                        ?>

                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="test_username" class="form-label">Nome de usuário (teste)</label>
                                        <input type="text" class="form-control" id="test_username" name="test_username" 
                                               value="usuario_teste" placeholder="Digite um nome de usuário">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="test_password" class="form-label">Senha (teste)</label>
                                        <input type="password" class="form-control" id="test_password" name="test_password" 
                                               value="123456" placeholder="Digite uma senha">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center">
                                <button type="submit" name="test_login" class="btn btn-primary">
                                    <i class="fas fa-test-tube"></i> Testar Token CSRF
                                </button>
                                <a href="../public/limpar_sessao.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-refresh"></i> Limpar Sessão
                                </a>
                                <a href="../public/login.php" class="btn btn-success">
                                    <i class="fas fa-sign-in-alt"></i> Ir para Login Real
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configurações de Sessão -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5><i class="fas fa-cog"></i> Configurações de Sessão</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <?php
                                echo "<p><strong>session.cookie_httponly:</strong> " . ini_get('session.cookie_httponly') . "</p>";
                                echo "<p><strong>session.cookie_secure:</strong> " . ini_get('session.cookie_secure') . "</p>";
                                echo "<p><strong>session.use_only_cookies:</strong> " . ini_get('session.use_only_cookies') . "</p>";
                                echo "<p><strong>session.cookie_samesite:</strong> " . ini_get('session.cookie_samesite') . "</p>";
                                ?>
                            </div>
                            <div class="col-md-6">
                                <?php
                                echo "<p><strong>SESSION_SECURE (.env):</strong> " . (defined('SESSION_SECURE') ? (SESSION_SECURE ? 'true' : 'false') : 'undefined') . "</p>";
                                echo "<p><strong>SESSION_HTTPONLY (.env):</strong> " . (defined('SESSION_HTTPONLY') ? (SESSION_HTTPONLY ? 'true' : 'false') : 'undefined') . "</p>";
                                echo "<p><strong>SESSION_SAMESITE (.env):</strong> " . (defined('SESSION_SAMESITE') ? SESSION_SAMESITE : 'undefined') . "</p>";
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <small class="text-muted">Teste executado em <?php echo date('Y-m-d H:i:s'); ?></small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
