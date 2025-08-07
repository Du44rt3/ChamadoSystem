<?php
/**
 * Página de chamados em andamento - refatorada usando componentes unificados
 * Elimina duplicação de código com abertos.php e fechados.php
 */

// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

// Incluir dependências necessárias
require_once '../src/DB.php';
require_once '../src/Chamado.php';
require_once '../src/components/ChamadosListView.php';
require_once '../src/templates/ChamadosPageTemplate.php';

// Configurar status da página
$status = 'em_andamento';
$page_title = "Chamados em Andamento";
$page_subtitle = "Chamados em atendimento";

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

