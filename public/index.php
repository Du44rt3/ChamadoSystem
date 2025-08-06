<?php
// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

// Configurações da página
$page_title = "Gestão de Chamados";
$page_subtitle = "Sistema de Controle e Atendimento";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELUS Facilities - Sistema de Chamados</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="apple-touch-icon" href="images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <style>
        /* Estilo para tabela com scroll horizontal suave */
        .table-responsive {
            scrollbar-width: thin;
            scrollbar-color: #6c757d #f8f9fa;
        }
        
        .table-responsive::-webkit-scrollbar {
            height: 8px;
        }
        
        .table-responsive::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
            background: #6c757d;
            border-radius: 4px;
        }
        
        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #495057;
        }
        
        /* Indicador visual de scroll */
        .scroll-indicator {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 14px;
            pointer-events: none;
            opacity: 0.7;
        }
        
        .table-container {
            position: relative;
        }
        
        /* Melhorias na seção de busca */
        .search-section {
            background: linear-gradient(135deg, var(--bs-white) 0%, #f8f9fa 100%);
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(var(--elus-blue-rgb), 0.1);
        }
        
        .search-form .input-group {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }
        
        .search-form .form-control {
            border: 1px solid #e3e6f0;
            font-size: 1rem;
            padding: 0.75rem 1rem;
        }
        
        .search-form .form-control:focus {
            border-color: var(--elus-blue);
            box-shadow: 0 0 0 0.2rem rgba(var(--elus-blue-rgb), 0.25);
        }
        
        .search-form .input-group-text {
            background: var(--bs-white);
            border: 1px solid #e3e6f0;
            color: var(--elus-blue);
        }
        
        .btn-novo-chamado {
            background: linear-gradient(135deg, var(--elus-blue) 0%, var(--elus-blue-dark) 100%);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(var(--elus-blue-rgb), 0.3);
        }
        
        .btn-novo-chamado:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(var(--elus-blue-rgb), 0.4);
        }
        
        /* Estilo para filtros */
        #filters-container {
            transition: all 0.3s ease;
        }
        
        #filters-container .card {
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Animação de piscar para SLA crítico */
        .blink {
            animation: blink-animation 1s infinite;
        }
        
        @keyframes blink-animation {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }
        
        /* Estilo para indicadores de SLA */
        .sla-vencido {
            color: #dc3545 !important;
            font-weight: bold !important;
        }
        
        .sla-critico {
            color: #fd7e14 !important;
            font-weight: bold !important;
        }
        
        .sla-ok {
            color: #0d6efd !important;
        }
        
        .sla-concluido {
            color: #198754 !important;
        }
        
        #show-filters-btn {
            transition: all 0.3s ease;
        }
        
        .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        /* Código do chamado clicável */
        .codigo-chamado {
            color: #0d6efd;
            text-decoration: none;
            border-bottom: 1px dotted #0d6efd;
            transition: all 0.3s ease;
        }
        
        .codigo-chamado:hover {
            color: #0b5ed7;
            text-decoration: none;
            border-bottom: 1px solid #0b5ed7;
        }
    </style>

</head>

<?php 
// Incluir header modernizado
require_once '../src/header.php'; 
?>

