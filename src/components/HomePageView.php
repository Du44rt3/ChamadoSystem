<?php
/**
 * Componente específico para a página inicial (index.php)
 * Inclui pesquisa geral, filtros avançados e exibição de todos os status
 */

class HomePageView {
    private $db;
    private $chamado;

    public function __construct($database) {
        $this->db = $database;
        $this->chamado = new Chamado($this->db);
    }

    /**
     * Renderiza a seção de busca modernizada
     */
    public function renderSearchSection() {
        echo "<div class='search-section mb-4'>";
        echo "<div class='card border-0 shadow-sm'>";
        echo "<div class='card-body p-4'>";
        echo "<div class='row align-items-center g-3'>";
        echo "<div class='col-xl-8 col-lg-7 col-md-12'>";
        echo "<form method='GET' action='index.php' class='search-form'>";
        echo "<div class='input-group input-group-lg'>";
        echo "<span class='input-group-text bg-white border-end-0'>";
        echo "<i class='fas fa-search'></i>";
        echo "</span>";
        echo "<input type='text' ";
        echo "name='pesquisa' ";
        echo "class='form-control border-start-0 border-end-0' ";
        echo "placeholder='Buscar por código, colaborador, setor ou descrição do problema...' ";
        echo "value='" . (isset($_GET['pesquisa']) ? htmlspecialchars($_GET['pesquisa']) : '') . "'>";
        echo "<button class='btn btn-elus-primary px-4' type='submit'>";
        echo "<i class='fas fa-search me-2'></i>Buscar";
        echo "</button>";
        if(isset($_GET['pesquisa']) && !empty($_GET['pesquisa'])) {
            echo "<a href='index.php' class='btn btn-outline-secondary'>";
            echo "<i class='fas fa-times me-1'></i>Limpar";
            echo "</a>";
        }
        echo "</div>";
        echo "</form>";
        echo "</div>";
        echo "<div class='col-xl-4 col-lg-5 col-md-12'>";
        echo "<div class='d-flex gap-2 justify-content-lg-end'>";
        echo "<a href='add.php' class='btn btn-novo-chamado btn-lg flex-fill flex-lg-grow-0 text-white'>";
        echo "<i class='fas fa-plus-circle me-2'></i>Novo Chamado";
        echo "</a>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }

    /**
     * Renderiza o alert de pesquisa
     */
    public function renderSearchAlert($pesquisa) {
        $termo_pesquisa = htmlspecialchars($pesquisa);
        echo "<div class='alert alert-info d-flex align-items-center'>";
        echo "<i class='fas fa-search me-2'></i>";
        echo "Resultados para: <strong>$termo_pesquisa</strong>";
        echo "</div>";
    }

    /**
     * Renderiza o cabeçalho da listagem
     */
    public function renderHeader($num) {
        echo "<div class='d-flex justify-content-between align-items-center mb-4'>";
        echo "<h5 class='mb-0'><i class='fas fa-list me-2'></i>Lista de Chamados <span class='badge bg-primary ms-2'>{$num}</span></h5>";
        echo $this->renderViewToggle();
        echo "</div>";
    }

    /**
     * Renderiza os botões de alternância de visualização
     */
    private function renderViewToggle() {
        echo "<div class='view-options d-flex align-items-center gap-2'>";
        echo "<small class='text-muted me-2'>Visualização:</small>";
        echo "<div class='btn-group btn-group-sm' role='group'>";
        echo "<button type='button' class='btn btn-outline-primary active' id='btn-cards' onclick='toggleView(\"cards\")'>";
        echo "<i class='fas fa-th-large me-1'></i>Cards";
        echo "</button>";
        echo "<button type='button' class='btn btn-outline-primary' id='btn-list' onclick='toggleView(\"list\")'>";
        echo "<i class='fas fa-list me-1'></i>Lista";
        echo "</button>";
        echo "</div>";
        echo "</div>";
    }

    /**
     * Renderiza os filtros avançados
     */
    public function renderFilters($rows) {
        echo "<div id='filters-container' class='mb-4' style='display: none;'>";
        echo "<div class='card'>";
        echo "<div class='card-body'>";
        echo "<div class='row g-3'>";
        
        // Filtro Status
        echo "<div class='col-md-2'>";
        echo "<label class='form-label'><i class='fas fa-filter me-1'></i>Status</label>";
        echo "<select class='form-select form-select-sm' id='filter-status'>";
        echo "<option value=''>Todos os Status</option>";
        echo "<option value='aberto'>Aberto</option>";
        echo "<option value='em_andamento'>Em Andamento</option>";
        echo "<option value='fechado'>Fechado</option>";
        echo "</select>";
        echo "</div>";
        
        // Filtro Gravidade
        echo "<div class='col-md-2'>";
        echo "<label class='form-label'><i class='fas fa-exclamation-triangle me-1'></i>Gravidade</label>";
        echo "<select class='form-select form-select-sm' id='filter-gravidade'>";
        echo "<option value=''>Todas as Gravidades</option>";
        echo "<option value='alta'>Alta</option>";
        echo "<option value='media'>Média</option>";
        echo "<option value='baixa'>Baixa</option>";
        echo "</select>";
        echo "</div>";
        
        // Filtro Setor
        echo "<div class='col-md-2'>";
        echo "<label class='form-label'><i class='fas fa-building me-1'></i>Setor</label>";
        echo "<select class='form-select form-select-sm' id='filter-setor'>";
        echo "<option value=''>Todos os Setores</option>";
        // Buscar setores únicos
        $setores_unicos = array_unique(array_column($rows, 'setor'));
        sort($setores_unicos);
        foreach($setores_unicos as $setor_unico) {
            echo "<option value='" . htmlspecialchars($setor_unico) . "'>" . htmlspecialchars($setor_unico) . "</option>";
        }
        echo "</select>";
        echo "</div>";
        
        // Data Início
        echo "<div class='col-md-2'>";
        echo "<label class='form-label'><i class='fas fa-calendar-alt me-1'></i>Data Início</label>";
        echo "<input type='date' class='form-control form-control-sm' id='filter-data-inicio' onchange='applyFilters()'>";
        echo "</div>";
        
        // Data Fim
        echo "<div class='col-md-2'>";
        echo "<label class='form-label'><i class='fas fa-calendar-alt me-1'></i>Data Fim</label>";
        echo "<input type='date' class='form-control form-control-sm' id='filter-data-fim' onchange='applyFilters()'>";
        echo "</div>";
        
        // Botões de ação
        echo "<div class='col-md-2 d-flex align-items-end'>";
        echo "<button class='btn btn-outline-secondary btn-sm me-2' onclick='clearFilters()'>";
        echo "<i class='fas fa-times me-1'></i>Limpar";
        echo "</button>";
        echo "<button class='btn btn-outline-primary btn-sm' onclick='toggleFilters()'>";
        echo "<i class='fas fa-eye-slash me-1'></i>Ocultar";
        echo "</button>";
        echo "</div>";
        
        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        
        // Botão para mostrar filtros
        echo "<div id='show-filters-btn' class='mb-3'>";
        echo "<button class='btn btn-outline-primary btn-sm' onclick='toggleFilters()'>";
        echo "<i class='fas fa-filter me-1'></i>Mostrar Filtros";
        echo "</button>";
        echo "</div>";
    }

    /**
     * Renderiza a visualização em cards
     */
    public function renderCardsView($rows) {
        echo "<div id='cards-view'>";
        echo "<div class='row'>";

        foreach ($rows as $row) {
            extract($row);
            
            // Formatação da data
            $data_formatada = date('d/m/Y', strtotime($data_abertura));
            $hora_formatada = date('H:i', strtotime($data_abertura));
            
            // Definir cor do card baseado no status
            $card_border = $this->getCardBorderClass($status);
            
            // Data para filtros
            $data_abertura_formatada = date('Y-m-d', strtotime($data_abertura));
            
            echo "<div class='col-lg-4 col-md-6 mb-4 card-item' data-status='{$status}' data-gravidade='{$gravidade}' data-setor='" . htmlspecialchars($setor) . "' data-data='{$data_abertura_formatada}'>";
            echo "<div class='card h-100 chamado-card {$card_border} fade-in'>";
            
            // Header do Card
            echo "<div class='card-header d-flex justify-content-between align-items-center'>";
            echo "<div>";
            echo "<small class='text-muted'>#$id</small><br>";
            echo "<a href='view.php?id={$id}' class='codigo-chamado' style='text-decoration: none;'>";
            echo "<code class='fs-6'>{$codigo_chamado}</code>";
            echo "</a>";
            echo "</div>";
            
            // Status badge
            echo $this->getStatusBadge($status);
            echo "</div>";
            
            // Body do Card
            echo "<div class='card-body d-flex flex-column'>";
            
            // Informações principais
            $this->renderCardMainInfo($row);
            
            // Informações de SLA
            $this->renderCardSlaInfo($row);
            
            // Descrição
            echo "<div class='mb-3'>";
            echo "<h6 class='card-title mb-2'><i class='fas fa-file-alt text-muted me-1'></i>Descrição</h6>";
            echo "<p class='card-text text-muted'>" . htmlspecialchars(substr($descricao_problema, 0, 100)) . (strlen($descricao_problema) > 100 ? '...' : '') . "</p>";
            echo "</div>";
            
            // Solução (se fechado)
            if($status == 'fechado' && isset($solucao) && !empty($solucao)) {
                echo "<div class='mb-3'>";
                echo "<h6 class='card-title mb-2'><i class='fas fa-tools text-success me-1'></i>Solução</h6>";
                echo "<p class='card-text text-success'>" . htmlspecialchars(substr($solucao, 0, 100)) . (strlen($solucao) > 100 ? '...' : '') . "</p>";
                echo "</div>";
            }
            
            // Gravidade
            echo "<div class='mb-3'>";
            echo "<span class='gravidade-{$gravidade}'><i class='fas fa-exclamation-triangle me-1'></i>" . ucfirst($gravidade) . "</span>";
            echo "</div>";
            
            // Status específico
            echo "<div class='mb-3'>";
            echo $this->getStatusSpecificInfo($status);
            echo "</div>";
            
            echo "</div>";
            
            // Footer do Card com ações
            echo "<div class='card-footer bg-transparent border-top-0'>";
            echo "<div class='btn-group w-100' role='group'>";
            echo "<a href='view.php?id={$id}' class='btn btn-outline-info btn-sm'>";
            echo "<i class='fas fa-eye me-1'></i>Ver";
            echo "</a>";
            echo "<a href='edit.php?id={$id}' class='btn btn-outline-warning btn-sm'>";
            echo "<i class='fas fa-edit me-1'></i>Editar";
            echo "</a>";
            echo "<a href='delete.php?id={$id}' class='btn btn-outline-danger btn-sm' onclick='return confirm(\"Tem certeza que deseja excluir este chamado?\")'>";
            echo "<i class='fas fa-trash me-1'></i>Excluir";
            echo "</a>";
            echo "</div>";
            echo "</div>";
            
            echo "</div>"; // Fim do card
            echo "</div>"; // Fim da coluna
        }

        echo "</div>"; // Fim da row
        echo "</div>"; // Fim do cards-view
    }

    /**
     * Renderiza a visualização em lista (tabela)
     */
    public function renderListView($rows) {
        echo "<div id='list-view' style='display: none;'>";
        echo "<div class='table-container'>";
        echo "<div class='table-responsive' style='overflow-x: auto; white-space: nowrap;'>";
        echo "<table class='table table-hover mb-0' style='min-width: 1800px;'>";
        
        // Cabeçalho da tabela
        echo "<thead>";
        echo "<tr>";
        echo "<th style='min-width: 60px;' class='text-center'>#</th>";
        echo "<th style='min-width: 160px;'>Código</th>";
        echo "<th style='min-width: 150px;'>Colaborador</th>";
        echo "<th style='min-width: 180px;'>Email</th>";
        echo "<th style='min-width: 120px;'>Setor</th>";
        echo "<th style='min-width: 200px;'>Descrição</th>";
        echo "<th style='min-width: 200px;'>Solução</th>";
        echo "<th style='min-width: 130px;' class='text-center'>Data Abertura</th>";
        echo "<th style='min-width: 130px;' class='text-center'>Data Fechamento</th>";
        echo "<th style='min-width: 150px;' class='text-center'>SLA</th>";
        echo "<th style='min-width: 100px;' class='text-center'>Gravidade</th>";
        echo "<th style='min-width: 120px;' class='text-center'>Status</th>";
        echo "<th style='min-width: 180px;' class='text-center'>Ações</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";

        foreach ($rows as $row) {
            extract($row);
            
            // Formatação de datas
            $data_formatada = date('d/m/Y', strtotime($data_abertura));
            $hora_formatada = date('H:i', strtotime($data_abertura));
            
            $data_fechamento_formatada = '';
            $hora_fechamento_formatada = '';
            if($status == 'fechado' && isset($data_fechamento) && !empty($data_fechamento)) {
                $data_fechamento_formatada = date('d/m/Y', strtotime($data_fechamento));
                $hora_fechamento_formatada = date('H:i', strtotime($data_fechamento));
            }
            
            // Truncar textos
            $descricao_curta = strlen($descricao_problema) > 50 ? 
                substr($descricao_problema, 0, 50) . '...' : 
                $descricao_problema;
            
            $solucao_curta = '';
            if(isset($solucao) && !empty($solucao)) {
                $solucao_curta = strlen($solucao) > 50 ? 
                    substr($solucao, 0, 50) . '...' : 
                    $solucao;
            }
            
            $data_abertura_attr = date('Y-m-d', strtotime($data_abertura));
            
            echo "<tr class='table-row' data-status='{$status}' data-gravidade='{$gravidade}' data-setor='" . htmlspecialchars($setor) . "' data-data='{$data_abertura_attr}'>";
            
            // Colunas da tabela
            echo "<td class='text-center fw-bold text-muted'>{$id}</td>";
            echo "<td>";
            echo "<a href='view.php?id={$id}' class='codigo-chamado' style='text-decoration: none;'>";
            echo "<code class='d-block'>{$codigo_chamado}</code>";
            echo "</a>";
            echo "</td>";
            echo "<td class='fw-medium' style='white-space: nowrap;'>{$nome_colaborador}</td>";
            echo "<td class='text-muted' style='white-space: nowrap;'><small>{$email}</small></td>";
            echo "<td><span class='badge bg-light text-dark border'>{$setor}</span></td>";
            
            // Descrição
            echo "<td style='white-space: normal; word-wrap: break-word; max-width: 200px;'>";
            echo "<div class='text-truncate' title='" . htmlspecialchars($descricao_problema) . "'>";
            echo htmlspecialchars($descricao_curta);
            echo "</div>";
            echo "</td>";
            
            // Solução
            echo "<td style='white-space: normal; word-wrap: break-word; max-width: 200px;'>";
            if($status == 'fechado' && !empty($solucao_curta)) {
                echo "<div class='text-truncate text-success' title='" . htmlspecialchars($solucao) . "'>";
                echo htmlspecialchars($solucao_curta);
                echo "</div>";
            } else {
                echo "<small class='text-muted'>-</small>";
            }
            echo "</td>";
            
            // Data Abertura
            echo "<td class='text-center'>";
            echo "<div class='small'>";
            echo "<div class='fw-medium'>{$data_formatada}</div>";
            echo "<div class='text-muted'>{$hora_formatada}</div>";
            echo "</div>";
            echo "</td>";
            
            // Data Fechamento
            echo "<td class='text-center'>";
            if($status == 'fechado' && !empty($data_fechamento_formatada)) {
                echo "<div class='small text-success'>";
                echo "<div class='fw-medium'>{$data_fechamento_formatada}</div>";
                echo "<div class='text-muted'>{$hora_fechamento_formatada}</div>";
                echo "</div>";
            } else {
                echo "<small class='text-muted'>-</small>";
            }
            echo "</td>";
            
            // SLA
            echo "<td class='text-center'>";
            $this->renderTableSlaInfo($row);
            echo "</td>";
            
            // Gravidade
            echo "<td class='text-center'><span class='gravidade-{$gravidade}'>" . ucfirst($gravidade) . "</span></td>";
            
            // Status
            echo "<td class='text-center'>";
            echo $this->getStatusBadge($status);
            echo "</td>";
            
            // Ações
            echo "<td class='text-center'>";
            echo "<div class='btn-group btn-group-sm' role='group'>";
            echo "<a href='view.php?id={$id}' class='btn btn-outline-info' title='Visualizar'><i class='fas fa-eye'></i></a>";
            echo "<a href='edit.php?id={$id}' class='btn btn-outline-warning' title='Editar'><i class='fas fa-edit'></i></a>";
            echo "<a href='delete.php?id={$id}' class='btn btn-outline-danger' title='Excluir' onclick='return confirm(\"Tem certeza que deseja excluir este chamado?\")'><i class='fas fa-trash'></i></a>";
            echo "</div>";
            echo "</td>";
            echo "</tr>";
        }

        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
        echo "</div>"; // Fim do list-view
    }

    /**
     * Renderiza mensagem quando não há dados
     */
    public function renderEmptyState($pesquisa = null) {
        if($pesquisa) {
            echo "<div class='alert alert-warning d-flex align-items-center'>";
            echo "<i class='fas fa-exclamation-triangle me-3'></i>";
            echo "<div>";
            echo "<strong>Nenhum resultado encontrado</strong><br>";
            echo "<small>Tente ajustar os termos da pesquisa ou <a href='index.php' class='alert-link'>ver todos os chamados</a></small>";
            echo "</div>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-info d-flex align-items-center'>";
            echo "<i class='fas fa-info-circle me-3'></i>";
            echo "<div>";
            echo "<strong>Nenhum chamado cadastrado</strong><br>";
            echo "<small><a href='add.php' class='alert-link'>Clique aqui para criar o primeiro chamado</a></small>";
            echo "</div>";
            echo "</div>";
        }
    }

    /**
     * Renderiza o rodapé com informações
     */
    public function renderFooter($num) {
        echo "<div class='d-flex justify-content-center align-items-center mt-4 p-3 bg-light rounded'>";
        echo "<small class='text-muted'><i class='fas fa-info-circle me-2'></i>Total de <strong>{$num}</strong> chamado(s) encontrado(s)</small>";
        echo "</div>";
    }

    /**
     * Métodos auxiliares
     */
    private function getCardBorderClass($status) {
        $borders = [
            'aberto' => 'border-info',
            'em_andamento' => 'border-warning',
            'fechado' => 'border-success'
        ];
        return $borders[$status] ?? 'border-light';
    }

    private function getStatusBadge($status) {
        $badges = [
            'aberto' => "<span class='status-badge status-aberto'><i class='fas fa-circle me-1'></i>Aberto</span>",
            'em_andamento' => "<span class='status-badge status-em_andamento'><i class='fas fa-spinner me-1'></i>Em Andamento</span>",
            'fechado' => "<span class='status-badge status-fechado'><i class='fas fa-check-circle me-1'></i>Fechado</span>"
        ];
        return $badges[$status] ?? '';
    }

    private function renderCardMainInfo($row) {
        extract($row);
        
        echo "<div class='mb-3'>";
        echo "<div class='d-flex align-items-center mb-2'>";
        echo "<i class='fas fa-user text-muted me-2'></i>";
        echo "<strong>{$nome_colaborador}</strong>";
        echo "</div>";
        echo "<div class='d-flex align-items-center mb-2'>";
        echo "<i class='fas fa-envelope text-muted me-2'></i>";
        echo "<small class='text-muted'>{$email}</small>";
        echo "</div>";
        echo "<div class='d-flex align-items-center mb-2'>";
        echo "<i class='fas fa-building text-muted me-2'></i>";
        echo "<span class='badge bg-light text-dark'>{$setor}</span>";
        echo "</div>";
        
        $data_formatada = date('d/m/Y', strtotime($data_abertura));
        $hora_formatada = date('H:i', strtotime($data_abertura));
        echo "<div class='d-flex align-items-center mb-2'>";
        echo "<i class='fas fa-calendar text-muted me-2'></i>";
        echo "<small>Aberto: {$data_formatada} às {$hora_formatada}</small>";
        echo "</div>";
        
        // Data de fechamento se fechado
        if($status == 'fechado' && isset($data_fechamento) && !empty($data_fechamento)) {
            $data_fechamento_formatada = date('d/m/Y H:i', strtotime($data_fechamento));
            echo "<div class='d-flex align-items-center mb-2'>";
            echo "<i class='fas fa-calendar-check text-success me-2'></i>";
            echo "<small class='text-success'>Fechado: {$data_fechamento_formatada}</small>";
            echo "</div>";
        }
        echo "</div>";
    }

    private function renderCardSlaInfo($row) {
        if(isset($row['data_limite_sla']) && !empty($row['data_limite_sla'])) {
            $data_limite_formatada = date('d/m/Y H:i', strtotime($row['data_limite_sla']));
            $status_sla = $this->chamado->verificarStatusSLA($row['data_limite_sla'], $row['status']);
            
            echo "<div class='d-flex align-items-center mb-2'>";
            if($status_sla == 'vencido') {
                echo "<i class='fas fa-exclamation-triangle text-danger me-2'></i>";
                echo "<small class='text-danger fw-bold'>SLA Vencido: {$data_limite_formatada}</small>";
            } elseif($status_sla == 'critico') {
                echo "<i class='fas fa-clock text-warning me-2'></i>";
                echo "<small class='text-warning fw-bold blink'>SLA Crítico: {$data_limite_formatada}</small>";
            } elseif($status_sla == 'concluido') {
                echo "<i class='fas fa-check-circle text-success me-2'></i>";
                echo "<small class='text-success'>SLA Atendido</small>";
            } else {
                echo "<i class='fas fa-clock text-info me-2'></i>";
                echo "<small class='text-info'>SLA: {$data_limite_formatada}</small>";
            }
            echo "</div>";
        }
    }

    private function renderTableSlaInfo($row) {
        if(isset($row['data_limite_sla']) && !empty($row['data_limite_sla'])) {
            $data_limite_formatada = date('d/m/Y H:i', strtotime($row['data_limite_sla']));
            $status_sla = $this->chamado->verificarStatusSLA($row['data_limite_sla'], $row['status']);
            
            echo "<div class='small'>";
            if($status_sla == 'vencido') {
                echo "<div class='text-danger fw-bold'>";
                echo "<i class='fas fa-exclamation-triangle me-1'></i>";
                echo "VENCIDO";
                echo "</div>";
                echo "<div class='text-danger'>{$data_limite_formatada}</div>";
            } elseif($status_sla == 'critico') {
                echo "<div class='text-warning fw-bold blink'>";
                echo "<i class='fas fa-clock me-1'></i>";
                echo "CRÍTICO";
                echo "</div>";
                echo "<div class='text-warning'>{$data_limite_formatada}</div>";
            } elseif($status_sla == 'concluido') {
                echo "<div class='text-success fw-bold'>";
                echo "<i class='fas fa-check-circle me-1'></i>";
                echo "ATENDIDO";
                echo "</div>";
                echo "<div class='text-success'>{$data_limite_formatada}</div>";
            } else {
                echo "<div class='text-info'>";
                echo "<i class='fas fa-clock me-1'></i>";
                echo "OK";
                echo "</div>";
                echo "<div class='text-info'>{$data_limite_formatada}</div>";
            }
            echo "</div>";
        } else {
            echo "<small class='text-muted'>-</small>";
        }
    }

    private function getStatusSpecificInfo($status) {
        echo "<div class='d-flex align-items-center'>";
        if($status == 'aberto') {
            echo "<i class='fas fa-clock text-info me-2'></i>";
            echo "<small class='text-info fw-bold'>AGUARDANDO ATENDIMENTO</small>";
        } elseif($status == 'em_andamento') {
            echo "<i class='fas fa-cog fa-spin text-warning me-2'></i>";
            echo "<small class='text-warning fw-bold'>EM ATENDIMENTO</small>";
        } elseif($status == 'fechado') {
            echo "<i class='fas fa-check-double text-success me-2'></i>";
            echo "<small class='text-success fw-bold'>RESOLVIDO</small>";
        }
        echo "</div>";
    }
}
