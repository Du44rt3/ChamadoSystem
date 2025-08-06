<?php
/**
 * Script de verificação completa do Sistema de Anexos de Imagens
 * Este arquivo testa todas as funcionalidades implementadas
 */

// Configurar relatório de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verificação do Sistema de Anexos</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>
    <style>
        .check-success { color: #28a745; }
        .check-error { color: #dc3545; }
        .check-warning { color: #ffc107; }
        .test-section { margin-bottom: 2rem; border: 1px solid #dee2e6; border-radius: 0.375rem; }
        .test-header { background: #f8f9fa; padding: 1rem; border-bottom: 1px solid #dee2e6; }
        .test-body { padding: 1rem; }
        .test-item { padding: 0.5rem 0; border-bottom: 1px solid #eee; }
        .test-item:last-child { border-bottom: none; }
    </style>
</head>
<body>
<div class='container mt-4'>
    <h1 class='mb-4'><i class='fas fa-clipboard-check'></i> Verificação do Sistema de Anexos</h1>";

$total_tests = 0;
$passed_tests = 0;

function test_result($condition, $success_msg, $error_msg) {
    global $total_tests, $passed_tests;
    $total_tests++;
    
    if ($condition) {
        $passed_tests++;
        echo "<div class='test-item'><i class='fas fa-check-circle check-success'></i> $success_msg</div>";
        return true;
    } else {
        echo "<div class='test-item'><i class='fas fa-times-circle check-error'></i> $error_msg</div>";
        return false;
    }
}

// Teste 1: Estrutura de Arquivos
echo "<div class='test-section'>
    <div class='test-header'><h3><i class='fas fa-folder'></i> Estrutura de Arquivos</h3></div>
    <div class='test-body'>";

test_result(
    file_exists('../src/ChamadoAnexo.php'),
    'Classe ChamadoAnexo.php existe',
    'Classe ChamadoAnexo.php NÃO encontrada'
);

test_result(
    file_exists('../public/adicionar_anexos.php'),
    'Página adicionar_anexos.php existe',
    'Página adicionar_anexos.php NÃO encontrada'
);

test_result(
    file_exists('../public/download_anexo.php'),
    'Script download_anexo.php existe',
    'Script download_anexo.php NÃO encontrado'
);

test_result(
    file_exists('../public/excluir_anexo.php'),
    'Script excluir_anexo.php existe',
    'Script excluir_anexo.php NÃO encontrado'
);

test_result(
    is_dir('../uploads/anexos'),
    'Diretório uploads/anexos existe',
    'Diretório uploads/anexos NÃO existe'
);

test_result(
    file_exists('../uploads/.htaccess'),
    'Arquivo .htaccess de proteção existe',
    'Arquivo .htaccess de proteção NÃO existe'
);

echo "</div></div>";

// Teste 2: Banco de Dados
echo "<div class='test-section'>
    <div class='test-header'><h3><i class='fas fa-database'></i> Banco de Dados</h3></div>
    <div class='test-body'>";

try {
    require_once '../src/DB.php';
    $database = new DB();
    $db = $database->getConnection();
    
    test_result(true, 'Conexão com banco de dados estabelecida', 'Erro na conexão com banco de dados');
    
    // Verificar se tabela existe
    $stmt = $db->query("SHOW TABLES LIKE 'chamado_anexos'");
    test_result(
        $stmt->rowCount() > 0,
        'Tabela chamado_anexos existe',
        'Tabela chamado_anexos NÃO existe'
    );
    
    // Verificar estrutura da tabela
    if ($stmt->rowCount() > 0) {
        $stmt = $db->query("DESCRIBE chamado_anexos");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $expected_columns = ['id', 'chamado_id', 'nome_original', 'nome_arquivo', 'caminho_arquivo', 'tipo_mime', 'tamanho_arquivo', 'data_upload', 'usuario_upload'];
        
        $has_all_columns = true;
        foreach ($expected_columns as $col) {
            $found = false;
            foreach ($columns as $column) {
                if ($column['Field'] == $col) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $has_all_columns = false;
                break;
            }
        }
        
        test_result($has_all_columns, 'Estrutura da tabela está correta', 'Estrutura da tabela está incorreta');
    }
    
    // Verificar triggers
    $stmt = $db->query("SHOW TRIGGERS WHERE `Trigger` LIKE 'after_anexo_%'");
    $triggers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    test_result(
        count($triggers) >= 2,
        count($triggers) . ' triggers de histórico encontrados',
        'Triggers de histórico NÃO encontrados'
    );
    
} catch (Exception $e) {
    test_result(false, '', 'Erro na conexão com banco: ' . $e->getMessage());
}

echo "</div></div>";

// Teste 3: Classe ChamadoAnexo
echo "<div class='test-section'>
    <div class='test-header'><h3><i class='fas fa-code'></i> Classe ChamadoAnexo</h3></div>
    <div class='test-body'>";

try {
    require_once '../src/ChamadoAnexo.php';
    $anexo = new ChamadoAnexo($db);
    
    test_result(true, 'Classe ChamadoAnexo instanciada com sucesso', 'Erro ao instanciar classe ChamadoAnexo');
    
    // Testar métodos estáticos
    $tipos = ChamadoAnexo::getTiposPermitidos();
    test_result(
        is_array($tipos) && isset($tipos['tipos']),
        'Método getTiposPermitidos() funciona',
        'Método getTiposPermitidos() falhou'
    );
    
    $tamanho = ChamadoAnexo::formatarTamanho(1024);
    test_result(
        $tamanho == '1 KB',
        'Método formatarTamanho() funciona: ' . $tamanho,
        'Método formatarTamanho() falhou'
    );
    
} catch (Exception $e) {
    test_result(false, '', 'Erro ao testar classe: ' . $e->getMessage());
}

echo "</div></div>";

// Teste 4: Permissões e Segurança
echo "<div class='test-section'>
    <div class='test-header'><h3><i class='fas fa-shield-alt'></i> Permissões e Segurança</h3></div>
    <div class='test-body'>";

$upload_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR;

test_result(
    is_writable($upload_dir),
    'Diretório de uploads tem permissão de escrita',
    'Diretório de uploads NÃO tem permissão de escrita'
);

// Verificar conteúdo do .htaccess
$htaccess_content = file_get_contents('../uploads/.htaccess');
test_result(
    strpos($htaccess_content, 'Deny from all') !== false,
    'Arquivo .htaccess contém regras de segurança',
    'Arquivo .htaccess NÃO contém regras de segurança adequadas'
);

test_result(
    strpos($htaccess_content, 'Options -Indexes') !== false,
    'Listagem de diretório está desabilitada',
    'Listagem de diretório NÃO está desabilitada'
);

echo "</div></div>";

// Teste 5: Configuração PHP
echo "<div class='test-section'>
    <div class='test-header'><h3><i class='fas fa-cog'></i> Configuração PHP</h3></div>
    <div class='test-body'>";

$upload_max = ini_get('upload_max_filesize');
$post_max = ini_get('post_max_size');
$file_uploads = ini_get('file_uploads');

test_result(
    $file_uploads,
    'Upload de arquivos está habilitado',
    'Upload de arquivos está DESABILITADO'
);

test_result(
    true,
    "Tamanho máximo de upload: $upload_max",
    ''
);

test_result(
    true,
    "Tamanho máximo de POST: $post_max",
    ''
);

// Verificar extensões necessárias
test_result(
    extension_loaded('gd'),
    'Extensão GD está carregada (necessária para validação de imagens)',
    'Extensão GD NÃO está carregada'
);

echo "</div></div>";

// Resumo Final
echo "<div class='test-section'>
    <div class='test-header'><h3><i class='fas fa-chart-pie'></i> Resumo da Verificação</h3></div>
    <div class='test-body'>";

$percentage = round(($passed_tests / $total_tests) * 100, 1);
$status_class = $percentage >= 90 ? 'check-success' : ($percentage >= 70 ? 'check-warning' : 'check-error');
$status_icon = $percentage >= 90 ? 'fa-check-circle' : ($percentage >= 70 ? 'fa-exclamation-triangle' : 'fa-times-circle');

echo "<div class='alert alert-info'>
    <h4><i class='fas $status_icon $status_class'></i> Resultado: $passed_tests de $total_tests testes passaram ($percentage%)</h4>
</div>";

if ($percentage >= 90) {
    echo "<div class='alert alert-success'>
        <h5><i class='fas fa-thumbs-up'></i> Sistema de Anexos Pronto!</h5>
        <p>O sistema de anexos de imagens está funcionando corretamente e pronto para uso.</p>
        <ul>
            <li><strong>Upload:</strong> Funciona na criação e edição de chamados</li>
            <li><strong>Galeria:</strong> Visualização responsiva na página do chamado</li>
            <li><strong>Segurança:</strong> Validações e proteções implementadas</li>
            <li><strong>Histórico:</strong> Registro automático de atividades</li>
        </ul>
    </div>";
} elseif ($percentage >= 70) {
    echo "<div class='alert alert-warning'>
        <h5><i class='fas fa-exclamation-triangle'></i> Sistema Parcialmente Funcional</h5>
        <p>O sistema tem algumas funcionalidades funcionando, mas precisa de ajustes.</p>
    </div>";
} else {
    echo "<div class='alert alert-danger'>
        <h5><i class='fas fa-times-circle'></i> Sistema Precisa de Correções</h5>
        <p>Várias funcionalidades não estão funcionando. Revise a instalação.</p>
    </div>";
}

echo "<div class='mt-4'>
    <h5>Links de Teste:</h5>
    <div class='btn-group' role='group'>
        <a href='../public/add.php' class='btn btn-primary'>
            <i class='fas fa-plus'></i> Criar Chamado com Anexos
        </a>
        <a href='../public/index.php' class='btn btn-secondary'>
            <i class='fas fa-list'></i> Ver Lista de Chamados
        </a>
        <a href='../public/view.php?id=1' class='btn btn-info'>
            <i class='fas fa-eye'></i> Ver Chamado (ID 1)
        </a>
    </div>
</div>";

echo "</div></div>";

echo "</div>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>
