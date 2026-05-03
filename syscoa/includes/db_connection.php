


<?php
function getPDOConnection() {
    try {
        $pdo = new PDO(
            'mysql:host=127.0.0.1;dbname=sysco_ohada;charset=utf8',
            'root',
            '123',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion : " . $e->getMessage());
    }
}
