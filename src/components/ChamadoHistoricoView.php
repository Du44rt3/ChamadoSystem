<?php
/**
 * Componente responsável por gerenciar e exibir o histórico de atividades de um chamado
 * Parte da refatoração para resolver violação do princípio de responsabilidade única
 */

class ChamadoHistoricoView {
    private $chamado_id;
    private $atividades;
    
    public function __construct($chamado_id, $atividades) {
        $this->chamado_id = $chamado_id;
        $this->atividades = $atividades;
    }
    
    /**
     * Renderiza a seção completa de histórico
     */
    public function render() {
        echo '<div class="card mt-4">';
        $this->renderHeader();
        $this->renderBody();
        echo '</div>';
        
        // Renderizar modais
        $this->renderModals();
    }
    
    /**
     * Renderiza o cabeçalho da seção de histórico
     */
    private function renderHeader() {
        echo '<div class="card-header d-flex justify-content-between align-items-center">';
        echo '<h5 class="mb-0 text-white"><i class="fas fa-history"></i> Histórico de Atividades</h5>';
        echo '<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#novaAtividadeModal">';
        echo '<i class="fas fa-plus-circle me-1"></i> Nova Atividade';
        echo '</button>';
        echo '</div>';
    }
    
    /**
     * Renderiza o corpo da seção de histórico
     */
    private function renderBody() {
        echo '<div class="card-body">';
        
        if (empty($this->atividades)) {
            $this->renderEmptyState();
        } else {
            $this->renderTimeline();
        }
        
        echo '</div>';
    }
    
    /**
     * Renderiza estado vazio quando não há atividades
     */
    private function renderEmptyState() {
        echo '<div class="alert alert-info">';
        echo '<i class="fas fa-info-circle"></i> Nenhuma atividade registrada ainda.';
        echo '</div>';
    }
    
    /**
     * Renderiza a timeline de atividades
     */
    private function renderTimeline() {
        echo '<div class="timeline">';
        
        foreach ($this->atividades as $atividade) {
            $this->renderTimelineItem($atividade);
        }
        
        echo '</div>';
    }
    
