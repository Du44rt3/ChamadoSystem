<?php
/**
 * Página de chamados fechados - refatorada usando componentes unificados
 * Elimina duplicação de código com abertos.php e em_andamento.php
 */

// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

// Verificar se foi solicitado refresh de cache
if (isset($_GET['refresh']) && $_GET['refresh'] == '1') {
    require_once '../src/CacheManager.php';
    $cache = new CacheManager('../cache');
    $cache->clear();
    
    // Redirecionar sem o parâmetro refresh para evitar reload desnecessário
    $redirect_url = strtok($_SERVER["REQUEST_URI"], '?');
    header("Location: $redirect_url");
    exit;
}

// Incluir dependências necessárias
require_once '../src/DB.php';
require_once '../src/Chamado.php';
require_once '../src/components/ChamadosListView.php';
require_once '../src/templates/ChamadosPageTemplate.php';

// Configurar status da página
$status = 'fechado';
$page_title = "Chamados Fechados";
$page_subtitle = "Chamados resolvidos e finalizados";

// Instanciar template e componente
$template = new ChamadosPageTemplate($status);
$database = new DB();
$db = $database->getConnection();
$listView = new ChamadosListView($db, $status);

// Obter parâmetro de pesquisa
$pesquisa = isset($_GET['pesquisa']) ? $_GET['pesquisa'] : '';

// Renderizar página
$template->renderHeader();
$template->renderSearchBar($pesquisa);
$listView->render($pesquisa);
$template->renderFooter();
?>