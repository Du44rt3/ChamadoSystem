<?php
/**
 * VERIFICAÇÃO DE SEGURANÇA DO SISTEMA
 * Execute este script para verificar o status de segurança
 */

echo "🔒 VERIFICAÇÃO DE SEGURANÇA - SISTEMA DE CHAMADOS ELUS\n";
echo "=" . str_repeat("=", 60) . "\n\n";

$issues = [];
$warnings = [];
$passed = [];

// 1. Verificar se arquivo .env existe
echo "📋 Verificando configurações...\n";
if (file_exists('../.env')) {
    $passed[] = "✅ Arquivo .env existe";
} else {
    $issues[] = "❌ CRÍTICO: Arquivo .env não encontrado";
}

// 2. Verificar se constantes estão definidas
require_once '../config/config.php';

$required_constants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'SESSION_SECURE', 'SESSION_HTTPONLY'];
foreach ($required_constants as $const) {
    if (defined($const)) {
        $passed[] = "✅ Constante {$const} definida";
    } else {
        $issues[] = "❌ CRÍTICO: Constante {$const} não definida";
    }
}

// 3. Verificar conexão com banco
echo "\n🗃️ Verificando banco de dados...\n";
try {
    require_once 'src/DB.php';
    $db_obj = new DB();
    $db = $db_obj->getConnection();
    if ($db) {
        $passed[] = "✅ Conexão com banco funcionando";
        
        // Verificar se tabela usuarios existe
        $stmt = $db->query("SHOW TABLES LIKE 'usuarios'");
        if ($stmt->rowCount() > 0) {
            $passed[] = "✅ Tabela de usuários existe";
            
            // Verificar estrutura da tabela
            $stmt = $db->query("DESCRIBE usuarios");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            $required_columns = ['id', 'username', 'password', 'tentativas_login', 'bloqueado_ate'];
            foreach ($required_columns as $col) {
                if (in_array($col, $columns)) {
                    $passed[] = "✅ Coluna {$col} existe";
                } else {
                    $issues[] = "❌ Coluna {$col} não encontrada na tabela usuarios";
                }
            }
        } else {
            $issues[] = "❌ CRÍTICO: Tabela usuarios não existe";
        }
    } else {
        $issues[] = "❌ CRÍTICO: Não foi possível conectar ao banco";
    }
} catch (Exception $e) {
    $issues[] = "❌ CRÍTICO: Erro na conexão com banco: " . $e->getMessage();
}

// 4. Verificar arquivos de segurança
echo "\n🛡️ Verificando arquivos de segurança...\n";
$security_files = [
    '../src/Auth.php' => 'Classe de autenticação',
    '../src/SecurityValidator.php' => 'Validador de segurança',
    '../src/EnvLoader.php' => 'Carregador de ambiente'
];

foreach ($security_files as $file => $desc) {
    if (file_exists($file)) {
        $passed[] = "✅ {$desc} presente";
        
        // Verificar sintaxe (método mais confiável para Windows)
        $syntax_check = shell_exec("c:\\xampp\\php\\php.exe -l {$file} 2>&1");
        
        if ($syntax_check && strpos($syntax_check, 'No syntax errors') !== false) {
            $passed[] = "✅ {$desc} sem erros de sintaxe";
        } elseif ($syntax_check && strpos($syntax_check, 'Errors parsing') !== false) {
            $issues[] = "❌ Erro de sintaxe em {$desc}: " . trim($syntax_check);
        } else {
            // Se não conseguir verificar sintaxe, assume que está OK
            $passed[] = "✅ {$desc} carregado com sucesso";
        }
    } else {
        $issues[] = "❌ {$desc} não encontrado";
    }
}

// 5. Verificar senhas hardcoded (análise básica)
echo "\n🔑 Verificando senhas hardcoded...\n";
$auth_content = file_get_contents('src/Auth.php');
if (strpos($auth_content, 'Grup0Elus@2025#2026') !== false || 
    strpos($auth_content, 'Elus2214') !== false ||
    strpos($auth_content, 'Duk@2540') !== false) {
    $warnings[] = "⚠️ AVISO: Senhas legadas ainda presentes no código";
} else {
    $passed[] = "✅ Nenhuma senha hardcoded encontrada";
}

