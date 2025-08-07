<?php
/**
 * Classe helper para funções de segurança
 * Resolver vulnerabilidades XSS, CSRF e validação de entrada
 */
class SecurityHelper {
    
    /**
     * Sanitiza saída HTML prevenindo XSS
     * @param string $data Dados a serem sanitizados
     * @return string Dados sanitizados
     */
    public static function sanitizeOutput($data) {
        return htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * Sanitiza dados para uso em atributos JavaScript
     * @param string $data Dados a serem sanitizados
     * @return string Dados sanitizados para JS
     */
    public static function sanitizeForJS($data) {
        // Remove caracteres perigosos e escapa aspas
        $data = str_replace(['\\', "'", '"', "\n", "\r", "\t"], ['\\\\', "\\'", '\\"', '\\n', '\\r', '\\t'], $data);
        return $data;
    }
    
    /**
     * Gera token CSRF
     * @return string Token CSRF
     */
    public static function generateCSRFToken() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Valida token CSRF
     * @param string $token Token a ser validado
     * @return bool True se válido
     */
    public static function validateCSRFToken($token) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Gera campo hidden com token CSRF
     * @return string HTML do campo hidden
     */
    public static function getCSRFField() {
        $token = self::generateCSRFToken();
        return '<input type="hidden" name="csrf_token" value="' . self::sanitizeOutput($token) . '">';
    }
    
    /**
     * Valida se um ID é um número inteiro válido
     * @param mixed $id ID a ser validado
     * @return int ID validado
     * @throws InvalidArgumentException Se ID inválido
     */
    public static function validateId($id) {
        if (!is_numeric($id) || $id <= 0) {
            throw new InvalidArgumentException('ID inválido fornecido');
        }
        return (int) $id;
    }
    
    /**
     * Valida entrada de texto
     * @param string $text Texto a ser validado
     * @param int $maxLength Comprimento máximo
     * @param bool $required Se é obrigatório
     * @return string Texto validado
     * @throws InvalidArgumentException Se inválido
     */
    public static function validateText($text, $maxLength = 1000, $required = true) {
        $text = trim($text);
        
        if ($required && empty($text)) {
            throw new InvalidArgumentException('Campo obrigatório não pode estar vazio');
        }
        
        if (strlen($text) > $maxLength) {
            throw new InvalidArgumentException("Texto excede o limite de {$maxLength} caracteres");
        }
        
        return $text;
    }
    
    /**
     * Valida email
     * @param string $email Email a ser validado
     * @return string Email validado
     * @throws InvalidArgumentException Se inválido
     */
    public static function validateEmail($email) {
        $email = trim($email);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email inválido');
        }
        
        return $email;
    }
    
    /**
     * Valida data/hora
     * @param string $datetime DateTime a ser validado
     * @return string DateTime validado
     * @throws InvalidArgumentException Se inválido
     */
    public static function validateDateTime($datetime) {
        if (empty($datetime)) {
            return date('Y-m-d H:i:s');
        }
        
        $date = DateTime::createFromFormat('Y-m-d\TH:i', $datetime);
        if (!$date) {
            throw new InvalidArgumentException('Data/hora inválida');
        }
        
        return $date->format('Y-m-d H:i:s');
    }
    
    /**
     * Remove timestamp sensível de comentários HTML
     * @param string $content Conteúdo HTML
     * @return string Conteúdo sem informações sensíveis
     */
    public static function removeSensitiveComments($content) {
        // Remove comentários com timestamp
        $content = preg_replace('/<!--\s*Página carregada em:.*?-->/', '', $content);
        return $content;
    }
    
    /**
     * Verifica se requisição é POST
     * @throws Exception Se não for POST
     */
    public static function requirePOST() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new Exception('Método de requisição inválido');
        }
    }
    
    /**
     * Obtém valor seguro de $_POST
     * @param string $key Chave
     * @param mixed $default Valor padrão
     * @return mixed Valor sanitizado
     */
    public static function getPostValue($key, $default = '') {
        return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
    }
    
    /**
     * Obtém valor seguro de $_GET
     * @param string $key Chave
     * @param mixed $default Valor padrão
     * @return mixed Valor sanitizado
     */
    public static function getGetValue($key, $default = '') {
        return isset($_GET[$key]) ? trim($_GET[$key]) : $default;
    }
}
