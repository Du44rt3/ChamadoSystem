<?php
// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

// Configurações da página
$page_title = "Chamados Fechados";
$page_subtitle = "Chamados resolvidos e finalizados";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chamados Fechados - ELUS Facilities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
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
        
        /* Estilos para SLA */
        .sla-prazo {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.85em;
        }
        
        .sla-prazo.normal {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .sla-prazo.atencao {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .sla-prazo.critico {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Animação para casos críticos */
        @keyframes blink {
            0%, 50% {
                opacity: 1;
            }
            51%, 100% {
                opacity: 0.3;
            }
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
<body>
    <?php 
    // Incluir header moderno
    require_once '../src/header.php'; 
    ?>

    <div class="container-fluid mt-4">
        
        <!-- Barra de Pesquisa -->
        <div class="search-bar">
            <div class="row">
                <div class="col-md-10">
                    <form method="GET" action="fechados.php" class="d-flex">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" name="pesquisa" class="form-control border-start-0" 
                                   placeholder="Pesquisar por código, colaborador, setor ou descrição..." 
                                   value="<?php echo isset($_GET['pesquisa']) ? htmlspecialchars($_GET['pesquisa']) : ''; ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search me-1"></i>Pesquisar
                            </button>
                            <?php if(isset($_GET['pesquisa']) && !empty($_GET['pesquisa'])): ?>
                                <a href="fechados.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Limpar
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <div class="col-md-2 text-end">
                    <a href="add.php" class="btn btn-elus w-100">
                        <i class="fas fa-plus me-1"></i>Novo Chamado
                    </a>
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
            $stmt = $chamado->searchByStatus($_GET['pesquisa'], 'fechado');
            $termo_pesquisa = htmlspecialchars($_GET['pesquisa']);
            echo "<div class='alert alert-info d-flex align-items-center'>";
            echo "<i class='fas fa-search me-2'></i>";
            echo "Resultados para: <strong>$termo_pesquisa</strong>";
            echo "</div>";
        } else {
            $stmt = $chamado->readByStatus('fechado');
        }

        $num = $stmt->rowCount();

        if($num > 0){
            // Header com contador de chamados e opções de visualização
            echo "<div class='d-flex justify-content-between align-items-center mb-4'>";
            echo "<h5 class='mb-0'><i class='fas fa-check-circle me-2 text-success'></i>Chamados Fechados <span class='badge bg-success ms-2'>{$num}</span></h5>";
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
            
            // Container de Cards
            echo "<div id='cards-view'>";
            echo "<div class='row'>";

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($rows as $row){
                extract($row);
                
                // Formatação da data
                $data_formatada = date('d/m/Y', strtotime($data_abertura));
                $hora_formatada = date('H:i', strtotime($data_abertura));
                
                // Formatação da data de fechamento (se existir)
                $data_fechamento_formatada = '';
                if(isset($data_fechamento) && !empty($data_fechamento)) {
                    $data_fechamento_formatada = date('d/m/Y H:i', strtotime($data_fechamento));
                }
                
                // Calcular SLA
                $sla_info = $chamado->verificarSLA($id);
                $sla_real = $sla_info['sla_real'];
                $status_sla = $sla_info['status_sla'];
                $data_limite_sla = $sla_info['data_limite_sla'];
                
                // Cards fechados têm borda verde
                $card_border = 'border-success';
                
                echo "<div class='col-lg-4 col-md-6 mb-4'>";
                echo "<div class='card h-100 chamado-card {$card_border}'>";
                
                // Header do Card com status fechado
                echo "<div class='card-header d-flex justify-content-between align-items-center'>";
                echo "<div>";
                echo "<small class='text-muted'>#$id</small><br>";
                echo "<a href='view.php?id={$id}' class='codigo-chamado' style='text-decoration: none;'>";
                echo "<code class='fs-6'>{$codigo_chamado}</code>";
                echo "</a>";
                echo "</div>";
                echo "<span class='status-badge status-fechado'><i class='fas fa-check-circle me-1'></i>Fechado</span>";
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
                if(!empty($data_fechamento_formatada)) {
                    echo "<div class='d-flex align-items-center mb-2'>";
                    echo "<i class='fas fa-calendar-check text-success me-2'></i>";
                    echo "<small class='text-success'>Fechado: {$data_fechamento_formatada}</small>";
                    echo "</div>";
                }
                echo "</div>";
                
                // Descrição
                echo "<div class='mb-3'>";
                echo "<h6 class='card-title mb-2'><i class='fas fa-file-alt text-muted me-1'></i>Descrição</h6>";
                echo "<p class='card-text text-muted'>" . htmlspecialchars(substr($descricao_problema, 0, 100)) . (strlen($descricao_problema) > 100 ? '...' : '') . "</p>";
                echo "</div>";
                
                // Solução (se existir)
                if(isset($solucao) && !empty($solucao)) {
                    echo "<div class='mb-3'>";
                    echo "<h6 class='card-title mb-2'><i class='fas fa-tools text-success me-1'></i>Solução</h6>";
                    echo "<p class='card-text text-success'>" . htmlspecialchars(substr($solucao, 0, 100)) . (strlen($solucao) > 100 ? '...' : '') . "</p>";
                    echo "</div>";
                }
                
                // Gravidade
                echo "<div class='mb-3'>";
                echo "<span class='gravidade-{$gravidade}'><i class='fas fa-exclamation-triangle me-1'></i>" . ucfirst($gravidade) . "</span>";
                echo "</div>";
                
                // SLA (histórico)
                echo "<div class='mb-3'>";
                echo "<div class='sla-info'>";
                echo "<div class='d-flex align-items-center mb-2'>";
                echo "<i class='fas fa-clock text-muted me-2'></i>";
                echo "<strong>SLA Final:</strong>";
                echo "<span class='ms-2 sla-prazo {$status_sla}'>";
                echo $sla_real;
                echo "</span>";
                echo "</div>";
                if($data_limite_sla) {
                    echo "<div class='d-flex align-items-center mb-2'>";
                    echo "<i class='fas fa-calendar-alt text-muted me-2'></i>";
                    echo "<small class='text-muted'>Prazo era: " . date('d/m/Y H:i', strtotime($data_limite_sla)) . "</small>";
                    echo "</div>";
                }
                echo "</div>";
                echo "</div>";
                
                // Informações de fechamento
                echo "<div class='mb-3'>";
                echo "<div class='d-flex align-items-center'>";
                echo "<i class='fas fa-check-circle text-success me-2'></i>";
                echo "<small class='text-success fw-bold'>RESOLVIDO</small>";
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
            echo "<table class='table table-hover mb-0' style='min-width: 1500px;'>";
            echo "<thead>";
            echo "<tr>";
            echo "<th style='min-width: 60px;' class='text-center'>#</th>";
            echo "<th style='min-width: 160px;'>Código</th>";
            echo "<th style='min-width: 150px;'>Colaborador</th>";
            echo "<th style='min-width: 180px;'>Email</th>";
            echo "<th style='min-width: 120px;'>Setor</th>";
            echo "<th style='min-width: 250px;'>Descrição</th>";
            echo "<th style='min-width: 130px;' class='text-center'>Data/Hora</th>";
            echo "<th style='min-width: 100px;' class='text-center'>Gravidade</th>";
            echo "<th style='min-width: 150px;' class='text-center'>SLA</th>";
            echo "<th style='min-width: 120px;' class='text-center'>Status</th>";
            echo "<th style='min-width: 150px;' class='text-center'>Solução</th>";
            echo "<th style='min-width: 180px;' class='text-center'>Ações</th>";
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";

            foreach($rows as $row){
                extract($row);
                
                // Formatação da data para tabela
                $data_formatada = date('d/m/Y', strtotime($data_abertura));
                $hora_formatada = date('H:i', strtotime($data_abertura));
                
                // Calcular SLA
                $sla_info = $chamado->verificarSLA($id);
                $sla_real = $sla_info['sla_real'];
                $status_sla = $sla_info['status_sla'];
                $data_limite_sla = $sla_info['data_limite_sla'];
                
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
                
                echo "<tr>";
                echo "<td class='text-center fw-bold text-muted'>{$id}</td>";
                echo "<td>";
                echo "<a href='view.php?id={$id}' class='codigo-chamado' style='text-decoration: none;'>";
                echo "<code class='d-block'>{$codigo_chamado}</code>";
                echo "</a>";
                echo "</td>";
                echo "<td class='fw-medium' style='white-space: nowrap;'>{$nome_colaborador}</td>";
                echo "<td class='text-muted' style='white-space: nowrap;'><small>{$email}</small></td>";
                echo "<td><span class='badge bg-light text-dark border'>{$setor}</span></td>";
                echo "<td style='white-space: normal; word-wrap: break-word; max-width: 250px;'>";
                echo "<div class='text-truncate' title='" . htmlspecialchars($descricao_problema) . "'>";
                echo htmlspecialchars($descricao_curta);
                echo "</div>";
                echo "</td>";
                echo "<td class='text-center'>";
                echo "<div class='small'>";
                echo "<div class='fw-medium'>{$data_formatada}</div>";
                echo "<div class='text-muted'>{$hora_formatada}</div>";
                echo "</div>";
                echo "</td>";
                echo "<td class='text-center'><span class='gravidade-{$gravidade}'>" . ucfirst($gravidade) . "</span></td>";
                echo "<td class='text-center'>";
                echo "<span class='sla-prazo {$status_sla}'>";
                echo $sla_real;
                echo "</span>";
                if($data_limite_sla) {
                    echo "<br><small class='text-muted'>" . date('d/m H:i', strtotime($data_limite_sla)) . "</small>";
                }
                echo "</td>";
                echo "<td class='text-center'><span class='status-badge status-fechado'><i class='fas fa-check-circle me-1'></i>Fechado</span></td>";
                echo "<td class='text-center'>";
                if(!empty($solucao_curta)) {
                    echo "<div class='text-truncate' title='" . htmlspecialchars($solucao) . "'>";
                    echo "<small class='text-success'>" . htmlspecialchars($solucao_curta) . "</small>";
                    echo "</div>";
                } else {
                    echo "<small class='text-muted'>-</small>";
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
            echo "<div class='d-flex justify-content-center align-items-center mt-4 p-3 bg-success bg-opacity-10 border border-success rounded'>";
            echo "<small class='text-success'><i class='fas fa-check-circle me-2'></i>Total de <strong>{$num}</strong> chamado(s) resolvido(s)</small>";
            echo "</div>";
            
        } else {
            if(isset($_GET['pesquisa']) && !empty($_GET['pesquisa'])){
                echo "<div class='alert alert-warning d-flex align-items-center'>";
                echo "<i class='fas fa-exclamation-triangle me-3'></i>";
                echo "<div>";
                echo "<strong>Nenhum chamado fechado encontrado</strong><br>";
                echo "<small>Tente ajustar os termos da pesquisa ou <a href='fechados.php' class='alert-link'>ver todos os chamados fechados</a></small>";
                echo "</div>";
                echo "</div>";
            } else {
                echo "<div class='alert alert-info d-flex align-items-center'>";
                echo "<i class='fas fa-info-circle me-3'></i>";
                echo "<div>";
                echo "<strong>Nenhum chamado fechado ainda</strong><br>";
                echo "<small>Quando os chamados forem resolvidos, aparecerão aqui. <a href='index.php' class='alert-link'>Ver todos os chamados</a></small>";
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
        });
    </script>
</body>
</html>