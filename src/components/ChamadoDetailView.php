<?php
/**
 * Componente responsável por exibir os detalhes de um chamado
 * Parte da refatoração para resolver violação do princípio de responsabilidade única
 */

class ChamadoDetailView {
    private $chamado;
    
    public function __construct($chamado) {
        $this->chamado = $chamado;
    }
    
    /**
     * Renderiza o card principal com os detalhes do chamado
     */
    public function render() {
        echo '<div class="card">';
        $this->renderHeader();
        $this->renderBody();
        $this->renderFooter();
        echo '</div>';
    }
    
    /**
     * Renderiza o cabeçalho do card
     */
    private function renderHeader() {
        echo '<div class="card-header">';
        echo '<h2>Chamado #' . SecurityHelper::sanitizeOutput($this->chamado->id) . '</h2>';
        echo '<h5><code>' . SecurityHelper::sanitizeOutput($this->chamado->codigo_chamado) . '</code></h5>';
        echo '</div>';
    }
    
    /**
     * Renderiza o corpo do card com informações detalhadas
     */
    private function renderBody() {
        echo '<div class="card-body">';
        echo '<div class="row">';
        
        // Coluna esquerda
        echo '<div class="col-md-6">';
        $this->renderLeftColumn();
        echo '</div>';
        
        // Coluna direita
        echo '<div class="col-md-6">';
        $this->renderRightColumn();
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Renderiza informações da coluna esquerda
     */
    private function renderLeftColumn() {
        echo '<p><strong>Código:</strong> <code>' . SecurityHelper::sanitizeOutput($this->chamado->codigo_chamado) . '</code></p>';
        echo '<p><strong>Setor:</strong> ' . SecurityHelper::sanitizeOutput($this->chamado->setor) . '</p>';
        echo '<p><strong>Usuário:</strong> ' . SecurityHelper::sanitizeOutput($this->chamado->nome_colaborador) . '</p>';
        
        if (!empty($this->chamado->email)) {
            echo '<p><strong>Email:</strong> ' . SecurityHelper::sanitizeOutput($this->chamado->email) . '</p>';
        }
        
        echo '<p><strong>Nome do Projeto:</strong> ' . SecurityHelper::sanitizeOutput($this->chamado->nome_projeto) . '</p>';
        echo '<p><strong>Descrição do Problema:</strong></p>';
        echo '<p>' . nl2br(SecurityHelper::sanitizeOutput($this->chamado->descricao_problema)) . '</p>';
        echo '<p><strong>Prioridade:</strong> <span class="gravidade-' . SecurityHelper::sanitizeOutput($this->chamado->gravidade) . '">' . ucfirst(SecurityHelper::sanitizeOutput($this->chamado->gravidade)) . '</span></p>';
    }
    
    /**
     * Renderiza informações da coluna direita
     */
    private function renderRightColumn() {
        echo '<p><strong>Data de Abertura:</strong> ' . EmailTemplate::formatarDataHoraPadrao($this->chamado->data_abertura) . '</p>';
        echo '<p><strong>Status do Chamado:</strong> ' . ucfirst(str_replace('_', ' ', SecurityHelper::sanitizeOutput($this->chamado->status))) . '</p>';
        
        // Exibir data de fechamento se o chamado estiver fechado
        if ($this->chamado->status === 'fechado' && !empty($this->chamado->data_fechamento)) {
            echo '<p><strong>Data de Fechamento:</strong> ' . EmailTemplate::formatarDataHoraPadrao($this->chamado->data_fechamento) . '</p>';
        }
        
        if ($this->chamado->solucao) {
            echo '<p><strong>Solução:</strong></p>';
            echo '<p>' . nl2br(SecurityHelper::sanitizeOutput($this->chamado->solucao)) . '</p>';
        }
    }
    
    /**
     * Renderiza o rodapé com ações
     */
    private function renderFooter() {
        echo '<div class="card-footer">';
        echo '<a href="edit.php?id=' . SecurityHelper::sanitizeOutput($this->chamado->id) . '" class="btn btn-warning">Editar</a>';
        echo '<a href="delete.php?id=' . SecurityHelper::sanitizeOutput($this->chamado->id) . '" class="btn btn-danger" onclick="return confirm(\'Tem certeza que deseja excluir este chamado?\')">Excluir</a>';
        
        if (!empty($this->chamado->email)) {
            echo '<a href="email_template.php?id=' . SecurityHelper::sanitizeOutput($this->chamado->id) . '&tipo=auto" class="btn btn-info">';
            echo '<i class="fas fa-envelope"></i> Template de Email';
            echo '</a>';
        }
        
        echo '<a href="index.php" class="btn btn-secondary">Voltar</a>';
        echo '</div>';
    }
}
