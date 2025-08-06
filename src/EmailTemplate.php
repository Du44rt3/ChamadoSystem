<?php

class EmailTemplate {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Formatar data/hora para exibição no template
     */
    private function formatarDataHora($data) {
        try {
            // Se a data já é um objeto DateTime
            if ($data instanceof DateTime) {
                $dt = $data;
            } else {
                // Criar objeto DateTime da string (assumindo que já está no horário local)
                $dt = new DateTime($data);
            }
            
            // Não aplicar timezone pois o MySQL já armazena no horário local
            // $dt->setTimezone(new DateTimeZone('America/Sao_Paulo'));
            
            // Retornar formatação padrão
            return $dt->format('d/m/Y \à\s H:i:s');
        } catch (Exception $e) {
            // Fallback em caso de erro
            return date('d/m/Y \à\s H:i:s');
        }
    }
    
    /**
     * Função estática para usar em outras partes da aplicação
     */
    public static function formatarDataHoraPadrao($data) {
        try {
            if ($data instanceof DateTime) {
                $dt = $data;
            } else {
                $dt = new DateTime($data);
            }
            
            // Não aplicar timezone pois o MySQL já armazena no horário local
            // $dt->setTimezone(new DateTimeZone('America/Sao_Paulo'));
            return $dt->format('d/m/Y \à\s H:i:s');
        } catch (Exception $e) {
            return date('d/m/Y \à\s H:i:s');
        }
    }
    
    /**
     * Obter histórico de atividades em ordem cronológica sem duplicações
     */
    private function obterHistoricoLimpo($chamado_id) {
        require_once 'ChamadoHistorico.php';
        $historico = new ChamadoHistorico($this->conn);
        return $historico->buscarHistoricoCompleto($chamado_id);
    }
    
    /**
     * Gera o template de email para abertura de chamado
     */
    public function templateAbertura($chamado) {
        $data_abertura = $this->formatarDataHora($chamado->data_abertura);
        
        $assunto = "✅ Chamado Aberto: {$chamado->codigo_chamado}";
        
        $corpo = "Olá {$chamado->nome_colaborador}!\n";
        $corpo .= "Recebemos seu chamado e já começaremos a trabalhar nele.\n";
        $corpo .= "Segue detalhes do chamado:\n";
        $corpo .= "-----------------------------------------------------------\n";
        $corpo .= "Chamado: {$chamado->codigo_chamado}\n";
        $corpo .= "Setor: {$chamado->setor}\n";
        $corpo .= "Usuário: {$chamado->nome_colaborador}\n";
        $corpo .= "Descrição do Problema:\n";
        $corpo .= "{$chamado->descricao_problema}\n";
        $corpo .= "Prioridade: " . ucfirst($chamado->gravidade) . "\n";
        
        // Mostrar data limite real do SLA se disponível
        if (isset($chamado->data_limite_sla) && !empty($chamado->data_limite_sla)) {
            $data_limite_sla = $this->formatarDataHora($chamado->data_limite_sla);
            $corpo .= "Data Limite para Resolução (SLA): {$data_limite_sla}\n";
        } else {
            $corpo .= "Prazo de Resolução (SLA) – Estimado: " . $this->calcularSLA($chamado->gravidade) . "\n";
        }
        
        $corpo .= "Tipo de Visita: remoto/local\n";
        $corpo .= "Abertura: {$data_abertura}\n";
        $corpo .= "Status do chamado:\n";
        
        // Buscar apenas atividades manuais (não incluir abertura automática)
        $historico = $this->obterHistoricoLimpo($chamado->id);
        if (!empty($historico)) {
            foreach ($historico as $atividade) {
                $data_atividade = $this->formatarDataHora($atividade['data_atividade']);
                $corpo .= "{$data_atividade}: {$atividade['atividade']}\n";
            }
        }
        
        $corpo .= "-----------------------------------------------------------\n\n";
        $corpo .= "Manteremos você informado sobre o progresso do atendimento.\n\n";
        $corpo .= "Atenciosamente,\n";
        $corpo .= "Grupo Elus | Operações e Facilities\n";
        $corpo .= "Infraestrutura & Tecnologia";
        
        return [
            'email' => $chamado->email,
            'assunto' => $assunto,
            'corpo' => $corpo
        ];
    }
    
