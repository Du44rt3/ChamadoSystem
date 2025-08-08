<?php
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

// Incluir dependências
require_once '../src/DB.php';
require_once '../src/Chamado.php';
require_once '../src/templates/HomePageTemplate.php';
require_once '../src/components/HomePageView.php';

// Inicializar template e componentes
$template = new HomePageTemplate();
$database = new DB();
$db = $database->getConnection();
$homeView = new HomePageView($database);

// Renderizar cabeçalho
$template->renderHeader();
$template->renderBody();

// Renderizar seção de busca
$homeView->renderSearchSection();

// Inicializar objeto Chamado
$chamado = new Chamado($db);

// Processar pesquisa
$termo_pesquisa = '';
$rows = [];

if(isset($_GET['pesquisa']) && !empty($_GET['pesquisa'])){
    // Para pesquisa, usar método que agora retorna array do cache
    $rows = $chamado->search($_GET['pesquisa']);
    $termo_pesquisa = $_GET['pesquisa'];
    $homeView->renderSearchAlert($termo_pesquisa);
} else {
    // Para listagem geral, usar cache (retorna array diretamente)
    $rows = $chamado->read();
}

$num = count($rows);

if($num > 0){
    // Renderizar cabeçalho da listagem
    $homeView->renderHeader($num);
    
    // Renderizar filtros
    $homeView->renderFilters($rows);
    
    // Renderizar visualizações
    $homeView->renderCardsView($rows);
    $homeView->renderListView($rows);
    
    // Renderizar rodapé
    $homeView->renderFooter($num);
    
} else {
    $homeView->renderEmptyState($termo_pesquisa);
}

// Renderizar rodapé da página
$template->renderFooter();
?>

<!-- Auto-refresh para manter dados atualizados -->
<script>
// Auto-refresh da página a cada 2 minutos se não houver pesquisa ativa
document.addEventListener('DOMContentLoaded', function() {
    // Só fazer auto-refresh se não estiver em uma pesquisa
    const isPesquisa = <?php echo isset($_GET['pesquisa']) && !empty($_GET['pesquisa']) ? 'true' : 'false'; ?>;
    
    if (!isPesquisa) {
        let refreshInterval = setInterval(function() {
            // Verificar se não há modals abertos ou formulários sendo preenchidos
            const hasOpenModal = document.querySelector('.modal.show');
            const hasActiveInput = document.activeElement && (document.activeElement.type === 'text' || document.activeElement.type === 'textarea');
            
            if (!hasOpenModal && !hasActiveInput) {
                console.log('Auto-refresh: Recarregando página para mostrar novos chamados...');
                
                // Mostrar indicador de atualização
                const indicator = document.createElement('div');
                indicator.innerHTML = `
                    <div class="alert alert-info alert-dismissible fade show position-fixed" 
                         style="top: 20px; right: 20px; z-index: 9999; max-width: 300px;">
                        <i class="fas fa-sync-alt fa-spin me-2"></i>
                        Atualizando lista de chamados...
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                document.body.appendChild(indicator);
                
                // Recarregar após um delay curto
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        }, 120000); // 2 minutos
        
        // Limpar interval se o usuário navegar para outra página
        window.addEventListener('beforeunload', function() {
            clearInterval(refreshInterval);
        });
        
        // Pausar auto-refresh quando a aba não estiver ativa
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearInterval(refreshInterval);
            } else {
                // Reiniciar quando voltar à aba
                refreshInterval = setInterval(function() {
                    const hasOpenModal = document.querySelector('.modal.show');
                    const hasActiveInput = document.activeElement && (document.activeElement.type === 'text' || document.activeElement.type === 'textarea');
                    
                    if (!hasOpenModal && !hasActiveInput) {
                        console.log('Auto-refresh: Recarregando página para mostrar novos chamados...');
                        
                        const indicator = document.createElement('div');
                        indicator.innerHTML = `
                            <div class="alert alert-info alert-dismissible fade show position-fixed" 
                                 style="top: 20px; right: 20px; z-index: 9999; max-width: 300px;">
                                <i class="fas fa-sync-alt fa-spin me-2"></i>
                                Atualizando lista de chamados...
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                        document.body.appendChild(indicator);
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                }, 120000); // 2 minutos
            }
        });
    }
    
    // Adicionar botão manual de refresh
    const pageHeader = document.querySelector('.page-header-clean .page-header-content');
    if (pageHeader) {
        const refreshBtn = document.createElement('button');
        refreshBtn.className = 'btn btn-outline-primary btn-sm';
        refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Atualizar';
        refreshBtn.onclick = function() {
            this.innerHTML = '<i class="fas fa-sync-alt fa-spin me-1"></i> Atualizando...';
            setTimeout(() => window.location.reload(), 500);
        };
        
        const actionsDiv = pageHeader.querySelector('.page-actions') || pageHeader;
        actionsDiv.appendChild(refreshBtn);
    }
});
</script>