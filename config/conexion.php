<?php
class Conexion {
    private $host = "127.0.0.1";
    private $db_name = "comerxync_bd"; // Tu base de datos
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // AQUÍ ESTÁ LA SOLUCIÓN: Se agregó explícitamente port=3307
            $this->conn = new PDO("mysql:host=" . $this->host . ";port=3307;dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            die("Error de conexión: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
?>