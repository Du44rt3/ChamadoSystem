<?php
// ===================================================================
// VERIFICAÇÃO COMPLETA DE STATUS HTTPS
// Sistema ELUS Facilities - Diagnóstico Ambiente
// ===================================================================

echo "🔍 RELATÓRIO DE VERIFICAÇÃO DE HTTPS\n";
echo "====================================\n\n";

// 1. Verificar protocolo atual
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'HTTPS' : 'HTTP';
$isSecure = $protocol === 'HTTPS';

echo "1. PROTOCOLO ATUAL:\n";
echo "   Protocolo: " . $protocol . "\n";
echo "   Porta: " . $_SERVER['SERVER_PORT'] . "\n";
echo "   URL Atual: " . $protocol . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n";
echo "   Status: " . ($isSecure ? "🔒 SEGURO (HTTPS)" : "🔓 NÃO SEGURO (HTTP)") . "\n\n";

// 2. Verificar configurações PHP
echo "2. CONFIGURAÇÕES PHP (SESSÕES):\n";
echo "   session.cookie_secure: " . (ini_get('session.cookie_secure') ? 'HABILITADO (apenas HTTPS)' : 'DESABILITADO (HTTP/HTTPS)') . "\n";
echo "   session.cookie_httponly: " . (ini_get('session.cookie_httponly') ? 'HABILITADO' : 'DESABILITADO') . "\n";
echo "   session.cookie_samesite: " . ini_get('session.cookie_samesite') . "\n\n";

// 3. Verificar arquivo .htaccess
echo "3. VERIFICAÇÃO ARQUIVO .HTACCESS:\n";
$htaccessPath = '../.htaccess';
if (file_exists($htaccessPath)) {
    $htaccessContent = file_get_contents($htaccessPath);
    
    // Verificar se há redirecionamentos HTTPS ativos
    $httpsRedirectActive = false;
    $httpsRedirectCommented = false;
    
    if (preg_match('/^\s*RewriteCond\s+%\{HTTPS\}\s+off/m', $htaccessContent)) {
        $httpsRedirectActive = true;
    }
    
    if (preg_match('/^\s*#\s*RewriteCond\s+%\{HTTPS\}\s+off/m', $htaccessContent)) {
        $httpsRedirectCommented = true;
    }
    
    echo "   Arquivo .htaccess: ENCONTRADO\n";
    echo "   Redirecionamento HTTPS ativo: " . ($httpsRedirectActive ? "❌ SIM (ATIVO)" : "✅ NÃO") . "\n";
    echo "   Redirecionamento HTTPS comentado: " . ($httpsRedirectCommented ? "✅ SIM (DESATIVADO)" : "❌ NÃO") . "\n";
    
    // Verificar session.cookie_secure
    $cookieSecureActive = false;
    $cookieSecureCommented = false;
    
    if (preg_match('/^\s*php_flag\s+session\.cookie_secure\s+On/m', $htaccessContent)) {
        $cookieSecureActive = true;
    }
    
    if (preg_match('/^\s*#\s*php_flag\s+session\.cookie_secure\s+On/m', $htaccessContent)) {
        $cookieSecureCommented = true;
    }
    
    echo "   session.cookie_secure ativo: " . ($cookieSecureActive ? "❌ SIM (força HTTPS)" : "✅ NÃO") . "\n";
    echo "   session.cookie_secure comentado: " . ($cookieSecureCommented ? "✅ SIM (desativado)" : "❌ NÃO") . "\n";
    
    // Verificar HSTS header
    $hstsActive = false;
    $hstsCommented = false;
    
    if (preg_match('/^\s*Header\s+always\s+set\s+Strict-Transport-Security/m', $htaccessContent)) {
        $hstsActive = true;
    }
    
    if (preg_match('/^\s*#\s*Header\s+always\s+set\s+Strict-Transport-Security/m', $htaccessContent)) {
        $hstsCommented = true;
    }
    
    echo "   HSTS header ativo: " . ($hstsActive ? "❌ SIM (força HTTPS)" : "✅ NÃO") . "\n";
    echo "   HSTS header comentado: " . ($hstsCommented ? "✅ SIM (desativado)" : "❌ NÃO") . "\n";
    
} else {
    echo "   Arquivo .htaccess: ❌ NÃO ENCONTRADO\n";
}

