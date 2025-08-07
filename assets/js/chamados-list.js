/**
 * JavaScript unificado para páginas de listagem de chamados
 * Elimina duplicação da função toggleView() e outros scripts
 */

class ChamadosListController {
    constructor() {
        this.init();
    }

    /**
     * Inicializa o controlador
     */
    init() {
        this.setupViewToggle();
        this.setupScrollIndicator();
        this.loadUserPreferences();
        this.setupAccessibility();
        this.setupAnimations();
    }

    /**
     * Configura a alternância entre visualização em cards e lista
     */
    setupViewToggle() {
        // Garantir que os elementos existem
        const cardsView = document.getElementById('cards-view');
        const listView = document.getElementById('list-view');
        const btnCards = document.getElementById('btn-cards');
        const btnList = document.getElementById('btn-list');

        if (!cardsView || !listView || !btnCards || !btnList) {
            console.warn('Elementos de visualização não encontrados');
            return;
        }

        // Adicionar event listeners
        btnCards.addEventListener('click', () => this.toggleView('cards'));
        btnList.addEventListener('click', () => this.toggleView('list'));
    }

    /**
     * Alterna entre visualização em cards e lista
     * @param {string} viewType - 'cards' ou 'list'
     */
    toggleView(viewType) {
        const cardsView = document.getElementById('cards-view');
        const listView = document.getElementById('list-view');
        const btnCards = document.getElementById('btn-cards');
        const btnList = document.getElementById('btn-list');

        if (!cardsView || !listView || !btnCards || !btnList) {
            return;
        }

        // Aplicar animação de fade
        this.fadeOut([cardsView, listView], () => {
            if (viewType === 'cards') {
                this.showCardsView(cardsView, listView, btnCards, btnList);
            } else {
                this.showListView(cardsView, listView, btnCards, btnList);
            }
        });

        // Salvar preferência do usuário
        this.saveViewPreference(viewType);
    }

    /**
     * Mostra visualização em cards
     */
    showCardsView(cardsView, listView, btnCards, btnList) {
        cardsView.style.display = 'block';
        listView.style.display = 'none';
        btnCards.classList.add('active');
        btnList.classList.remove('active');

        this.fadeIn([cardsView]);
    }

    /**
     * Mostra visualização em lista
     */
    showListView(cardsView, listView, btnCards, btnList) {
        cardsView.style.display = 'none';
        listView.style.display = 'block';
        btnList.classList.add('active');
        btnCards.classList.remove('active');

        this.fadeIn([listView]);

        // Reconfigurar indicador de scroll após mostrar a tabela
        setTimeout(() => this.checkScrollIndicator(), 100);
    }

    /**
     * Configura o indicador de scroll para tabelas
     */
    setupScrollIndicator() {
        const tableContainer = document.querySelector('.table-responsive');

        if (!tableContainer) {
            return;
        }

        // Criar indicador se não existir
        let scrollIndicator = document.getElementById('scroll-indicator');
        if (!scrollIndicator) {
            scrollIndicator = document.createElement('div');
            scrollIndicator.id = 'scroll-indicator';
            scrollIndicator.className = 'scroll-indicator d-none';
            scrollIndicator.innerHTML = '<i class="fas fa-arrow-right"></i> Role →';

            const tableContainerParent = tableContainer.closest('.table-container');
            if (tableContainerParent) {
                tableContainerParent.appendChild(scrollIndicator);
            }
        }

        // Configurar event listeners
        this.setupScrollIndicatorEvents(tableContainer);
    }

    /**
     * Configura eventos do indicador de scroll
     */
    setupScrollIndicatorEvents(tableContainer) {
        const checkScrollIndicator = () => this.checkScrollIndicator();

        // Verificar quando carregar e redimensionar
        checkScrollIndicator();
        window.addEventListener('resize', checkScrollIndicator);

        // Esconder indicador quando fazer scroll
        tableContainer.addEventListener('scroll', () => {
            setTimeout(checkScrollIndicator, 100);
        });
    }

    /**
     * Verifica se deve mostrar o indicador de scroll
     */
    checkScrollIndicator() {
        const tableContainer = document.querySelector('.table-responsive');
        const scrollIndicator = document.getElementById('scroll-indicator');

        if (!tableContainer || !scrollIndicator) {
            return;
        }

        const needsScroll = tableContainer.scrollWidth > tableContainer.clientWidth;
        const isAtEnd = tableContainer.scrollLeft >= (tableContainer.scrollWidth - tableContainer.clientWidth - 5);

        if (needsScroll && !isAtEnd) {
            scrollIndicator.classList.remove('d-none');
        } else {
            scrollIndicator.classList.add('d-none');
        }
    }

    /**
     * Carrega preferências do usuário
     */
    loadUserPreferences() {
        const savedView = localStorage.getItem('viewPreference') || 'cards';

        // Aplicar visualização salva sem animação na carga inicial
        this.setViewWithoutAnimation(savedView);
    }

    /**
     * Define visualização sem animação (para carga inicial)
     */
    setViewWithoutAnimation(viewType) {
        const cardsView = document.getElementById('cards-view');
        const listView = document.getElementById('list-view');
        const btnCards = document.getElementById('btn-cards');
        const btnList = document.getElementById('btn-list');

        if (!cardsView || !listView || !btnCards || !btnList) {
            return;
        }

        if (viewType === 'cards') {
            cardsView.style.display = 'block';
            listView.style.display = 'none';
            btnCards.classList.add('active');
            btnList.classList.remove('active');
        } else {
            cardsView.style.display = 'none';
            listView.style.display = 'block';
            btnList.classList.add('active');
            btnCards.classList.remove('active');

            // Configurar indicador de scroll para lista
            setTimeout(() => this.checkScrollIndicator(), 100);
        }
    }

