<?php

require_once 'CacheManager.php';

class Chamado {
    private $conn;
    private $cache;
    private $table_name = "chamados";

    public $id;
    public $codigo_chamado;
    public $nome_colaborador;
    public $email;
    public $setor;
    public $descricao_problema;
    public $nome_projeto;
    public $data_abertura;
    public $gravidade;
    public $status;
    public $solucao;
    public $data_limite_sla;
    public $data_fechamento;

    public function __construct($db){
        $this->conn = $db;
        $this->cache = new CacheManager();
    }

    function create(){
        try {
            // Gerar código do chamado automaticamente com proteção contra race condition
            $this->codigo_chamado = $this->generateCodigoChamado($this->nome_projeto);
            
            // Iniciar transação para a inserção
            $this->conn->beginTransaction();
            
            // Calcular SLA baseado na gravidade
            $data_limite_sla = $this->calcularSLA($this->gravidade);
            
            $query = "INSERT INTO " . $this->table_name . " SET codigo_chamado=:codigo_chamado, nome_colaborador=:nome_colaborador, email=:email, setor=:setor, descricao_problema=:descricao_problema, nome_projeto=:nome_projeto, gravidade=:gravidade, data_limite_sla=:data_limite_sla";

            $stmt = $this->conn->prepare($query);

            $this->codigo_chamado=htmlspecialchars(strip_tags($this->codigo_chamado));
            $this->nome_colaborador=htmlspecialchars(strip_tags($this->nome_colaborador));
            $this->email=htmlspecialchars(strip_tags($this->email));
            $this->setor=htmlspecialchars(strip_tags($this->setor));
            $this->descricao_problema=htmlspecialchars(strip_tags($this->descricao_problema));
            $this->nome_projeto=htmlspecialchars(strip_tags($this->nome_projeto));
            $this->gravidade=htmlspecialchars(strip_tags($this->gravidade));

            $stmt->bindParam(":codigo_chamado", $this->codigo_chamado);
            $stmt->bindParam(":nome_colaborador", $this->nome_colaborador);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":setor", $this->setor);
            $stmt->bindParam(":descricao_problema", $this->descricao_problema);
            $stmt->bindParam(":nome_projeto", $this->nome_projeto);
            $stmt->bindParam(":gravidade", $this->gravidade);
            $stmt->bindParam(":data_limite_sla", $data_limite_sla);

            if($stmt->execute()){
                // Obter o ID antes do commit
                $this->id = $this->conn->lastInsertId();
                
                // Confirmar transação
                $this->conn->commit();
                
                // Limpar cache relacionado após criação
                $this->invalidateCache();
                return $this->id; // Retornar o ID ao invés de true
            }
            
            // Se chegou aqui, houve erro
            $this->conn->rollback();
            return false;
            
        } catch (Exception $e) {
            // Em caso de erro, reverter transação se estiver ativa
            if($this->conn->inTransaction()) {
                $this->conn->rollback();
            }
            error_log("Erro ao criar chamado: " . $e->getMessage());
            return false;
        }
    }

    function read(){
        $cache_key = 'chamados_all_' . date('Y-m-d-H-i'); // Cache por minuto
        
        return $this->cache->rememberQuery($cache_key, function() {
            $query = "SELECT id, codigo_chamado, nome_colaborador, email, setor, descricao_problema, 
                             nome_projeto, data_abertura, gravidade, status, data_limite_sla, data_fechamento 
                      FROM " . $this->table_name . " ORDER BY data_abertura DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }, 300); // Cache por 5 minutos apenas
    }

    function readOne(){
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar se encontrou o registro
        if (!$row) {
            return false;
        }

        $this->nome_colaborador = $row["nome_colaborador"];
        $this->codigo_chamado = $row["codigo_chamado"];
        $this->email = $row["email"];
        $this->setor = $row["setor"];
        $this->descricao_problema = $row["descricao_problema"];
        $this->nome_projeto = $row["nome_projeto"];
        $this->data_abertura = $row["data_abertura"];
        $this->gravidade = $row["gravidade"];
        $this->status = $row["status"];
        $this->solucao = $row["solucao"];
        $this->data_limite_sla = $row["data_limite_sla"];
        $this->data_fechamento = $row["data_fechamento"];
        
        return true;
    }

    function update(){
        // Verificar se o nome do projeto foi alterado para regenerar o código
        $query_check = "SELECT nome_projeto, status, gravidade FROM " . $this->table_name . " WHERE id = :id";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(":id", $this->id);
        $stmt_check->execute();
        $row = $stmt_check->fetch(PDO::FETCH_ASSOC);
        $nome_projeto_original = $row['nome_projeto'];
        $status_original = $row['status'];
        $gravidade_original = $row['gravidade'];
        
        // Verificar se o chamado está sendo fechado
        $data_fechamento = null;
        if($this->status == 'fechado' && $status_original != 'fechado') {
            $data_fechamento = date('Y-m-d H:i:s');
        }
        
        // Se a gravidade mudou, recalcular SLA
        $recalcular_sla = ($gravidade_original != $this->gravidade && $this->status != 'fechado');
        $nova_data_limite_sla = null;
        if($recalcular_sla) {
            $nova_data_limite_sla = $this->calcularSLA($this->gravidade);
        }
        
        // Montar query baseada nas alterações
        $query = "UPDATE " . $this->table_name . " SET nome_colaborador=:nome_colaborador, email=:email, setor=:setor, descricao_problema=:descricao_problema, nome_projeto=:nome_projeto, gravidade=:gravidade, status=:status, solucao=:solucao";
        
        if($nome_projeto_original != $this->nome_projeto) {
            $this->codigo_chamado = $this->generateCodigoChamado($this->nome_projeto);
            $query .= ", codigo_chamado=:codigo_chamado";
        }
        
        if($data_fechamento) {
            $query .= ", data_fechamento=:data_fechamento";
        }
        
        if($recalcular_sla) {
            $query .= ", data_limite_sla=:data_limite_sla";
        }
        
        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->nome_colaborador=htmlspecialchars(strip_tags($this->nome_colaborador));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->setor=htmlspecialchars(strip_tags($this->setor));
        $this->descricao_problema=htmlspecialchars(strip_tags($this->descricao_problema));
        $this->nome_projeto=htmlspecialchars(strip_tags($this->nome_projeto));
        $this->gravidade=htmlspecialchars(strip_tags($this->gravidade));
        $this->status=htmlspecialchars(strip_tags($this->status));
        $this->solucao=htmlspecialchars(strip_tags($this->solucao));
        $this->id=htmlspecialchars(strip_tags($this->id));

        // Bind dos parâmetros
        $stmt->bindParam(":nome_colaborador", $this->nome_colaborador);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":setor", $this->setor);
        $stmt->bindParam(":descricao_problema", $this->descricao_problema);
        $stmt->bindParam(":nome_projeto", $this->nome_projeto);
        $stmt->bindParam(":gravidade", $this->gravidade);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":solucao", $this->solucao);
        $stmt->bindParam(":id", $this->id);
        
        if($nome_projeto_original != $this->nome_projeto) {
            $this->codigo_chamado=htmlspecialchars(strip_tags($this->codigo_chamado));
            $stmt->bindParam(":codigo_chamado", $this->codigo_chamado);
        }
        
        if($data_fechamento) {
            $stmt->bindParam(":data_fechamento", $data_fechamento);
        }
        
        if($recalcular_sla) {
            $stmt->bindParam(":data_limite_sla", $nova_data_limite_sla);
        }

        if($stmt->execute()){
            // Limpar cache relacionado após atualização
            $this->invalidateCache();
            return true;
        }
        return false;
    }

    function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()){
            // Limpar cache relacionado após exclusão
            $this->invalidateCache();
            return true;
        }
        return false;
    }
    
    /**
     * Invalida cache relacionado aos chamados
     */
    private function invalidateCache() {
        $patterns = [
            'chamados_all_',
            'chamados_status_',
            'search_',
            'search_status_',
            'stats_'
        ];
        
        foreach ($patterns as $pattern) {
            // Limpar cache das últimas 24 horas (por minuto agora)
            for ($i = 0; $i < 1440; $i++) { // 1440 minutos = 24 horas
                $timestamp = date('Y-m-d-H-i', strtotime("-{$i} minutes"));
                $this->cache->delete($pattern . $timestamp);
            }
        }
    }

    function readByStatus($status){
        $cache_key = 'chamados_status_' . $status . '_' . date('Y-m-d-H');
        
        return $this->cache->rememberQuery($cache_key, function() use ($status) {
            $query = "SELECT id, codigo_chamado, nome_colaborador, email, setor, descricao_problema, 
                             nome_projeto, data_abertura, gravidade, status, data_limite_sla, data_fechamento 
                      FROM " . $this->table_name . " WHERE status = ? ORDER BY data_abertura DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }, 1800); // Cache por 30 minutos
    }

    function generateCodigoChamado($nome_projeto = ""){
        $max_tentativas = 10;
        $tentativa = 0;
        
        while($tentativa < $max_tentativas) {
            $tentativa++;
            
            try {
                $ano = date('y'); // 25 para 2025
                $mes = date('m'); // 08 para agosto (com zero à esquerda)
                $dia = date('d'); // 07 para dia 07 (com zero à esquerda)
                
                $data_base = $ano . $mes . $dia;
                $prefixo_base = "FAC-TI-" . $data_base . ".";
                
                // Usar GET_LOCK para evitar race conditions entre processos
                $lock_name = "codigo_chamado_" . $data_base;
                $lock_timeout = 10; // 10 segundos
                
                $get_lock = $this->conn->prepare("SELECT GET_LOCK(?, ?)");
                $get_lock->execute([$lock_name, $lock_timeout]);
                $lock_result = $get_lock->fetchColumn();
                
                if($lock_result != 1) {
                    // Se não conseguiu o lock, aguardar e tentar novamente
                    usleep(rand(50000, 200000)); // 50-200ms
                    continue;
                }
                
                try {
                    // Buscar todos os códigos do dia
                    $query = "SELECT codigo_chamado FROM " . $this->table_name . " 
                              WHERE codigo_chamado LIKE ? 
                              AND DATE(data_abertura) = CURDATE()";
                    $stmt = $this->conn->prepare($query);
                    $pattern = $prefixo_base . "%";
                    $stmt->execute([$pattern]);
                    $codigos_existentes = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    // Extrair números sequenciais
                    $numeros_usados = [];
                    foreach($codigos_existentes as $codigo) {
                        $resto = str_replace($prefixo_base, '', $codigo);
                        $partes = explode('_', $resto);
                        if(count($partes) > 0 && is_numeric($partes[0])) {
                            $numeros_usados[] = (int)$partes[0];
                        }
                    }
                    
                    // Encontrar próximo número
                    $contador = 1;
                    while(in_array($contador, $numeros_usados)) {
                        $contador++;
                    }
                    
                    // Adicionar um pouco de aleatoriedade na tentativa para evitar colisões
                    if($tentativa > 1) {
                        $contador += $tentativa - 1;
                    }
                    
                    // Gerar código final
                    $nome_projeto_limpo = strtolower(str_replace([' ', '-', '.', '/', '\\'], '_', $nome_projeto));
                    $nome_projeto_limpo = preg_replace('/[^a-z0-9_]/', '', $nome_projeto_limpo);
                    $codigo_final = $prefixo_base . $contador . "_" . $nome_projeto_limpo;
                    
                    // Verificação final
                    $check_query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE codigo_chamado = ?";
                    $check_stmt = $this->conn->prepare($check_query);
                    $check_stmt->execute([$codigo_final]);
                    
                    if($check_stmt->fetchColumn() == 0) {
                        // Código único encontrado
                        return $codigo_final;
                    }
                    
                } finally {
                    // Sempre liberar o lock
                    $release_lock = $this->conn->prepare("SELECT RELEASE_LOCK(?)");
                    $release_lock->execute([$lock_name]);
                }
                
            } catch(Exception $e) {
                error_log("Erro na tentativa $tentativa de gerar código: " . $e->getMessage());
                usleep(rand(100000, 300000)); // 100-300ms
            }
        }
        
        // Se chegou aqui, todas as tentativas falharam - usar timestamp para garantir unicidade
        $timestamp = time() . substr(microtime(), 2, 6);
        $nome_projeto_limpo = strtolower(str_replace([' ', '-', '.', '/', '\\'], '_', $nome_projeto));
        $nome_projeto_limpo = preg_replace('/[^a-z0-9_]/', '', $nome_projeto_limpo);
        return "FAC-TI-" . date('ymd') . "." . $timestamp . "_" . $nome_projeto_limpo;
    }

    function search($termo_pesquisa){
        $cache_key = 'search_' . md5($termo_pesquisa) . '_' . date('Y-m-d-H');
        
        return $this->cache->rememberQuery($cache_key, function() use ($termo_pesquisa) {
            $query = "SELECT id, codigo_chamado, nome_colaborador, email, setor, descricao_problema, 
                             nome_projeto, data_abertura, gravidade, status, data_limite_sla, data_fechamento 
                      FROM " . $this->table_name . " 
                      WHERE nome_colaborador LIKE :termo 
                      OR email LIKE :termo 
                      OR setor LIKE :termo 
                      OR codigo_chamado LIKE :termo 
                      OR descricao_problema LIKE :termo 
                      OR nome_projeto LIKE :termo 
                      ORDER BY data_abertura DESC";
            
            $stmt = $this->conn->prepare($query);
            $termo_pesquisa = "%" . htmlspecialchars(strip_tags($termo_pesquisa)) . "%";
            $stmt->bindParam(':termo', $termo_pesquisa);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }, 1800); // Cache por 30 minutos
    }

    function searchByStatus($termo_pesquisa, $status){
        $cache_key = 'search_status_' . $status . '_' . md5($termo_pesquisa) . '_' . date('Y-m-d-H');
        
        return $this->cache->rememberQuery($cache_key, function() use ($termo_pesquisa, $status) {
            $query = "SELECT id, codigo_chamado, nome_colaborador, email, setor, descricao_problema, 
                             nome_projeto, data_abertura, gravidade, status, data_limite_sla, data_fechamento 
                      FROM " . $this->table_name . " 
                      WHERE status = :status 
                      AND (nome_colaborador LIKE :termo 
                           OR email LIKE :termo 
                           OR setor LIKE :termo 
                           OR codigo_chamado LIKE :termo 
                           OR descricao_problema LIKE :termo 
                           OR nome_projeto LIKE :termo) 
                      ORDER BY data_abertura DESC";
            
            $stmt = $this->conn->prepare($query);
            $termo_pesquisa = "%" . htmlspecialchars(strip_tags($termo_pesquisa)) . "%";
            $stmt->bindParam(':termo', $termo_pesquisa);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }, 1800); // Cache por 30 minutos
    }
    
    function calcularSLA($gravidade) {
        $data_atual = new DateTime();
        
        switch($gravidade) {
            case 'alta':
                // SLA de 24 horas para alta gravidade
                $data_atual->add(new DateInterval('PT24H'));
                break;
            case 'media':
                // SLA de 72 horas para média gravidade
                $data_atual->add(new DateInterval('PT72H'));
                break;
            case 'baixa':
                // SLA de 120 horas para baixa gravidade
                $data_atual->add(new DateInterval('PT120H'));
                break;
        }
        
        return $data_atual->format('Y-m-d H:i:s');
    }
    
    function verificarStatusSLA($data_limite_sla, $status) {
        if($status == 'fechado') {
            return 'concluido';
        }
        
        $agora = new DateTime();
        $limite = new DateTime($data_limite_sla);
        $diff = $agora->diff($limite);
        
        if($agora > $limite) {
            return 'vencido';
        }
        
        // Se falta menos de 25% do tempo, considerar crítico
        $tempo_total = $this->calcularTempoTotalSLA($data_limite_sla);
        $tempo_restante = $limite->getTimestamp() - $agora->getTimestamp();
        
        if($tempo_restante <= ($tempo_total * 0.25)) {
            return 'critico';
        }
        
        return 'ok';
    }
    
    private function calcularTempoTotalSLA($data_limite_sla) {
        $limite = new DateTime($data_limite_sla);
        $abertura = clone $limite;
        
        // Subtrair o tempo de SLA baseado na gravidade padrão
        $abertura->sub(new DateInterval('PT24H')); // Assumindo média como padrão
        
        return $limite->getTimestamp() - $abertura->getTimestamp();
    }
    
    public function verificarSLA($id) {
        $query = "SELECT data_abertura, data_limite_sla, status, data_fechamento FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$row) {
            return [
                'sla_real' => 'N/A',
                'status_sla' => 'normal',
                'data_limite_sla' => null
            ];
        }
        
        $data_abertura = new DateTime($row['data_abertura']);
        $data_limite = $row['data_limite_sla'] ? new DateTime($row['data_limite_sla']) : null;
        $agora = new DateTime();
        $data_fechamento = $row['data_fechamento'] ? new DateTime($row['data_fechamento']) : null;
        
        // Se não tem data limite, usar cálculo padrão
        if (!$data_limite) {
            $data_limite = clone $data_abertura;
            $data_limite->add(new DateInterval('PT24H')); // 24 horas padrão
        }
        
        // Determinar data de referência (fechamento ou atual)
        $data_referencia = $data_fechamento ? $data_fechamento : $agora;
        
        // Calcular tempo decorrido
        $tempo_decorrido = $data_referencia->getTimestamp() - $data_abertura->getTimestamp();
        $tempo_limite = $data_limite->getTimestamp() - $data_abertura->getTimestamp();
        
        // Calcular SLA real
        $horas_decorridas = $tempo_decorrido / 3600;
        $horas_limite = $tempo_limite / 3600;
        
        if ($horas_decorridas < 1) {
            $sla_real = round($horas_decorridas * 60) . ' min';
        } else {
            $sla_real = round($horas_decorridas, 1) . 'h';
        }
        
        // Determinar status do SLA
        $status_sla = 'normal';
        
        if ($data_referencia > $data_limite) {
            $status_sla = 'critico';
        } elseif ($horas_decorridas > ($horas_limite * 0.75)) {
            $status_sla = 'atencao';
        }
        
        return [
            'sla_real' => $sla_real,
            'status_sla' => $status_sla,
            'data_limite_sla' => $row['data_limite_sla']
        ];
    }
}

?>