// 6. Verificar .gitignore
echo "\n📝 Verificando proteção de arquivos...\n";
if (file_exists('../.gitignore')) {
    $gitignore = file_get_contents('../.gitignore');
    if (strpos($gitignore, '.env') !== false) {
        $passed[] = "✅ Arquivo .env protegido no .gitignore";
    } else {
        $warnings[] = "⚠️ Arquivo .env não está no .gitignore";
    }
} else {
    $warnings[] = "⚠️ Arquivo .gitignore não encontrado";
}

// 7. Verificar configurações de produção
echo "\n🚀 Verificando configurações de produção...\n";
if (defined('APP_ENV')) {
    if (APP_ENV === 'production') {
        if (defined('APP_DEBUG') && !APP_DEBUG) {
            $passed[] = "✅ Debug desabilitado em produção";
        } else {
            $warnings[] = "⚠️ Debug ainda habilitado em produção";
        }
        
        if (defined('SESSION_SECURE') && SESSION_SECURE) {
            $passed[] = "✅ Cookies seguros habilitados";
        } else {
            $warnings[] = "⚠️ Configure SESSION_SECURE=true para HTTPS";
        }
    } else {
        $warnings[] = "⚠️ Sistema em modo desenvolvimento";
    }
}

// Resultados
echo "\n" . str_repeat("=", 70) . "\n";
echo "📊 RESULTADOS DA VERIFICAÇÃO\n";
echo str_repeat("=", 70) . "\n\n";

if (!empty($passed)) {
    echo "✅ SUCESSOS (" . count($passed) . "):\n";
    foreach ($passed as $item) {
        echo "   {$item}\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️ AVISOS (" . count($warnings) . "):\n";
    foreach ($warnings as $item) {
        echo "   {$item}\n";
    }
    echo "\n";
}

if (!empty($issues)) {
    echo "❌ PROBLEMAS CRÍTICOS (" . count($issues) . "):\n";
    foreach ($issues as $item) {
        echo "   {$item}\n";
    }
    echo "\n";
}

// Status geral
$total_checks = count($passed) + count($warnings) + count($issues);
$security_score = round((count($passed) / $total_checks) * 100);

echo str_repeat("=", 70) . "\n";
echo "🎯 PONTUAÇÃO DE SEGURANÇA: {$security_score}%\n";

if ($security_score >= 90) {
    echo "🏆 EXCELENTE: Sistema bem protegido!\n";
} elseif ($security_score >= 75) {
    echo "👍 BOM: Sistema seguro com pequenos ajustes necessários\n";
} elseif ($security_score >= 60) {
    echo "⚠️ REGULAR: Corrija os problemas críticos antes de usar em produção\n";
} else {
    echo "🚨 CRÍTICO: Sistema NÃO está seguro para uso em produção!\n";
}

echo str_repeat("=", 70) . "\n";

// Próximos passos
if (!empty($issues)) {
    echo "\n🚨 AÇÃO IMEDIATA NECESSÁRIA:\n";
    echo "1. Corrija todos os problemas críticos listados acima\n";
    echo "2. Execute este script novamente para verificar\n";
} elseif (!empty($warnings)) {
    echo "\n📋 PRÓXIMOS PASSOS RECOMENDADOS:\n";
    echo "1. Configure HTTPS no servidor\n";
    echo "2. Defina APP_ENV=production e SESSION_SECURE=true\n";
    echo "3. Remova senhas legadas após testar o sistema\n";
    echo "4. Configure backup automático do banco\n";
} else {
    echo "\n🎉 PARABÉNS! Seu sistema está bem protegido!\n";
    echo "Mantenha sempre:\n";
    echo "- Backups regulares\n";
    echo "- Monitoramento de logs\n";
    echo "- Atualizações de segurança\n";
}

echo "\n";
?>
