<?php
/**
 * Script de Diagnóstico de Conexão - Sistema de Chamados
 * Execute este arquivo para diagnosticar problemas de conexão
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Diagnóstico de Conexão - Sistema de Chamados</h1>";
echo "<hr>";

// Verificar se as configurações estão carregadas
echo "<h2>1. Verificação das Configurações</h2>";

// Tentar carregar configurações
try {
    require_once __DIR__ . '/config/config.php';
    echo "✅ <strong>config.php carregado com sucesso</strong><br>";
    
    echo "<ul>";
    echo "<li><strong>DB_HOST:</strong> " . (defined('DB_HOST') ? DB_HOST : 'NÃO DEFINIDO') . "</li>";
    echo "<li><strong>DB_NAME:</strong> " . (defined('DB_NAME') ? DB_NAME : 'NÃO DEFINIDO') . "</li>";
    echo "<li><strong>DB_USER:</strong> " . (defined('DB_USER') ? DB_USER : 'NÃO DEFINIDO') . "</li>";
    echo "<li><strong>DB_PASS:</strong> " . (defined('DB_PASS') ? (DB_PASS ? '[DEFINIDA]' : '[VAZIA]') : 'NÃO DEFINIDO') . "</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "❌ <strong>Erro ao carregar configurações:</strong> " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Verificar extensões PHP
echo "<h2>2. Verificação das Extensões PHP</h2>";
echo "<ul>";
echo "<li><strong>Versão PHP:</strong> " . phpversion() . "</li>";
echo "<li><strong>PDO:</strong> " . (extension_loaded('pdo') ? '✅ Ativa' : '❌ Inativa') . "</li>";
echo "<li><strong>PDO MySQL:</strong> " . (extension_loaded('pdo_mysql') ? '✅ Ativa' : '❌ Inativa') . "</li>";
echo "<li><strong>MySQLi:</strong> " . (extension_loaded('mysqli') ? '✅ Ativa' : '❌ Inativa') . "</li>";
echo "</ul>";

echo "<hr>";

// Verificar se o MySQL está rodando
echo "<h2>3. Verificação do Servidor MySQL</h2>";

$host = defined('DB_HOST') ? DB_HOST : 'localhost';
$port = 3306;

// Teste de conectividade TCP
$connection = @fsockopen($host, $port, $errno, $errstr, 5);
if ($connection) {
    echo "✅ <strong>MySQL servidor respondendo na porta $port</strong><br>";
    fclose($connection);
} else {
    echo "❌ <strong>MySQL servidor NÃO respondendo na porta $port</strong><br>";
    echo "Erro: $errstr ($errno)<br>";
    echo "<div style='background-color: #ffebcd; padding: 10px; border: 1px solid #daa520; margin: 10px 0;'>";
    echo "<strong>Possíveis soluções:</strong><br>";
    echo "1. Iniciar o XAMPP Control Panel<br>";
    echo "2. Clicar em 'Start' no módulo MySQL<br>";
    echo "3. Verificar se não há outro serviço usando a porta 3306<br>";
    echo "4. Reiniciar o XAMPP se necessário";
    echo "</div>";
}

echo "<hr>";

// Teste de conexão PDO
echo "<h2>4. Teste de Conexão PDO</h2>";

if (defined('DB_HOST') && defined('DB_NAME') && defined('DB_USER')) {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        
        echo "✅ <strong>Conexão PDO estabelecida com sucesso!</strong><br>";
        
        // Verificar versão do MySQL
        $stmt = $pdo->query('SELECT VERSION() as version');
        $version = $stmt->fetch();
        echo "📋 <strong>Versão MySQL:</strong> " . $version['version'] . "<br>";
        
        // Verificar se o banco existe e as tabelas
        echo "<hr>";
        echo "<h3>5. Verificação das Tabelas</h3>";
        
        $tabelas = ['chamados', 'usuarios', 'chamado_historico', 'niveis_acesso'];
        foreach ($tabelas as $tabela) {
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM $tabela");
                $stmt->execute();
                $result = $stmt->fetch();
                echo "✅ <strong>Tabela '$tabela':</strong> " . $result['count'] . " registros<br>";
            } catch (PDOException $e) {
                echo "❌ <strong>Tabela '$tabela':</strong> Erro - " . $e->getMessage() . "<br>";
            }
        }
        
    } catch (PDOException $e) {
        echo "❌ <strong>Erro de conexão PDO:</strong> " . $e->getMessage() . "<br>";
        echo "<div style='background-color: #ffe6e6; padding: 10px; border: 1px solid #ff6b6b; margin: 10px 0;'>";
        echo "<strong>Código do erro:</strong> " . $e->getCode() . "<br>";
        
        // Interpretar códigos de erro comuns
        switch($e->getCode()) {
            case 2002:
                echo "<strong>Problema:</strong> Servidor MySQL não está rodando ou não é acessível<br>";
                echo "<strong>Solução:</strong> Inicie o MySQL através do XAMPP Control Panel";
                break;
            case 1045:
                echo "<strong>Problema:</strong> Usuário ou senha incorretos<br>";
                echo "<strong>Solução:</strong> Verifique as credenciais no arquivo .env";
                break;
            case 1049:
                echo "<strong>Problema:</strong> Banco de dados não existe<br>";
                echo "<strong>Solução:</strong> Criar o banco de dados ou executar o script de instalação";
                break;
            default:
                echo "<strong>Problema:</strong> Erro desconhecido<br>";
                echo "<strong>Solução:</strong> Verificar logs do MySQL para mais detalhes";
        }
        echo "</div>";
    }
} else {
    echo "❌ <strong>Configurações do banco não foram carregadas corretamente</strong><br>";
}

echo "<hr>";
echo "<h2>6. Comandos Úteis</h2>";
echo "<div style='background-color: #f0f0f0; padding: 10px; font-family: monospace;'>";
echo "<strong>Para reiniciar o MySQL no XAMPP:</strong><br>";
echo "1. Abra o XAMPP Control Panel<br>";
echo "2. Pare o MySQL (se estiver rodando)<br>";
echo "3. Inicie o MySQL novamente<br><br>";

echo "<strong>Para verificar se o MySQL está rodando via comando:</strong><br>";
echo "netstat -an | findstr :3306<br><br>";

echo "<strong>Para acessar o phpMyAdmin:</strong><br>";
echo "http://localhost/phpmyadmin/<br>";
echo "</div>";

echo "<hr>";
echo "<p><em>Script executado em: " . date('Y-m-d H:i:s') . "</em></p>";
?>
