<?php
/**
 * Classe para carregar variáveis de ambiente do arquivo .env
 */
class EnvLoader {
    
    /**
     * Carrega as variáveis do arquivo .env
     */
    public static function load($filePath) {
        if (!file_exists($filePath)) {
            throw new Exception("Arquivo .env não encontrado: {$filePath}");
        }
        
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Ignorar comentários
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Verificar se a linha contém uma variável
            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remover aspas se existirem
                $value = trim($value, '"\'');
                
                // Definir a variável de ambiente se ainda não estiver definida
                if (!array_key_exists($name, $_ENV)) {
                    $_ENV[$name] = $value;
                    putenv("$name=$value");
                }
            }
        }
    }
    
    /**
     * Obtém uma variável de ambiente com valor padrão
     */
    public static function get($key, $default = null) {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }
    
    /**
     * Verifica se uma variável de ambiente existe
     */
    public static function has($key) {
        return array_key_exists($key, $_ENV) || getenv($key) !== false;
    }
}
?>
