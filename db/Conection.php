<?php
class Conection {
    private $host = '';
    private $db = '';
    private $user = '';
    private $password = '';
    private $charset = 'utf8mb4';
    private $pdo;

    public function __construct() {
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Error en la conexión a la base de datos: " . $e->getMessage());
        }
    }

    public function getPDO() {
        return $this->pdo;
    }
}
?>