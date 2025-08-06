<?php
/**
 * Debug do Sistema de Token CSRF
 */

// Ativar exibição de erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 Debug do Sistema de Token CSRF</h1>";
echo "<hr>";

// Verificar configurações de sessão
echo "<h2>1. Configurações de Sessão</h2>";
echo "<ul>";
echo "<li><strong>Session Status:</strong> " . session_status() . " (0=disabled, 1=none, 2=active)</li>";
echo "<li><strong>Session ID:</strong> " . (session_id() ?: 'NENHUM') . "</li>";
echo "<li><strong>Session Name:</strong> " . session_name() . "</li>";
echo "<li><strong>Session Save Path:</strong> " . session_save_path() . "</li>";
echo "</ul>";

// Tentar carregar as classes
echo "<h2>2. Carregamento das Classes</h2>";
try {
    require_once '../config/config.php';
    echo "✅ config.php carregado<br>";
    
    require_once '../src/DB.php';
    echo "✅ DB.php carregado<br>";
    
    require_once '../src/Auth.php';
    echo "✅ Auth.php carregado<br>";
    
    require_once '../src/SecurityValidator.php';
    echo "✅ SecurityValidator.php carregado<br>";
    
} catch (Exception $e) {
    echo "❌ Erro ao carregar classes: " . $e->getMessage() . "<br>";
    exit;
}

// Testar conexão com banco
echo "<h2>3. Teste de Conexão com Banco</h2>";
try {
    $database = new DB();
    $db = $database->getConnection();
    
    if ($db) {
        echo "✅ Conexão com banco estabelecida<br>";
        
        // Criar instância do Auth
        $auth = new Auth($db);
        echo "✅ Classe Auth instanciada<br>";
        
    } else {
        echo "❌ Falha na conexão com banco<br>";
        exit;
    }
} catch (Exception $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
    exit;
}

// Testar geração de token CSRF
echo "<h2>4. Teste de Token CSRF</h2>";
try {
    // Primeira geração
    $token1 = $auth->generateCSRFToken();
    echo "✅ <strong>Token gerado:</strong> " . $token1 . "<br>";
    
    // Segunda geração (deve ser o mesmo)
    $token2 = $auth->generateCSRFToken();
    echo "✅ <strong>Token reutilizado:</strong> " . $token2 . "<br>";
    
    if ($token1 === $token2) {
        echo "✅ <strong>Tokens são consistentes</strong><br>";
    } else {
        echo "❌ <strong>ERRO: Tokens diferentes!</strong><br>";
    }
    
    // Verificar se está na sessão
    echo "<strong>Token na sessão:</strong> " . ($_SESSION['csrf_token'] ?? 'NÃO ENCONTRADO') . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erro ao gerar token: " . $e->getMessage() . "<br>";
}

// Testar validação CSRF
echo "<h2>5. Teste de Validação CSRF</h2>";
try {
    // Testar com token correto
    $isValid1 = SecurityValidator::validateCSRF($token1);
    echo "Validação com token correto: " . ($isValid1 ? '✅ VÁLIDO' : '❌ INVÁLIDO') . "<br>";
    
    // Testar com token incorreto
    $isValid2 = SecurityValidator::validateCSRF('token_falso');
    echo "Validação com token falso: " . ($isValid2 ? '❌ VÁLIDO (ERRO!)' : '✅ INVÁLIDO (CORRETO)') . "<br>";
    
    // Testar método alternativo da classe Auth
    $isValid3 = $auth->verifyCSRFToken($token1);
    echo "Validação via Auth::verifyCSRFToken: " . ($isValid3 ? '✅ VÁLIDO' : '❌ INVÁLIDO') . "<br>";
    
} catch (Exception $e) {
    echo "❌ Erro na validação: " . $e->getMessage() . "<br>";
}

// Mostrar todas as variáveis de sessão
echo "<h2>6. Variáveis de Sessão</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Simular formulário de teste
echo "<h2>7. Teste de Formulário</h2>";
echo "<form method='POST' action=''>";
echo "<input type='hidden' name='csrf_token' value='" . $token1 . "'>";
echo "<input type='text' name='test_field' placeholder='Campo de teste' required>";
echo "<button type='submit' name='test_submit'>Testar Token</button>";
echo "</form>";

// Processar teste do formulário
if (isset($_POST['test_submit'])) {
    echo "<h3>Resultado do Teste do Formulário:</h3>";
    $submittedToken = $_POST['csrf_token'] ?? '';
    echo "<strong>Token enviado:</strong> " . $submittedToken . "<br>";
    echo "<strong>Token na sessão:</strong> " . ($_SESSION['csrf_token'] ?? 'NENHUM') . "<br>";
    
    $validation = SecurityValidator::validateCSRF($submittedToken);
    echo "<strong>Validação:</strong> " . ($validation ? '✅ SUCESSO' : '❌ FALHA') . "<br>";
    
    if (!$validation) {
        echo "<div style='background-color: #ffe6e6; padding: 10px; border: 1px solid #ff6b6b; margin: 10px 0;'>";
        echo "<strong>❌ Token inválido detectado!</strong><br>";
        echo "Possíveis causas:<br>";
        echo "1. Sessão expirou ou foi reiniciada<br>";
        echo "2. Token foi modificado durante o envio<br>";
        echo "3. Problema na geração/armazenamento do token<br>";
        echo "4. Configuração de sessão incorreta<br>";
        echo "</div>";
    }
}

echo "<hr>";
echo "<h2>8. Soluções Sugeridas</h2>";
echo "<div style='background-color: #f0f8ff; padding: 15px; border: 1px solid #4169e1; margin: 10px 0;'>";
echo "<strong>Para corrigir o erro de token inválido:</strong><br><br>";
echo "1. <strong>Limpar sessão:</strong> Feche o navegador e abra novamente<br>";
echo "2. <strong>Verificar cookies:</strong> Limpe os cookies do site<br>";
echo "3. <strong>Configuração de sessão:</strong> Verifique as configurações no .env<br>";
echo "4. <strong>Permissões:</strong> Verifique se o diretório de sessão tem permissão de escrita<br>";
echo "5. <strong>HTTPS:</strong> Se estiver usando HTTPS, ajuste SESSION_SECURE no .env<br>";
echo "</div>";

echo "<p><em>Debug executado em: " . date('Y-m-d H:i:s') . "</em></p>";
?>
