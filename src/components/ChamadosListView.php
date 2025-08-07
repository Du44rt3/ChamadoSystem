<?php
/**
 * Componente unificado para listagem de chamados
 * Elimina duplicação de código entre abertos.php, em_andamento.php e fechados.php
 */

class ChamadosListView {
    private $db;
    private $chamado;
    private $status;
    private $config;

    public function __construct($database, $status) {
        $this->db = $database;
        $this->chamado = new Chamado($this->db);
        $this->status = $status;
        $this->config = $this->getStatusConfig($status);
    }

    /**
     * Configurações específicas por status
     */
    private function getStatusConfig($status) {
        $configs = [
            'aberto' => [
                'title' => 'Chamados Abertos',
                'subtitle' => 'Chamados aguardando atendimento',
                'icon' => 'fas fa-exclamation-circle',
                'badge_color' => 'bg-info',
                'text_color' => 'text-info',
                'border_color' => 'border-info',
                'status_badge' => 'status-aberto',
                'status_icon' => 'fas fa-circle',
                'status_text' => 'Aberto',
                'footer_message' => 'aguardando atendimento',
                'empty_message' => 'Todos os chamados foram atendidos!',
                'empty_submessage' => 'Não há chamados pendentes no momento.',
                'action_url' => 'abertos.php'
            ],
            'em_andamento' => [
                'title' => 'Chamados em Andamento',
                'subtitle' => 'Chamados em atendimento',
                'icon' => 'fas fa-clock',
                'badge_color' => 'bg-warning text-dark',
                'text_color' => 'text-warning',
                'border_color' => 'border-warning',
                'status_badge' => 'status-em_andamento',
                'status_icon' => 'fas fa-cog fa-spin',
                'status_text' => 'Em Andamento',
                'footer_message' => 'em atendimento',
                'empty_message' => 'Nenhum chamado em andamento',
                'empty_submessage' => 'Todos os chamados estão aguardando início ou foram finalizados.',
                'action_url' => 'em_andamento.php'
            ],
            'fechado' => [
                'title' => 'Chamados Fechados',
                'subtitle' => 'Chamados resolvidos e finalizados',
                'icon' => 'fas fa-check-circle',
                'badge_color' => 'bg-success',
                'text_color' => 'text-success',
                'border_color' => 'border-success',
                'status_badge' => 'status-fechado',
                'status_icon' => 'fas fa-check-circle',
                'status_text' => 'Fechado',
                'footer_message' => 'resolvido(s)',
                'empty_message' => 'Nenhum chamado fechado ainda',
                'empty_submessage' => 'Quando os chamados forem resolvidos, aparecerão aqui.',
                'action_url' => 'fechados.php'
            ]
        ];

        return $configs[$status] ?? $configs['aberto'];
    }

    /**
     * Renderiza a página completa de listagem
     */
    public function render($pesquisa = null) {
        // Buscar dados (agora retorna array do cache)
        if ($pesquisa && !empty($pesquisa)) {
            $rows = $this->chamado->searchByStatus($pesquisa, $this->status);
            $this->renderSearchAlert($pesquisa);
        } else {
            $rows = $this->chamado->readByStatus($this->status);
        }

        $num = count($rows);

        if ($num > 0) {
            $this->renderHeader($num);
            $this->renderViewToggle();
            $this->renderCardsView($rows);
            $this->renderListView($rows);
            $this->renderFooter($num);
        } else {
            $this->renderEmptyState($pesquisa);
        }
    }

    /**
     * Renderiza o alert de pesquisa
     */
    private function renderSearchAlert($pesquisa) {
        $termo_pesquisa = htmlspecialchars($pesquisa);
        echo "<div class='alert alert-info d-flex align-items-center'>";
        echo "<i class='fas fa-search me-2'></i>";
        echo "Resultados para: <strong>$termo_pesquisa</strong>";
        echo "</div>";
    }

