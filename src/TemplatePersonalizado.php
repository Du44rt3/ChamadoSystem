<?php

class TemplatePersonalizado {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Salvar ou atualizar template personalizado
     */
    public function salvar($chamado_id, $tipo, $assunto, $corpo) {
        try {
            // Verificar se já existe um template para este chamado e tipo
            $query = "SELECT id FROM templates_personalizados WHERE chamado_id = ? AND tipo = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$chamado_id, $tipo]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                // Atualizar template existente
                $updateQuery = "UPDATE templates_personalizados SET assunto = ?, corpo = ?, data_modificacao = NOW() WHERE id = ?";
                $updateStmt = $this->conn->prepare($updateQuery);
                return $updateStmt->execute([$assunto, $corpo, $result['id']]);
            } else {
                // Inserir novo template
                $insertQuery = "INSERT INTO templates_personalizados (chamado_id, tipo, assunto, corpo) VALUES (?, ?, ?, ?)";
                $insertStmt = $this->conn->prepare($insertQuery);
                return $insertStmt->execute([$chamado_id, $tipo, $assunto, $corpo]);
            }
            
        } catch (Exception $e) {
            throw new Exception("Erro ao salvar template: " . $e->getMessage());
        }
    }
    
    /**
     * Buscar template personalizado
     */
    public function buscar($chamado_id, $tipo) {
        try {
            $query = "SELECT assunto, corpo, data_modificacao FROM templates_personalizados WHERE chamado_id = ? AND tipo = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$chamado_id, $tipo]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result : null;
            
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar template: " . $e->getMessage());
        }
    }
    
    /**
     * Verificar se existe template personalizado
     */
    public function existe($chamado_id, $tipo) {
        try {
            $query = "SELECT id FROM templates_personalizados WHERE chamado_id = ? AND tipo = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$chamado_id, $tipo]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? true : false;
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Excluir template personalizado
     */
    public function excluir($chamado_id, $tipo) {
        try {
            $query = "DELETE FROM templates_personalizados WHERE chamado_id = ? AND tipo = ?";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$chamado_id, $tipo]);
            
        } catch (Exception $e) {
            throw new Exception("Erro ao excluir template: " . $e->getMessage());
        }
    }
    
    /**
     * Listar todos os templates de um chamado
     */
    public function listarPorChamado($chamado_id) {
        try {
            $query = "SELECT tipo, assunto, corpo, data_modificacao FROM templates_personalizados WHERE chamado_id = ? ORDER BY data_modificacao DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$chamado_id]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
            
        } catch (Exception $e) {
            throw new Exception("Erro ao listar templates: " . $e->getMessage());
        }
    }
}

?>
