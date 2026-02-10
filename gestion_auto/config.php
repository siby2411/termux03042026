<?php
// config.php - Version corrigée sans récursion
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Limiter la mémoire pour éviter les boucles infinies
ini_set('memory_limit', '256M');

define('DB_HOST', 'localhost');
define('DB_NAME', 'gestion_auto'); 
define('DB_USER', 'root');
define('DB_PASS', '123');
define('ROOT_PATH', '/var/www/gestion_auto');
define('UPLOAD_DIR', ROOT_PATH . '/uploads');

class Database {
    private $pdo;
    private static $instance = null;
    
    // Constructeur privé pour forcer l'usage du singleton
    private function __construct() {
        try {
            $this->pdo = new PDO(
                "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
                DB_USER, 
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            throw new Exception("Erreur DB: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
    
    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }
    
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}

// Fonction de compatibilité SIMPLIFIÉE
function getDBConnection() {
    return Database::getInstance()->getConnection();
}

// Vérification uploads sans récursion
if (!file_exists(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0755, true);
}

// Timezone
date_default_timezone_set('Europe/Paris');
?>