    /**
     * Gera o template de email para chamado em andamento
     */
    public function templateAndamento($chamado, $ultimaAtividade = null) {
        $data_abertura = $this->formatarDataHora($chamado->data_abertura);
        
        $assunto = "🔄 Chamado em Andamento: {$chamado->codigo_chamado}";
        
        $corpo = "Olá {$chamado->nome_colaborador}!\n";
        $corpo .= "Estamos trabalhando no seu chamado, segue status atualizado;\n";
        $corpo .= "Segue detalhes do chamado:\n";
        $corpo .= "-----------------------------------------------------------\n";
        $corpo .= "Chamado: {$chamado->codigo_chamado}\n";
        $corpo .= "Setor: {$chamado->setor}\n";
        $corpo .= "Usuário: {$chamado->nome_colaborador}\n";
        $corpo .= "Descrição do Problema:\n";
        $corpo .= "{$chamado->descricao_problema}\n";
        $corpo .= "Prioridade: " . ucfirst($chamado->gravidade) . "\n";
        
        // Mostrar data limite real do SLA se disponível
        if (isset($chamado->data_limite_sla) && !empty($chamado->data_limite_sla)) {
            $data_limite_sla = $this->formatarDataHora($chamado->data_limite_sla);
            $corpo .= "Data Limite para Resolução (SLA): {$data_limite_sla}\n";
        } else {
            $corpo .= "Prazo de Resolução (SLA) – Estimado: " . $this->calcularSLA($chamado->gravidade) . "\n";
        }
        
        $corpo .= "Tipo de Visita: remoto/local\n";
        $corpo .= "Abertura: {$data_abertura}\n";
        $corpo .= "Status do chamado:\n";
        
        // Buscar histórico completo para mostrar evolução
        $historico = $this->obterHistoricoLimpo($chamado->id);
        if (!empty($historico)) {
            foreach ($historico as $atividade) {
                $data_atividade = $this->formatarDataHora($atividade['data_atividade']);
                $corpo .= "{$data_atividade}: {$atividade['atividade']}\n";
            }
        } else {
            if ($ultimaAtividade) {
                $data_atividade = $this->formatarDataHora($ultimaAtividade['data_atividade']);
                $corpo .= "{$data_atividade}: {$ultimaAtividade['atividade']}\n";
            }
        }
        
        $corpo .= "-----------------------------------------------------------\n\n";
        $corpo .= "Atenciosamente,\n";
        $corpo .= "Grupo Elus | Operações e Facilities\n";
        $corpo .= "Infraestrutura & Tecnologia";
        
        return [
            'email' => $chamado->email,
            'assunto' => $assunto,
            'corpo' => $corpo
        ];
    }
    
