<?php
/**
 * Classe de Autenticação do Sistema
 * Gerencia login, logout e verificação de sessões
 */
class Auth {
    private $conn;
    private $table_name = "usuarios";
    
    public function __construct($db) {
        $this->conn = $db;
        
        // Configurações de sessão segura baseadas no .env (com fallbacks)
        if (session_status() == PHP_SESSION_NONE) {
            // Configurações de segurança da sessão
            ini_set('session.cookie_httponly', defined('SESSION_HTTPONLY') && SESSION_HTTPONLY ? 1 : 0);
            ini_set('session.cookie_secure', defined('SESSION_SECURE') && SESSION_SECURE ? 1 : 0);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_samesite', defined('SESSION_SAMESITE') ? SESSION_SAMESITE : 'Strict');
            
            // Configurações adicionais de segurança
            ini_set('session.entropy_file', '/dev/urandom');
            ini_set('session.entropy_length', 32);
            ini_set('session.hash_function', 'sha256');
            ini_set('session.hash_bits_per_character', 6);
            
            // Nome da sessão personalizado
            session_name('ELUS_CHAMADOS_SESSION');
            
            session_start();
        }
    }
    
    /**
     * Realiza o login do usuário
     */
    public function login($username, $password) {
        try {
            // Verificar se o usuário existe e está ativo
            $query = "SELECT id, nome, username, password, ativo, tentativas_login, bloqueado_ate, nivel_acesso 
                     FROM " . $this->table_name . " 
                     WHERE username = :username AND ativo = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verificar se o usuário está bloqueado
                if ($this->isUserBlocked($user)) {
                    return [
                        'success' => false,
                        'message' => 'Usuário temporariamente bloqueado devido a muitas tentativas de login incorretas.'
                    ];
                }
                
                // Verificar a senha
                if ($this->verifyPassword($password, $user['password'])) {
                    // Login bem-sucedido
                    $this->createSession($user);
                    $this->updateLastLogin($user['id']);
                    $this->resetLoginAttempts($user['id']);
                    
                    return [
                        'success' => true,
                        'message' => 'Login realizado com sucesso!',
                        'user' => [
                            'id' => $user['id'],
                            'nome' => $user['nome'],
                            'username' => $user['username']
                        ]
                    ];
                } else {
                    // Senha incorreta - incrementar tentativas
                    $this->incrementLoginAttempts($user['id']);
                    
                    return [
                        'success' => false,
                        'message' => 'Usuário ou senha incorretos.'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Usuário ou senha incorretos.'
                ];
            }
        } catch (Exception $e) {
            error_log("Erro no login: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erro interno do sistema. Tente novamente.'
            ];
        }
    }
    
    /**
     * Verifica a senha usando password_verify com hash seguro
     */
    private function verifyPassword($password, $hashedPassword) {
        // Verificar se a senha está hasheada corretamente
        return password_verify($password, $hashedPassword);
    }
    
