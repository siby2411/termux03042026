<?php
// config.php - Version Finale "Proxy" pour Business Suite Pro
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('DB_SOCKET', '/var/run/mysqld/mysqld.sock');
define('DB_NAME', 'gestion_auto');
define('DB_USER', 'root');
define('DB_PASS', '');

class Database {
    private $pdo;
    private static $instance = null;
    
    private function __construct() {
        try {
            $dsn = "mysql:unix_socket=" . DB_SOCKET . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die("Erreur de connexion Socket : " . $e->getMessage());
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

    // Cette méthode manquait pour votre index.php
    public function query($sql) {
        return $this->pdo->query($sql);
    }
}

function getDBConnection() {
    return Database::getInstance()->getConnection();
}

date_default_timezone_set('Africa/Dakar');
?>