    /**
     * Renderiza o cabeçalho da listagem
     */
    private function renderHeader($num) {
        $config = $this->config;
        echo "<div class='d-flex justify-content-between align-items-center mb-4'>";
        echo "<h5 class='mb-0'>";
        echo "<i class='{$config['icon']} me-2 {$config['text_color']}'></i>";
        echo "{$config['title']} ";
        echo "<span class='badge {$config['badge_color']} ms-2'>{$num}</span>";
        echo "</h5>";
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
        echo "</div>";
    }

    /**
     * Renderiza a visualização em cards
     */
    private function renderCardsView($rows) {
        echo "<div id='cards-view'>";
        echo "<div class='row'>";

        foreach ($rows as $row) {
            $this->renderCard($row);
        }

        echo "</div>";
        echo "</div>";
    }

    /**
     * Renderiza um card individual
     */
    private function renderCard($row) {
        extract($row);
        $config = $this->config;
        
        // Formatação da data
        $data_formatada = date('d/m/Y', strtotime($data_abertura));
        $hora_formatada = date('H:i', strtotime($data_abertura));
        
        // Calcular SLA
        $sla_info = $this->chamado->verificarSLA($id);
        $sla_real = $sla_info['sla_real'];
        $status_sla = $sla_info['status_sla'];
        $data_limite_sla = $sla_info['data_limite_sla'];
        
        // Determinar borda do card baseado na gravidade ou status
        $card_border = $this->getCardBorder($gravidade);
        
        echo "<div class='col-lg-4 col-md-6 mb-4'>";
        echo "<div class='card h-100 chamado-card {$card_border}'>";
        
        // Header do Card
        echo "<div class='card-header d-flex justify-content-between align-items-center'>";
        echo "<div>";
        echo "<small class='text-muted'>#$id</small><br>";
        echo "<a href='view.php?id={$id}' class='codigo-chamado' style='text-decoration: none;'>";
        echo "<code class='fs-6'>{$codigo_chamado}</code>";
        echo "</a>";
        echo "</div>";
        echo "<span class='status-badge {$config['status_badge']}'>";
        echo "<i class='{$config['status_icon']} me-1'></i>{$config['status_text']}";
        echo "</span>";
        echo "</div>";
        
        // Body do Card
        echo "<div class='card-body d-flex flex-column'>";
        
        // Informações principais
        $this->renderCardInfo($row);
        
        // Descrição
        $this->renderCardDescription($descricao_problema);
        
        // Solução (para chamados fechados)
        if ($this->status === 'fechado' && isset($solucao) && !empty($solucao)) {
            $this->renderCardSolution($solucao);
        }
        
        // Gravidade
        echo "<div class='mb-3'>";
        echo "<span class='gravidade-{$gravidade}'>";
        echo "<i class='fas fa-exclamation-triangle me-1'></i>" . ucfirst($gravidade);
        echo "</span>";
        echo "</div>";
        
        // SLA
        $this->renderCardSLA($sla_real, $status_sla, $data_limite_sla);
        
        // Status específico
        $this->renderCardStatus();
        
        echo "</div>";
        
        // Footer com ações
        $this->renderCardActions($id);
        
        echo "</div>";
        echo "</div>";
    }

    /**
     * Renderiza informações principais do card
     */
    private function renderCardInfo($row) {
        extract($row);
        $data_formatada = date('d/m/Y', strtotime($data_abertura));
        $hora_formatada = date('H:i', strtotime($data_abertura));
        
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
        echo "<div class='d-flex align-items-center mb-2'>";
        echo "<i class='fas fa-calendar text-muted me-2'></i>";
        echo "<small>Aberto: {$data_formatada} às {$hora_formatada}</small>";
        echo "</div>";
        
        // Data de fechamento para chamados fechados
        if ($this->status === 'fechado' && isset($data_fechamento) && !empty($data_fechamento)) {
            $data_fechamento_formatada = date('d/m/Y H:i', strtotime($data_fechamento));
            echo "<div class='d-flex align-items-center mb-2'>";
            echo "<i class='fas fa-calendar-check text-success me-2'></i>";
            echo "<small class='text-success'>Fechado: {$data_fechamento_formatada}</small>";
            echo "</div>";
        }
        echo "</div>";
    }