    /**
     * Cria a sessão do usuário
     */
    private function createSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nome'] = $user['nome'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_nivel_acesso'] = $user['nivel_acesso'] ?? 'usuario';
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // Token de segurança
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    /**
     * Realiza o logout
     */
    public function logout() {
        // Limpar todas as variáveis de sessão
        $_SESSION = array();
        
        // Deletar o cookie de sessão
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destruir a sessão
        session_destroy();
        
        return true;
    }
    
    /**
     * Verifica se o usuário está logado
     */
    public function isLoggedIn() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            return false;
        }
        
        // Verificar timeout da sessão (30 minutos)
        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 1800) {
            $this->logout();
            return false;
        }
        
        // Atualizar tempo da sessão
        $_SESSION['login_time'] = time();
        
        return true;
    }
    
    /**
     * Obtém os dados do usuário logado
     */
    public function getLoggedUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'nome' => $_SESSION['user_nome'],
                'username' => $_SESSION['user_username'],
                'nivel_acesso' => $_SESSION['user_nivel_acesso'] ?? 'usuario'
            ];
        }
        return null;
    }
    
    /**
     * Redireciona para login se não estiver autenticado
     */
    public function requireAuth($redirect_url = 'login.php') {
        if (!$this->isLoggedIn()) {
            header("Location: $redirect_url");
            exit();
        }
    }
    
    /**
     * Verifica se o usuário está bloqueado
     */
    private function isUserBlocked($user) {
        if ($user['tentativas_login'] >= 5) {
            if ($user['bloqueado_ate'] && strtotime($user['bloqueado_ate']) > time()) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Incrementa tentativas de login
     */
    private function incrementLoginAttempts($user_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET tentativas_login = tentativas_login + 1";
        
        // Se chegou a 5 tentativas, bloquear por 15 minutos
        $query .= ", bloqueado_ate = CASE 
                      WHEN tentativas_login >= 4 THEN DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                      ELSE bloqueado_ate 
                   END";
        
        $query .= " WHERE id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }
    
    /**
     * Reseta tentativas de login
     */
    private function resetLoginAttempts($user_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET tentativas_login = 0, bloqueado_ate = NULL 
                 WHERE id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }
    
    /**
     * Atualiza último login
     */
    private function updateLastLogin($user_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET ultimo_login = NOW() 
                 WHERE id = :user_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
    }
    
    /**
     * Gera token CSRF
     */
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verifica token CSRF
     */
    public function verifyCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Gera hash seguro para senha
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterações
            'threads' => 3          // 3 threads
        ]);
    }
    
    /**
     * Valida força da senha
     */
    public function validatePasswordStrength($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'A senha deve ter pelo menos 8 caracteres.';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos uma letra maiúscula.';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos uma letra minúscula.';
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos um número.';
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'A senha deve conter pelo menos um caractere especial.';
        }
        
        return $errors;
    }
    
    /**
     * Migra senha antiga para hash seguro
     */
    public function migrateUserPassword($userId, $newPassword) {
        $hashedPassword = $this->hashPassword($newPassword);
        
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':id', $userId);
        
        return $stmt->execute();
    }
    
    /**
     * Verifica se o usuário tem um nível de acesso específico
     */
    public function hasAccessLevel($requiredLevel) {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $userLevel = $_SESSION['user_nivel_acesso'] ?? 'usuario';
        
        // Definir hierarquia de níveis
        $hierarchy = [
            'usuario' => 1,
            'admin' => 2,
            'desenvolvedor' => 3
        ];
        
        $userLevelValue = $hierarchy[$userLevel] ?? 0;
        $requiredLevelValue = $hierarchy[$requiredLevel] ?? 0;
        
        return $userLevelValue >= $requiredLevelValue;
    }
    
    /**
     * Verifica se o usuário é administrador
     */
    public function isAdmin() {
        return $this->hasAccessLevel('admin');
    }
    
    /**
     * Verifica se o usuário é desenvolvedor
     */
    public function isDeveloper() {
        return $this->hasAccessLevel('desenvolvedor');
    }
    
    /**
     * Exige nível de acesso específico ou redireciona
     */
    public function requireAccessLevel($requiredLevel, $redirect_url = 'index.php') {
        if (!$this->hasAccessLevel($requiredLevel)) {
            $_SESSION['error_message'] = 'Acesso negado. Você não possui permissão para acessar esta área.';
            header("Location: $redirect_url");
            exit();
        }
    }
    
    /**
     * Exige acesso de administrador ou redireciona
     */
    public function requireAdmin($redirect_url = 'index.php') {
        $this->requireAccessLevel('admin', $redirect_url);
    }
    
    /**
     * Exige acesso de desenvolvedor ou redireciona
     */
    public function requireDeveloper($redirect_url = 'index.php') {
        $this->requireAccessLevel('desenvolvedor', $redirect_url);
    }
    
    /**
     * Obtém o nível de acesso do usuário atual
     */
    public function getUserAccessLevel() {
        if ($this->isLoggedIn()) {
            return $_SESSION['user_nivel_acesso'] ?? 'usuario';
        }
        return null;
    }
}
?>
