<?php
// Proteção de autenticação
require_once '../src/AuthMiddleware.php';

// Verificar se ID do chamado foi fornecido
if (!isset($_GET['chamado_id']) || empty($_GET['chamado_id'])) {
    header('Location: index.php');
    exit;
}

$chamado_id = $_GET['chamado_id'];

// Processar upload se formulário foi enviado
if ($_POST && isset($_FILES['anexos'])) {
    include_once '../src/DB.php';
    include_once '../src/ChamadoAnexo.php';

    $database = new DB();
    $db = $database->getConnection();
    $anexo = new ChamadoAnexo($db);

    $uploads_success = true;
    $upload_errors = [];
    $uploads_realizados = 0;

    if (!empty($_FILES['anexos']['name'][0])) {
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
                
                if ($anexo->upload($file, $chamado_id, $_POST['usuario'] ?? 'Sistema')) {
                    $uploads_realizados++;
                } else {
                    $uploads_success = false;
                    $upload_errors[] = $_FILES['anexos']['name'][$i];
                }
            }
        }
    }

    // Redirecionar com mensagens
    if ($uploads_realizados > 0) {
        if ($uploads_success) {
            header("Location: view.php?id=$chamado_id&success=anexos_adicionados");
        } else {
            header("Location: view.php?id=$chamado_id&success=anexos_parcial&error=" . urlencode(implode(', ', $upload_errors)));
        }
    } else {
        header("Location: view.php?id=$chamado_id&error=nenhum_anexo_enviado");
    }
    exit;
}

// Configurações da página
$page_title = "Adicionar Anexos";
$page_subtitle = "Anexar imagens ao chamado";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Anexos - ELUS Facilities</title>
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
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">
                            <i class="fas fa-paperclip"></i> Adicionar Anexos de Imagem
                        </h4>
                        <small class="text-muted">Chamado #<?php echo $chamado_id; ?></small>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="usuario" class="form-label">Usuário</label>
                                <input type="text" class="form-control" id="usuario" name="usuario" 
                                       value="Técnico TI" required>
                            </div>

                            <div class="mb-3">
                                <label for="anexos" class="form-label">Selecionar Imagens</label>
                                <input type="file" class="form-control" id="anexos" name="anexos[]" 
                                       multiple accept="image/*" required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle"></i> 
                                    Você pode selecionar múltiplas imagens (JPG, PNG, GIF, WEBP, BMP) de até 5MB cada.
                                </div>
                                                <div id="preview-container" class="upload-preview"></div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-upload"></i> Enviar Anexos
                                </button>
                                <a href="view.php?id=<?php echo $chamado_id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Voltar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Card com informações sobre tipos de arquivo aceitos -->
                <div class="card mt-3">
                    <div class="card-body">
                        <h6><i class="fas fa-info-circle text-info"></i> Informações Importantes</h6>
                        <ul class="mb-0">
                            <li><strong>Tipos aceitos:</strong> JPG, JPEG, PNG, GIF, WEBP, BMP</li>
                            <li><strong>Tamanho máximo:</strong> 5MB por arquivo</li>
                            <li><strong>Múltiplos arquivos:</strong> Você pode selecionar várias imagens de uma vez</li>
                            <li><strong>Segurança:</strong> Apenas imagens são aceitas por questões de segurança</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
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
            previewTitle.innerHTML = '<i class="fas fa-eye"></i> Preview das imagens selecionadas:';
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
                    fileName.title = file.name;
                    
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
