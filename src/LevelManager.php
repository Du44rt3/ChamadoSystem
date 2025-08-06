<?php
/**
 * Gerenciador de Níveis de Acesso
 * ELUS Facilities - Sistema de Chamados
 */

class LevelManager {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Obter todos os níveis ativos
     */
    public function getAllLevels($includeSystem = true) {
        $query = "SELECT * FROM niveis_acesso WHERE ativo = 1";
        if (!$includeSystem) {
            $query .= " AND nivel_sistema = 'customizado'";
        }
        $query .= " ORDER BY nivel_sistema DESC, nome ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter um nível específico por nome
     */
    public function getLevel($nome) {
        $query = "SELECT * FROM niveis_acesso WHERE nome = :nome AND ativo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nome', $nome);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Criar novo nível customizado
     */
    public function createLevel($nome, $descricao, $permissoes, $cor = '#6c757d', $created_by = null) {
        // Verificar se já existe
        if ($this->getLevel($nome)) {
            throw new Exception("Nível '$nome' já existe!");
        }
        
        // Não permitir nomes reservados
        $reserved = ['desenvolvedor', 'admin', 'usuario'];
        if (in_array(strtolower($nome), $reserved)) {
            throw new Exception("Nome '$nome' é reservado do sistema!");
        }
        
        $query = "INSERT INTO niveis_acesso (nome, descricao, nivel_sistema, permissoes, cor, created_by) 
                  VALUES (:nome, :descricao, 'customizado', :permissoes, :cor, :created_by)";
        
        $stmt = $this->db->prepare($query);
        $permissoes_json = json_encode($permissoes);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':permissoes', $permissoes_json);
        $stmt->bindParam(':cor', $cor);
        $stmt->bindParam(':created_by', $created_by);
        
        return $stmt->execute();
    }
    
    /**
     * Atualizar nível customizado (não pode alterar níveis do sistema)
     */
    public function updateLevel($id, $nome, $descricao, $permissoes, $cor = '#6c757d') {
        // Verificar se é nível do sistema
        $query = "SELECT nivel_sistema FROM niveis_acesso WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $level = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$level) {
            throw new Exception("Nível não encontrado!");
        }
        
        if ($level['nivel_sistema'] === 'sistema') {
            throw new Exception("Não é possível alterar níveis do sistema!");
        }
        
        $query = "UPDATE niveis_acesso 
                  SET nome = :nome, descricao = :descricao, permissoes = :permissoes, cor = :cor 
                  WHERE id = :id AND nivel_sistema = 'customizado'";
        
        $stmt = $this->db->prepare($query);
        $permissoes_json = json_encode($permissoes);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':descricao', $descricao);
        $stmt->bindParam(':permissoes', $permissoes_json);
        $stmt->bindParam(':cor', $cor);
        
