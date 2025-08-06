<?php

class Chamado {
    private $conn;
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
    }

    function create(){
        // Gerar código do chamado automaticamente
        $this->codigo_chamado = $this->generateCodigoChamado($this->nome_projeto);
        
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
            return true;
        }
        return false;
    }

    function read(){
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY data_abertura DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    function readOne(){
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

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
            return true;
        }
        return false;
    }

    function readByStatus($status){
        $query = "SELECT * FROM " . $this->table_name . " WHERE status = ? ORDER BY data_abertura DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $status);
        $stmt->execute();
        return $stmt;
    }

    function generateCodigoChamado($nome_projeto = ""){
        $ano = date('y'); // 25 para 2025
        $mes = date('m'); // 07 para julho
        $dia = date('d'); // 17 para dia 17
        
        $data_base = $ano . $mes . $dia;
        
        // Buscar quantos chamados já foram criados hoje para gerar contador único
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE DATE(data_abertura) = CURDATE()";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $contador = $row['total'] + 1;
        
        // Garantir que o código seja único verificando se já existe
        $codigo_base = "";
        $tentativas = 0;
        do {
            $codigo_base = "FAC-TI-" . $data_base . "." . ($contador + $tentativas) . "_" . strtolower(str_replace(' ', '_', $nome_projeto));
            
            // Verificar se já existe um código igual
            $query_check = "SELECT id FROM " . $this->table_name . " WHERE codigo_chamado = :codigo";
            $stmt_check = $this->conn->prepare($query_check);
            $stmt_check->bindParam(':codigo', $codigo_base);
            $stmt_check->execute();
            
            if($stmt_check->rowCount() == 0) {
                break; // Código único encontrado
            }
            
            $tentativas++;
        } while($tentativas < 100); // Limite de segurança
        
        return $codigo_base;
    }

    function search($termo_pesquisa){
        $query = "SELECT * FROM " . $this->table_name . " 
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
        return $stmt;
    }

    function searchByStatus($termo_pesquisa, $status){
        $query = "SELECT * FROM " . $this->table_name . " 
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
        return $stmt;
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

