<?php
namespace App\Models;
use PDO;
use PDOException;

class Db {
    private static ?Db $instance = null;
    private PDO $pdo;

    private function __construct() {
        $config = require __DIR__ . '/../../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        try {
            $this->pdo = new PDO($dsn, $config['user'], $config['pass']);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            die("Erreur DB : " . $e->getMessage());
        }
    }

    public static function getInstance(): Db {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }
}
