<?php
/**
 * Página de chamados abertos - refatorada usando componentes unificados
 * Elimina duplicação de código com em_andamento.php e fechados.php
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
$status = 'aberto';
$page_title = "Chamados Abertos";
$page_subtitle = "Chamados aguardando atendimento";

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

<!-- Auto-refresh para chamados abertos -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isPesquisa = <?php echo !empty($pesquisa) ? 'true' : 'false'; ?>;
    
    if (!isPesquisa) {
        let refreshInterval = setInterval(function() {
            const hasOpenModal = document.querySelector('.modal.show');
            const hasActiveInput = document.activeElement && (document.activeElement.type === 'text' || document.activeElement.type === 'textarea');
            
            if (!hasOpenModal && !hasActiveInput) {
                console.log('Auto-refresh: Atualizando chamados abertos...');
                
                const indicator = document.createElement('div');
                indicator.innerHTML = `
                    <div class="alert alert-warning alert-dismissible fade show position-fixed" 
                         style="top: 20px; right: 20px; z-index: 9999; max-width: 300px;">
                        <i class="fas fa-sync-alt fa-spin me-2"></i>
                        Verificando novos chamados abertos...
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                document.body.appendChild(indicator);
                
                setTimeout(() => window.location.reload(), 1000);
            }
        }, 90000); // 1.5 minutos para abertos (mais frequente)
        
        window.addEventListener('beforeunload', () => clearInterval(refreshInterval));
    }
    
    // Botão de refresh manual
    const pageHeader = document.querySelector('.page-header-clean .page-header-content');
    if (pageHeader) {
        const refreshBtn = document.createElement('button');
        refreshBtn.className = 'btn btn-outline-warning btn-sm';
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Atualizar Abertos';
        refreshBtn.onclick = function() {
            this.innerHTML = '<i class="fas fa-sync-alt fa-spin me-1"></i> Atualizando...';
            setTimeout(() => window.location.reload(), 500);
        };
        
        const actionsDiv = pageHeader.querySelector('.page-actions') || pageHeader;
        actionsDiv.appendChild(refreshBtn);
    }
});
</script>