<body>
    <div class="container-fluid mt-4">
        <!-- Seção de Busca e Ações Modernizada -->
        <div class="search-section mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">
                        <div class="col-xl-8 col-lg-7 col-md-12">
                            <form method="GET" action="index.php" class="search-form">
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="fas fa-search"></i>
                                    </span>
                                    <input type="text" 
                                           name="pesquisa" 
                                           class="form-control border-start-0 border-end-0" 
                                           placeholder="Buscar por código, colaborador, setor ou descrição do problema..." 
                                           value="<?php echo isset($_GET['pesquisa']) ? htmlspecialchars($_GET['pesquisa']) : ''; ?>">
                                    <button class="btn btn-elus-primary px-4" type="submit">
                                        <i class="fas fa-search me-2"></i>Buscar
                                    </button>
                                    <?php if(isset($_GET['pesquisa']) && !empty($_GET['pesquisa'])): ?>
                                        <a href="index.php" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>Limpar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                        <div class="col-xl-4 col-lg-5 col-md-12">
                            <div class="d-flex gap-2 justify-content-lg-end">
                                <a href="add.php" class="btn btn-novo-chamado btn-lg flex-fill flex-lg-grow-0 text-white">
                                    <i class="fas fa-plus-circle me-2"></i>Novo Chamado
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
        include_once '../src/DB.php';
        include_once '../src/Chamado.php';

        $database = new DB();
        $db = $database->getConnection();
        $chamado = new Chamado($db);

        // Verificar se há pesquisa
        if(isset($_GET['pesquisa']) && !empty($_GET['pesquisa'])){
            $stmt = $chamado->search($_GET['pesquisa']);
            $termo_pesquisa = htmlspecialchars($_GET['pesquisa']);
            echo "<div class='alert alert-info d-flex align-items-center'>";
            echo "<i class='fas fa-search me-2'></i>";
            echo "Resultados para: <strong>$termo_pesquisa</strong>";
            echo "</div>";
        } else {
            $stmt = $chamado->read();
        }

        $num = $stmt->rowCount();

        if($num > 0){
            // Buscar os dados primeiro
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Header com contador de chamados e opções de visualização
            echo "<div class='d-flex justify-content-between align-items-center mb-4'>";
            echo "<h5 class='mb-0'><i class='fas fa-list me-2'></i>Lista de Chamados <span class='badge bg-primary ms-2'>{$num}</span></h5>";
            echo "<div class='view-options d-flex align-items-center gap-2'>";
            echo "<small class='text-muted me-2'>Visualização:</small>";
            echo "<div class='btn-group btn-group-sm' role='group'>";
            echo "<button type='button' class='btn btn-outline-primary active' id='btn-cards' onclick='toggleView(\"cards\")'>";
            echo "<i class='fas fa-th-large me-1'></i>Cards";
            echo "</button>";
            echo "<button type='button' class='btn btn-outline-primary' id='btn-list' onclick='toggleView(\"list\")'>";
            echo "<i class='fas fa-list me-1'></i>Lista";
            echo "</button>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            
            // Filtros para a lista
            echo "<div id='filters-container' class='mb-4' style='display: none;'>";
            echo "<div class='card'>";
            echo "<div class='card-body'>";
            echo "<div class='row g-3'>";
            echo "<div class='col-md-2'>";
            echo "<label class='form-label'><i class='fas fa-filter me-1'></i>Status</label>";
            echo "<select class='form-select form-select-sm' id='filter-status'>";
            echo "<option value=''>Todos os Status</option>";
            echo "<option value='aberto'>Aberto</option>";
            echo "<option value='em_andamento'>Em Andamento</option>";
            echo "<option value='fechado'>Fechado</option>";
            echo "</select>";
            echo "</div>";
            echo "<div class='col-md-2'>";
            echo "<label class='form-label'><i class='fas fa-exclamation-triangle me-1'></i>Gravidade</label>";
            echo "<select class='form-select form-select-sm' id='filter-gravidade'>";
            echo "<option value=''>Todas as Gravidades</option>";
            echo "<option value='alta'>Alta</option>";
            echo "<option value='media'>Média</option>";
            echo "<option value='baixa'>Baixa</option>";
            echo "</select>";
            echo "</div>";
            echo "<div class='col-md-2'>";
            echo "<label class='form-label'><i class='fas fa-building me-1'></i>Setor</label>";
            echo "<select class='form-select form-select-sm' id='filter-setor'>";
            echo "<option value=''>Todos os Setores</option>";
            // Buscar setores únicos
            $setores_unicos = array_unique(array_column($rows, 'setor'));
            sort($setores_unicos);
            foreach($setores_unicos as $setor_unico) {
                echo "<option value='" . htmlspecialchars($setor_unico) . "'>" . htmlspecialchars($setor_unico) . "</option>";
            }
            echo "</select>";
            echo "</div>";
            echo "<div class='col-md-2'>";
            echo "<label class='form-label'><i class='fas fa-calendar-alt me-1'></i>Data Início</label>";
            echo "<input type='date' class='form-control form-control-sm' id='filter-data-inicio' onchange='applyFilters()'>";
            echo "</div>";
            echo "<div class='col-md-2'>";
            echo "<label class='form-label'><i class='fas fa-calendar-alt me-1'></i>Data Fim</label>";
            echo "<input type='date' class='form-control form-control-sm' id='filter-data-fim' onchange='applyFilters()'>";
            echo "</div>";
            echo "<div class='col-md-2 d-flex align-items-end'>";
            echo "<button class='btn btn-outline-secondary btn-sm me-2' onclick='clearFilters()'>";
            echo "<i class='fas fa-times me-1'></i>Limpar";
            echo "</button>";
            echo "<button class='btn btn-outline-primary btn-sm' onclick='toggleFilters()'>";
            echo "<i class='fas fa-eye-slash me-1'></i>Ocultar";
            echo "</button>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            echo "</div>";
            
            // Botão para mostrar filtros (inicialmente visível)
            echo "<div id='show-filters-btn' class='mb-3'>";
            echo "<button class='btn btn-outline-primary btn-sm' onclick='toggleFilters()'>";
            echo "<i class='fas fa-filter me-1'></i>Mostrar Filtros";
            echo "</button>";
            echo "</div>";
            
            // Container de Cards
            echo "<div id='cards-view'>";
            echo "<div class='row'>";
            foreach($rows as $row){
                extract($row);
                
                // Formatação da data
                $data_formatada = date('d/m/Y', strtotime($data_abertura));
                $hora_formatada = date('H:i', strtotime($data_abertura));
                
                // Definir cor do card baseado no status
                $card_border = '';
                switch($status) {
                    case 'aberto':
                        $card_border = 'border-info';
                        break;
                    case 'em_andamento':
                        $card_border = 'border-warning';
                        break;
                    case 'fechado':
                        $card_border = 'border-success';
                        break;
                    default:
                        $card_border = 'border-light';
                }
                
                // Adicionar data de abertura como atributo para filtros
                $data_abertura_formatada = date('Y-m-d', strtotime($data_abertura));
                
                echo "<div class='col-lg-4 col-md-6 mb-4 card-item' data-status='{$status}' data-gravidade='{$gravidade}' data-setor='" . htmlspecialchars($setor) . "' data-data='{$data_abertura_formatada}'>";
                echo "<div class='card h-100 chamado-card {$card_border}'>";
                
                // Header do Card
                echo "<div class='card-header d-flex justify-content-between align-items-center'>";
                echo "<div>";
                echo "<small class='text-muted'>#$id</small><br>";
                echo "<a href='view.php?id={$id}' class='codigo-chamado' style='text-decoration: none;'>";
                echo "<code class='fs-6'>{$codigo_chamado}</code>";
                echo "</a>";
                echo "</div>";
                
                // Status badge com ícone específico
                if($status == 'aberto') {
                    echo "<span class='status-badge status-aberto'><i class='fas fa-circle me-1'></i>Aberto</span>";
                } elseif($status == 'em_andamento') {
                    echo "<span class='status-badge status-em_andamento'><i class='fas fa-spinner me-1'></i>Em Andamento</span>";
                } elseif($status == 'fechado') {
                    echo "<span class='status-badge status-fechado'><i class='fas fa-check-circle me-1'></i>Fechado</span>";
                }
                
                echo "</div>";
                
                // Body do Card
                echo "<div class='card-body d-flex flex-column'>";
                
                // Informações principais
                echo "<div class='mb-3'>";
                echo "<div class='d-flex align-items-center mb-2'>";
                echo "<i class='fas fa-user text-muted me-2'></i>";
                echo "<strong>{$nome_colaborador}</strong>";
                echo "</div>";
                echo "<div class='d-flex align-items-center mb-2'>";
                echo "<i class='fas fa-envelope text-muted me-2'></i>";
                echo "<small class='text-muted'>{$email}</small>";
                echo "</div>";
                echo "<div class='d-flex align-items-center mb-2'>";
                echo "<i class='fas fa-building text-muted me-2'></i>";
                echo "<span class='badge bg-light text-dark'>{$setor}</span>";
                echo "</div>";
                echo "<div class='d-flex align-items-center mb-2'>";
                echo "<i class='fas fa-calendar text-muted me-2'></i>";
                echo "<small>Aberto: {$data_formatada} às {$hora_formatada}</small>";
                echo "</div>";
                
                // Se for fechado, mostrar data de fechamento
                if($status == 'fechado' && isset($data_fechamento) && !empty($data_fechamento)) {
                    $data_fechamento_formatada = date('d/m/Y H:i', strtotime($data_fechamento));
                    echo "<div class='d-flex align-items-center mb-2'>";
                    echo "<i class='fas fa-calendar-check text-success me-2'></i>";
                    echo "<small class='text-success'>Fechado: {$data_fechamento_formatada}</small>";
                    echo "</div>";
                }
                
                // Informações de SLA
                if(isset($data_limite_sla) && !empty($data_limite_sla)) {
                    $data_limite_formatada = date('d/m/Y H:i', strtotime($data_limite_sla));
                    $status_sla = $chamado->verificarStatusSLA($data_limite_sla, $status);
                    
                    echo "<div class='d-flex align-items-center mb-2'>";
                    if($status_sla == 'vencido') {
                        echo "<i class='fas fa-exclamation-triangle text-danger me-2'></i>";
                        echo "<small class='text-danger fw-bold'>SLA Vencido: {$data_limite_formatada}</small>";
                    } elseif($status_sla == 'critico') {
                        echo "<i class='fas fa-clock text-warning me-2'></i>";
                        echo "<small class='text-warning fw-bold blink'>SLA Crítico: {$data_limite_formatada}</small>";
                    } elseif($status_sla == 'concluido') {
                        echo "<i class='fas fa-check-circle text-success me-2'></i>";
                        echo "<small class='text-success'>SLA Atendido</small>";
                    } else {
                        echo "<i class='fas fa-clock text-info me-2'></i>";
                        echo "<small class='text-info'>SLA: {$data_limite_formatada}</small>";
                    }
                    echo "</div>";
                }
                
                echo "</div>";
                
                // Descrição
                echo "<div class='mb-3'>";
                echo "<h6 class='card-title mb-2'><i class='fas fa-file-alt text-muted me-1'></i>Descrição</h6>";
                echo "<p class='card-text text-muted'>" . htmlspecialchars(substr($descricao_problema, 0, 100)) . (strlen($descricao_problema) > 100 ? '...' : '') . "</p>";
                echo "</div>";
                
                // Solução (se for fechado e tiver solução)
                if($status == 'fechado' && isset($solucao) && !empty($solucao)) {
                    echo "<div class='mb-3'>";
                    echo "<h6 class='card-title mb-2'><i class='fas fa-tools text-success me-1'></i>Solução</h6>";
                    echo "<p class='card-text text-success'>" . htmlspecialchars(substr($solucao, 0, 100)) . (strlen($solucao) > 100 ? '...' : '') . "</p>";
                    echo "</div>";
                }
                
                // Gravidade
                echo "<div class='mb-3'>";
                echo "<span class='gravidade-{$gravidade}'><i class='fas fa-exclamation-triangle me-1'></i>" . ucfirst($gravidade) . "</span>";
                echo "</div>";
                
                // Status específico
                echo "<div class='mb-3'>";
                echo "<div class='d-flex align-items-center'>";
                if($status == 'aberto') {
                    echo "<i class='fas fa-clock text-info me-2'></i>";
                    echo "<small class='text-info fw-bold'>AGUARDANDO ATENDIMENTO</small>";
                } elseif($status == 'em_andamento') {
                    echo "<i class='fas fa-cog fa-spin text-warning me-2'></i>";
                    echo "<small class='text-warning fw-bold'>EM ATENDIMENTO</small>";
                } elseif($status == 'fechado') {
                    echo "<i class='fas fa-check-double text-success me-2'></i>";
                    echo "<small class='text-success fw-bold'>RESOLVIDO</small>";
                }
                echo "</div>";
                echo "</div>";
                
                echo "</div>";
                
                // Footer do Card com ações
                echo "<div class='card-footer bg-transparent border-top-0'>";
                echo "<div class='btn-group w-100' role='group'>";
                echo "<a href='view.php?id={$id}' class='btn btn-outline-info btn-sm'>";
                echo "<i class='fas fa-eye me-1'></i>Ver";
                echo "</a>";
                echo "<a href='edit.php?id={$id}' class='btn btn-outline-warning btn-sm'>";
                echo "<i class='fas fa-edit me-1'></i>Editar";
                echo "</a>";
                echo "<a href='delete.php?id={$id}' class='btn btn-outline-danger btn-sm' onclick='return confirm(\"Tem certeza que deseja excluir este chamado?\")'>";
                echo "<i class='fas fa-trash me-1'></i>Excluir";
                echo "</a>";
                echo "</div>";
                echo "</div>";
                
                echo "</div>"; // Fim do card
                echo "</div>"; // Fim da coluna
            }
            
            echo "</div>"; // Fim da row
            echo "</div>"; // Fim do cards-view
            
            // Container de Lista (Tabela) - inicialmente oculto
            echo "<div id='list-view' style='display: none;'>";
            echo "<div class='table-container'>";
            echo "<div class='table-responsive' style='overflow-x: auto; white-space: nowrap;'>";
            echo "<table class='table table-hover mb-0' style='min-width: 1800px;'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th style='min-width: 60px;' class='text-center'>#</th>";
            echo "<th style='min-width: 160px;'>Código</th>";
            echo "<th style='min-width: 150px;'>Colaborador</th>";
            echo "<th style='min-width: 180px;'>Email</th>";
            echo "<th style='min-width: 120px;'>Setor</th>";
            echo "<th style='min-width: 200px;'>Descrição</th>";
            echo "<th style='min-width: 200px;'>Solução</th>";
            echo "<th style='min-width: 130px;' class='text-center'>Data Abertura</th>";
            echo "<th style='min-width: 130px;' class='text-center'>Data Fechamento</th>";
            echo "<th style='min-width: 150px;' class='text-center'>SLA</th>";
            echo "<th style='min-width: 100px;' class='text-center'>Gravidade</th>";
            echo "<th style='min-width: 120px;' class='text-center'>Status</th>";
            echo "<th style='min-width: 180px;' class='text-center'>Ações</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            foreach($rows as $row){
                extract($row);
                
                // Formatação da data para tabela
                $data_formatada = date('d/m/Y', strtotime($data_abertura));
                $hora_formatada = date('H:i', strtotime($data_abertura));
                
                // Formatação da data de fechamento
                $data_fechamento_formatada = '';
                $hora_fechamento_formatada = '';
                if($status == 'fechado' && isset($data_fechamento) && !empty($data_fechamento)) {
                    $data_fechamento_formatada = date('d/m/Y', strtotime($data_fechamento));
                    $hora_fechamento_formatada = date('H:i', strtotime($data_fechamento));
                }
                
                // Truncar descrição e solução de forma inteligente
                $descricao_curta = strlen($descricao_problema) > 50 ? 
                    substr($descricao_problema, 0, 50) . '...' : 
                    $descricao_problema;
                
                $solucao_curta = '';
                if(isset($solucao) && !empty($solucao)) {
                    $solucao_curta = strlen($solucao) > 50 ? 
                        substr($solucao, 0, 50) . '...' : 
                        $solucao;
                }
                
                // Adicionar data de abertura como atributo para filtros
                $data_abertura_attr = date('Y-m-d', strtotime($data_abertura));
                
                echo "<tr class='table-row' data-status='{$status}' data-gravidade='{$gravidade}' data-setor='" . htmlspecialchars($setor) . "' data-data='{$data_abertura_attr}'>";
                echo "<td class='text-center fw-bold text-muted'>{$id}</td>";
                echo "<td>";
                echo "<a href='view.php?id={$id}' class='codigo-chamado' style='text-decoration: none;'>";
                echo "<code class='d-block'>{$codigo_chamado}</code>";
                echo "</a>";
                echo "</td>";
                echo "<td class='fw-medium' style='white-space: nowrap;'>{$nome_colaborador}</td>";
                echo "<td class='text-muted' style='white-space: nowrap;'><small>{$email}</small></td>";
                echo "<td><span class='badge bg-light text-dark border'>{$setor}</span></td>";
                echo "<td style='white-space: normal; word-wrap: break-word; max-width: 200px;'>";
                echo "<div class='text-truncate' title='" . htmlspecialchars($descricao_problema) . "'>";
                echo htmlspecialchars($descricao_curta);
                echo "</div>";
                echo "</td>";
                echo "<td style='white-space: normal; word-wrap: break-word; max-width: 200px;'>";
                if($status == 'fechado' && !empty($solucao_curta)) {
                    echo "<div class='text-truncate text-success' title='" . htmlspecialchars($solucao) . "'>";
                    echo htmlspecialchars($solucao_curta);
                    echo "</div>";
                } else {
                    echo "<small class='text-muted'>-</small>";
                }
                echo "</td>";
                echo "<td class='text-center'>";
                echo "<div class='small'>";
                echo "<div class='fw-medium'>{$data_formatada}</div>";
                echo "<div class='text-muted'>{$hora_formatada}</div>";
                echo "</div>";
                echo "</td>";
                echo "<td class='text-center'>";
                if($status == 'fechado' && !empty($data_fechamento_formatada)) {
                    echo "<div class='small text-success'>";
                    echo "<div class='fw-medium'>{$data_fechamento_formatada}</div>";
                    echo "<div class='text-muted'>{$hora_fechamento_formatada}</div>";
                    echo "</div>";
                } else {
                    echo "<small class='text-muted'>-</small>";
                }
                echo "</td>";
                
                // Coluna SLA
                echo "<td class='text-center'>";
                if(isset($data_limite_sla) && !empty($data_limite_sla)) {
                    $data_limite_formatada = date('d/m/Y H:i', strtotime($data_limite_sla));
                    $status_sla = $chamado->verificarStatusSLA($data_limite_sla, $status);
                    
                    echo "<div class='small'>";
                    if($status_sla == 'vencido') {
                        echo "<div class='text-danger fw-bold'>";
                        echo "<i class='fas fa-exclamation-triangle me-1'></i>";
                        echo "VENCIDO";
                        echo "</div>";
                        echo "<div class='text-danger'>{$data_limite_formatada}</div>";
                    } elseif($status_sla == 'critico') {
                        echo "<div class='text-warning fw-bold blink'>";
                        echo "<i class='fas fa-clock me-1'></i>";
                        echo "CRÍTICO";
                        echo "</div>";
                        echo "<div class='text-warning'>{$data_limite_formatada}</div>";
                    } elseif($status_sla == 'concluido') {
                        echo "<div class='text-success fw-bold'>";
                        echo "<i class='fas fa-check-circle me-1'></i>";
                        echo "ATENDIDO";
                        echo "</div>";
                        echo "<div class='text-success'>{$data_limite_formatada}</div>";
                    } else {
                        echo "<div class='text-info'>";
                        echo "<i class='fas fa-clock me-1'></i>";
                        echo "OK";
                        echo "</div>";
                        echo "<div class='text-info'>{$data_limite_formatada}</div>";
                    }
                    echo "</div>";
                } else {
                    echo "<small class='text-muted'>-</small>";
                }
                echo "</td>";
                
                echo "<td class='text-center'><span class='gravidade-{$gravidade}'>" . ucfirst($gravidade) . "</span></td>";
                
                // Status com ícone específico
                echo "<td class='text-center'>";
                if($status == 'aberto') {
                    echo "<span class='status-badge status-aberto'><i class='fas fa-circle me-1'></i>Aberto</span>";
                } elseif($status == 'em_andamento') {
                    echo "<span class='status-badge status-em_andamento'><i class='fas fa-spinner me-1'></i>Em Andamento</span>";
                } elseif($status == 'fechado') {
                    echo "<span class='status-badge status-fechado'><i class='fas fa-check-circle me-1'></i>Fechado</span>";
                }
                echo "</td>";
                
                echo "<td class='text-center'>";
                echo "<div class='btn-group btn-group-sm' role='group'>";
                echo "<a href='view.php?id={$id}' class='btn btn-outline-info' title='Visualizar'><i class='fas fa-eye'></i></a>";
                echo "<a href='edit.php?id={$id}' class='btn btn-outline-warning' title='Editar'><i class='fas fa-edit'></i></a>";
                echo "<a href='delete.php?id={$id}' class='btn btn-outline-danger' title='Excluir' onclick='return confirm(\"Tem certeza que deseja excluir este chamado?\")'><i class='fas fa-trash'></i></a>";
                echo "</div>";
                echo "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            echo "</div>";
            echo "</div>"; // Fim do list-view
            
            // Rodapé com informações
            echo "<div class='d-flex justify-content-center align-items-center mt-4 p-3 bg-light rounded'>";
            echo "<small class='text-muted'><i class='fas fa-info-circle me-2'></i>Total de <strong>{$num}</strong> chamado(s) encontrado(s)</small>";
            echo "</div>";
            
        } else {
            if(isset($_GET['pesquisa']) && !empty($_GET['pesquisa'])){
                echo "<div class='alert alert-warning d-flex align-items-center'>";
                echo "<i class='fas fa-exclamation-triangle me-3'></i>";
                echo "<div>";
                echo "<strong>Nenhum resultado encontrado</strong><br>";
                echo "<small>Tente ajustar os termos da pesquisa ou <a href='index.php' class='alert-link'>ver todos os chamados</a></small>";
                echo "</div>";
                echo "</div>";
            } else {
                echo "<div class='alert alert-info d-flex align-items-center'>";
                echo "<i class='fas fa-info-circle me-3'></i>";
                echo "<div>";
                echo "<strong>Nenhum chamado cadastrado</strong><br>";
                echo "<small><a href='add.php' class='alert-link'>Clique aqui para criar o primeiro chamado</a></small>";
                echo "</div>";
                echo "</div>";
            }
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleView(viewType) {
            const cardsView = document.getElementById('cards-view');
            const listView = document.getElementById('list-view');
            const btnCards = document.getElementById('btn-cards');
            const btnList = document.getElementById('btn-list');
            
            if (viewType === 'cards') {
                cardsView.style.display = 'block';
                listView.style.display = 'none';
                btnCards.classList.add('active');
                btnList.classList.remove('active');
                localStorage.setItem('viewPreference', 'cards');
            } else {
                cardsView.style.display = 'none';
                listView.style.display = 'block';
                btnList.classList.add('active');
                btnCards.classList.remove('active');
                localStorage.setItem('viewPreference', 'list');
            }
        }
        
        // Carregar preferência salva
        document.addEventListener('DOMContentLoaded', function() {
            const savedView = localStorage.getItem('viewPreference') || 'cards';
            toggleView(savedView);
            
            // Configurar indicador de scroll para tabela
            const tableContainer = document.querySelector('.table-responsive');
            const scrollIndicator = document.getElementById('scroll-indicator');
            
            if (tableContainer && scrollIndicator) {
                // Verificar se precisa mostrar o indicador
                function checkScrollIndicator() {
                    const needsScroll = tableContainer.scrollWidth > tableContainer.clientWidth;
                    const isAtEnd = tableContainer.scrollLeft >= (tableContainer.scrollWidth - tableContainer.clientWidth - 5);
                    
                    if (needsScroll && !isAtEnd) {
                        scrollIndicator.classList.remove('d-none');
                    } else {
                        scrollIndicator.classList.add('d-none');
                    }
                }
                
                // Verificar quando carregar e quando redimensionar
                checkScrollIndicator();
                window.addEventListener('resize', checkScrollIndicator);
                
                // Esconder indicador quando fizer scroll
                tableContainer.addEventListener('scroll', function() {
                    setTimeout(checkScrollIndicator, 100);
                });
                
                // Também verificar quando mudar de view
                document.getElementById('btn-list').addEventListener('click', function() {
                    setTimeout(checkScrollIndicator, 100);
                });
            }
            
            // Inicializar filtros
            initializeFilters();
        });
        
        function toggleFilters() {
            const filtersContainer = document.getElementById('filters-container');
            const showFiltersBtn = document.getElementById('show-filters-btn');
            const toggleBtn = document.querySelector('#filters-container button[onclick="toggleFilters()"]');
            
            if (filtersContainer.style.display === 'none') {
                filtersContainer.style.display = 'block';
                showFiltersBtn.style.display = 'none';
                toggleBtn.innerHTML = '<i class="fas fa-eye-slash me-1"></i>Ocultar';
            } else {
                filtersContainer.style.display = 'none';
                showFiltersBtn.style.display = 'block';
                toggleBtn.innerHTML = '<i class="fas fa-eye me-1"></i>Mostrar';
            }
        }
        
        function initializeFilters() {
            const filterStatus = document.getElementById('filter-status');
            const filterGravidade = document.getElementById('filter-gravidade');
            const filterSetor = document.getElementById('filter-setor');
            const filterDataInicio = document.getElementById('filter-data-inicio');
            const filterDataFim = document.getElementById('filter-data-fim');
            
            // Adicionar event listeners
            [filterStatus, filterGravidade, filterSetor, filterDataInicio, filterDataFim].forEach(filter => {
                if (filter) {
                    filter.addEventListener('change', applyFilters);
                }
            });
        }
        
        function applyFilters() {
            const statusFilter = document.getElementById('filter-status').value;
            const gravidadeFilter = document.getElementById('filter-gravidade').value;
            const setorFilter = document.getElementById('filter-setor').value;
            const dataInicioFilter = document.getElementById('filter-data-inicio').value;
            const dataFimFilter = document.getElementById('filter-data-fim').value;
            
            // Filtrar cards
            const cardItems = document.querySelectorAll('.card-item');
            let visibleCards = 0;
            
            cardItems.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                const cardGravidade = card.getAttribute('data-gravidade');
                const cardSetor = card.getAttribute('data-setor');
                const cardData = card.getAttribute('data-data');
                
                let showCard = true;
                
                if (statusFilter && cardStatus !== statusFilter) showCard = false;
                if (gravidadeFilter && cardGravidade !== gravidadeFilter) showCard = false;
                if (setorFilter && cardSetor !== setorFilter) showCard = false;
                
                // Filtro por data
                if (dataInicioFilter && cardData < dataInicioFilter) showCard = false;
                if (dataFimFilter && cardData > dataFimFilter) showCard = false;
                
                if (showCard) {
                    card.style.display = 'block';
                    visibleCards++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Filtrar linhas da tabela
            const tableRows = document.querySelectorAll('.table-row');
            let visibleRows = 0;
            
            tableRows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                const rowGravidade = row.getAttribute('data-gravidade');
                const rowSetor = row.getAttribute('data-setor');
                const rowData = row.getAttribute('data-data');
                
                let showRow = true;
                
                if (statusFilter && rowStatus !== statusFilter) showRow = false;
                if (gravidadeFilter && rowGravidade !== gravidadeFilter) showRow = false;
                if (setorFilter && rowSetor !== setorFilter) showRow = false;
                
                // Filtro por data
                if (dataInicioFilter && rowData < dataInicioFilter) showRow = false;
                if (dataFimFilter && rowData > dataFimFilter) showRow = false;
                
                if (showRow) {
                    row.style.display = 'table-row';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Atualizar contador
            updateCounter(Math.max(visibleCards, visibleRows));
        }
        
        function clearFilters() {
            document.getElementById('filter-status').value = '';
            document.getElementById('filter-gravidade').value = '';
            document.getElementById('filter-setor').value = '';
            document.getElementById('filter-data-inicio').value = '';
            document.getElementById('filter-data-fim').value = '';
            
            // Mostrar todos os itens
            document.querySelectorAll('.card-item').forEach(card => {
                card.style.display = 'block';
            });
            
            document.querySelectorAll('.table-row').forEach(row => {
                row.style.display = 'table-row';
            });
            
            // Resetar contador
            const totalItems = document.querySelectorAll('.card-item').length;
            updateCounter(totalItems);
        }
        
        function updateCounter(count) {
            const badge = document.querySelector('.badge.bg-primary');
            if (badge) {
                badge.textContent = count;
            }
        }
    </script>
</body>
</html>

