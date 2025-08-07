<?php

/**
 * Gerenciador de Assets - Resolve problemas de carregamento desnecessário
 * Implementa lazy loading e bundling para Bootstrap e FontAwesome
 */
class AssetManager {
    private $assets = [];
    private $critical_css = [];
    private $deferred_js = [];
    private $preload_fonts = [];
    private $version;
    
    public function __construct($version = null) {
        $this->version = $version ?? time(); // Para cache busting
    }
    
    /**
     * Adiciona CSS crítico (será inline)
     */
    public function addCriticalCSS($css) {
        $this->critical_css[] = $css;
    }
    
    /**
     * Adiciona CSS não crítico
     */
    public function addCSS($href, $media = 'all', $critical = false) {
        if ($critical) {
            $this->addCriticalCSS($href);
            return;
        }
        
        $this->assets['css'][] = [
            'href' => $href,
            'media' => $media
        ];
    }
    
    /**
     * Adiciona JavaScript com defer
     */
    public function addJS($src, $defer = true, $async = false) {
        $this->assets['js'][] = [
            'src' => $src,
            'defer' => $defer,
            'async' => $async
        ];
    }
    
    /**
     * Adiciona fonte para preload
     */
    public function addFont($href, $type = 'font/woff2') {
        $this->preload_fonts[] = [
            'href' => $href,
            'type' => $type
        ];
    }
    
    /**
     * Renderiza preloads no <head>
     */
    public function renderPreloads() {
        $output = '';
        
        // Preload de fontes
        foreach ($this->preload_fonts as $font) {
            $output .= "<link rel='preload' href='{$font['href']}' as='font' type='{$font['type']}' crossorigin>\n";
        }
        
        // DNS prefetch para CDNs
        $output .= "<link rel='dns-prefetch' href='//cdn.jsdelivr.net'>\n";
        $output .= "<link rel='dns-prefetch' href='//cdnjs.cloudflare.com'>\n";
        
        return $output;
    }
    
    /**
     * Renderiza CSS crítico inline
     */
    public function renderCriticalCSS() {
        if (empty($this->critical_css)) {
            return '';
        }
        
        $output = "<style>\n";
        foreach ($this->critical_css as $css) {
            if (file_exists($css)) {
                $output .= file_get_contents($css);
            } else {
                $output .= $css;
            }
        }
        $output .= "\n</style>\n";
        
        return $output;
    }
    
    /**
     * Renderiza CSS não crítico com loading assíncrono
     */
    public function renderCSS() {
        if (empty($this->assets['css'])) {
            return '';
        }
        
        $output = '';
        foreach ($this->assets['css'] as $css) {
            $href = $this->addVersion($css['href']);
            // Carregamento assíncrono de CSS não crítico
            $output .= "<link rel='preload' href='{$href}' as='style' onload=\"this.onload=null;this.rel='stylesheet'\" media='{$css['media']}'>\n";
            // Fallback para navegadores que não suportam preload
            $output .= "<noscript><link rel='stylesheet' href='{$href}' media='{$css['media']}'></noscript>\n";
        }
        
        return $output;
    }
    
    /**
     * Renderiza JavaScript otimizado
     */
    public function renderJS() {
        if (empty($this->assets['js'])) {
            return '';
        }
        
        $output = '';
        foreach ($this->assets['js'] as $js) {
            $src = $this->addVersion($js['src']);
            $attributes = '';
            
            if ($js['defer']) {
                $attributes .= ' defer';
            }
            
            if ($js['async']) {
                $attributes .= ' async';
            }
            
            $output .= "<script src='{$src}'{$attributes}></script>\n";
        }
        
        return $output;
    }
    
    /**
     * Adiciona versão para cache busting
     */
    private function addVersion($url) {
        if (strpos($url, 'http') === 0) {
            return $url; // URL externa, não adicionar versão
        }
        
        $separator = strpos($url, '?') !== false ? '&' : '?';
        return $url . $separator . 'v=' . $this->version;
    }
    
    /**
     * Configuração otimizada para páginas de listagem
     */
    public function setupListingPage() {
        // CSS crítico - apenas estilos essenciais
        $this->addCriticalCSS('
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
            .container-fluid { max-width: 1200px; margin: 0 auto; }
            .loading-skeleton { background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); background-size: 200% 100%; animation: loading 1.5s infinite; }
            @keyframes loading { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        ');
        
        // Bootstrap - carregamento assíncrono
        $this->addCSS('https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');
        
        // CSS customizado
        $this->addCSS('../css/style.css');
        $this->addCSS('../assets/css/chamados-list.css');
        
        // JavaScript com defer
        $this->addJS('https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', true);
        $this->addJS('../assets/js/chamados-list.js', true);
    }
    
    /**
     * Configuração para páginas com FontAwesome
     */
    public function setupWithFontAwesome() {
        // FontAwesome - apenas quando necessário
        $this->addCSS('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css');
    }
    
    /**
     * Configuração minimalista para login
     */
    public function setupLoginPage() {
        // Apenas o essencial para login
        $this->addCriticalCSS('
            body { margin: 0; padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; font-family: system-ui; }
            .login-container { max-width: 400px; margin: 0 auto; background: white; border-radius: 8px; padding: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        ');
        
        // Bootstrap mínimo apenas se necessário
        $this->addCSS('https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');
    }
    
    /**
     * Renderiza tudo de uma vez
     */
    public function render() {
        $output = '';
        $output .= $this->renderPreloads();
        $output .= $this->renderCriticalCSS();
        $output .= $this->renderCSS();
        $output .= $this->renderJS();
        
        return $output;
    }
    
    /**
     * Script para carregamento progressivo de recursos
     */
    public function getProgressiveLoadingScript() {
        return "
        <script>
        // Carregamento progressivo de FontAwesome apenas quando necessário
        function loadFontAwesome() {
            if (document.querySelector('.fa, .fas, .far, .fal, .fab')) {
                const link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css';
                document.head.appendChild(link);
            }
        }
        
        // Carregamento sob demanda
        if ('IntersectionObserver' in window) {
            // Lazy loading para imagens
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        observer.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => imageObserver.observe(img));
        }
        
        // Verificar e carregar FontAwesome se necessário
        document.addEventListener('DOMContentLoaded', loadFontAwesome);
        </script>
        ";
    }
}

?>
