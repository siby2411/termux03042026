<?php
$host = '127.0.0.1'; // Utiliser l'IP au lieu de localhost pour forcer le protocole TCP
$db   = 'pme';
$user = 'root';
$pass = ''; // <--- METTEZ VOTRE MOT DE PASSE ICI SI NECESSAIRE
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     // En cas d'erreur, on affiche un message clair
     die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