        return $stmt->execute();
    }
    
    /**
     * Desativar nível (não excluir para manter histórico)
     */
    public function deactivateLevel($id) {
        // Verificar se é nível do sistema
        $query = "SELECT nivel_sistema, nome FROM niveis_acesso WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $level = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$level) {
            throw new Exception("Nível não encontrado!");
        }
        
        if ($level['nivel_sistema'] === 'sistema') {
            throw new Exception("Não é possível desativar níveis do sistema!");
        }
        
        // Verificar se há usuários usando este nível
        $query = "SELECT COUNT(*) as count FROM usuarios WHERE nivel_acesso = :nome AND ativo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':nome', $level['nome']);
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        if ($count > 0) {
            throw new Exception("Não é possível desativar. Existem $count usuário(s) usando este nível!");
        }
        
        $query = "UPDATE niveis_acesso SET ativo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
    
    /**
     * Verificar permissão específica para um usuário
     */
    public function hasPermission($user_level, $permission, $action = null) {
        $level = $this->getLevel($user_level);
        if (!$level) {
            return false;
        }
        
        $permissoes = json_decode($level['permissoes'], true);
        
        if ($action) {
            return isset($permissoes[$permission][$action]) && $permissoes[$permission][$action] === true;
        }
        
        return isset($permissoes[$permission]) && $permissoes[$permission] === true;
    }
    
    /**
     * Obter todas as permissões de um nível
     */
    public function getLevelPermissions($user_level) {
        $level = $this->getLevel($user_level);
        if (!$level) {
            return [];
        }
        
        return json_decode($level['permissoes'], true);
    }
    
    /**
     * Registrar mudança de nível no histórico
     */
    public function logLevelChange($usuario_id, $nivel_anterior, $nivel_novo, $alterado_por, $observacoes = '') {
        $query = "INSERT INTO historico_niveis (usuario_id, nivel_anterior, nivel_novo, alterado_por, observacoes) 
                  VALUES (:usuario_id, :nivel_anterior, :nivel_novo, :alterado_por, :observacoes)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':nivel_anterior', $nivel_anterior);
        $stmt->bindParam(':nivel_novo', $nivel_novo);
        $stmt->bindParam(':alterado_por', $alterado_por);
        $stmt->bindParam(':observacoes', $observacoes);
        
        return $stmt->execute();
    }
    
    /**
     * Obter histórico de mudanças de um usuário
     */
    public function getUserLevelHistory($usuario_id, $limit = 50) {
        $query = "SELECT h.*, u.nome as alterado_por_nome 
                  FROM historico_niveis h 
                  LEFT JOIN usuarios u ON h.alterado_por = u.id 
                  WHERE h.usuario_id = :usuario_id 
                  ORDER BY h.data_alteracao DESC 
                  LIMIT :limit";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obter estatísticas dos níveis
     */
    public function getLevelStats() {
        $query = "SELECT 
                    n.nome, 
                    n.cor,
                    COUNT(u.id) as total_usuarios,
                    SUM(CASE WHEN u.ativo = 1 THEN 1 ELSE 0 END) as usuarios_ativos
                  FROM niveis_acesso n 
                  LEFT JOIN usuarios u ON n.nome = u.nivel_acesso 
                  WHERE n.ativo = 1 
                  GROUP BY n.id, n.nome, n.cor 
                  ORDER BY total_usuarios DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Estrutura padrão de permissões
     */
    public static function getDefaultPermissions() {
        return [
            'chamados' => [
                'criar' => false,
                'editar' => false,
                'excluir' => false,
                'ver_todos' => false
            ],
            'usuarios' => [
                'criar' => false,
                'editar' => false,
                'excluir' => false,
                'ver_todos' => false
            ],
            'backup' => false,
            'logs' => false,
            'debug' => false,
            'security' => false,
            'dev_area' => false,
            'manage_levels' => false
        ];
    }
    
    /**
     * Excluir permanentemente um nível customizado
     */
    public function deleteLevelPermanent($id) {
        try {
            // Verificar se é um nível customizado
            $stmt = $this->db->prepare("SELECT nivel_sistema, nome FROM niveis_acesso WHERE id = ?");
            $stmt->execute([$id]);
            $level = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$level || $level['nivel_sistema'] === 'sistema') {
                throw new Exception("Não é possível excluir níveis do sistema.");
            }
            
            // Verificar se há usuários vinculados
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE nivel_acesso = ?");
            $stmt->execute([$level['nome']]);
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            if ($count > 0) {
                throw new Exception("Não é possível excluir. Há $count usuário(s) vinculado(s) a este nível.");
            }
            
            $this->db->beginTransaction();
            
            // Não registrar no histórico para operações de exclusão de nível
            // (diferente de mudança de nível de usuário)
            
            // Excluir o nível
            $stmt = $this->db->prepare("DELETE FROM niveis_acesso WHERE id = ? AND nivel_sistema = 'customizado'");
            $result = $stmt->execute([$id]);
            
            $this->db->commit();
            return $result;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Cores disponíveis para níveis
     */
    public static function getAvailableColors() {
        return [
            '#dc3545' => 'Vermelho',
            '#fd7e14' => 'Laranja', 
            '#ffc107' => 'Amarelo',
            '#28a745' => 'Verde',
            '#17a2b8' => 'Azul Claro',
            '#007bff' => 'Azul',
            '#6f42c1' => 'Roxo',
            '#e83e8c' => 'Rosa',
            '#6c757d' => 'Cinza',
            '#343a40' => 'Preto'
        ];
    }
}
?>
