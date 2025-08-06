<?php
// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

// Configurações da página
$page_title = "Editar Chamado";
$page_subtitle = "Alterar informações do chamado";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Chamado - ELUS Facilities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
</head>
<body>
    <?php 
    // Incluir header moderno
    require_once '../src/header.php'; 
    ?>

    <div class="container-fluid mt-4">
        <h1>Editar Chamado</h1>

        <?php
        $id = isset($_GET['id']) ? $_GET['id'] : die('ID não especificado.');

        include_once '../src/DB.php';
        include_once '../src/Chamado.php';
        include_once '../src/ChamadoHistorico.php';

        $database = new DB();
        $db = $database->getConnection();
        $chamado = new Chamado($db);
        $historico = new ChamadoHistorico($db);

        // Tratamento de requisições AJAX
        if(isset($_GET['ajax']) && $_GET['ajax'] == '1') {
            $chamado->id = $id;
            $chamado->readOne();
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data_limite_sla' => $chamado->data_limite_sla
            ]);
            exit;
        }

        // Tratamento de edição de prazo via AJAX
        if(isset($_POST['editar_prazo']) && $_POST['editar_prazo'] == '1') {
            try {
                $chamado->id = $_POST['id'];
                $chamado->readOne();
                
                $nova_data_limite = $_POST['data_limite_sla'];
                
                // Validar formato da data - aceitar múltiplos formatos
                $data_teste = DateTime::createFromFormat('Y-m-d\TH:i', $nova_data_limite);
                if(!$data_teste) {
                    $data_teste = DateTime::createFromFormat('Y-m-d H:i', $nova_data_limite);
                }
                if(!$data_teste) {
                    $data_teste = DateTime::createFromFormat('d/m/Y H:i', $nova_data_limite);
                }
                
                if(!$data_teste) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Formato de data inválido. Use: YYYY-MM-DD HH:MM'
                    ]);
                    exit;
                }
                
                // Converter para formato MySQL
                $data_formatada = $data_teste->format('Y-m-d H:i:s');
                
                // Atualizar apenas o prazo
                $query = "UPDATE chamados SET data_limite_sla = ? WHERE id = ?";
                $stmt = $db->prepare($query);
                
                if($stmt->execute([$data_formatada, $_POST['id']])) {
                    // Registrar atividade no histórico
                    $historico->adicionarAtividade($_POST['id'], 'Prazo SLA atualizado para ' . $data_teste->format('d/m/Y H:i'), 'Sistema');
                    
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Prazo SLA atualizado com sucesso'
                    ]);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Erro ao atualizar prazo no banco de dados'
                    ]);
                }
            } catch (Exception $e) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Erro interno: ' . $e->getMessage()
                ]);
            }
            exit;
        }

        $chamado->id = $id;
        $chamado->readOne();
        
        // Guardar valores originais para comparação
        $nome_colaborador_original = $chamado->nome_colaborador;
        $email_original = $chamado->email;
        $setor_original = $chamado->setor;
        $descricao_problema_original = $chamado->descricao_problema;
        $nome_projeto_original = $chamado->nome_projeto;
        $gravidade_original = $chamado->gravidade;
        $status_original = $chamado->status;
        $solucao_original = $chamado->solucao;

        if($_POST){
            // Guardar status original para detectar mudanças
            $status_original = $chamado->status;
            
            $chamado->nome_colaborador = $_POST['nome_colaborador'];
            $chamado->email = $_POST['email'];
            $chamado->setor = $_POST['setor'];
            $chamado->descricao_problema = $_POST['descricao_problema'];
            $chamado->nome_projeto = $_POST['nome_projeto'];
            $chamado->gravidade = $_POST['gravidade'];
            $chamado->status = $_POST['status'];
            $chamado->solucao = $_POST['solucao'];

            if($chamado->update()){
                // Registrar atividades no histórico baseado nas mudanças
                $atividades = [];
                
                // Verificar mudança de status e registrar automaticamente
                if($status_original != $chamado->status) {
                    date_default_timezone_set('America/Sao_Paulo');
                    $timestamp = date('Y-m-d H:i:s');
                    
                    switch($chamado->status) {
                        case 'em_andamento':
                            $historico->adicionarAtividade($id, 'Chamado colocado em andamento', 'Sistema', $timestamp);
                            break;
                        case 'fechado':
                            $historico->adicionarAtividade($id, 'Chamado fechado', 'Sistema', $timestamp);
                            break;
                        case 'aberto':
                            $historico->adicionarAtividade($id, 'Chamado reaberto', 'Sistema', $timestamp);
                            break;
                    }
                }
                
                // Verificar se foi adicionada solução
                if(empty($solucao_original) && !empty($chamado->solucao)) {
                    $atividades[] = "Solução adicionada ao chamado";
                } elseif(!empty($solucao_original) && $solucao_original != $chamado->solucao) {
                    $atividades[] = "Solução do chamado foi atualizada";
                }
                
                // Verificar outras mudanças importantes
                $mudancas_importantes = false;
                if($_POST['nome_colaborador'] != $nome_colaborador_original ||
                   $_POST['email'] != ($email_original ?? '') ||
                   $_POST['setor'] != $setor_original ||
                   $_POST['descricao_problema'] != $descricao_problema_original ||
                   $_POST['nome_projeto'] != $nome_projeto_original ||
                   $_POST['gravidade'] != $gravidade_original) {
                    $mudancas_importantes = true;
                }
                
                // Se houve mudanças mas não de solução nem status, registrar edição geral
                if(empty($atividades) && $mudancas_importantes) {
                    $atividades[] = "Chamado editado - informações atualizadas";
                }
                
                // Registrar outras atividades no histórico
                foreach($atividades as $atividade) {
                    $historico->adicionarAtividade($id, $atividade, 'Sistema');
                }
                
                echo "<div class='alert alert-success'>Chamado atualizado com sucesso!</div>";
                echo "<a href='view.php?id=$id' class='btn btn-primary'>Ver Chamado</a> ";
                echo "<a href='index.php' class='btn btn-secondary'>Voltar para lista</a>";
            } else {
                echo "<div class='alert alert-danger'>Erro ao atualizar chamado.</div>";
            }
        } else {
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>?id=<?php echo $id; ?>" method="post">
            <div class="mb-3">
                <label class="form-label">Código do Chamado</label>
                <div class="form-control-plaintext"><code><?php echo $chamado->codigo_chamado; ?></code></div>
            </div>

            <div class="mb-3">
                <label for="nome_colaborador" class="form-label">Nome do Colaborador</label>
                <input type="text" class="form-control" id="nome_colaborador" name="nome_colaborador" value="<?php echo $chamado->nome_colaborador; ?>" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $chamado->email ?? ''; ?>" required>
            </div>

            <div class="mb-3">
                <label for="setor" class="form-label">Setor</label>
                <div class="position-relative">
                    <select class="form-control" id="setor" name="setor" required style="appearance: none; background-image: none; padding-right: 2.5rem;">
                        <option value="">Selecione o setor</option>
                        <option value="Recebimento" <?php echo ($chamado->setor == 'Recebimento') ? 'selected' : ''; ?>>Recebimento</option>
                        <option value="Inspensão da Qualidade" <?php echo ($chamado->setor == 'Inspensão da Qualidade') ? 'selected' : ''; ?>>Inspensão da Qualidade</option>
                        <option value="Lab. Temperatura" <?php echo ($chamado->setor == 'Lab. Temperatura') ? 'selected' : ''; ?>>Lab. Temperatura</option>
                        <option value="Lab. Pressão" <?php echo ($chamado->setor == 'Lab. Pressão') ? 'selected' : ''; ?>>Lab. Pressão</option>
                        <option value="Lab. Vazão" <?php echo ($chamado->setor == 'Lab. Vazão') ? 'selected' : ''; ?>>Lab. Vazão</option>
                        <option value="Lab. Eletrica" <?php echo ($chamado->setor == 'Lab. Eletrica') ? 'selected' : ''; ?>>Lab. Eletrica</option>
                        <option value="Lab Volume" <?php echo ($chamado->setor == 'Lab Volume') ? 'selected' : ''; ?>>Lab Volume</option>
                        <option value="Lab. Fisico - Quimico" <?php echo ($chamado->setor == 'Lab. Fisico - Quimico') ? 'selected' : ''; ?>>Lab. Fisico - Quimico</option>
                        <option value="Lab. MRC" <?php echo ($chamado->setor == 'Lab. MRC') ? 'selected' : ''; ?>>Lab. MRC</option>
                        <option value="Lab. Dimensional" <?php echo ($chamado->setor == 'Lab. Dimensional') ? 'selected' : ''; ?>>Lab. Dimensional</option>
                        <option value="Lab." <?php echo ($chamado->setor == 'Lab.') ? 'selected' : ''; ?>>Lab.</option>
                        <option value="SGQ" <?php echo ($chamado->setor == 'SGQ') ? 'selected' : ''; ?>>SGQ</option>
                        <option value="Qualidade Externa" <?php echo ($chamado->setor == 'Qualidade Externa') ? 'selected' : ''; ?>>Qualidade Externa</option>
                        <option value="Financeiro" <?php echo ($chamado->setor == 'Financeiro') ? 'selected' : ''; ?>>Financeiro</option>
                        <option value="Comercial" <?php echo ($chamado->setor == 'Comercial') ? 'selected' : ''; ?>>Comercial</option>
                        <option value="Fiscal" <?php echo ($chamado->setor == 'Fiscal') ? 'selected' : ''; ?>>Fiscal</option>
                        <option value="Administrativo" <?php echo ($chamado->setor == 'Administrativo') ? 'selected' : ''; ?>>Administrativo</option>
                        <option value="RH" <?php echo ($chamado->setor == 'RH') ? 'selected' : ''; ?>>RH</option>
                        <option value="Field Service" <?php echo ($chamado->setor == 'Field Service') ? 'selected' : ''; ?>>Field Service</option>
                        <option value="Operações Facilities" <?php echo ($chamado->setor == 'Operações Facilities') ? 'selected' : ''; ?>>Operações Facilities</option>
                        <option value="Outros" <?php echo ($chamado->setor == 'Outros') ? 'selected' : ''; ?>>Outros</option>
                    </select>
                    <i class="fas fa-chevron-down position-absolute" style="right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                </div>
            </div>

            <div class="mb-3">
                <label for="descricao_problema" class="form-label">Descrição do Problema</label>
                <textarea class="form-control" id="descricao_problema" name="descricao_problema" rows="4" required><?php echo $chamado->descricao_problema; ?></textarea>
            </div>

            <div class="mb-3">
                <label for="nome_projeto" class="form-label">Nome do Projeto</label>
                <input type="text" class="form-control" id="nome_projeto" name="nome_projeto" value="<?php echo $chamado->nome_projeto; ?>" required>
                <div class="form-text">Este nome aparece após o underscore no código do chamado</div>
            </div>

            <div class="mb-3">
                <label for="gravidade" class="form-label">Gravidade</label>
                <div class="position-relative">
                    <select class="form-control" id="gravidade" name="gravidade" required style="appearance: none; background-image: none; padding-right: 2.5rem;">
                        <option value="baixa" <?php echo ($chamado->gravidade == 'baixa') ? 'selected' : ''; ?>>Baixa</option>
                        <option value="media" <?php echo ($chamado->gravidade == 'media') ? 'selected' : ''; ?>>Média</option>
                        <option value="alta" <?php echo ($chamado->gravidade == 'alta') ? 'selected' : ''; ?>>Alta</option>
                    </select>
                    <i class="fas fa-chevron-down position-absolute" style="right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                </div>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <div class="position-relative">
                    <select class="form-control" id="status" name="status" required style="appearance: none; background-image: none; padding-right: 2.5rem;">
                        <option value="aberto" <?php echo ($chamado->status == 'aberto') ? 'selected' : ''; ?>>Aberto</option>
                        <option value="em_andamento" <?php echo ($chamado->status == 'em_andamento') ? 'selected' : ''; ?>>Em Andamento</option>
                        <option value="fechado" <?php echo ($chamado->status == 'fechado') ? 'selected' : ''; ?>>Fechado</option>
                    </select>
                    <i class="fas fa-chevron-down position-absolute" style="right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                </div>
            </div>

            <div class="mb-3">
                <label for="solucao" class="form-label">Solução (opcional)</label>
                <textarea class="form-control" id="solucao" name="solucao" rows="4" placeholder="Descreva a solução aplicada..."><?php echo $chamado->solucao; ?></textarea>
            </div>

            <div class="mb-3">
                <label for="data_limite_sla" class="form-label">Prazo SLA</label>
                <div class="row">
                    <div class="col-md-8">
                        <input type="datetime-local" class="form-control" id="data_limite_sla" name="data_limite_sla" 
                               value="<?php echo $chamado->data_limite_sla ? date('Y-m-d\TH:i', strtotime($chamado->data_limite_sla)) : ''; ?>">
                        <div class="form-text">Defina o prazo limite para resolução do chamado</div>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-outline-primary" onclick="atualizarSLA()">
                            <i class="fas fa-clock"></i> Atualizar SLA
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-success">Atualizar Chamado</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>

        <?php
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function atualizarSLA() {
            const dataLimiteSLA = document.getElementById('data_limite_sla').value;
            const chamadoId = <?php echo $id; ?>;
            
            if (!dataLimiteSLA) {
                alert('Por favor, selecione uma data e hora para o SLA.');
                return;
            }
            
            // Confirmar a atualização
            if (!confirm('Tem certeza que deseja atualizar o prazo SLA?')) {
                return;
            }
            
            // Desabilitar botão durante a requisição
            const botao = event.target;
            const textoOriginal = botao.innerHTML;
            botao.disabled = true;
            botao.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Atualizando...';
            
            // Fazer requisição AJAX
            const formData = new FormData();
            formData.append('editar_prazo', '1');
            formData.append('id', chamadoId);
            formData.append('data_limite_sla', dataLimiteSLA);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erro na resposta do servidor');
                }
                return response.json();
            })
            .then(data => {
                // Reabilitar botão
                botao.disabled = false;
                botao.innerHTML = textoOriginal;
                
                if (data.success) {
                    // Mostrar mensagem de sucesso
                    mostrarAlerta('success', data.message);
                    
                    // Recarregar a página após 2 segundos para atualizar os dados
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    mostrarAlerta('danger', 'Erro: ' + data.message);
                }
            })
            .catch(error => {
                // Reabilitar botão
                botao.disabled = false;
                botao.innerHTML = textoOriginal;
                
                console.error('Erro:', error);
                mostrarAlerta('danger', 'Erro ao atualizar SLA. Tente novamente.');
            });
        }
        
        function mostrarAlerta(tipo, mensagem) {
            // Remover alertas existentes
            const alertasExistentes = document.querySelectorAll('.alert-ajax');
            alertasExistentes.forEach(alerta => alerta.remove());
            
            // Criar novo alerta
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${tipo} alert-dismissible fade show alert-ajax`;
            alertDiv.innerHTML = `
                <i class="fas fa-${tipo === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${mensagem}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            // Inserir o alerta no topo do container
            const container = document.querySelector('.container-fluid');
            const h1 = container.querySelector('h1');
            container.insertBefore(alertDiv, h1.nextSibling);
            
            // Remover o alerta após 5 segundos
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>