echo "\n";

// 4. Verificar ambiente detectado
echo "4. AMBIENTE DETECTADO:\n";
$isDev = false;
$environment = 'produção';

// Verificar se é ambiente de desenvolvimento
if (strpos($_SERVER['REQUEST_URI'], 'chamados_system_dev') !== false ||
    isset($_GET['dev']) ||
    $_SERVER['SERVER_PORT'] == '8080') {
    $isDev = true;
    $environment = 'desenvolvimento';
}

echo "   Ambiente: " . strtoupper($environment) . "\n";
echo "   Pasta dev detectada: " . (strpos($_SERVER['REQUEST_URI'], 'chamados_system_dev') !== false ? 'SIM' : 'NÃO') . "\n";
echo "   Parâmetro ?dev=1: " . (isset($_GET['dev']) ? 'SIM' : 'NÃO') . "\n";
echo "   Porta 8080: " . ($_SERVER['SERVER_PORT'] == '8080' ? 'SIM' : 'NÃO') . "\n\n";

// 5. Verificar headers de resposta
echo "5. HEADERS DE RESPOSTA:\n";
$headers = getallheaders();
foreach ($headers as $name => $value) {
    if (stripos($name, 'environment') !== false ||
        stripos($name, 'strict-transport') !== false ||
        stripos($name, 'security') !== false) {
        echo "   $name: $value\n";
    }
}

// 6. Diagnóstico final
echo "\n6. DIAGNÓSTICO FINAL:\n";

if (!$isSecure && !$httpsRedirectActive && !$cookieSecureActive && !$hstsActive) {
    echo "   ✅ HTTPS COMPLETAMENTE DESATIVADO\n";
    echo "   ✅ Sistema configurado para HTTP apenas\n";
    echo "   ✅ Não há redirecionamentos forçados\n";
    echo "   ✅ Sessões funcionam em HTTP\n";
} else {
    echo "   ⚠️  ATENÇÃO: Algumas configurações HTTPS ainda estão ativas:\n";
    
    if ($isSecure) {
        echo "   ❌ Conexão atual é HTTPS\n";
    }
    
    if ($httpsRedirectActive) {
        echo "   ❌ Redirecionamento HTTPS ativo no .htaccess\n";
    }
    
    if ($cookieSecureActive) {
        echo "   ❌ session.cookie_secure ativo (força HTTPS para sessões)\n";
    }
    
    if ($hstsActive) {
        echo "   ❌ HSTS header ativo (navegador força HTTPS)\n";
    }
}

echo "\n7. RECOMENDAÇÕES:\n";

if ($isSecure) {
    echo "   - Acesse via: http://" . $_SERVER['HTTP_HOST'] . str_replace($_SERVER['REQUEST_URI'], '/chamados_system/public/', $_SERVER['REQUEST_URI']) . "\n";
    echo "   - Para resolver problema do navegador, use: scripts/limpar_cache_hsts.bat\n";
}

if ($httpsRedirectActive || $cookieSecureActive || $hstsActive) {
    echo "   - Execute: git restore . (para garantir que .htaccess está correto)\n";
    echo "   - Verifique se não há configurações HTTPS ativas\n";
}

echo "   - Para ambiente dev use: http://" . $_SERVER['HTTP_HOST'] . "/chamados_system_dev/public/\n";
echo "   - Para limpar cache do navegador: chrome://net-internals/#hsts\n";

echo "\n====================================\n";
echo "Relatório gerado em: " . date('d/m/Y H:i:s') . "\n";
echo "====================================\n";
?>
