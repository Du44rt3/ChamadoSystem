<?php
/**
 * Página de visualização de chamado - REFATORADA
 * 
 * Aplicação do princípio de responsabilidade única:
 * - ChamadoViewController: Validação e controle de fluxo
 * - ChamadoDetailView: Exibição dos detalhes do chamado
 * - ChamadoAnexosView: Gerenciamento de anexos
 * - ChamadoHistoricoView: Gerenciamento do histórico
 * - ChamadoViewTemplate: Estrutura da página
 * 
 * Esta refatoração resolve o problema identificado no PONTOS_FRACOS_CODIGO.txt
 * sobre múltiplas responsabilidades em um único arquivo.
 */

// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

// Incluir componentes necessários
require_once '../src/components/ChamadoViewController.php';
require_once '../src/components/ChamadoDetailView.php';
require_once '../src/components/ChamadoAnexosView.php';
require_once '../src/components/ChamadoHistoricoView.php';
require_once '../src/templates/ChamadoViewTemplate.php';

// Inicializar controlador
$controller = new ChamadoViewController();

// Validar e carregar dados do chamado
$chamado = $controller->validateAndLoadChamado();

// Buscar dados relacionados
$atividades = $controller->getHistorico($chamado->id);
$anexos = $controller->getAnexos($chamado->id);

// Inicializar template e componentes de visualização
$template = new ChamadoViewTemplate($chamado);
$detailView = new ChamadoDetailView($chamado);
$anexosView = new ChamadoAnexosView($chamado->id, $anexos);
$historicoView = new ChamadoHistoricoView($chamado->id, $atividades);

// Renderizar página
$template->renderHead();
$template->renderHeader();
$template->renderContainerStart();

// Exibir mensagens de feedback
$controller->renderFeedbackMessages();

// Renderizar componentes principais
$detailView->render();
$anexosView->render();
$historicoView->render();

// Finalizar página
$template->renderFooter();
?>
