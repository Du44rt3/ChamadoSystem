<?php

/**
 * Sistema de Progressive Disclosure para resolver sobrecarga visual
 * Organiza informações em camadas, mostrando apenas o essencial inicialmente
 */
class ProgressiveDisclosureUI {
    private $sections = [];
    private $current_level = 1;
    
    /**
     * Adiciona uma seção que pode ser expandida/recolhida
     */
    public function addSection($id, $title, $content, $level = 1, $initial_state = 'collapsed') {
        $this->sections[] = [
            'id' => $id,
            'title' => $title,
            'content' => $content,
            'level' => $level,
            'initial_state' => $initial_state,
            'priority' => $this->calculatePriority($level, $initial_state)
        ];
    }
    
    /**
     * Calcula prioridade baseada no nível e estado inicial
     */
    private function calculatePriority($level, $initial_state) {
        $priority = $level * 10;
        if ($initial_state === 'expanded') {
            $priority -= 5;
        }
        return $priority;
    }
    
    /**
     * Renderiza card de chamado com progressive disclosure
     */
    public function renderChamadoCard($chamado, $show_details = false) {
        // Garantir que todas as chaves necessárias existem com valores padrão
        $chamado = array_merge([
            'id' => 0,
            'codigo_chamado' => 'N/A',
            'nome_colaborador' => 'N/A',
            'email' => 'N/A',
            'setor' => 'N/A',
            'descricao_problema' => 'N/A',
            'nome_projeto' => 'N/A',
            'data_abertura' => date('Y-m-d H:i:s'),
            'gravidade' => 'baixa',
            'status' => 'aberto',
            'solucao' => '',
            'data_limite_sla' => null,
            'data_fechamento' => null
        ], $chamado);
        
        $status_class = $this->getStatusClass($chamado['status']);
        $gravidade_class = $this->getGravidadeClass($chamado['gravidade']);
        $sla_info = $this->calculateSLAStatus($chamado);
        
        $card_id = 'card-' . $chamado['id'];
        $details_id = 'details-' . $chamado['id'];
        
        $output = "
        <div class='card chamado-card h-100 {$status_class}' data-chamado-id='{$chamado['id']}'>
            <!-- Informações Primárias (sempre visíveis) -->
            <div class='card-header d-flex justify-content-between align-items-start'>
                <div class='chamado-header-main'>
                    <h6 class='card-title mb-1'>
                        <span class='codigo-chamado'>" . htmlspecialchars($chamado['codigo_chamado']) . "</span>
                        <span class='badge {$gravidade_class} ms-2'>" . htmlspecialchars($chamado['gravidade']) . "</span>
                    </h6>
                    <p class='colaborador-nome mb-0 text-muted'>" . htmlspecialchars($chamado['nome_colaborador']) . "</p>
                </div>
                
                <div class='chamado-actions'>
                    <button class='btn btn-sm btn-outline-primary toggle-details' 
                            data-target='{$details_id}' 
                            title='Ver detalhes'>
                        <i class='fas fa-chevron-down'></i>
                    </button>
                </div>
            </div>
            
            <!-- Informações Essenciais (resumo) -->
            <div class='card-body'>
                <div class='essential-info'>
                    <p class='problem-summary' title='" . htmlspecialchars($chamado['descricao_problema']) . "'>
                        " . $this->truncateText($chamado['descricao_problema'], 80) . "
                    </p>
                    
                    <div class='meta-info d-flex justify-content-between align-items-center'>
                        <small class='text-muted'>
                            <i class='fas fa-calendar-alt me-1'></i>
                            " . $this->formatDate($chamado['data_abertura']) . "
                        </small>
                        <span class='sla-indicator {$sla_info['class']}' title='{$sla_info['tooltip']}'>
                            <i class='fas {$sla_info['icon']}'></i>
                        </span>
                    </div>
                </div>
                
                <!-- Detalhes Expandíveis (Progressive Disclosure) -->
                <div class='detailed-info collapse' id='{$details_id}'>
                    <hr class='my-3'>
                    
                    <!-- Nível 2: Informações Secundárias -->
                    <div class='level-2-info'>
                        <div class='row'>
                            <div class='col-6'>
                                <strong>Setor:</strong><br>
                                <span class='text-muted'>" . htmlspecialchars($chamado['setor']) . "</span>
                            </div>
                            <div class='col-6'>
                                <strong>Projeto:</strong><br>
                                <span class='text-muted'>" . htmlspecialchars($chamado['nome_projeto']) . "</span>
                            </div>
                        </div>
                        
                        <div class='mt-2'>
                            <strong>Email:</strong><br>
                            <span class='text-muted'>" . htmlspecialchars($chamado['email']) . "</span>
                        </div>
                        
                        <button class='btn btn-sm btn-link p-0 mt-2 toggle-advanced' 
                                data-target='advanced-{$chamado['id']}'>
                            <i class='fas fa-plus-circle me-1'></i>Informações Avançadas
                        </button>
                    </div>
                    
                    <!-- Nível 3: Informações Avançadas (sob demanda) -->
                    <div class='advanced-info collapse mt-3' id='advanced-{$chamado['id']}'>
                        <div class='card bg-light'>
                            <div class='card-body p-3'>
                                <h6 class='card-title mb-2'>Descrição Completa:</h6>
                                <p class='card-text'>" . htmlspecialchars($chamado['descricao_problema']) . "</p>
                                
                                " . (!empty($chamado['solucao']) ? "
                                <h6 class='card-title mb-2 mt-3'>Solução:</h6>
                                <p class='card-text'>" . htmlspecialchars($chamado['solucao']) . "</p>
                                " : "") . "
                                
                                <div class='row mt-3'>
                                    <div class='col-md-6'>
                                        <small class='text-muted'>
                                            <strong>SLA:</strong> {$sla_info['text']}<br>
                                            <strong>Status:</strong> " . ucfirst($chamado['status']) . "
                                        </small>
                                    </div>
                                    <div class='col-md-6 text-end'>
                                        <a href='view.php?id={$chamado['id']}' class='btn btn-primary btn-sm'>
                                            <i class='fas fa-eye me-1'></i>Ver Completo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>";
        
        return $output;
    }
    