    /**
     * Renderiza a descrição do card
     */
    private function renderCardDescription($descricao) {
        echo "<div class='mb-3 flex-grow-1'>";
        echo "<h6 class='card-title mb-2'><i class='fas fa-file-alt text-muted me-1'></i>Descrição</h6>";
        echo "<p class='card-text text-muted'>";
        echo htmlspecialchars(substr($descricao, 0, 120));
        echo (strlen($descricao) > 120 ? '...' : '');
        echo "</p>";
        echo "</div>";
    }

    /**
     * Renderiza a solução do card (para fechados)
     */
    private function renderCardSolution($solucao) {
        echo "<div class='mb-3'>";
        echo "<h6 class='card-title mb-2'><i class='fas fa-tools text-success me-1'></i>Solução</h6>";
        echo "<p class='card-text text-success'>";
        echo htmlspecialchars(substr($solucao, 0, 100));
        echo (strlen($solucao) > 100 ? '...' : '');
        echo "</p>";
        echo "</div>";
    }

    /**
     * Renderiza informações de SLA do card
     */
    private function renderCardSLA($sla_real, $status_sla, $data_limite_sla) {
        echo "<div class='mb-3'>";
        echo "<div class='sla-info'>";
        echo "<div class='d-flex align-items-center mb-2'>";
        echo "<i class='fas fa-clock text-muted me-2'></i>";
        echo "<strong>SLA" . ($this->status === 'fechado' ? ' Final' : '') . ":</strong>";
        echo "<span class='ms-2 sla-prazo {$status_sla}' ";
        echo ($status_sla == 'critico' ? 'style="animation: blink 1s infinite;"' : '');
        echo ">{$sla_real}</span>";
        echo "</div>";
        
        if ($data_limite_sla) {
            echo "<div class='d-flex align-items-center mb-2'>";
            echo "<i class='fas fa-calendar-alt text-muted me-2'></i>";
            $prazo_text = $this->status === 'fechado' ? 'Prazo era' : 'Prazo';
            echo "<small class='text-muted'>{$prazo_text}: " . date('d/m/Y H:i', strtotime($data_limite_sla)) . "</small>";
            echo "</div>";
        }
        echo "</div>";
        echo "</div>";
    }

    /**
     * Renderiza status específico do card
     */
    private function renderCardStatus() {
        $config = $this->config;
        $status_messages = [
            'aberto' => ['icon' => 'fas fa-clock', 'text' => 'AGUARDANDO ATENDIMENTO', 'color' => 'info'],
            'em_andamento' => ['icon' => 'fas fa-tasks', 'text' => 'SENDO PROCESSADO', 'color' => 'warning'],
            'fechado' => ['icon' => 'fas fa-check-circle', 'text' => 'RESOLVIDO', 'color' => 'success']
        ];
        
        $status_info = $status_messages[$this->status];
        
        echo "<div class='mb-3'>";
        echo "<div class='d-flex align-items-center'>";
        echo "<i class='{$status_info['icon']} text-{$status_info['color']} me-2'></i>";
        echo "<small class='text-{$status_info['color']} fw-bold'>{$status_info['text']}</small>";
        echo "</div>";
        echo "</div>";
    }

    /**
     * Renderiza ações do card
     */
    private function renderCardActions($id) {
        echo "<div class='card-footer bg-transparent border-top-0'>";
        echo "<div class='btn-group w-100' role='group'>";
        echo "<a href='view.php?id={$id}' class='btn btn-outline-info btn-sm'>";
        echo "<i class='fas fa-eye me-1'></i>Ver";
        echo "</a>";
        echo "<a href='edit.php?id={$id}' class='btn btn-outline-warning btn-sm'>";
        echo "<i class='fas fa-edit me-1'></i>Editar";
        echo "</a>";
        echo "<a href='delete.php?id={$id}' class='btn btn-outline-danger btn-sm' ";
        echo "onclick='return confirm(\"Tem certeza que deseja excluir este chamado?\")'>";
        echo "<i class='fas fa-trash me-1'></i>Excluir";
        echo "</a>";
        echo "</div>";
        echo "</div>";
    }

