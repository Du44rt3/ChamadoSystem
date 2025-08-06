<?php
// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

// Configurações da página
$page_title = "Visualizar Chamado";
$page_subtitle = "Detalhes e histórico do chamado";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Chamado - ELUS Facilities</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="../css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <!-- Página carregada em: <?php echo date('Y-m-d H:i:s'); ?> -->
    <style>
        /* CSS inline para forçar aplicação das mudanças nos anexos */
        .anexo-image {
            width: 100% !important;
            height: 50px !important;
            object-fit: cover !important;
            border-radius: 4px 4px 0 0 !important;
        }
        
        .anexo-info {
            padding: 0.25rem !important;
        }
        
        .anexo-title {
            font-size: 0.7rem !important;
            margin-bottom: 0.15rem !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            line-height: 1.2 !important;
        }
        
        .anexo-meta {
            font-size: 0.6rem !important;
            margin-bottom: 0.2rem !important;
            color: #666 !important;
        }
        
        .anexo-actions {
            justify-content: center !important;
            gap: 0.1rem !important;
            margin-top: 0.2rem !important;
        }
        
        .anexo-actions .btn {
            padding: 0.05rem 0.2rem !important;
            font-size: 0.55rem !important;
            min-width: 16px !important;
            height: 16px !important;
            border-radius: 1px !important;
            line-height: 1 !important;
        }
        
        .anexos-gallery .col-xl-2,
        .anexos-gallery .col-lg-3,
        .anexos-gallery .col-md-4,
        .anexos-gallery .col-sm-6,
        .anexos-gallery .col-6 {
            padding: 0.25rem !important;
            margin-bottom: 0.3rem !important;
        }
        
        .anexos-gallery .row {
            margin: -0.25rem !important;
        }
        
        .anexo-card {
            box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
            border: 1px solid #e0e0e0 !important;
            background: #fff !important;
        }
        
        /* Modo compacto ainda menor */
        .anexos-gallery.compact-mode .anexo-image {
            height: 35px !important;
        }
        
        .anexos-gallery.compact-mode .anexo-info {
            padding: 0.15rem !important;
        }
        
        .anexos-gallery.compact-mode .anexo-title {
            font-size: 0.6rem !important;
        }
        
        .anexos-gallery.compact-mode .anexo-meta {
            display: none !important;
        }
        
        .anexos-gallery.compact-mode .anexo-actions .btn {
            min-width: 14px !important;
            height: 14px !important;
            font-size: 0.5rem !important;
        }
        
        @media (max-width: 768px) {
            .anexo-image {
                height: 45px !important;
            }
            
            .anexos-gallery.compact-mode .anexo-image {
                height: 30px !important;
            }
            
            .anexo-actions .btn {
                min-width: 14px !important;
                height: 14px !important;
                font-size: 0.5rem !important;
            }
        }
        
        /* Botões do header mais compactos */
        .card-header .btn {
            padding: 0.5rem 1rem !important;
            font-size: 0.9rem !important;
            line-height: 1.5 !important;
            min-width: auto !important;
            height: auto !important;
            border-radius: 0.375rem !important;
        }
        
        .card-header .d-flex.gap-2 {
            gap: 0.75rem !important;
        }
        
        /* Botão de adicionar atividade específico */
        .btn-success {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 4px rgba(40, 167, 69, 0.2) !important;
            transition: all 0.2s ease !important;
        }
        
        .btn-success:hover {
            background-color: #218838 !important;
            border-color: #218838 !important;
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.3) !important;
            transform: translateY(-1px) !important;
        }
        
        /* Botão toggle mais visível */
        .btn-outline-light {
            border-width: 1px !important;
            font-weight: 500 !important;
            background-color: rgba(255, 255, 255, 0.1) !important;
        }
        
        .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.2) !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
        }
    </style>