    /**
     * Salva preferência de visualização
     */
    saveViewPreference(viewType) {
        try {
            localStorage.setItem('viewPreference', viewType);
        } catch (error) {
            console.warn('Não foi possível salvar preferência de visualização:', error);
        }
    }

    /**
     * Configura melhorias de acessibilidade
     */
    setupAccessibility() {
        // Adicionar suporte a teclado para toggle de visualização
        document.addEventListener('keydown', (event) => {
            // Ctrl/Cmd + 1 para cards, Ctrl/Cmd + 2 para lista
            if ((event.ctrlKey || event.metaKey)) {
                if (event.key === '1') {
                    event.preventDefault();
                    this.toggleView('cards');
                } else if (event.key === '2') {
                    event.preventDefault();
                    this.toggleView('list');
                }
            }
        });

        // Adicionar títulos descritivos aos botões
        const btnCards = document.getElementById('btn-cards');
        const btnList = document.getElementById('btn-list');

        if (btnCards) {
            btnCards.title = 'Visualizar em cards (Ctrl+1)';
            btnCards.setAttribute('aria-label', 'Alternar para visualização em cards');
        }

        if (btnList) {
            btnList.title = 'Visualizar em lista (Ctrl+2)';
            btnList.setAttribute('aria-label', 'Alternar para visualização em lista');
        }
    }

    /**
     * Configura animações suaves
     */
    setupAnimations() {
        // Adicionar classe fade-in aos cards quando carregar
        const cards = document.querySelectorAll('.chamado-card');
        cards.forEach((card, index) => {
            setTimeout(() => {
                card.classList.add('fade-in');
            }, index * 50); // Delay progressivo para efeito cascata
        });
    }

    /**
     * Aplica efeito de fade out
     */
    fadeOut(elements, callback) {
        elements.forEach(element => {
            if (element && element.style.display !== 'none') {
                element.style.transition = 'opacity 0.2s ease-out';
                element.style.opacity = '0';
            }
        });

        setTimeout(() => {
            if (callback) callback();
        }, 200);
    }

    /**
     * Aplica efeito de fade in
     */
    fadeIn(elements) {
        elements.forEach(element => {
            if (element && element.style.display !== 'none') {
                element.style.opacity = '0';
                element.style.transition = 'opacity 0.3s ease-in';

                setTimeout(() => {
                    element.style.opacity = '1';
                }, 10);
            }
        });
    }

    /**
     * Método utilitário para busca dinâmica (futuro)
     */
    setupDynamicSearch() {
        const searchInput = document.querySelector('input[name="pesquisa"]');

        if (!searchInput) {
            return;
        }

        let searchTimeout;

        searchInput.addEventListener('input', (event) => {
            clearTimeout(searchTimeout);

            // Debounce para evitar muitas requisições
            searchTimeout = setTimeout(() => {
                const searchTerm = event.target.value.trim();

                if (searchTerm.length >= 3) {
                    // Implementar busca dinâmica no futuro
                    console.log('Buscar por:', searchTerm);
                }
            }, 500);
        });
    }

    /**
     * Método para atualizar contador de resultados
     */
    updateResultsCounter(count) {
        const badge = document.querySelector('.badge');
        if (badge) {
            badge.textContent = count;
        }
    }

    /**
     * Método para mostrar loading state
     */
    showLoadingState() {
        const cardsView = document.getElementById('cards-view');
        const listView = document.getElementById('list-view');

        if (cardsView) {
            cardsView.innerHTML = this.generateLoadingSkeleton('cards');
        }

        if (listView) {
            const tableBody = listView.querySelector('tbody');
            if (tableBody) {
                tableBody.innerHTML = this.generateLoadingSkeleton('table');
            }
        }
    }

    /**
     * Gera skeleton para loading state
     */
    generateLoadingSkeleton(type) {
        if (type === 'cards') {
            let html = '<div class="row">';
            for (let i = 0; i < 6; i++) {
                html += `
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <div class="loading-skeleton" style="height: 20px; width: 60%; margin-bottom: 5px;"></div>
                                <div class="loading-skeleton" style="height: 15px; width: 40%;"></div>
                            </div>
                            <div class="card-body">
                                <div class="loading-skeleton" style="height: 15px; width: 80%; margin-bottom: 10px;"></div>
                                <div class="loading-skeleton" style="height: 15px; width: 60%; margin-bottom: 10px;"></div>
                                <div class="loading-skeleton" style="height: 60px; width: 100%; margin-bottom: 10px;"></div>
                            </div>
                        </div>
                    </div>
                `;
            }
            html += '</div>';
            return html;
        }

        return ''; // Implementar skeleton para tabela se necessário
    }
}

// Função global para compatibilidade (manter para não quebrar código existente)
function toggleView(viewType) {
    if (window.chamadosController) {
        window.chamadosController.toggleView(viewType);
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', function () {
    // Criar instância global do controlador
    window.chamadosController = new ChamadosListController();

    // Log para debug
    console.log('ChamadosListController inicializado');
});
