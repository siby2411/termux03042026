<?php
if (!class_exists('Database')) {
    class Database {
        private $host = "127.0.0.1";
        private $db_name = "piece_auto";
        private $username = "root";
        private $password = "";
        public $conn;

        public function getConnection() {
            if ($this->conn !== null) {
                return $this->conn;
            }
            try {
                $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->exec("set names utf8");
            } catch(PDOException $e) {
                die("Erreur de connexion : " . $e->getMessage());
            }
            return $this->conn;
        }
    }
}
?>
