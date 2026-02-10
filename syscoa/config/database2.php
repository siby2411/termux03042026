<?php
// config/database.php
// CONFIGURATION SÉCURISÉE DE LA BASE DE DONNÉES OHADA

class DatabaseConfig {
    private $host = 'localhost';
    private $dbname = 'sysco_ohada';
    private $username = 'root';
    private $password = '123';
    private $pdo = null;
    
    public function getPDOConnection() {
        if ($this->pdo === null) {
            try {
                $this->pdo = new PDO(
                    "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                die("ERREUR DE CONNEXION À LA BASE DE DONNÉES: " . $e->getMessage());
            }
        }
        return $this->pdo;
    }
    
    public function testConnection() {
        try {
            $pdo = $this->getPDOConnection();
            $stmt = $pdo->query("SELECT 1");
            return $stmt !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
}

// Fonction helper pour obtenir une connexion PDO
function getPDOConnection() {
    $config = new DatabaseConfig();
    return $config->getPDOConnection();
}

// Test de la connexion au chargement
$config = new DatabaseConfig();
if (!$config->testConnection()) {
    error_log("ALERTE: Échec de connexion à la base de données sysco_ohada");
}
?>
