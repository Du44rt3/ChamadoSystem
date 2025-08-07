<?php
/**
 * Template unificado para páginas de listagem de chamados
 * Elimina duplicação entre abertos.php, em_andamento.php e fechados.php
 */

class ChamadosPageTemplate {
    private $status;
    private $config;

    public function __construct($status) {
        $this->status = $status;
        $this->config = $this->getStatusConfig($status);
    }

    /**
     * Configurações específicas por status
     */
    private function getStatusConfig($status) {
        $configs = [
            'aberto' => [
                'title' => 'Chamados Abertos - ELUS Facilities',
                'page_title' => 'Chamados Abertos',
                'page_subtitle' => 'Chamados aguardando atendimento',
                'action_url' => 'abertos.php'
            ],
            'em_andamento' => [
                'title' => 'Chamados Em Andamento - ELUS Facilities',
                'page_title' => 'Chamados em Andamento',
                'page_subtitle' => 'Chamados em atendimento',
                'action_url' => 'em_andamento.php'
            ],
            'fechado' => [
                'title' => 'Chamados Fechados - ELUS Facilities',
                'page_title' => 'Chamados Fechados',
                'page_subtitle' => 'Chamados resolvidos e finalizados',
                'action_url' => 'fechados.php'
            ]
        ];

        return $configs[$status] ?? $configs['aberto'];
    }

    /**
     * Renderiza o início da página (head e início do body)
     */
    public function renderHeader() {
        $config = $this->config;
        
        echo '<!DOCTYPE html>';
        echo '<html lang="pt-BR">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo "<title>{$config['title']}</title>";
        echo '<link rel="icon" type="image/svg+xml" href="favicon.svg">';
        echo '<link rel="icon" type="image/png" href="images/favicon.png">';
        echo '<link rel="apple-touch-icon" href="images/favicon.png">';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">';
        echo '<link href="../css/style.css?v=' . time() . '" rel="stylesheet">';
        echo '<link href="../assets/css/chamados-list.css?v=' . time() . '" rel="stylesheet">';
        echo '</head>';
        echo '<body>';
        
        // Definir variáveis para o header
        global $page_title, $page_subtitle, $auth, $current_user;
        $page_title = $config['page_title'];
        $page_subtitle = $config['page_subtitle'];
        
        require_once '../src/header.php';
        
        echo '<div class="container-fluid mt-4">';
    }

    /**
     * Renderiza a barra de pesquisa
     */
    public function renderSearchBar($pesquisa = '') {
        $config = $this->config;
        $pesquisa_value = htmlspecialchars($pesquisa);
        
        echo '<div class="search-bar">';
        echo '<div class="row">';
        echo '<div class="col-md-10">';
        echo "<form method='GET' action='{$config['action_url']}' class='d-flex'>";
        echo '<div class="input-group">';
        echo '<span class="input-group-text bg-white border-end-0">';
        echo '<i class="fas fa-search text-muted"></i>';
        echo '</span>';
        echo '<input type="text" name="pesquisa" class="form-control border-start-0" ';
        echo 'placeholder="Pesquisar por código, colaborador, setor ou descrição..." ';
        echo "value=\"{$pesquisa_value}\">";
        echo '<button class="btn btn-primary" type="submit">';
        echo '<i class="fas fa-search me-1"></i>Pesquisar';
        echo '</button>';
        
        if (!empty($pesquisa)) {
            echo "<a href='{$config['action_url']}' class='btn btn-secondary'>";
            echo '<i class="fas fa-times me-1"></i>Limpar';
            echo '</a>';
        }
        
        echo '</div>';
        echo '</form>';
        echo '</div>';
        echo '<div class="col-md-2 text-end">';
        echo '<a href="add.php" class="btn btn-elus w-100">';
        echo '<i class="fas fa-plus me-1"></i>Novo Chamado';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }

    /**
     * Renderiza o final da página
     */
    public function renderFooter() {
        echo '</div>'; // Fechar container-fluid
        
        // Scripts
        echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>';
        echo '<script src="../assets/js/chamados-list.js?v=' . time() . '"></script>';
        
        echo '</body>';
        echo '</html>';
    }
}
