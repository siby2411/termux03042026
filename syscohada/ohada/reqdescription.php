<?php
// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Connexion à la base de données
$host = '127.0.0.1';
$dbname = 'ohada';
$username = 'root';
$password = '123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Vérifier si une requête Ajax a été envoyée
if (isset($_GET['description'])) {
    $description = $_GET['description'];

    // Préparer la requête SQL pour rechercher les comptes par description
    $sql = "SELECT num_compte, intitule FROM comptes_ohada WHERE description LIKE ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%" . $description . "%"]);

    // Récupérer les résultats
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Retourner les résultats en JSON
    echo json_encode($resultats);
}
?>