<?php
// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

// Configurações da página
$page_title = "Novo Chamado";
$page_subtitle = "Criar nova solicitação";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Chamado - ELUS Facilities</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="icon" type="image/png" href="images/favicon.png">
    <link rel="apple-touch-icon" href="images/favicon.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body>
    <?php 
    // Definir variáveis globais para o header
    global $auth, $current_user;
    
    // Incluir header moderno
    require_once '../src/header.php'; 
    ?>

    <div class="container-fluid mt-4">
        <h1>Novo Chamado</h1>

        <?php
            if($_POST){
            include_once '../src/DB.php';
            include_once '../src/Chamado.php';
            include_once '../src/ChamadoHistorico.php';
            include_once '../src/ChamadoAnexo.php';

            $database = new DB();
            $db = $database->getConnection();
            $chamado = new Chamado($db);
            $historico = new ChamadoHistorico($db);
            $anexo = new ChamadoAnexo($db);

            $chamado->nome_colaborador = $_POST['nome_colaborador'];
            $chamado->email = $_POST['email'];
            $chamado->setor = $_POST['setor'];
            $chamado->descricao_problema = $_POST['descricao_problema'];
            $chamado->nome_projeto = $_POST['nome_projeto'];
            $chamado->gravidade = $_POST['gravidade'];

            // Tentar criar chamado com retry em caso de código duplicado
            $novo_id = false;
            $tentativas = 0;
            $max_tentativas = 5;
            
            while($novo_id === false && $tentativas < $max_tentativas) {
                $tentativas++;
                $novo_id = $chamado->create();
                
                // Se falhou, aguardar um pouco antes da próxima tentativa
                if($novo_id === false && $tentativas < $max_tentativas) {
                    usleep(100000 * $tentativas); // 100ms, 200ms, 300ms, etc.
                }
            }
            
            if($novo_id):
                // Adicionar atividade de abertura no histórico (o trigger já deve fazer isso, mas vamos garantir)
                date_default_timezone_set('America/Sao_Paulo');
                
                // Verificar se já existe atividade de abertura (trigger pode ter criado)
                $historico_existente = $historico->buscarHistorico($novo_id);
                $tem_abertura = false;
                foreach($historico_existente as $atividade) {
                    if(strpos($atividade['atividade'], 'Abertura') !== false) {
                        $tem_abertura = true;
                        break;
                    }
                }
                
                // Se não tem atividade de abertura, criar uma
                if(!$tem_abertura) {
                    $historico->adicionarAtividade($novo_id, 'Abertura do chamado', 'Sistema', date('Y-m-d H:i:s'));
                }
                
                // Processar uploads de imagens se houver
                $uploads_success = true;
                $upload_errors = [];
                
                if (isset($_FILES['anexos']) && !empty($_FILES['anexos']['name'][0])) {
                    $total_files = count($_FILES['anexos']['name']);
                    
                    for ($i = 0; $i < $total_files; $i++) {
                        if ($_FILES['anexos']['error'][$i] == UPLOAD_ERR_OK) {
                            $file = [
                                'name' => $_FILES['anexos']['name'][$i],
                                'type' => $_FILES['anexos']['type'][$i],
                                'tmp_name' => $_FILES['anexos']['tmp_name'][$i],
                                'error' => $_FILES['anexos']['error'][$i],
                                'size' => $_FILES['anexos']['size'][$i]
                            ];
                            
                            if (!$anexo->upload($file, $novo_id, $_POST['nome_colaborador'])) {
                                $uploads_success = false;
                                $upload_errors[] = $_FILES['anexos']['name'][$i];
                            }
                        }
                    }
                }
                
                echo "<div class='alert alert-success'>Chamado criado com sucesso!</div>";
                
                if (!$uploads_success && !empty($upload_errors)) {
                    echo "<div class='alert alert-warning'>Alguns anexos não puderam ser enviados: " . implode(', ', $upload_errors) . "</div>";
                }
                
                echo "<a href='view.php?id=$novo_id' class='btn btn-primary'>Ver Chamado</a> ";
                echo "<a href='index.php' class='btn btn-secondary'>Voltar para lista</a>";
            else:
                echo "<div class='alert alert-danger'>Erro ao criar chamado após $tentativas tentativas. Tente novamente em alguns segundos.</div>";
                echo "<a href='add.php' class='btn btn-primary'>Tentar Novamente</a> ";
                echo "<a href='index.php' class='btn btn-secondary'>Voltar para lista</a>";
            endif;
        } else {
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nome_colaborador" class="form-label">Nome do Colaborador</label>
                <input type="text" class="form-control" id="nome_colaborador" name="nome_colaborador" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="mb-3">
                <label for="setor" class="form-label">Setor</label>
                <div class="position-relative">
                    <select class="form-control" id="setor" name="setor" required style="appearance: none; background-image: none; padding-right: 2.5rem;">
                        <option value="">Selecione o setor</option>
                        <option value="Recebimento">Recebimento</option>
                        <option value="Inspensão da Qualidade">Inspensão da Qualidade</option>
                        <option value="Lab. Temperatura">Lab. Temperatura</option>
                        <option value="Lab. Pressão">Lab. Pressão</option>
                        <option value="Lab. Vazão">Lab. Vazão</option>
                        <option value="Lab. Eletrica">Lab. Eletrica</option>
                        <option value="Lab Volume">Lab Volume</option>
                        <option value="Lab. Fisico - Quimico">Lab. Fisico - Quimico</option>
                        <option value="Lab. MRC">Lab. MRC</option>
                        <option value="Lab. Dimensional">Lab. Dimensional</option>
                        <option value="Lab.">Lab.</option>
                        <option value="SGQ">SGQ</option>
                        <option value="Qualidade Externa">Qualidade Externa</option>
                        <option value="Financeiro">Financeiro</option>
                        <option value="Comercial">Comercial</option>
                        <option value="Fiscal">Fiscal</option>
                        <option value="Administrativo">Administrativo</option>
                        <option value="RH">RH</option>
                        <option value="Field Service">Field Service</option>
                        <option value="Operações Facilities">Operações Facilities</option>
                        <option value="Outros">Outros</option>
                    </select>
                    <i class="fas fa-chevron-down position-absolute" style="right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                </div>
            </div>

            <div class="mb-3">
                <label for="descricao_problema" class="form-label">Descrição do Problema</label>
                <textarea class="form-control" id="descricao_problema" name="descricao_problema" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label for="nome_projeto" class="form-label">Nome do Projeto</label>
                <input type="text" class="form-control" id="nome_projeto" name="nome_projeto" placeholder="Ex: update, instalacao, manutencao" required>
                <div class="form-text">Este nome será usado para gerar o código do chamado (FAC-TI-250717.1_nome_projeto)</div>
            </div>

            <div class="mb-3">
                <label for="gravidade" class="form-label">Gravidade</label>
                <div class="position-relative">
                    <select class="form-control" id="gravidade" name="gravidade" required style="appearance: none; background-image: none; padding-right: 2.5rem;">
                        <option value="">Selecione a gravidade</option>
                        <option value="baixa">Baixa</option>
                        <option value="media">Média</option>
                        <option value="alta">Alta</option>
                    </select>
                    <i class="fas fa-chevron-down position-absolute" style="right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: #6c757d;"></i>
                </div>
            </div>

            <div class="mb-3">
                <label for="anexos" class="form-label">Anexar Imagens <span class="text-muted">(Opcional)</span></label>
                <input type="file" class="form-control" id="anexos" name="anexos[]" multiple accept="image/*">
                <div class="form-text">
                    <i class="fas fa-info-circle"></i> 
                    Você pode anexar múltiplas imagens (JPG, PNG, GIF, WEBP, BMP) de até 5MB cada.
                </div>
                <div id="preview-container" class="upload-preview"></div>
            </div>

            <button type="submit" class="btn btn-success">Criar Chamado</button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
        </form>

        <?php
        }
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Preview de imagens antes do upload
    document.getElementById('anexos').addEventListener('change', function(e) {
        const files = e.target.files;
        const previewContainer = document.getElementById('preview-container');
        previewContainer.innerHTML = '';
        
        if (files.length > 0) {
            const previewTitle = document.createElement('div');
            previewTitle.className = 'upload-preview-title';
            previewTitle.innerHTML = '<i class="fas fa-eye"></i> Preview das imagens:';
            previewContainer.appendChild(previewTitle);
            
            const previewGrid = document.createElement('div');
            previewGrid.className = 'upload-preview-grid';
            previewContainer.appendChild(previewGrid);
        }
        
        Array.from(files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const item = document.createElement('div');
                    item.className = 'upload-preview-item';
                    
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'upload-preview-image';
                    
                    const info = document.createElement('div');
                    info.className = 'upload-preview-info';
                    
                    const fileName = document.createElement('div');
                    fileName.className = 'upload-preview-name';
                    fileName.textContent = file.name;
                    
                    const fileSize = document.createElement('div');
                    fileSize.className = 'upload-preview-size';
                    fileSize.textContent = formatFileSize(file.size);
                    
                    info.appendChild(fileName);
                    info.appendChild(fileSize);
                    item.appendChild(img);
                    item.appendChild(info);
                    
                    document.querySelector('.upload-preview-grid').appendChild(item);
                };
                reader.readAsDataURL(file);
            }
        });
    });
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    </script>
</body>
</html>

