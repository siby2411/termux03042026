<?php
// /root/shared/htdocs/apachewsl2026/analyse_medicale/includes/db.php

function getPDO() {
    static $pdo = null;
    if ($pdo === null) {
        $host = 'localhost';
        $dbname = 'laboratoire_medical';
        $user = 'root';
        $password = '';   // mot de passe vide

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("❌ Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    return $pdo;
}
?>
