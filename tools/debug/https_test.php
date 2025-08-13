<?php
// ===================================================================
// TESTE HTTPS - SISTEMA ELUS
// ===================================================================

// Detectar protocolo
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'HTTPS' : 'HTTP';
$isSecure = $protocol === 'HTTPS';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste HTTPS - Sistema ELUS</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; }
        .status { padding: 15px; margin: 20px 0; border-radius: 8px; font-size: 18px; font-weight: bold; }
        .secure { background: #d5f4e6; color: #27ae60; border: 2px solid #27ae60; }
        .insecure { background: #ffe6e6; color: #e74c3c; border: 2px solid #e74c3c; }
        .info { background: #e3f2fd; color: #1976d2; padding: 15px; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #34495e; color: white; }
        .button { display: inline-block; padding: 10px 20px; margin: 10px 5px; text-decoration: none; border-radius: 5px; color: white; font-weight: bold; }
        .btn-https { background: #27ae60; }
        .btn-http { background: #e74c3c; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔒 Teste de Configuração HTTPS</h1>
        
        <div class="status <?php echo $isSecure ? 'secure' : 'insecure'; ?>">
            <?php if ($isSecure): ?>
                ✅ CONEXÃO SEGURA (HTTPS)
            <?php else: ?>
                ⚠️ CONEXÃO NÃO SEGURA (HTTP)
            <?php endif; ?>
        </div>
        
        <h2>📊 Informações da Conexão</h2>
        <table>
            <tr>
                <th>Propriedade</th>
                <th>Valor</th>
            </tr>
            <tr>
                <td>Protocolo</td>
                <td><strong><?php echo $protocol; ?></strong></td>
            </tr>
            <tr>
                <td>Porta</td>
                <td><?php echo $_SERVER['SERVER_PORT']; ?></td>
            </tr>
            <tr>
                <td>Host</td>
                <td><?php echo $_SERVER['HTTP_HOST']; ?></td>
            </tr>
            <tr>
                <td>URL Completa</td>
                <td><?php echo ($isSecure ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?></td>
            </tr>
            <tr>
                <td>HTTPS Variável</td>
                <td><?php echo isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'Não definida'; ?></td>
            </tr>
        </table>
        
        <h2>🔐 Status dos Cookies de Sessão</h2>
        <div class="info">
            <p><strong>session.cookie_secure:</strong> <?php echo ini_get('session.cookie_secure') ? 'Habilitado (apenas HTTPS)' : 'Desabilitado (HTTP/HTTPS)'; ?></p>
            <p><strong>session.cookie_httponly:</strong> <?php echo ini_get('session.cookie_httponly') ? 'Habilitado' : 'Desabilitado'; ?></p>
            <p><strong>session.cookie_samesite:</strong> <?php echo ini_get('session.cookie_samesite') ?: 'Não definido'; ?></p>
        </div>
        
        <h2>🌐 Headers de Segurança</h2>
        <div class="info">
            <?php
            $securityHeaders = [
                'X-Environment' => 'Ambiente',
                'Strict-Transport-Security' => 'HSTS',
                'X-Content-Type-Options' => 'Content Type Protection',
                'X-Frame-Options' => 'Clickjacking Protection',
                'X-XSS-Protection' => 'XSS Protection'
            ];
            
            $headers = getallheaders();
            foreach ($securityHeaders as $header => $description) {
                $value = isset($headers[$header]) ? $headers[$header] : 'Não definido';
                echo "<p><strong>$description ($header):</strong> $value</p>";
            }
            ?>
        </div>
        
        <h2>🔄 Teste de Redirecionamento</h2>
        <p>Use os botões abaixo para testar o redirecionamento HTTP → HTTPS:</p>
        
        <a href="http://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" class="button btn-http">
            🔓 Testar HTTP
        </a>
        
        <a href="https://<?php echo $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>" class="button btn-https">
            🔒 Testar HTTPS
        </a>
        
        <?php if (!$isSecure): ?>
        <div class="info">
            <h3>⚠️ Para ativar HTTPS:</h3>
            <ol>
                <li>Execute como <strong>Administrador</strong>: <code>scripts/configurar_https_xampp_novo.ps1</code></li>
                <li>Reinicie o Apache no XAMPP</li>
                <li>Acesse: <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/chamados_system/public/">https://<?php echo $_SERVER['HTTP_HOST']; ?>/chamados_system/public/</a></li>
                <li>Aceite o certificado autoassinado no navegador</li>
            </ol>
        </div>
        <?php else: ?>
        <div class="info" style="background: #d5f4e6; color: #27ae60;">
            <h3>✅ HTTPS Ativo!</h3>
            <p>Sua conexão está segura. O redirecionamento HTTP → HTTPS está funcionando.</p>
        </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; text-align: center; color: #7f8c8d;">
            <p>Sistema ELUS - Teste HTTPS | <?php echo date('d/m/Y H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