    /**
     * Renderiza a visualização em lista/tabela
     */
    private function renderListView($rows) {
        echo "<div id='list-view' style='display: none;'>";
        echo "<div class='table-container'>";
        echo "<div class='table-responsive' style='overflow-x: auto; white-space: nowrap;'>";
        
        $min_width = $this->status === 'fechado' ? '1500px' : '1400px';
        echo "<table class='table table-hover mb-0' style='min-width: {$min_width};'>";
        echo "<thead>";
        echo "<tr>";
        $this->renderTableHeaders();
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        
        foreach ($rows as $row) {
            $this->renderTableRow($row);
        }
        
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
    }

    /**
     * Renderiza cabeçalhos da tabela
     */
    private function renderTableHeaders() {
        echo "<th style='min-width: 60px;' class='text-center'>#</th>";
        echo "<th style='min-width: 160px;'>Código</th>";
        echo "<th style='min-width: 150px;'>Colaborador</th>";
        echo "<th style='min-width: 180px;'>Email</th>";
        echo "<th style='min-width: 120px;'>Setor</th>";
        echo "<th style='min-width: 280px;'>Descrição</th>";
        echo "<th style='min-width: 130px;' class='text-center'>Data/Hora</th>";
        echo "<th style='min-width: 100px;' class='text-center'>Gravidade</th>";
        echo "<th style='min-width: 150px;' class='text-center'>SLA</th>";
        echo "<th style='min-width: 120px;' class='text-center'>Status</th>";
        
        if ($this->status === 'fechado') {
            echo "<th style='min-width: 150px;' class='text-center'>Solução</th>";
        }
        
        echo "<th style='min-width: 180px;' class='text-center'>Ações</th>";
    }

    /**
     * Renderiza uma linha da tabela
     */
    private function renderTableRow($row) {
        extract($row);
        $config = $this->config;
        
        // Formatação da data
        $data_formatada = date('d/m/Y', strtotime($data_abertura));
        $hora_formatada = date('H:i', strtotime($data_abertura));
        
        // Calcular SLA
        $sla_info = $this->chamado->verificarSLA($id);
        $sla_real = $sla_info['sla_real'];
        $status_sla = $sla_info['status_sla'];
        $data_limite_sla = $sla_info['data_limite_sla'];
        
        // Truncar descrição
        $descricao_curta = strlen($descricao_problema) > 60 ? 
            substr($descricao_problema, 0, 60) . '...' : 
            $descricao_problema;
        
        echo "<tr>";
        echo "<td class='text-center fw-bold text-muted'>{$id}</td>";
        echo "<td>";
        echo "<a href='view.php?id={$id}' class='codigo-chamado' style='text-decoration: none;'>";
        echo "<code class='d-block'>{$codigo_chamado}</code>";
        echo "</a>";
        echo "</td>";
        echo "<td class='fw-medium' style='white-space: nowrap;'>{$nome_colaborador}</td>";
        echo "<td class='text-muted' style='white-space: nowrap;'><small>{$email}</small></td>";
        echo "<td><span class='badge bg-light text-dark border'>{$setor}</span></td>";
        echo "<td style='white-space: normal; word-wrap: break-word; max-width: 280px;'>";
        echo "<div class='text-truncate' title='" . htmlspecialchars($descricao_problema) . "'>";
        echo htmlspecialchars($descricao_curta);
        echo "</div>";
        echo "</td>";
        echo "<td class='text-center'>";
        echo "<div class='small'>";
        echo "<div class='fw-medium'>{$data_formatada}</div>";
        echo "<div class='text-muted'>{$hora_formatada}</div>";
        echo "</div>";
        echo "</td>";
        echo "<td class='text-center'><span class='gravidade-{$gravidade}'>" . ucfirst($gravidade) . "</span></td>";
        echo "<td class='text-center'>";
        echo "<span class='sla-prazo {$status_sla}' ";
        echo ($status_sla == 'critico' ? 'style="animation: blink 1s infinite;"' : '');
        echo ">{$sla_real}</span>";
        if ($data_limite_sla) {
            echo "<br><small class='text-muted'>" . date('d/m H:i', strtotime($data_limite_sla)) . "</small>";
        }
        echo "</td>";
        echo "<td class='text-center'>";
        echo "<span class='status-badge {$config['status_badge']}'>";
        echo "<i class='{$config['status_icon']} me-1'></i>{$config['status_text']}";
        echo "</span>";
        echo "</td>";
        
        // Coluna solução (apenas para fechados)
        if ($this->status === 'fechado') {
            echo "<td class='text-center'>";
            if (isset($solucao) && !empty($solucao)) {
                $solucao_curta = strlen($solucao) > 50 ? substr($solucao, 0, 50) . '...' : $solucao;
                echo "<div class='text-truncate' title='" . htmlspecialchars($solucao) . "'>";
                echo "<small class='text-success'>" . htmlspecialchars($solucao_curta) . "</small>";
                echo "</div>";
            } else {
                echo "<small class='text-muted'>-</small>";
            }
            echo "</td>";
        }
        
        echo "<td class='text-center'>";
        echo "<div class='btn-group btn-group-sm' role='group'>";
        echo "<a href='view.php?id={$id}' class='btn btn-outline-info' title='Visualizar'><i class='fas fa-eye'></i></a>";
        echo "<a href='edit.php?id={$id}' class='btn btn-outline-warning' title='Editar'><i class='fas fa-edit'></i></a>";
        echo "<a href='delete.php?id={$id}' class='btn btn-outline-danger' title='Excluir' onclick='return confirm(\"Tem certeza que deseja excluir este chamado?\")'><i class='fas fa-trash'></i></a>";
        echo "</div>";
        echo "</td>";
        echo "</tr>";
    }