</head>
<body>
    <?php 
    // Incluir header moderno
    require_once '../src/header.php'; 
    ?>

    <div class="container-fluid mt-4">
        <?php
        // Exibir mensagens de sucesso ou erro
        if (isset($_GET['success'])) {
            $mensagem = '';
            switch($_GET['success']) {
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
            }
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> ' . $mensagem . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
        } elseif (isset($_GET['error'])) {
            $erro = '';
            switch($_GET['error']) {
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
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> ' . $erro . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
        }
        
        $id = isset($_GET['id']) ? $_GET['id'] : die('ID não especificado.');

        include_once '../src/DB.php';
        include_once '../src/Chamado.php';
        include_once '../src/ChamadoHistorico.php';
        include_once '../src/EmailTemplate.php';
        include_once '../src/ChamadoAnexo.php';

        $database = new DB();
        $db = $database->getConnection();
        $chamado = new Chamado($db);
        $historico = new ChamadoHistorico($db);
        $anexo = new ChamadoAnexo($db);

        $chamado->id = $id;
        $chamado->readOne();
        
        // Buscar histórico do chamado
        $atividades = $historico->buscarHistoricoCompleto($id);
        
        // Buscar anexos do chamado
        $anexos = $anexo->buscarPorChamado($id);
        ?>

        <div class="card">
            <div class="card-header">
                <h2>Chamado #<?php echo $chamado->id; ?></h2>
                <h5><code><?php echo $chamado->codigo_chamado; ?></code></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Código:</strong> <code><?php echo $chamado->codigo_chamado; ?></code></p>
                        <p><strong>Setor:</strong> <?php echo $chamado->setor; ?></p>
                        <p><strong>Usuário:</strong> <?php echo $chamado->nome_colaborador; ?></p>
                        <?php if(!empty($chamado->email)): ?>
                        <p><strong>Email:</strong> <?php echo $chamado->email; ?></p>
                        <?php endif; ?>
                        <p><strong>Nome do Projeto:</strong> <?php echo $chamado->nome_projeto; ?></p>
                        <p><strong>Descrição do Problema:</strong></p>
                        <p><?php echo nl2br($chamado->descricao_problema); ?></p>
                        <p><strong>Prioridade:</strong> <span class="gravidade-<?php echo $chamado->gravidade; ?>"><?php echo ucfirst($chamado->gravidade); ?></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Data de Abertura:</strong> <?php echo EmailTemplate::formatarDataHoraPadrao($chamado->data_abertura); ?></p>
                        <p><strong>Status do Chamado:</strong> <?php echo ucfirst(str_replace('_', ' ', $chamado->status)); ?></p>
                        
                        <?php if($chamado->solucao): ?>
                        <p><strong>Solução:</strong></p>
                        <p><?php echo nl2br($chamado->solucao); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="edit.php?id=<?php echo $chamado->id; ?>" class="btn btn-warning">Editar</a>
                <a href="delete.php?id=<?php echo $chamado->id; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este chamado?')">Excluir</a>
                <?php if(!empty($chamado->email)): ?>
                <a href="email_template.php?id=<?php echo $chamado->id; ?>&tipo=auto" class="btn btn-info">
                    <i class="fas fa-envelope"></i> Template de Email
                </a>
                <?php endif; ?>
                <a href="index.php" class="btn btn-secondary">Voltar</a>
            </div>
        </div>

        <!-- Seção de Anexos -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-paperclip"></i> Anexos de Imagem 
                    <?php if (!empty($anexos)): ?>
                        <span class="anexos-count"><?php echo count($anexos); ?></span>
                    <?php endif; ?>
                </h5>
                <div class="d-flex gap-2">
                    <?php if (!empty($anexos)): ?>
                    <button type="button" class="btn btn-outline-light" onclick="toggleViewMode()" id="viewModeBtn" title="Alternar visualização">
                        <i class="fas fa-th me-1"></i> <span class="d-none d-sm-inline">Modo</span>
                    </button>
                    <?php endif; ?>
                    <a href="adicionar_anexos.php?chamado_id=<?php echo $chamado->id; ?>" class="btn btn-success">
                        <i class="fas fa-plus-circle me-1"></i> Adicionar Anexos
                    </a>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($anexos)): ?>
                <div class="anexos-gallery">
                    <div class="row">
                        <?php foreach ($anexos as $anexo_item): ?>
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                            <div class="anexo-card">
                                <img src="../<?php echo htmlspecialchars($anexo_item['caminho_arquivo']); ?>" 
                                     class="anexo-image" 
                                     onclick="abrirModalImagem('<?php echo htmlspecialchars($anexo_item['caminho_arquivo']); ?>', '<?php echo htmlspecialchars($anexo_item['nome_original']); ?>')"
                                     alt="<?php echo htmlspecialchars($anexo_item['nome_original']); ?>">
                                <div class="anexo-info">
                                    <h6 class="anexo-title" title="<?php echo htmlspecialchars($anexo_item['nome_original']); ?>">
                                        <?php echo htmlspecialchars($anexo_item['nome_original']); ?>
                                    </h6>
                                    <div class="anexo-meta">
                                        <i class="fas fa-weight-hanging"></i> 
                                        <span><?php echo ChamadoAnexo::formatarTamanho($anexo_item['tamanho_arquivo']); ?></span>
                                    </div>
                                    <div class="anexo-actions">
                                        <a href="../<?php echo htmlspecialchars($anexo_item['caminho_arquivo']); ?>" 
                                           class="btn btn-primary btn-sm" target="_blank" title="Ver imagem">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="download_anexo.php?id=<?php echo $anexo_item['id']; ?>" 
                                           class="btn btn-success btn-sm" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" 
                                                onclick="confirmarExclusaoAnexo(<?php echo $anexo_item['id']; ?>, '<?php echo htmlspecialchars($anexo_item['nome_original']); ?>')"
                                                title="Excluir anexo">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-center py-4">
                    <p class="text-muted mb-3">
                        <i class="fas fa-images opacity-50 fs-4"></i><br>
                        <span class="mt-2 d-inline-block">Nenhuma imagem anexada</span>
                    </p>
                    <a href="adicionar_anexos.php?chamado_id=<?php echo $chamado->id; ?>" class="btn btn-success">
                        <i class="fas fa-plus-circle me-1"></i> Adicionar Primeira Imagem
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Seção de Histórico -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white"><i class="fas fa-history"></i> Histórico de Atividades</h5>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#novaAtividadeModal">
                    <i class="fas fa-plus-circle me-1"></i> Nova Atividade
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($atividades)): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Nenhuma atividade registrada ainda.
                    </div>
                <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($atividades as $atividade): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker">
                                    <?php echo ChamadoHistorico::getIconeAtividade($atividade['atividade']); ?>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <div>
                                            <strong class="text-dark"><?php echo EmailTemplate::formatarDataHoraPadrao($atividade['data_atividade']); ?></strong>
                                            <span class="badge bg-secondary ms-2"><?php echo htmlspecialchars($atividade['usuario']); ?></span>
                                        </div>
                                        <div class="timeline-actions">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="editarAtividade(<?php echo $atividade['id']; ?>, '<?php echo addslashes($atividade['atividade']); ?>', '<?php echo date('Y-m-d\TH:i', strtotime($atividade['data_atividade'])); ?>', '<?php echo addslashes($atividade['usuario']); ?>')"
                                                    title="Editar atividade">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deletarAtividade(<?php echo $atividade['id']; ?>, <?php echo $chamado->id; ?>)"
                                                    title="Deletar atividade">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="timeline-body text-dark">
                                        <?php echo htmlspecialchars($atividade['atividade']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal para Nova Atividade -->
        <div class="modal fade" id="novaAtividadeModal" tabindex="-1" aria-labelledby="novaAtividadeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="add_atividade.php" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="novaAtividadeModalLabel">
                                <i class="fas fa-plus-circle text-success"></i> Nova Atividade
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="chamado_id" value="<?php echo $chamado->id; ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="usuario" class="form-label">
                                            <i class="fas fa-user"></i> Usuário:
                                        </label>
                                        <input type="text" class="form-control" id="usuario" name="usuario" required 
                                               placeholder="Ex: João Silva">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="data_atividade" class="form-label">
                                            <i class="fas fa-calendar-alt"></i> Data e Hora:
                                        </label>
                                        <input type="datetime-local" class="form-control" id="data_atividade" name="data_atividade">
                                        <div class="form-text">Deixe em branco para usar horário atual</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="atividade" class="form-label">
                                    <i class="fas fa-clipboard-list"></i> Descrição da Atividade:
                                </label>
                                <textarea class="form-control" id="atividade" name="atividade" rows="3" required 
                                          placeholder="Ex: Iniciado diagnóstico do problema. Verificando configurações do sistema..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Adicionar Atividade
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal para Editar Atividade -->
        <div class="modal fade" id="editarAtividadeModal" tabindex="-1" aria-labelledby="editarAtividadeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="edit_atividade.php" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editarAtividadeModalLabel">Editar Atividade</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="atividade_id" id="edit_atividade_id">
                            <input type="hidden" name="chamado_id" value="<?php echo $chamado->id; ?>">
                            <div class="mb-3">
                                <label for="edit_usuario" class="form-label">Usuário:</label>
                                <input type="text" class="form-control" id="edit_usuario" name="usuario" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_data_atividade" class="form-label">Data e Hora:</label>
                                <input type="datetime-local" class="form-control" id="edit_data_atividade" name="data_atividade" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_atividade" class="form-label">Descrição da Atividade:</label>
                                <textarea class="form-control" id="edit_atividade" name="atividade" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-warning">Atualizar Atividade</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para editar atividade
        function editarAtividade(id, atividade, dataHora, usuario) {
            document.getElementById('edit_atividade_id').value = id;
            document.getElementById('edit_usuario').value = usuario;
            // Garantir formato correto para input datetime-local
            if (dataHora && dataHora.length >= 16) {
                document.getElementById('edit_data_atividade').value = dataHora.substring(0,16);
            } else {
                // Preencher com data/hora atual se vier vazio
                var now = new Date();
                var local = now.toISOString().slice(0,16);
                document.getElementById('edit_data_atividade').value = local;
            }
            document.getElementById('edit_atividade').value = atividade;
            var modal = new bootstrap.Modal(document.getElementById('editarAtividadeModal'));
            modal.show();
        }
        
        // Função para deletar atividade
        function deletarAtividade(id, chamadoId) {
            if (confirm('Tem certeza que deseja excluir esta atividade?')) {
                window.location.href = 'delete_atividade.php?id=' + id + '&chamado_id=' + chamadoId;
            }
        }

        // Melhorar usabilidade do modal de nova atividade
        document.addEventListener('DOMContentLoaded', function() {
            var novaAtividadeModal = document.getElementById('novaAtividadeModal');
            
            novaAtividadeModal.addEventListener('show.bs.modal', function (event) {
                // Focar no campo de usuário quando o modal abrir
                setTimeout(function() {
                    document.getElementById('usuario').focus();
                }, 200);
                
                // Sugerir usuário padrão se estiver vazio
                var usuarioField = document.getElementById('usuario');
                if (usuarioField.value === '') {
                    usuarioField.value = 'Técnico TI';
                }
            });
            
            // Limpar formulário ao fechar modal
            novaAtividadeModal.addEventListener('hidden.bs.modal', function (event) {
                document.getElementById('usuario').value = '';
                document.getElementById('data_atividade').value = '';
                document.getElementById('atividade').value = '';
            });
        });
        
        // Função para abrir modal de imagem
        function abrirModalImagem(caminho, nome) {
            const imgElement = document.getElementById('imagemModalImg');
            const downloadLink = document.getElementById('imagemModalDownload');
            const modalTitle = document.getElementById('imagemModalLabel');
            
            // Configurar imagem
            imgElement.src = '../' + caminho;
            imgElement.alt = nome;
            
            // Configurar título truncado se muito longo
            const nomeExibicao = nome.length > 50 ? nome.substring(0, 47) + '...' : nome;
            modalTitle.innerHTML = '<i class="fas fa-image"></i> ' + nomeExibicao;
            
            // Configurar link de download
            downloadLink.href = '../' + caminho;
            downloadLink.download = nome;
            
            // Mostrar modal
            var modal = new bootstrap.Modal(document.getElementById('imagemModal'));
            modal.show();
        }
        
        // Função para confirmar exclusão de anexo
        function confirmarExclusaoAnexo(id, nome) {
            document.getElementById('nomeAnexoExclusao').textContent = nome;
            document.getElementById('confirmarExclusaoBtn').href = 'excluir_anexo.php?id=' + id + '&chamado_id=<?php echo $chamado->id; ?>';
            var modal = new bootstrap.Modal(document.getElementById('confirmarExclusaoModal'));
            modal.show();
        }
        
        // Função para alternar modo de visualização
        function toggleViewMode() {
            const gallery = document.querySelector('.anexos-gallery');
            const btn = document.getElementById('viewModeBtn');
            const icon = btn.querySelector('i');
            const textSpan = btn.querySelector('span');
            
            if (gallery.classList.contains('compact-mode')) {
                gallery.classList.remove('compact-mode');
                icon.className = 'fas fa-th me-1';
                btn.title = 'Modo compacto';
                if (textSpan) textSpan.textContent = 'Modo';
                localStorage.setItem('anexos_view_mode', 'normal');
            } else {
                gallery.classList.add('compact-mode');
                icon.className = 'fas fa-th-large me-1';
                btn.title = 'Modo normal';
                if (textSpan) textSpan.textContent = 'Compacto';
                localStorage.setItem('anexos_view_mode', 'compact');
            }
        }
        
        // Restaurar modo de visualização salvo
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Página carregada - checando anexos...');
            const gallery = document.querySelector('.anexos-gallery');
            const images = document.querySelectorAll('.anexo-image');
            console.log('Galeria encontrada:', gallery ? 'Sim' : 'Não');
            console.log('Número de imagens:', images.length);
            
            const savedMode = localStorage.getItem('anexos_view_mode');
            if (savedMode === 'compact') {
                const btn = document.getElementById('viewModeBtn');
                if (gallery && btn) {
                    gallery.classList.add('compact-mode');
                    btn.querySelector('i').className = 'fas fa-th-large me-1';
                    const textSpan = btn.querySelector('span');
                    if (textSpan) textSpan.textContent = 'Compacto';
                    btn.title = 'Modo normal';
                }
            }
        });
    </script>
    
    <!-- Modal para visualizar imagem em tamanho completo -->
    <div class="modal fade" id="imagemModal" tabindex="-1" aria-labelledby="imagemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="imagemModalLabel">
                        <i class="fas fa-image"></i> Visualizar Imagem
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img id="imagemModalImg" src="" alt="" class="img-fluid">
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Fechar
                    </button>
                    <a id="imagemModalDownload" href="#" target="_blank" class="btn btn-primary">
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para confirmar exclusão de anexo -->
    <div class="modal fade" id="confirmarExclusaoModal" tabindex="-1" aria-labelledby="confirmarExclusaoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmarExclusaoModalLabel">
                        <i class="fas fa-exclamation-triangle text-warning"></i> Confirmar Exclusão
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir o anexo:</p>
                    <p><strong id="nomeAnexoExclusao"></strong></p>
                    <p class="text-muted">Esta ação não pode ser desfeita.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="#" id="confirmarExclusaoBtn" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Excluir
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

