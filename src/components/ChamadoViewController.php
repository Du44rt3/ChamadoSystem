<?php
/**
 * Controlador responsável por validar dados e gerenciar o fluxo da página de visualização
 * Parte da refatoração para resolver violação do princípio de responsabilidade única
 */

class ChamadoViewController {
    private $db;
    private $chamado;
    private $historico;
    private $anexo;
    
    public function __construct() {
        // Incluir classes necessárias
        include_once __DIR__ . '/../SecurityHelper.php';
        include_once __DIR__ . '/../DB.php';
        include_once __DIR__ . '/../Chamado.php';
        include_once __DIR__ . '/../ChamadoHistorico.php';
        include_once __DIR__ . '/../EmailTemplate.php';
        include_once __DIR__ . '/../ChamadoAnexo.php';
        
        // Inicializar conexões
        $database = new DB();
        $this->db = $database->getConnection();
        $this->chamado = new Chamado($this->db);
        $this->historico = new ChamadoHistorico($this->db);
        $this->anexo = new ChamadoAnexo($this->db);
    }
    
    /**
     * Valida o ID do chamado e carrega os dados
     */
    public function validateAndLoadChamado() {
        try {
            $id_param = SecurityHelper::getGetValue('id');
            if (empty($id_param)) {
                throw new InvalidArgumentException('ID não fornecido');
            }
            $id = SecurityHelper::validateId($id_param);
        } catch (InvalidArgumentException $e) {
            header('Location: index.php?error=id_invalido');
            exit;
        }
        
        $this->chamado->id = $id;
        if (!$this->chamado->readOne()) {
            header('Location: index.php?error=chamado_nao_encontrado');
            exit;
        }
        
        return $this->chamado;
    }
    
    /**
     * Busca o histórico do chamado
     */
    public function getHistorico($chamado_id) {
        return $this->historico->buscarHistoricoCompleto($chamado_id);
    }
    
    /**
     * Busca os anexos do chamado
     */
    public function getAnexos($chamado_id) {
        return $this->anexo->buscarPorChamado($chamado_id);
    }
    
    /**
     * Renderiza mensagens de feedback baseadas nos parâmetros GET
     */
    public function renderFeedbackMessages() {
        if (isset($_GET['success'])) {
            $this->renderSuccessMessage($_GET['success']);
        } elseif (isset($_GET['error'])) {
            $this->renderErrorMessage($_GET['error']);
        }
    }
    
    /**
     * Renderiza mensagem de sucesso
     */
    private function renderSuccessMessage($successCode) {
        $mensagem = '';
        switch($successCode) {
            case 1:
                $mensagem = 'Atividade adicionada com sucesso!';
                break;
            case 2:
                $mensagem = 'Atividade editada com sucesso!';
                break;
            case 3:
                $mensagem = 'Atividade excluída com sucesso!';
                break;
            case 'anexo_excluido':
                $mensagem = 'Anexo excluído com sucesso!';
                break;
            case 'anexos_adicionados':
                $mensagem = 'Anexos adicionados com sucesso!';
                break;
            case 'anexos_parcial':
                $mensagem = 'Alguns anexos foram adicionados com sucesso, mas outros falharam.';
                break;
            default:
                $mensagem = 'Operação realizada com sucesso!';
        }
        
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
        echo '<i class="fas fa-check-circle"></i> ' . $mensagem;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
    
    /**
     * Renderiza mensagem de erro
     */
    private function renderErrorMessage($errorCode) {
        $erro = '';
        switch($errorCode) {
            case 1:
                $erro = 'Erro ao adicionar atividade.';
                break;
            case 2:
                $erro = 'Preencha todos os campos obrigatórios.';
                break;
            case 3:
                $erro = 'Erro ao editar atividade.';
                break;
            case 4:
                $erro = 'Erro ao excluir atividade.';
                break;
            case 'anexo_nao_encontrado':
                $erro = 'Anexo não encontrado.';
                break;
            case 'anexo_invalido':
                $erro = 'Anexo inválido para este chamado.';
                break;
            case 'erro_excluir_anexo':
                $erro = 'Erro ao excluir anexo.';
                break;
            case 'nenhum_anexo_enviado':
                $erro = 'Nenhum anexo foi selecionado para upload.';
                break;
            default:
                $erro = 'Erro desconhecido.';
        }
        
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
        echo '<i class="fas fa-exclamation-circle"></i> ' . $erro;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}