    /**
     * Renderiza o rodapé com informações
     */
    private function renderFooter($num) {
        $config = $this->config;
        echo "<div class='d-flex justify-content-center align-items-center mt-4 p-3 {$config['border_color']} {$config['badge_color']} bg-opacity-10 border rounded'>";
        echo "<small class='{$config['text_color']}'>";
        echo "<i class='{$config['icon']} me-2'></i>";
        echo "Total de <strong>{$num}</strong> chamado(s) {$config['footer_message']}";
        echo "</small>";
        echo "</div>";
    }

    /**
     * Renderiza estado vazio
     */
    private function renderEmptyState($pesquisa) {
        $config = $this->config;
        
        if ($pesquisa && !empty($pesquisa)) {
            echo "<div class='alert alert-warning d-flex align-items-center'>";
            echo "<i class='fas fa-exclamation-triangle me-3'></i>";
            echo "<div>";
            echo "<strong>Nenhum chamado encontrado</strong><br>";
            echo "<small>Tente ajustar os termos da pesquisa ou ";
            echo "<a href='{$config['action_url']}' class='alert-link'>ver todos os chamados</a>";
            echo "</small>";
            echo "</div>";
            echo "</div>";
        } else {
            $alert_class = $this->status === 'aberto' ? 'alert-success' : 'alert-info';
            echo "<div class='alert {$alert_class} d-flex align-items-center'>";
            echo "<i class='{$config['icon']} me-3'></i>";
            echo "<div>";
            echo "<strong>{$config['empty_message']}</strong><br>";
            echo "<small>{$config['empty_submessage']} ";
            echo "<a href='add.php' class='alert-link'>Criar novo chamado</a>";
            echo "</small>";
            echo "</div>";
            echo "</div>";
        }
    }

    /**
     * Determina a borda do card baseado na gravidade
     */
    private function getCardBorder($gravidade) {
        if ($this->status === 'em_andamento') {
            return 'border-warning';
        } elseif ($this->status === 'fechado') {
            return 'border-success';
        }
        
        // Para abertos, usar gravidade
        switch ($gravidade) {
            case 'alta':
                return 'border-danger';
            case 'media':
                return 'border-warning';
            case 'baixa':
                return 'border-success';
            default:
                return 'border-info';
        }
    }
}
