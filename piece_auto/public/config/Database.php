<?php
require_once __DIR__ . '/../includes/auth_check.php';
// config/Database.php
class Database {
    private $host = "localhost";
    private $db_name = "piece_auto";
    private $username = "root";
    private $password = ""; // Laissez vide ou modifiez selon votre config MariaDB
    public $conn;

    /**
     * Établit la connexion à la base de données
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, 
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
                ]
            );
        } catch(PDOException $exception) {
            // En production, il vaut mieux logger l'erreur que de l'afficher
            error_log("Erreur de connexion : " . $exception->getMessage());
        }
        return $this->conn;
    }
}
?>
