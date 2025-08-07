<?php

/**
 * Sistema de Cache Simples para Queries Frequentes
 * Resolve o problema de "AUSÊNCIA DE CACHE - Dados recalculados sempre"
 */
class CacheManager {
    private $cache_dir;
    private $default_ttl;
    
    public function __construct($cache_dir = '../cache', $default_ttl = 300) {
        $this->cache_dir = rtrim($cache_dir, '/');
        $this->default_ttl = $default_ttl; // 5 minutos por padrão
        
        // Criar diretório de cache se não existir
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
        
        // Criar arquivo .htaccess para proteger o diretório
        $htaccess_file = $this->cache_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            file_put_contents($htaccess_file, "Deny from all\n");
        }
    }
    
    /**
     * Gera chave única para o cache
     */
    private function generateKey($key) {
        return md5($key);
    }
    
    /**
     * Caminho completo do arquivo de cache
     */
    private function getCacheFilePath($key) {
        $hash = $this->generateKey($key);
        return $this->cache_dir . '/' . $hash . '.cache';
    }
    
    /**
     * Armazena dados no cache
     */
    public function set($key, $data, $ttl = null) {
        $ttl = $ttl ?? $this->default_ttl;
        $cache_file = $this->getCacheFilePath($key);
        
        $cache_data = [
            'data' => $data,
            'timestamp' => time(),
            'ttl' => $ttl,
            'expires_at' => time() + $ttl
        ];
        
        return file_put_contents($cache_file, serialize($cache_data)) !== false;
    }
    
    /**
     * Recupera dados do cache
     */
    public function get($key, $default = null) {
        $cache_file = $this->getCacheFilePath($key);
        
        if (!file_exists($cache_file)) {
            return $default;
        }
        
        $cache_data = unserialize(file_get_contents($cache_file));
        
        if (!$cache_data || !isset($cache_data['expires_at'])) {
            return $default;
        }
        
        // Verificar se expirou
        if (time() > $cache_data['expires_at']) {
            $this->delete($key);
            return $default;
        }
        
        return $cache_data['data'];
    }
    
    /**
     * Verifica se existe no cache e não expirou
     */
    public function has($key) {
        $cache_file = $this->getCacheFilePath($key);
        
        if (!file_exists($cache_file)) {
            return false;
        }
        
        $cache_data = unserialize(file_get_contents($cache_file));
        
        if (!$cache_data || !isset($cache_data['expires_at'])) {
            return false;
        }
        
        // Verificar se expirou
        if (time() > $cache_data['expires_at']) {
            $this->delete($key);
            return false;
        }
        
        return true;
    }
    
    /**
     * Remove item específico do cache
     */
    public function delete($key) {
        $cache_file = $this->getCacheFilePath($key);
        
        if (file_exists($cache_file)) {
            return unlink($cache_file);
        }
        
        return true;
    }
    
    /**
     * Limpa todo o cache
     */
    public function clear() {
        $files = glob($this->cache_dir . '/*.cache');
        $cleared = 0;
        
        foreach ($files as $file) {
            if (unlink($file)) {
                $cleared++;
            }
        }
        
        return $cleared;
    }
    
    /**
     * Remove itens expirados do cache
     */
    public function cleanup() {
        $files = glob($this->cache_dir . '/*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $cache_data = unserialize(file_get_contents($file));
            
            if (!$cache_data || !isset($cache_data['expires_at']) || time() > $cache_data['expires_at']) {
                if (unlink($file)) {
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Obtém estatísticas do cache
     */
    public function getStats() {
        $files = glob($this->cache_dir . '/*.cache');
        $total_files = count($files);
        $total_size = 0;
        $expired_files = 0;
        $valid_files = 0;
        
        foreach ($files as $file) {
            $total_size += filesize($file);
            
            $cache_data = unserialize(file_get_contents($file));
            
            if (!$cache_data || !isset($cache_data['expires_at']) || time() > $cache_data['expires_at']) {
                $expired_files++;
            } else {
                $valid_files++;
            }
        }
        
        return [
            'total_files' => $total_files,
            'valid_files' => $valid_files,
            'expired_files' => $expired_files,
            'total_size' => $total_size,
            'total_size_mb' => round($total_size / 1024 / 1024, 2),
            'cache_dir' => $this->cache_dir
        ];
    }
    
    /**
     * Método utilitário para cache de queries SQL
     */
    public function rememberQuery($key, $callable, $ttl = null) {
        $data = $this->get($key);
        
        if ($data === null) {
            $data = $callable();
            $this->set($key, $data, $ttl);
        }
        
        return $data;
    }
}

?>
