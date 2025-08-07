<?php
class ChamadoHistorico {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    // Adicionar uma nova atividade ao histórico
    public function adicionarAtividade($chamado_id, $atividade, $usuario = 'Sistema', $data_atividade = null) {
        // Verificar se o chamado existe antes de tentar inserir
        if (!$this->chamadoExiste($chamado_id)) {
            error_log("Tentativa de inserir histórico para chamado inexistente: ID $chamado_id");
            return false;
        }
        
        if ($data_atividade === null) {
            $sql = "INSERT INTO chamado_historico (chamado_id, atividade, usuario) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$chamado_id, $atividade, $usuario]);
        } else {
            $sql = "INSERT INTO chamado_historico (chamado_id, atividade, usuario, data_atividade) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$chamado_id, $atividade, $usuario, $data_atividade]);
        }
    }
    
    // Verificar se um chamado existe
    private function chamadoExiste($chamado_id) {
        $sql = "SELECT COUNT(*) FROM chamados WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$chamado_id]);
        return $stmt->fetchColumn() > 0;
    }

    // Verificar se já existe atividade igual para evitar duplicidade
    public function existeAtividade($chamado_id, $atividade, $usuario, $data_atividade) {
        $sql = "SELECT COUNT(*) FROM chamado_historico WHERE chamado_id = ? AND atividade = ? AND usuario = ? AND data_atividade = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$chamado_id, $atividade, $usuario, $data_atividade]);
        return $stmt->fetchColumn() > 0;
    }
    
    // Buscar todo o histórico de um chamado
    public function buscarHistorico($chamado_id) {
        $sql = "SELECT * FROM chamado_historico WHERE chamado_id = ? ORDER BY data_atividade DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$chamado_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Buscar histórico ordenado por data (mais antigo primeiro) para templates
    public function buscarHistoricoOrdenado($chamado_id) {
        $sql = "SELECT * FROM chamado_historico WHERE chamado_id = ? ORDER BY data_atividade ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$chamado_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Buscar últimas atividades (para dashboard)
    public function buscarUltimasAtividades($limite = 10) {
        $sql = "SELECT h.*, c.codigo_chamado as chamado_titulo 
                FROM chamado_historico h 
                LEFT JOIN chamados c ON h.chamado_id = c.id 
                ORDER BY h.data_atividade DESC 
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limite]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Atualizar uma atividade existente
    public function atualizarAtividade($id, $atividade, $data_atividade, $usuario) {
        $sql = "UPDATE chamado_historico SET atividade = ?, data_atividade = ?, usuario = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$atividade, $data_atividade, $usuario, $id]);
    }
    
    // Buscar uma atividade específica por ID
    public function buscarAtividadePorId($id) {
        $sql = "SELECT * FROM chamado_historico WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Deletar uma atividade
    public function deletarAtividade($id) {
        $sql = "DELETE FROM chamado_historico WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    // Verificar se o usuário pode editar (opcional - para controle de permissões)
    public function podeEditar($atividade_id, $usuario_logado = null) {
        $atividade = $this->buscarAtividadePorId($atividade_id);
        if (!$atividade) return false;
        
        // Se não há controle de usuário, permite edição
        if (!$usuario_logado) return true;
        
        // Permite edição se for o mesmo usuário ou se for admin/sistema
        return ($atividade['usuario'] === $usuario_logado || 
                $atividade['usuario'] === 'Sistema' || 
                $usuario_logado === 'Admin');
    }
    
    // Formatar data para exibição brasileira
    public static function formatarData($data) {
        $datetime = new DateTime($data);
        return $datetime->format('d/m/Y \à\s H:i');
    }
    
    // Gerar ícone baseado no tipo de atividade
    public static function getIconeAtividade($atividade) {
        $atividade_lower = strtolower($atividade);
        
        if (strpos($atividade_lower, 'abertura') !== false) {
            return '<i class="fas fa-plus-circle text-success"></i>';
        } elseif (strpos($atividade_lower, 'iniciado') !== false || strpos($atividade_lower, 'andamento') !== false) {
            return '<i class="fas fa-play-circle text-warning"></i>';
        } elseif (strpos($atividade_lower, 'finalizado') !== false || strpos($atividade_lower, 'fechado') !== false) {
            return '<i class="fas fa-check-circle text-success"></i>';
        } elseif (strpos($atividade_lower, 'reaberto') !== false) {
            return '<i class="fas fa-redo text-info"></i>';
        } elseif (strpos($atividade_lower, 'comentário') !== false || strpos($atividade_lower, 'observação') !== false) {
            return '<i class="fas fa-comment text-primary"></i>';
        } elseif (strpos($atividade_lower, 'editado') !== false || strpos($atividade_lower, 'alterado') !== false) {
            return '<i class="fas fa-edit text-info"></i>';
        } else {
            return '<i class="fas fa-info-circle text-secondary"></i>';
        }
    }
    
    // Buscar histórico sem duplicações direto do banco
    public function buscarHistoricoSemDuplicacoes($chamado_id) {
        $sql = "
            SELECT DISTINCT 
                h1.id,
                h1.chamado_id,
                h1.atividade,
                h1.data_atividade,
                h1.usuario,
                c.data_abertura
            FROM chamado_historico h1
            JOIN chamados c ON h1.chamado_id = c.id
            WHERE h1.chamado_id = ?
            AND NOT EXISTS (
                SELECT 1 FROM chamado_historico h2 
                WHERE h2.chamado_id = h1.chamado_id 
                AND h2.atividade = h1.atividade 
                AND h2.id < h1.id
                AND ABS(TIMESTAMPDIFF(SECOND, h1.data_atividade, h2.data_atividade)) <= 30
            )
            ORDER BY 
                CASE 
                    WHEN h1.data_atividade < c.data_abertura THEN c.data_abertura
                    ELSE h1.data_atividade
                END ASC,
                h1.id ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$chamado_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Buscar histórico completo com atividade de abertura incluída
    public function buscarHistoricoCompleto($chamado_id) {
        // Buscar atividades sem duplicações (usar o método existente que já funciona bem)
        return $this->buscarHistoricoSemDuplicacoes($chamado_id);
    }
}
?>
