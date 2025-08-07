<?php
/**
 * Componente responsável por gerenciar e exibir anexos de um chamado
 * Parte da refatoração para resolver violação do princípio de responsabilidade única
 */

class ChamadoAnexosView {
    private $chamado_id;
    private $anexos;
    
    public function __construct($chamado_id, $anexos) {
        $this->chamado_id = $chamado_id;
        $this->anexos = $anexos;
    }
    
    /**
     * Renderiza a seção completa de anexos
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
     * Renderiza o cabeçalho da seção de anexos
     */
    private function renderHeader() {
        echo '<div class="card-header d-flex justify-content-between align-items-center">';
        echo '<h5 class="mb-0 text-white">';
        echo '<i class="fas fa-paperclip"></i> Anexos de Imagem ';
        
        if (!empty($this->anexos)) {
            echo '<span class="anexos-count">' . count($this->anexos) . '</span>';
        }
        
        echo '</h5>';
        echo '<div class="d-flex gap-2">';
        
        if (!empty($this->anexos)) {
            echo '<button type="button" class="btn btn-outline-light" onclick="toggleViewMode()" id="viewModeBtn" title="Alternar visualização">';
            echo '<i class="fas fa-th me-1"></i> <span class="d-none d-sm-inline">Modo</span>';
            echo '</button>';
        }
        
        echo '<a href="adicionar_anexos.php?chamado_id=' . SecurityHelper::sanitizeOutput($this->chamado_id) . '" class="btn btn-success">';
        echo '<i class="fas fa-plus-circle me-1"></i> Adicionar Anexos';
        echo '</a>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Renderiza o corpo da seção de anexos
     */
    private function renderBody() {
        echo '<div class="card-body">';
        
        if (!empty($this->anexos)) {
            $this->renderAnexosGallery();
        } else {
            $this->renderEmptyState();
        }
        
        echo '</div>';
    }
    
    /**
     * Renderiza a galeria de anexos
     */
    private function renderAnexosGallery() {
        echo '<div class="anexos-gallery">';
        echo '<div class="row">';
        
        foreach ($this->anexos as $anexo_item) {
            $this->renderAnexoCard($anexo_item);
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Renderiza um card individual de anexo
     */
    private function renderAnexoCard($anexo_item) {
        echo '<div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">';
        echo '<div class="anexo-card">';
        
        // Imagem
        echo '<img src="../' . SecurityHelper::sanitizeOutput($anexo_item['caminho_arquivo']) . '" ';
        echo 'class="anexo-image" ';
        echo 'onclick="abrirModalImagem(\'' . SecurityHelper::sanitizeForJS($anexo_item['caminho_arquivo']) . '\', \'' . SecurityHelper::sanitizeForJS($anexo_item['nome_original']) . '\')" ';
        echo 'alt="' . SecurityHelper::sanitizeOutput($anexo_item['nome_original']) . '">';
        
        // Informações
        echo '<div class="anexo-info">';
        echo '<h6 class="anexo-title" title="' . SecurityHelper::sanitizeOutput($anexo_item['nome_original']) . '">';
        echo SecurityHelper::sanitizeOutput($anexo_item['nome_original']);
        echo '</h6>';
        echo '<div class="anexo-meta">';
        echo '<i class="fas fa-weight-hanging"></i> ';
        echo '<span>' . ChamadoAnexo::formatarTamanho($anexo_item['tamanho_arquivo']) . '</span>';
        echo '</div>';
        
        // Ações
        echo '<div class="anexo-actions">';
        echo '<a href="../' . SecurityHelper::sanitizeOutput($anexo_item['caminho_arquivo']) . '" ';
        echo 'class="btn btn-primary btn-sm" target="_blank" title="Ver imagem">';
        echo '<i class="fas fa-eye"></i>';
        echo '</a>';
        echo '<a href="download_anexo.php?id=' . SecurityHelper::sanitizeOutput($anexo_item['id']) . '" ';
        echo 'class="btn btn-success btn-sm" title="Download">';
        echo '<i class="fas fa-download"></i>';
        echo '</a>';
        echo '<button type="button" class="btn btn-danger btn-sm" ';
        echo 'onclick="confirmarExclusaoAnexo(' . SecurityHelper::sanitizeOutput($anexo_item['id']) . ', \'' . SecurityHelper::sanitizeForJS($anexo_item['nome_original']) . '\')" ';
        echo 'title="Excluir anexo">';
        echo '<i class="fas fa-trash"></i>';
        echo '</button>';
        echo '</div>';
        
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Renderiza estado vazio quando não há anexos
     */
    private function renderEmptyState() {
        echo '<div class="text-center py-4">';
        echo '<p class="text-muted mb-3">';
        echo '<i class="fas fa-images opacity-50 fs-4"></i><br>';
        echo '<span class="mt-2 d-inline-block">Nenhuma imagem anexada</span>';
        echo '</p>';
        echo '<a href="adicionar_anexos.php?chamado_id=' . SecurityHelper::sanitizeOutput($this->chamado_id) . '" class="btn btn-success">';
        echo '<i class="fas fa-plus-circle me-1"></i> Adicionar Primeira Imagem';
        echo '</a>';
        echo '</div>';
    }
    
    /**
     * Renderiza os modais necessários para funcionalidade dos anexos
     */
    private function renderModals() {
        $this->renderImageModal();
        $this->renderDeleteConfirmModal();
    }
    
    /**
     * Renderiza modal para visualizar imagem em tamanho completo
     */
    private function renderImageModal() {
        echo '<div class="modal fade" id="imagemModal" tabindex="-1" aria-labelledby="imagemModalLabel" aria-hidden="true">';
        echo '<div class="modal-dialog modal-xl modal-dialog-centered">';
        echo '<div class="modal-content">';
        echo '<div class="modal-header">';
        echo '<h5 class="modal-title" id="imagemModalLabel">';
        echo '<i class="fas fa-image"></i> Visualizar Imagem';
        echo '</h5>';
        echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        echo '</div>';
        echo '<div class="modal-body text-center p-0">';
        echo '<img id="imagemModalImg" src="" alt="" class="img-fluid">';
        echo '</div>';
        echo '<div class="modal-footer justify-content-center">';
        echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">';
        echo '<i class="fas fa-times"></i> Fechar';
        echo '</button>';
        echo '<a id="imagemModalDownload" href="#" target="_blank" class="btn btn-primary">';
        echo '<i class="fas fa-download"></i> Download';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Renderiza modal para confirmar exclusão de anexo
     */
    private function renderDeleteConfirmModal() {
        echo '<div class="modal fade" id="confirmarExclusaoModal" tabindex="-1" aria-labelledby="confirmarExclusaoModalLabel" aria-hidden="true">';
        echo '<div class="modal-dialog modal-dialog-centered">';
        echo '<div class="modal-content">';
        echo '<div class="modal-header">';
        echo '<h5 class="modal-title" id="confirmarExclusaoModalLabel">';
        echo '<i class="fas fa-exclamation-triangle text-warning"></i> Confirmar Exclusão';
        echo '</h5>';
        echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
        echo '</div>';
        echo '<div class="modal-body">';
        echo '<p>Tem certeza que deseja excluir o anexo:</p>';
        echo '<p><strong id="nomeAnexoExclusao"></strong></p>';
        echo '<p class="text-muted">Esta ação não pode ser desfeita.</p>';
        echo '</div>';
        echo '<div class="modal-footer">';
        echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>';
        echo '<a href="#" id="confirmarExclusaoBtn" class="btn btn-danger">';
        echo '<i class="fas fa-trash"></i> Excluir';
        echo '</a>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
    
    /**
     * Renderiza o CSS específico para anexos (inline devido ao problema atual)
     */
    public static function renderInlineCSS() {
        echo '<style>';
        echo '.anexo-image {';
        echo '    width: 100% !important;';
        echo '    height: 50px !important;';
        echo '    object-fit: cover !important;';
        echo '    border-radius: 4px 4px 0 0 !important;';
        echo '}';
        
        echo '.anexo-info {';
        echo '    padding: 0.25rem !important;';
        echo '}';
        
        echo '.anexo-title {';
        echo '    font-size: 0.7rem !important;';
        echo '    margin-bottom: 0.15rem !important;';
        echo '    white-space: nowrap !important;';
        echo '    overflow: hidden !important;';
        echo '    text-overflow: ellipsis !important;';
        echo '    line-height: 1.2 !important;';
        echo '}';
        
        echo '.anexo-meta {';
        echo '    font-size: 0.6rem !important;';
        echo '    margin-bottom: 0.2rem !important;';
        echo '    color: #666 !important;';
        echo '}';
        
        echo '.anexo-actions {';
        echo '    justify-content: center !important;';
        echo '    gap: 0.1rem !important;';
        echo '    margin-top: 0.2rem !important;';
        echo '}';
        
        echo '.anexo-actions .btn {';
        echo '    padding: 0.05rem 0.2rem !important;';
        echo '    font-size: 0.55rem !important;';
        echo '    min-width: 16px !important;';
        echo '    height: 16px !important;';
        echo '    border-radius: 1px !important;';
        echo '    line-height: 1 !important;';
        echo '}';
        
        echo '.anexos-gallery .col-xl-2,';
        echo '.anexos-gallery .col-lg-3,';
        echo '.anexos-gallery .col-md-4,';
        echo '.anexos-gallery .col-sm-6,';
        echo '.anexos-gallery .col-6 {';
        echo '    padding: 0.25rem !important;';
        echo '    margin-bottom: 0.3rem !important;';
        echo '}';
        
        echo '.anexos-gallery .row {';
        echo '    margin: -0.25rem !important;';
        echo '}';
        
        echo '.anexo-card {';
        echo '    box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;';
        echo '    border: 1px solid #e0e0e0 !important;';
        echo '    background: #fff !important;';
        echo '}';
        
        echo '.anexos-gallery.compact-mode .anexo-image {';
        echo '    height: 35px !important;';
        echo '}';
        
        echo '.anexos-gallery.compact-mode .anexo-info {';
        echo '    padding: 0.15rem !important;';
        echo '}';
        
        echo '.anexos-gallery.compact-mode .anexo-title {';
        echo '    font-size: 0.6rem !important;';
        echo '}';
        
        echo '.anexos-gallery.compact-mode .anexo-meta {';
        echo '    display: none !important;';
        echo '}';
        
        echo '.anexos-gallery.compact-mode .anexo-actions .btn {';
        echo '    min-width: 14px !important;';
        echo '    height: 14px !important;';
        echo '    font-size: 0.5rem !important;';
        echo '}';
        
        echo '@media (max-width: 768px) {';
        echo '    .anexo-image {';
        echo '        height: 45px !important;';
        echo '    }';
        echo '    .anexos-gallery.compact-mode .anexo-image {';
        echo '        height: 30px !important;';
        echo '    }';
        echo '    .anexo-actions .btn {';
        echo '        min-width: 14px !important;';
        echo '        height: 14px !important;';
        echo '        font-size: 0.5rem !important;';
        echo '    }';
        echo '}';
        
        echo '.card-header .btn {';
        echo '    padding: 0.5rem 1rem !important;';
        echo '    font-size: 0.9rem !important;';
        echo '    line-height: 1.5 !important;';
        echo '    min-width: auto !important;';
        echo '    height: auto !important;';
        echo '    border-radius: 0.375rem !important;';
        echo '}';
        
        echo '.card-header .d-flex.gap-2 {';
        echo '    gap: 0.75rem !important;';
        echo '}';
        
        echo '.btn-success {';
        echo '    background-color: #28a745 !important;';
        echo '    border-color: #28a745 !important;';
        echo '    font-weight: 600 !important;';
        echo '    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2) !important;';
        echo '    transition: all 0.2s ease !important;';
        echo '}';
        
        echo '.btn-success:hover {';
        echo '    background-color: #218838 !important;';
        echo '    border-color: #218838 !important;';
        echo '    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3) !important;';
        echo '    transform: translateY(-1px) !important;';
        echo '}';
        
        echo '.btn-outline-light {';
        echo '    border-width: 1px !important;';
        echo '    font-weight: 500 !important;';
        echo '    background-color: rgba(255, 255, 255, 0.1) !important;';
        echo '}';
        
        echo '.btn-outline-light:hover {';
        echo '    background-color: rgba(255, 255, 255, 0.2) !important;';
        echo '    border-color: rgba(255, 255, 255, 0.5) !important;';
        echo '}';
        
        echo '</style>';
    }
}
