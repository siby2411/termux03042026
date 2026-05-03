<?php
class Database {
    private $host = "127.0.0.1";
    private $db_name = "ingenierie"; // Nom de la base de données
    private $username = "root";      // Remplacez par votre utilisateur
    private $password = "123";  // Remplacez par votre mot de passe
    public $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            // Utilisation de DSN pour MariaDB/MySQL
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            // Configuration pour lever les exceptions en cas d'erreur SQL
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // En cas d'échec de connexion, afficher l'erreur et arrêter
            echo "<div class='alert alert-danger'>Erreur de connexion : " . $exception->getMessage() . "</div>";
            die();
        }
        return $this->conn;
    }
}
?>
