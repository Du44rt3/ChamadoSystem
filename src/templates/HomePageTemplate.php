<?php
/**
 * Template específico para a página inicial (index.php)
 * Inclui funcionalidades de pesquisa geral e filtros avançados
 */

class HomePageTemplate {
    private $page_title;
    private $page_subtitle;

    public function __construct() {
        $this->page_title = "Gestão de Chamados";
        $this->page_subtitle = "Sistema de Controle e Atendimento";
    }

    /**
     * Renderiza o cabeçalho HTML
     */
    public function renderHeader() {
        echo "<!DOCTYPE html>";
        echo "<html lang='pt-BR'>";
        echo "<head>";
        echo "<meta charset='UTF-8'>";
        echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
        echo "<title>ELUS Facilities - Sistema de Chamados</title>";
        echo "<link rel='icon' type='image/svg+xml' href='favicon.svg'>";
        echo "<link rel='icon' type='image/png' href='images/favicon.png'>";
        echo "<link rel='apple-touch-icon' href='images/favicon.png'>";
        echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css' rel='stylesheet'>";
        echo "<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css' rel='stylesheet'>";
        echo "<link href='../css/style.css?v=" . time() . "' rel='stylesheet'>";
        echo "<link href='../assets/css/chamados-list.css?v=" . time() . "' rel='stylesheet'>";
        echo "</head>";
    }

    /**
     * Renderiza o corpo da página
     */
    public function renderBody() {
        echo "<body>";
        
        // Definir variáveis para o header
        global $page_title, $page_subtitle, $auth, $current_user;
        $page_title = $this->page_title;
        $page_subtitle = $this->page_subtitle;
        
        // Incluir header modernizado
        require_once '../src/header.php';
        
        echo "<div class='container-fluid mt-4'>";
    }

    /**
     * Renderiza o rodapé da página
     */
    public function renderFooter() {
        echo "</div>"; // Fim do container-fluid
        
        // Scripts
        echo "<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js'></script>";
        echo "<script src='../assets/js/chamados-list.js?v=" . time() . "'></script>";
        
        // JavaScript específico da página inicial
        echo "<script>";
        $this->renderPageSpecificJS();
        echo "</script>";
        
        echo "</body>";
        echo "</html>";
    }

    /**
     * Renderiza JavaScript específico da página inicial
     */
    private function renderPageSpecificJS() {
        echo "
        function toggleFilters() {
            const filtersContainer = document.getElementById('filters-container');
            const showFiltersBtn = document.getElementById('show-filters-btn');
            const toggleBtn = document.querySelector('#filters-container button[onclick=\"toggleFilters()\"]');
            
            if (filtersContainer.style.display === 'none') {
                filtersContainer.style.display = 'block';
                showFiltersBtn.style.display = 'none';
                toggleBtn.innerHTML = '<i class=\"fas fa-eye-slash me-1\"></i>Ocultar';
            } else {
                filtersContainer.style.display = 'none';
                showFiltersBtn.style.display = 'block';
                toggleBtn.innerHTML = '<i class=\"fas fa-eye me-1\"></i>Mostrar';
            }
        }
        
        function initializeFilters() {
            const filterStatus = document.getElementById('filter-status');
            const filterGravidade = document.getElementById('filter-gravidade');
            const filterSetor = document.getElementById('filter-setor');
            const filterDataInicio = document.getElementById('filter-data-inicio');
            const filterDataFim = document.getElementById('filter-data-fim');
            
            // Adicionar event listeners
            [filterStatus, filterGravidade, filterSetor, filterDataInicio, filterDataFim].forEach(filter => {
                if (filter) {
                    filter.addEventListener('change', applyFilters);
                }
            });
        }
        
        function applyFilters() {
            const statusFilter = document.getElementById('filter-status').value;
            const gravidadeFilter = document.getElementById('filter-gravidade').value;
            const setorFilter = document.getElementById('filter-setor').value;
            const dataInicioFilter = document.getElementById('filter-data-inicio').value;
            const dataFimFilter = document.getElementById('filter-data-fim').value;
            
            // Filtrar cards
            const cardItems = document.querySelectorAll('.card-item');
            let visibleCards = 0;
            
            cardItems.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                const cardGravidade = card.getAttribute('data-gravidade');
                const cardSetor = card.getAttribute('data-setor');
                const cardData = card.getAttribute('data-data');
                
                let showCard = true;
                
                if (statusFilter && cardStatus !== statusFilter) showCard = false;
                if (gravidadeFilter && cardGravidade !== gravidadeFilter) showCard = false;
                if (setorFilter && cardSetor !== setorFilter) showCard = false;
                
                // Filtro por data
                if (dataInicioFilter && cardData < dataInicioFilter) showCard = false;
                if (dataFimFilter && cardData > dataFimFilter) showCard = false;
                
                if (showCard) {
                    card.style.display = 'block';
                    visibleCards++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Filtrar linhas da tabela
            const tableRows = document.querySelectorAll('.table-row');
            let visibleRows = 0;
            
            tableRows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                const rowGravidade = row.getAttribute('data-gravidade');
                const rowSetor = row.getAttribute('data-setor');
                const rowData = row.getAttribute('data-data');
                
                let showRow = true;
                
                if (statusFilter && rowStatus !== statusFilter) showRow = false;
                if (gravidadeFilter && rowGravidade !== gravidadeFilter) showRow = false;
                if (setorFilter && rowSetor !== setorFilter) showRow = false;
                
                // Filtro por data
                if (dataInicioFilter && rowData < dataInicioFilter) showRow = false;
                if (dataFimFilter && rowData > dataFimFilter) showRow = false;
                
                if (showRow) {
                    row.style.display = 'table-row';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Atualizar contador
            updateCounter(Math.max(visibleCards, visibleRows));
        }
        
        function clearFilters() {
            document.getElementById('filter-status').value = '';
            document.getElementById('filter-gravidade').value = '';
            document.getElementById('filter-setor').value = '';
            document.getElementById('filter-data-inicio').value = '';
            document.getElementById('filter-data-fim').value = '';
            
            // Mostrar todos os itens
            document.querySelectorAll('.card-item').forEach(card => {
                card.style.display = 'block';
            });
            
            document.querySelectorAll('.table-row').forEach(row => {
                row.style.display = 'table-row';
            });
            
            // Resetar contador
            const totalItems = document.querySelectorAll('.card-item').length;
            updateCounter(totalItems);
        }
        
        function updateCounter(count) {
            const badge = document.querySelector('.badge.bg-primary');
            if (badge) {
                badge.textContent = count;
            }
        }
        
        // Inicializar quando carregar
        document.addEventListener('DOMContentLoaded', function() {
            initializeFilters();
        });
        ";
    }
}