    /**
     * Renderiza um item individual da timeline
     */
    private function renderTimelineItem($atividade) {
        echo '<div class="timeline-item">';
        echo '<div class="timeline-marker">';
        echo ChamadoHistorico::getIconeAtividade($atividade['atividade']);
        echo '</div>';
        echo '<div class="timeline-content">';
        
        // Header da atividade
        echo '<div class="timeline-header">';
        echo '<div>';
        echo '<strong class="text-dark">' . EmailTemplate::formatarDataHoraPadrao($atividade['data_atividade']) . '</strong>';
        echo '<span class="badge bg-secondary ms-2">' . SecurityHelper::sanitizeOutput($atividade['usuario']) . '</span>';
        echo '</div>';
        echo '<div class="timeline-actions">';
        echo '<button type="button" class="btn btn-sm btn-outline-primary" ';
        echo 'onclick="editarAtividade(' . SecurityHelper::sanitizeOutput($atividade['id']) . ', \'' . SecurityHelper::sanitizeForJS($atividade['atividade']) . '\', \'' . date('Y-m-d\TH:i', strtotime($atividade['data_atividade'])) . '\', \'' . SecurityHelper::sanitizeForJS($atividade['usuario']) . '\')" ';
        echo 'title="Editar atividade">';
        echo '<i class="fas fa-edit"></i>';
        echo '</button>';
        echo '<button type="button" class="btn btn-sm btn-outline-danger" ';
        echo 'onclick="deletarAtividade(' . SecurityHelper::sanitizeOutput($atividade['id']) . ', ' . SecurityHelper::sanitizeOutput($this->chamado_id) . ')" ';
        echo 'title="Deletar atividade">';
        echo '<i class="fas fa-trash"></i>';
        echo '</button>';
        echo '</div>';
        echo '</div>';
        
        // Corpo da atividade
        echo '<div class="timeline-body text-dark">';
        echo SecurityHelper::sanitizeOutput($atividade['atividade']);
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Renderiza os modais necessários para funcionalidade do histórico
     */
    private function renderModals() {
        $this->renderNewActivityModal();
        $this->renderEditActivityModal();
    }
    
    /**
     * Renderiza modal para nova atividade
     */
    private function renderNewActivityModal() {
        echo '<div class="modal fade" id="novaAtividadeModal" tabindex="-1" aria-labelledby="novaAtividadeModalLabel" aria-hidden="true">';
        echo '<div class="modal-dialog modal-dialog-centered">';
        echo '<div class="modal-content">';
        echo '<form action="add_atividade.php" method="POST">';
        echo '<div class="modal-header">';
        echo '<h5 class="modal-title" id="novaAtividadeModalLabel">';
        echo '<i class="fas fa-plus-circle text-success"></i> Nova Atividade';
        echo '</h5>';
        echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        echo '</div>';
        echo '<div class="modal-body">';
        
        echo '<input type="hidden" name="chamado_id" value="' . SecurityHelper::sanitizeOutput($this->chamado_id) . '">';
        echo SecurityHelper::getCSRFField();
        
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<div class="mb-3">';
        echo '<label for="usuario" class="form-label">';
        echo '<i class="fas fa-user"></i> Usuário:';
        echo '</label>';
        echo '<input type="text" class="form-control" id="usuario" name="usuario" required ';
        echo 'placeholder="Ex: João Silva">';
        echo '</div>';
        echo '</div>';
        echo '<div class="col-md-6">';
        echo '<div class="mb-3">';
        echo '<label for="data_atividade" class="form-label">';
        echo '<i class="fas fa-calendar-alt"></i> Data e Hora:';
        echo '</label>';
        echo '<input type="datetime-local" class="form-control" id="data_atividade" name="data_atividade">';
        echo '<div class="form-text">Deixe em branco para usar horário atual</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '<div class="mb-3">';
        echo '<label for="atividade" class="form-label">';
        echo '<i class="fas fa-clipboard-list"></i> Descrição da Atividade:';
        echo '</label>';
        echo '<textarea class="form-control" id="atividade" name="atividade" rows="3" required ';
        echo 'placeholder="Ex: Iniciado diagnóstico do problema. Verificando configurações do sistema..."></textarea>';
        echo '</div>';
        
        echo '</div>';
        echo '<div class="modal-footer">';
        echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">';
        echo '<i class="fas fa-times"></i> Cancelar';
        echo '</button>';
        echo '<button type="submit" class="btn btn-success">';
        echo '<i class="fas fa-save"></i> Adicionar Atividade';
        echo '</button>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Renderiza modal para editar atividade
     */
    private function renderEditActivityModal() {
        echo '<div class="modal fade" id="editarAtividadeModal" tabindex="-1" aria-labelledby="editarAtividadeModalLabel" aria-hidden="true">';
        echo '<div class="modal-dialog">';
        echo '<div class="modal-content">';
        echo '<form action="edit_atividade.php" method="POST">';
        echo '<div class="modal-header">';
        echo '<h5 class="modal-title" id="editarAtividadeModalLabel">Editar Atividade</h5>';
        echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        echo '</div>';
        echo '<div class="modal-body">';
        
        echo '<input type="hidden" name="atividade_id" id="edit_atividade_id">';
        echo '<input type="hidden" name="chamado_id" value="' . SecurityHelper::sanitizeOutput($this->chamado_id) . '">';
        echo SecurityHelper::getCSRFField();
        
        echo '<div class="mb-3">';
        echo '<label for="edit_usuario" class="form-label">Usuário:</label>';
        echo '<input type="text" class="form-control" id="edit_usuario" name="usuario" required>';
        echo '</div>';
        echo '<div class="mb-3">';
        echo '<label for="edit_data_atividade" class="form-label">Data e Hora:</label>';
        echo '<input type="datetime-local" class="form-control" id="edit_data_atividade" name="data_atividade" required>';
        echo '</div>';
        echo '<div class="mb-3">';
        echo '<label for="edit_atividade" class="form-label">Descrição da Atividade:</label>';
        echo '<textarea class="form-control" id="edit_atividade" name="atividade" rows="3" required></textarea>';
        echo '</div>';
        
        echo '</div>';
        echo '<div class="modal-footer">';
        echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>';
        echo '<button type="submit" class="btn btn-warning">Atualizar Atividade</button>';
        echo '</div>';
        echo '</form>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}
