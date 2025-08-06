<?php
/**
 * Classe de Validação e Sanitização Segura
 */
class SecurityValidator {
    
    /**
     * Sanitiza string removendo tags e caracteres perigosos
     */
    public static function sanitizeString($input, $allowHtml = false) {
        if ($allowHtml) {
            // Permitir apenas tags HTML seguras
            $allowedTags = '<p><br><strong><em><u><ol><ul><li>';
            return strip_tags(trim($input), $allowedTags);
        } else {
            return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Valida email
     */
    public static function validateEmail($email) {
        $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
        if ($email === false) {
            return ['valid' => false, 'message' => 'Email inválido'];
        }
        
        // Verificar se o domínio existe
        $domain = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($domain, "MX")) {
            return ['valid' => false, 'message' => 'Domínio do email inválido'];
        }
        
        return ['valid' => true, 'email' => $email];
    }
    
    /**
     * Valida telefone brasileiro
     */
    public static function validatePhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (strlen($phone) < 10 || strlen($phone) > 11) {
            return ['valid' => false, 'message' => 'Telefone deve ter 10 ou 11 dígitos'];
        }
        
        return ['valid' => true, 'phone' => $phone];
    }
    
    /**
     * Valida e sanitiza texto longo
     */
    public static function validateText($text, $minLength = 1, $maxLength = 5000) {
        $text = trim($text);
        $length = strlen($text);
        
        if ($length < $minLength) {
            return ['valid' => false, 'message' => "Texto deve ter pelo menos {$minLength} caracteres"];
        }
        
        if ($length > $maxLength) {
            return ['valid' => false, 'message' => "Texto não pode exceder {$maxLength} caracteres"];
        }
        
        // Sanitizar mantendo quebras de linha
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        
        return ['valid' => true, 'text' => $text];
    }
    
    /**
     * Valida CSRF token
     */
    public static function validateCSRF($token) {
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Valida upload de arquivo
     */
    public static function validateFileUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'], $maxSize = 5242880) {
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return ['valid' => false, 'message' => 'Nenhum arquivo foi enviado'];
        }
        
        // Verificar tamanho
        if ($file['size'] > $maxSize) {
            $maxMB = round($maxSize / 1024 / 1024, 1);
            return ['valid' => false, 'message' => "Arquivo muito grande. Máximo: {$maxMB}MB"];
        }
        
        // Verificar tipo
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $allowedTypes)) {
            $types = implode(', ', $allowedTypes);
            return ['valid' => false, 'message' => "Tipo de arquivo não permitido. Permitidos: {$types}"];
        }
        
        // Verificar MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg', 
            'png' => 'image/png',
            'pdf' => 'application/pdf'
        ];
        
        if (!isset($allowedMimes[$extension]) || $mimeType !== $allowedMimes[$extension]) {
            return ['valid' => false, 'message' => 'Tipo de arquivo inválido'];
        }
        
        return ['valid' => true, 'file' => $file];
    }
    
    /**
     * Rate limiting para prevenir ataques de força bruta
     */
    public static function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
        $key = "rate_limit_" . md5($identifier);
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'first_attempt' => time()];
        }
        
        $data = $_SESSION[$key];
        
        // Reset se passou do tempo limite
        if (time() - $data['first_attempt'] > $timeWindow) {
            $_SESSION[$key] = ['count' => 1, 'first_attempt' => time()];
            return ['allowed' => true, 'remaining' => $maxAttempts - 1];
        }
        
        // Verificar se excedeu limite
        if ($data['count'] >= $maxAttempts) {
            $remainingTime = $timeWindow - (time() - $data['first_attempt']);
            return ['allowed' => false, 'retry_after' => $remainingTime];
        }
        
        // Incrementar contador
        $_SESSION[$key]['count']++;
        
        return ['allowed' => true, 'remaining' => $maxAttempts - $data['count']];
    }
    
    /**
     * Gera token seguro
     */
    public static function generateSecureToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Valida UUID
     */
    public static function validateUUID($uuid) {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $uuid);
    }
}
?>
