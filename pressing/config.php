<?php
class Database {
    private $host = "127.0.0.1";
    private $db_name = "pressing_management";
    private $username = "root";
    private $password = ""; 
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Sur proot-distro, l'accès par IP (127.0.0.1) est préférable au socket
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            // On enregistre l'erreur sans bloquer l'exécution
            error_log("Erreur OMEGA Pressing : " . $e->getMessage());
        }
        return $this->conn;
    }
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
