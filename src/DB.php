<?php

class DB {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Usar constantes definidas no config.php
        $this->host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $this->db_name = defined('DB_NAME') ? DB_NAME : 'chamados_db';
        $this->username = defined('DB_USER') ? DB_USER : 'root';
        $this->password = defined('DB_PASS') ? DB_PASS : '';
    }

    public function getConnection(){
        $this->conn = null;
        try{
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, $this->username, $this->password);
            $this->conn->exec('set names utf8');
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }catch(PDOException $exception){
            if (defined('APP_DEBUG') && APP_DEBUG) {
                echo 'Connection error: ' . $exception->getMessage();
            } else {
                error_log('Database connection error: ' . $exception->getMessage());
                echo 'Erro de conexão com o banco de dados. Contate o administrador.';
            }
        }
        return $this->conn;
    }
}

?>