    /**
     * Renderiza filtros com progressive disclosure
     */
    public function renderProgressiveFilters($current_filters = []) {
        // Garantir que current_filters é um array com valores padrão
        $filters = array_merge([
            'pesquisa' => '',
            'status' => '',
            'gravidade' => '',
            'setor' => '',
            'data_inicio' => '',
            'data_fim' => ''
        ], $current_filters);
        
        return "
        <div class='progressive-filters'>
            <!-- Filtros Básicos (sempre visíveis) -->
            <div class='basic-filters row g-2 mb-3'>
                <div class='col-md-4'>
                    <input type='text' class='form-control' name='pesquisa' placeholder='Buscar...' 
                           value='" . htmlspecialchars($filters['pesquisa']) . "'>
                </div>
                <div class='col-md-3'>
                    <select class='form-select' name='status'>
                        <option value=''>Todos os Status</option>
                        <option value='aberto'" . ($filters['status'] === 'aberto' ? ' selected' : '') . ">Aberto</option>
                        <option value='em_andamento'" . ($filters['status'] === 'em_andamento' ? ' selected' : '') . ">Em Andamento</option>
                        <option value='fechado'" . ($filters['status'] === 'fechado' ? ' selected' : '') . ">Fechado</option>
                    </select>
                </div>
                <div class='col-md-3'>
                    <button type='submit' class='btn btn-primary'>
                        <i class='fas fa-search me-1'></i>Buscar
                    </button>
                </div>
                <div class='col-md-2'>
                    <button type='button' class='btn btn-outline-secondary toggle-advanced-filters' 
                            data-target='advanced-filters'>
                        <i class='fas fa-filter me-1'></i>Mais
                    </button>
                </div>
            </div>
            
            <!-- Filtros Avançados (expandíveis) -->
            <div class='advanced-filters collapse' id='advanced-filters'>
                <div class='card bg-light'>
                    <div class='card-body'>
                        <div class='row g-2'>
                            <div class='col-md-3'>
                                <label class='form-label'>Gravidade</label>
                                <select class='form-select' name='gravidade'>
                                    <option value=''>Todas</option>
                                    <option value='alta'" . ($filters['gravidade'] === 'alta' ? ' selected' : '') . ">Alta</option>
                                    <option value='media'" . ($filters['gravidade'] === 'media' ? ' selected' : '') . ">Média</option>
                                    <option value='baixa'" . ($filters['gravidade'] === 'baixa' ? ' selected' : '') . ">Baixa</option>
                                </select>
                            </div>
                            <div class='col-md-3'>
                                <label class='form-label'>Setor</label>
                                <select class='form-select' name='setor'>
                                    <option value=''>Todos</option>
                                    <option value='TI'" . ($filters['setor'] === 'TI' ? ' selected' : '') . ">TI</option>
                                    <option value='RH'" . ($filters['setor'] === 'RH' ? ' selected' : '') . ">RH</option>
                                    <option value='Financeiro'" . ($filters['setor'] === 'Financeiro' ? ' selected' : '') . ">Financeiro</option>
                                </select>
                            </div>
                            <div class='col-md-3'>
                                <label class='form-label'>Data Inicial</label>
                                <input type='date' class='form-control' name='data_inicio' 
                                       value='" . htmlspecialchars($filters['data_inicio']) . "'>
                            </div>
                            <div class='col-md-3'>
                                <label class='form-label'>Data Final</label>
                                <input type='date' class='form-control' name='data_fim' 
                                       value='" . htmlspecialchars($filters['data_fim']) . "'>
                            </div>
                        </div>
                        
                        <div class='mt-3 text-end'>
                            <button type='button' class='btn btn-secondary me-2' onclick='clearFilters()'>
                                <i class='fas fa-times me-1'></i>Limpar
                            </button>
                            <button type='submit' class='btn btn-primary'>
                                <i class='fas fa-filter me-1'></i>Aplicar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>";
    }
    
