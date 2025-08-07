<?php
/**
 * Template responsável pela estrutura da página de visualização de chamado
 * Parte da refatoração para resolver violação do princípio de responsabilidade única
 */

class ChamadoViewTemplate {
    private $chamado;
    
    public function __construct($chamado) {
        $this->chamado = $chamado;
    }
    
    /**
     * Renderiza o cabeçalho HTML da página
     */
    public function renderHead() {
        echo '<!DOCTYPE html>';
        echo '<html lang="pt-BR">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>Visualizar Chamado - ELUS Facilities</title>';
        echo '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">';
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">';
        echo '<link href="../css/style.css?v=' . time() . '" rel="stylesheet">';
        
        // Renderizar CSS inline para anexos (temporário até refatoração completa)
        ChamadoAnexosView::renderInlineCSS();
        
        echo '</head>';
        echo '<body>';
    }
    
    /**
     * Renderiza o cabeçalho da página (header + navbar)
     */
    public function renderHeader() {
        // Definir variáveis para o header
        global $page_title, $page_subtitle, $auth, $current_user;
        $page_title = "Visualizar Chamado";
        $page_subtitle = "Detalhes e histórico do chamado";
        
        require_once '../src/header.php';
    }
    
    /**
     * Renderiza o início do container principal
     */
    public function renderContainerStart() {
        echo '<div class="container-fluid mt-4">';
    }
    
    /**
     * Renderiza o final do container e scripts
     */
    public function renderFooter() {
        echo '</div>'; // Fechar container-fluid
        
        // Scripts
        echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>';
        
        // JavaScript específico da página
        $this->renderJavaScript();
        
        echo '</body>';
        echo '</html>';
    }
    
    /**
     * Renderiza o JavaScript específico da página
     */
    private function renderJavaScript() {
        echo '<script>';
        
        // Função para editar atividade
        echo 'function editarAtividade(id, atividade, dataHora, usuario) {';
        echo '    document.getElementById("edit_atividade_id").value = id;';
        echo '    document.getElementById("edit_usuario").value = usuario;';
        echo '    if (dataHora && dataHora.length >= 16) {';
        echo '        document.getElementById("edit_data_atividade").value = dataHora.substring(0,16);';
        echo '    } else {';
        echo '        var now = new Date();';
        echo '        var local = now.toISOString().slice(0,16);';
        echo '        document.getElementById("edit_data_atividade").value = local;';
        echo '    }';
        echo '    document.getElementById("edit_atividade").value = atividade;';
        echo '    var modal = new bootstrap.Modal(document.getElementById("editarAtividadeModal"));';
        echo '    modal.show();';
        echo '}';
        
        // Função para deletar atividade
        echo 'function deletarAtividade(id, chamadoId) {';
        echo '    if (confirm("Tem certeza que deseja excluir esta atividade?")) {';
        echo '        window.location.href = "delete_atividade.php?id=" + id + "&chamado_id=" + chamadoId;';
        echo '    }';
        echo '}';
        
        // Melhorar usabilidade do modal de nova atividade
        echo 'document.addEventListener("DOMContentLoaded", function() {';
        echo '    var novaAtividadeModal = document.getElementById("novaAtividadeModal");';
        echo '    if (novaAtividadeModal) {';
        echo '        novaAtividadeModal.addEventListener("show.bs.modal", function (event) {';
        echo '            setTimeout(function() {';
        echo '                document.getElementById("usuario").focus();';
        echo '            }, 200);';
        echo '            var usuarioField = document.getElementById("usuario");';
        echo '            if (usuarioField.value === "") {';
        echo '                usuarioField.value = "Técnico TI";';
        echo '            }';
        echo '        });';
        echo '        novaAtividadeModal.addEventListener("hidden.bs.modal", function (event) {';
        echo '            document.getElementById("usuario").value = "";';
        echo '            document.getElementById("data_atividade").value = "";';
        echo '            document.getElementById("atividade").value = "";';
        echo '        });';
        echo '    }';
        echo '});';
        
        // Função para abrir modal de imagem
        echo 'function abrirModalImagem(caminho, nome) {';
        echo '    const imgElement = document.getElementById("imagemModalImg");';
        echo '    const downloadLink = document.getElementById("imagemModalDownload");';
        echo '    const modalTitle = document.getElementById("imagemModalLabel");';
        echo '    imgElement.src = "../" + caminho;';
        echo '    imgElement.alt = nome;';
        echo '    const nomeExibicao = nome.length > 50 ? nome.substring(0, 47) + "..." : nome;';
        echo '    modalTitle.innerHTML = "<i class=\"fas fa-image\"></i> " + nomeExibicao;';
        echo '    downloadLink.href = "../" + caminho;';
        echo '    downloadLink.download = nome;';
        echo '    var modal = new bootstrap.Modal(document.getElementById("imagemModal"));';
        echo '    modal.show();';
        echo '}';
        
        // Função para confirmar exclusão de anexo
        echo 'function confirmarExclusaoAnexo(id, nome) {';
        echo '    document.getElementById("nomeAnexoExclusao").textContent = nome;';
        echo '    document.getElementById("confirmarExclusaoBtn").href = "excluir_anexo.php?id=" + id + "&chamado_id=' . SecurityHelper::sanitizeForJS($this->chamado->id) . '";';
        echo '    var modal = new bootstrap.Modal(document.getElementById("confirmarExclusaoModal"));';
        echo '    modal.show();';
        echo '}';
        
        // Função para alternar modo de visualização
        echo 'function toggleViewMode() {';
        echo '    const gallery = document.querySelector(".anexos-gallery");';
        echo '    const btn = document.getElementById("viewModeBtn");';
        echo '    const icon = btn.querySelector("i");';
        echo '    const textSpan = btn.querySelector("span");';
        echo '    if (gallery.classList.contains("compact-mode")) {';
        echo '        gallery.classList.remove("compact-mode");';
        echo '        icon.className = "fas fa-th me-1";';
        echo '        btn.title = "Modo compacto";';
        echo '        if (textSpan) textSpan.textContent = "Modo";';
        echo '        localStorage.setItem("anexos_view_mode", "normal");';
        echo '    } else {';
        echo '        gallery.classList.add("compact-mode");';
        echo '        icon.className = "fas fa-th-large me-1";';
        echo '        btn.title = "Modo normal";';
        echo '        if (textSpan) textSpan.textContent = "Compacto";';
        echo '        localStorage.setItem("anexos_view_mode", "compact");';
        echo '    }';
        echo '}';
        
        // Restaurar modo de visualização salvo
        echo 'document.addEventListener("DOMContentLoaded", function() {';
        echo '    const gallery = document.querySelector(".anexos-gallery");';
        echo '    const images = document.querySelectorAll(".anexo-image");';
        echo '    const savedMode = localStorage.getItem("anexos_view_mode");';
        echo '    if (savedMode === "compact") {';
        echo '        const btn = document.getElementById("viewModeBtn");';
        echo '        if (gallery && btn) {';
        echo '            gallery.classList.add("compact-mode");';
        echo '            btn.querySelector("i").className = "fas fa-th-large me-1";';
        echo '            const textSpan = btn.querySelector("span");';
        echo '            if (textSpan) textSpan.textContent = "Compacto";';
        echo '            btn.title = "Modo normal";';
        echo '        }';
        echo '    }';
        echo '});';
        
        echo '</script>';
    }
}
