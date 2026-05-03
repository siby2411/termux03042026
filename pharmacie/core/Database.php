<?php
class Database {
    private static $instance = null;
    private $connection;
    
    // Paramètres de connexion adaptés à ton environnement WSL
    private $host = '127.0.0.1';
    private $db   = 'pharmacie';
    private $user = 'root';
    private $pass = ''; // Vide comme demandé
    private $charset = 'utf8mb4';

    private function __construct() {
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $this->connection = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            // Message d'erreur propre pour le débogage
            die("Erreur de connexion à la base de données Omega Pharma : " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    /**
     * Méthode statique utilitaire pour exécuter des requêtes rapidement
     */
    public static function query($sql, $params = []) {
        $stmt = self::getInstance()->getConnection()->prepare($sql);
        $stmt->execute($params);
        
        // Si c'est un SELECT, on retourne les résultats
        if (stripos($sql, 'SELECT') === 0 || stripos($sql, 'SHOW') === 0 || stripos($sql, 'DESC') === 0) {
            return $stmt->fetchAll();
        }
        // Sinon (INSERT/UPDATE/DELETE), on retourne le nombre de lignes affectées
        return $stmt->rowCount();
    }
}
