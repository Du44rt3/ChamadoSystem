<?php

class ChamadoAnexo {
    private $conn;
    private $table_name = "chamado_anexos";
    private $upload_dir;

    public $id;
    public $chamado_id;
    public $nome_original;
    public $nome_arquivo;
    public $caminho_arquivo;
    public $tipo_mime;
    public $tamanho_arquivo;
    public $data_upload;
    public $usuario_upload;

    public function __construct($db){
        $this->conn = $db;
        // Definir diretório de upload
        $this->upload_dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'anexos' . DIRECTORY_SEPARATOR;
        
        // Criar diretório se não existir
        if (!is_dir($this->upload_dir)) {
            mkdir($this->upload_dir, 0755, true);
        }
    }

    /**
     * Fazer upload de um anexo
     */
    public function upload($file, $chamado_id, $usuario = null) {
        // Validar arquivo
        if (!$this->validarArquivo($file)) {
            return false;
        }

        // Gerar nome único para o arquivo
        $extensao = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nome_arquivo = uniqid('anexo_' . $chamado_id . '_') . '.' . $extensao;
        $caminho_completo = $this->upload_dir . $nome_arquivo;

        // Fazer upload do arquivo
        if (move_uploaded_file($file['tmp_name'], $caminho_completo)) {
            // Salvar informações no banco
            $this->chamado_id = $chamado_id;
            $this->nome_original = $file['name'];
            $this->nome_arquivo = $nome_arquivo;
            $this->caminho_arquivo = 'uploads/anexos/' . $nome_arquivo;
            $this->tipo_mime = $file['type'];
            $this->tamanho_arquivo = $file['size'];
            $this->usuario_upload = $usuario;

            return $this->salvarNoBanco();
        }

        return false;
    }

    /**
     * Validar arquivo antes do upload
     */
    private function validarArquivo($file) {
        // Verificar se houve erro no upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        // Tipos de arquivo permitidos (imagens)
        $tipos_permitidos = [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp'
        ];

        // Verificar tipo MIME
        if (!in_array($file['type'], $tipos_permitidos)) {
            return false;
        }

        // Verificar extensão do arquivo
        $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, $extensoes_permitidas)) {
            return false;
        }

        // Verificar tamanho (máximo 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return false;
        }

        // Verificar se é realmente uma imagem
        $info_imagem = @getimagesize($file['tmp_name']);
        if ($info_imagem === false) {
            return false;
        }

        return true;
    }

    /**
     * Salvar informações do anexo no banco
     */
    private function salvarNoBanco() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET chamado_id=:chamado_id, 
                      nome_original=:nome_original, 
                      nome_arquivo=:nome_arquivo, 
                      caminho_arquivo=:caminho_arquivo, 
                      tipo_mime=:tipo_mime, 
                      tamanho_arquivo=:tamanho_arquivo, 
                      usuario_upload=:usuario_upload";

        $stmt = $this->conn->prepare($query);

        // Sanitizar dados
        $this->chamado_id = htmlspecialchars(strip_tags($this->chamado_id));
        $this->nome_original = htmlspecialchars(strip_tags($this->nome_original));
        $this->nome_arquivo = htmlspecialchars(strip_tags($this->nome_arquivo));
        $this->caminho_arquivo = htmlspecialchars(strip_tags($this->caminho_arquivo));
        $this->tipo_mime = htmlspecialchars(strip_tags($this->tipo_mime));
        $this->tamanho_arquivo = (int) $this->tamanho_arquivo;
        $this->usuario_upload = htmlspecialchars(strip_tags($this->usuario_upload));

        // Bind dos parâmetros
        $stmt->bindParam(":chamado_id", $this->chamado_id);
        $stmt->bindParam(":nome_original", $this->nome_original);
        $stmt->bindParam(":nome_arquivo", $this->nome_arquivo);
        $stmt->bindParam(":caminho_arquivo", $this->caminho_arquivo);
        $stmt->bindParam(":tipo_mime", $this->tipo_mime);
        $stmt->bindParam(":tamanho_arquivo", $this->tamanho_arquivo);
        $stmt->bindParam(":usuario_upload", $this->usuario_upload);

        return $stmt->execute();
    }

    /**
     * Buscar anexos de um chamado
     */
    public function buscarPorChamado($chamado_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE chamado_id = :chamado_id 
                  ORDER BY data_upload ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":chamado_id", $chamado_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar um anexo pelo ID
     */
    public function buscarPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Excluir um anexo
     */
    public function excluir($id) {
        // Buscar informações do anexo
        $anexo = $this->buscarPorId($id);
        if (!$anexo) {
            return false;
        }

        // Excluir arquivo físico
        $caminho_completo = dirname(__DIR__) . DIRECTORY_SEPARATOR . $anexo['caminho_arquivo'];
        if (file_exists($caminho_completo)) {
            unlink($caminho_completo);
        }

        // Excluir do banco
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    /**
     * Obter informações sobre tipos de arquivo aceitos
     */
    public static function getTiposPermitidos() {
        return [
            'tipos' => ['JPG', 'JPEG', 'PNG', 'GIF', 'WEBP', 'BMP'],
            'tamanho_max' => '5MB',
            'descricao' => 'Apenas imagens são aceitas (JPG, PNG, GIF, WEBP, BMP) até 5MB'
        ];
    }

    /**
     * Formatar tamanho do arquivo para exibição
     */
    public static function formatarTamanho($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