    /**
     * Utilitários para formatação
     */
    private function truncateText($text, $length) {
        if (strlen($text) <= $length) {
            return htmlspecialchars($text);
        }
        return htmlspecialchars(substr($text, 0, $length)) . '...';
    }
    
    private function formatDate($date) {
        return date('d/m/Y H:i', strtotime($date));
    }
    
    private function getStatusClass($status) {
        switch ($status) {
            case 'aberto':
                return 'border-warning';
            case 'em_andamento':
                return 'border-info';
            case 'fechado':
                return 'border-success';
            default:
                return 'border-secondary';
        }
    }
    
    private function getGravidadeClass($gravidade) {
        switch ($gravidade) {
            case 'alta':
                return 'bg-danger';
            case 'media':
                return 'bg-warning';
            case 'baixa':
                return 'bg-success';
            default:
                return 'bg-secondary';
        }
    }
    
    private function calculateSLAStatus($chamado) {
        // Garantir valores padrão seguros
        $defaults = [
            'data_abertura' => date('Y-m-d H:i:s'),
            'data_limite_sla' => null,
            'status' => 'aberto'
        ];
        $chamado = array_merge($defaults, $chamado);
        
        // Lógica simplificada - seria conectada com o sistema real
        $now = time();
        $abertura = strtotime($chamado['data_abertura']);
        $limite = isset($chamado['data_limite_sla']) ? strtotime($chamado['data_limite_sla']) : $abertura + (24 * 3600);
        
        if ($chamado['status'] === 'fechado') {
            return [
                'class' => 'text-success',
                'icon' => 'fa-check-circle',
                'text' => 'Concluído',
                'tooltip' => 'Chamado concluído'
            ];
        }
        
        if ($now > $limite) {
            return [
                'class' => 'text-danger',
                'icon' => 'fa-exclamation-triangle',
                'text' => 'Vencido',
                'tooltip' => 'SLA vencido'
            ];
        }
        
        $tempo_restante = $limite - $now;
        if ($tempo_restante < 3600) { // Menos de 1 hora
            return [
                'class' => 'text-warning',
                'icon' => 'fa-clock',
                'text' => 'Crítico',
                'tooltip' => 'SLA próximo do vencimento'
            ];
        }
        
        return [
            'class' => 'text-success',
            'icon' => 'fa-check',
            'text' => 'No prazo',
            'tooltip' => 'Dentro do SLA'
        ];
    }
    
