<?php

namespace App\Models;

use PDO;
use PDOException;

class Db
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        // Charger la configuration
        $config = require __DIR__ . '/../../config.php';

        $host = $config['host'];
        $dbname = $config['dbname'];
        $user = $config['user'];
        $pass = $config['password'];

        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (PDOException $e) {
            die("Erreur DB : " . $e->getMessage());
        }
    }

    // Méthode Singleton
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Db();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}