    /**
     * Gera o template de email para chamado finalizado
     */
    public function templateFinalizado($chamado, $atividades = []) {
        $data_abertura = $this->formatarDataHora($chamado->data_abertura);
        
        // Buscar histórico completo e limpo
        $historico = $this->obterHistoricoLimpo($chamado->id);
        
        // Formato do assunto: Chamado:(codigo) | FINALIZADO (DATA DE HOJE)
        $data_hoje = date('d/m/Y');
        $assunto = "Chamado:{$chamado->codigo_chamado} | FINALIZADO ({$data_hoje})";
        
        $corpo = "Olá {$chamado->nome_colaborador}!\n";
        $corpo .= "Segue chamado aberto ,realizado e Finalizado:\n";
        $corpo .= "-----------------------------------------------------------\n";
        $corpo .= "Chamado: {$chamado->codigo_chamado}\n";
        $corpo .= "Setor: {$chamado->setor}\n";
        $corpo .= "Usuário: {$chamado->nome_colaborador}\n";
        $corpo .= "Descrição do Problema:\n";
        $corpo .= "{$chamado->descricao_problema}\n";
        $corpo .= "Prioridade: " . ucfirst($chamado->gravidade) . "\n";
        
        // Mostrar data limite real do SLA se disponível
        if (isset($chamado->data_limite_sla) && !empty($chamado->data_limite_sla)) {
            $data_limite_sla = $this->formatarDataHora($chamado->data_limite_sla);
            $corpo .= "Data Limite para Resolução (SLA): {$data_limite_sla}\n";
            
            // Verificar se foi atendido dentro do prazo
            if (isset($chamado->data_fechamento) && !empty($chamado->data_fechamento)) {
                $data_fechamento = new DateTime($chamado->data_fechamento);
                $data_limite = new DateTime($chamado->data_limite_sla);
                if ($data_fechamento <= $data_limite) {
                    $corpo .= "✅ SLA ATENDIDO - Resolvido dentro do prazo!\n";
                } else {
                    $corpo .= "⚠️ SLA excedido - Resolvido fora do prazo estabelecido.\n";
                }
            }
        } else {
            $corpo .= "Prazo de Resolução (SLA) – Estimado: " . $this->calcularSLA($chamado->gravidade) . "\n";
        }
        
        $corpo .= "Tipo de Visita: remoto/local\n";
        $corpo .= "Abertura: {$data_abertura}\n";
        
        // Adicionar data de fechamento se disponível
        if (isset($chamado->data_fechamento) && !empty($chamado->data_fechamento)) {
            $data_fechamento = $this->formatarDataHora($chamado->data_fechamento);
            $corpo .= "Fechamento: {$data_fechamento}\n";
        }
        
        // Adicionar solução se disponível
        if (!empty($chamado->solucao)) {
            $corpo .= "Solução:\n";
            $corpo .= "{$chamado->solucao}\n";
        }
        
        $corpo .= "Status do chamado:\n";
        
        // Mostrar apenas atividades manuais (não incluir abertura nem fechamento automático)
        if (!empty($historico)) {
            foreach ($historico as $atividade) {
                $data_atividade = $this->formatarDataHora($atividade['data_atividade']);
                $corpo .= "{$data_atividade}: {$atividade['atividade']}\n";
            }
        } else {
            // Fallback se não há histórico
            if (!empty($atividades)) {
                foreach ($atividades as $atividade) {
                    $data_atividade = $this->formatarDataHora($atividade['data_atividade']);
                    $corpo .= "{$data_atividade}: {$atividade['atividade']}\n";
                }
            }
        }
        
        $corpo .= "-----------------------------------------------------------\n\n";
        $corpo .= "Atenciosamente,\n";
        $corpo .= "Grupo Elus | Operações e Facilities\n";
        $corpo .= "Infraestrutura & Tecnologia";
        
        return [
            'email' => $chamado->email,
            'assunto' => $assunto,
            'corpo' => $corpo
        ];
    }
    
    /**
     * Calcula o SLA baseado na prioridade
     */
    private function calcularSLA($prioridade) {
        try {
            $hoje = new DateTime();
            // Não aplicar timezone pois já estamos no horário local
            // $hoje->setTimezone(new DateTimeZone('America/Sao_Paulo'));
            
            switch (strtolower($prioridade)) {
                case 'alta':
                    $hoje->add(new DateInterval('P1D'));
                    break;
                case 'media':
                    $hoje->add(new DateInterval('P3D'));
                    break;
                case 'baixa':
                    $hoje->add(new DateInterval('P7D'));
                    break;
                default:
                    $hoje->add(new DateInterval('P3D'));
            }
            
            return $hoje->format('d/m/Y');
        } catch (Exception $e) {
            return date('d/m/Y', strtotime('+3 days'));
        }
    }
    
    /**
     * Gera template baseado no status do chamado
     */
    public function gerarTemplate($chamado, $tipo = 'abertura', $dadosExtras = null) {
        switch ($tipo) {
            case 'abertura':
                return $this->templateAbertura($chamado);
            case 'andamento':
                return $this->templateAndamento($chamado, $dadosExtras);
            case 'finalizado':
                return $this->templateFinalizado($chamado, $dadosExtras);
            default:
                return $this->templateAbertura($chamado);
        }
    }
}
?>