    /**
     * Gera JavaScript para Progressive Disclosure
     */
    public function getProgressiveDisclosureScript() {
        return "
        <script>
        class ProgressiveDisclosureController {
            constructor() {
                this.init();
            }
            
            init() {
                this.bindEvents();
                this.setupKeyboardNavigation();
                this.setupAutoCollapse();
            }
            
            bindEvents() {
                // Toggle para detalhes de cards
                document.querySelectorAll('.toggle-details').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.toggleDetails(btn);
                    });
                });
                
                // Toggle para informações avançadas
                document.querySelectorAll('.toggle-advanced').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.toggleAdvanced(btn);
                    });
                });
                
                // Toggle para filtros avançados
                document.querySelectorAll('.toggle-advanced-filters').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.toggleAdvancedFilters(btn);
                    });
                });
            }
            
            toggleDetails(button) {
                const target = button.dataset.target;
                const details = document.getElementById(target);
                const icon = button.querySelector('i');
                
                if (details.classList.contains('show')) {
                    details.classList.remove('show');
                    icon.className = 'fas fa-chevron-down';
                    button.title = 'Ver detalhes';
                } else {
                    // Colapsar outros cards abertos (opcional)
                    this.collapseOtherCards(target);
                    
                    details.classList.add('show');
                    icon.className = 'fas fa-chevron-up';
                    button.title = 'Ocultar detalhes';
                    
                    // Scroll suave até o card
                    setTimeout(() => {
                        button.closest('.card').scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                    }, 300);
                }
            }
            
            toggleAdvanced(button) {
                const target = button.dataset.target;
                const advanced = document.getElementById(target);
                const icon = button.querySelector('i');
                
                if (advanced.classList.contains('show')) {
                    advanced.classList.remove('show');
                    icon.className = 'fas fa-plus-circle';
                    button.innerHTML = '<i class=\"fas fa-plus-circle me-1\"></i>Informações Avançadas';
                } else {
                    advanced.classList.add('show');
                    icon.className = 'fas fa-minus-circle';
                    button.innerHTML = '<i class=\"fas fa-minus-circle me-1\"></i>Ocultar Avançadas';
                }
            }
            
            toggleAdvancedFilters(button) {
                const target = button.dataset.target;
                const filters = document.getElementById(target);
                const icon = button.querySelector('i');
                
                if (filters.classList.contains('show')) {
                    filters.classList.remove('show');
                    icon.className = 'fas fa-filter';
                } else {
                    filters.classList.add('show');
                    icon.className = 'fas fa-filter-circle-xmark';
                }
            }
            
            collapseOtherCards(currentTarget) {
                document.querySelectorAll('.detailed-info.show').forEach(detail => {
                    if (detail.id !== currentTarget) {
                        detail.classList.remove('show');
                        
                        // Atualizar botão correspondente
                        const button = document.querySelector(`[data-target=\"\${detail.id}\"]`);
                        if (button) {
                            const icon = button.querySelector('i');
                            icon.className = 'fas fa-chevron-down';
                            button.title = 'Ver detalhes';
                        }
                    }
                });
            }
            
            setupKeyboardNavigation() {
                document.addEventListener('keydown', (e) => {
                    // ESC para fechar detalhes abertos
                    if (e.key === 'Escape') {
                        document.querySelectorAll('.detailed-info.show').forEach(detail => {
                            detail.classList.remove('show');
                            
                            const button = document.querySelector(`[data-target=\"\${detail.id}\"]`);
                            if (button) {
                                const icon = button.querySelector('i');
                                icon.className = 'fas fa-chevron-down';
                                button.title = 'Ver detalhes';
                            }
                        });
                    }
                });
            }
            
            setupAutoCollapse() {
                // Auto-colapsar quando há muitos cards abertos
                setInterval(() => {
                    const openCards = document.querySelectorAll('.detailed-info.show').length;
                    if (openCards > 3) {
                        // Manter apenas os 2 mais recentemente abertos
                        const allOpen = document.querySelectorAll('.detailed-info.show');
                        for (let i = 0; i < allOpen.length - 2; i++) {
                            allOpen[i].classList.remove('show');
                            
                            const button = document.querySelector(`[data-target=\"\${allOpen[i].id}\"]`);
                            if (button) {
                                const icon = button.querySelector('i');
                                icon.className = 'fas fa-chevron-down';
                                button.title = 'Ver detalhes';
                            }
                        }
                    }
                }, 5000);
            }
        }
        
        // Função global para limpar filtros
        function clearFilters() {
            document.querySelectorAll('input, select').forEach(input => {
                if (input.type === 'text' || input.type === 'date') {
                    input.value = '';
                } else if (input.type === 'select-one') {
                    input.selectedIndex = 0;
                }
            });
        }
        
        // Inicializar quando DOM estiver pronto
        document.addEventListener('DOMContentLoaded', () => {
            window.progressiveDisclosure = new ProgressiveDisclosureController();
        });
        </script>";
    }
}

?>
