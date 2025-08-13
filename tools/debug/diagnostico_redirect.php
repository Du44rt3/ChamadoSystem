<?php
// ===================================================================
// DIAGNÓSTICO DE REDIRECIONAMENTO - SISTEMA ELUS
// ===================================================================

// Mostrar todas as informações relevantes
echo "<h1>🔍 Diagnóstico de Redirecionamento</h1>";

echo "<h2>📊 Informações da Requisição</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Variável</th><th>Valor</th></tr>";
echo "<tr><td>REQUEST_URI</td><td>" . ($_SERVER['REQUEST_URI'] ?? 'Não definido') . "</td></tr>";
echo "<tr><td>HTTP_HOST</td><td>" . ($_SERVER['HTTP_HOST'] ?? 'Não definido') . "</td></tr>";
echo "<tr><td>HTTPS</td><td>" . (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'Não definido') . "</td></tr>";
echo "<tr><td>SERVER_PORT</td><td>" . ($_SERVER['SERVER_PORT'] ?? 'Não definido') . "</td></tr>";
echo "<tr><td>REQUEST_SCHEME</td><td>" . ($_SERVER['REQUEST_SCHEME'] ?? 'Não definido') . "</td></tr>";
echo "<tr><td>HTTP_X_FORWARDED_PROTO</td><td>" . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'Não definido') . "</td></tr>";
echo "</table>";

echo "<h2>🌐 Todos os Headers HTTP</h2>";
echo "<pre>";
foreach (getallheaders() as $name => $value) {
    echo "$name: $value\n";
}
echo "</pre>";

echo "<h2>🔧 Condições do .htaccess</h2>";
echo "<p><strong>Ambiente detectado:</strong> ";

// Verificar detecção de ambiente
$is_dev = false;
if (strpos($_SERVER['REQUEST_URI'], 'chamados_system_dev') !== false) {
    $is_dev = true;
    echo "DESENVOLVIMENTO (pasta _dev)";
} elseif (isset($_GET['dev']) && $_GET['dev'] == '1') {
    $is_dev = true;
    echo "DESENVOLVIMENTO (parâmetro dev=1)";
} elseif (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], ':8080') !== false) {
    $is_dev = true;
    echo "DESENVOLVIMENTO (porta 8080)";
} else {
    echo "PRODUÇÃO";
}
echo "</p>";

echo "<h2>⚙️ Configurações PHP Ativas</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Configuração</th><th>Valor</th></tr>";
echo "<tr><td>display_errors</td><td>" . (ini_get('display_errors') ? 'On' : 'Off') . "</td></tr>";
echo "<tr><td>session.cookie_secure</td><td>" . (ini_get('session.cookie_secure') ? 'On' : 'Off') . "</td></tr>";
echo "<tr><td>memory_limit</td><td>" . ini_get('memory_limit') . "</td></tr>";
echo "<tr><td>max_execution_time</td><td>" . ini_get('max_execution_time') . "</td></tr>";
echo "</table>";

echo "<h2>🚨 Possíveis Causas do Redirecionamento</h2>";
echo "<ul>";

// Verificar possíveis causas
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    echo "<li>❌ Variável HTTPS está definida</li>";
} else {
    echo "<li>✅ Variável HTTPS não está forçando HTTPS</li>";
}

if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    echo "<li>❌ Proxy/Load Balancer está forçando HTTPS</li>";
} else {
    echo "<li>✅ Sem proxy forçando HTTPS</li>";
}

if ($_SERVER['SERVER_PORT'] == 443) {
    echo "<li>❌ Servidor rodando na porta HTTPS (443)</li>";
} else {
    echo "<li>✅ Servidor na porta HTTP (" . $_SERVER['SERVER_PORT'] . ")</li>";
}

echo "</ul>";

echo "<h2>🔄 Teste de Redirecionamento Manual</h2>";
echo "<p><a href='http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "'>🔗 Forçar HTTP</a></p>";
echo "<p><a href='https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "'>🔗 Testar HTTPS</a></p>";

echo "<h2>📱 Informações do Navegador</h2>";
echo "<p><strong>User Agent:</strong> " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Não definido') . "</p>";

echo "<script>";
echo "console.log('Protocolo atual:', window.location.protocol);";
echo "console.log('Host atual:', window.location.host);";
echo "console.log('URL completa:', window.location.href);";
echo "</script>";

echo "<p><em>Abra F12 > Console para ver informações do JavaScript</em></p>";
?>
