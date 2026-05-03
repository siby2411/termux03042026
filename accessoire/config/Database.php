<?php
// /var/www/piece_auto/config/Database.php

class Database {
    private $host = '127.0.0.1';
    private $db_name = 'piece_auto';
    private $username = 'root'; // À changer en production !
    private $password = '123';     // Entrez votre mot de passe MariaDB si nécessaire
    private $conn;

    /**
     * Connexion à la base de données (méthode standard)
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->db_name, 
                                  $this->username, 
                                  $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            // Affichage de l'erreur seulement si l'on est en environnement de développement
            error_log("Erreur de connexion DB : " . $exception->getMessage());
            die("Erreur de connexion à la base de données. Veuillez vérifier la configuration.");
        }
        return $this->conn;
    }
}
