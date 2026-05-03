<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Début du test<br>";
require_once 'header.php';
echo "Header chargé<br>";
require_once '../includes/db.php';
echo "DB chargé<br>";
require_once '../includes/functions.php';
echo "Functions chargé<br>";

$pdo = getPDO();
echo "PDO OK<br>";

$action = $_GET['action'] ?? 'liste';
echo "Action: $action<br>";

if ($action == 'nouvelle') {
    echo "Mode nouvelle vente<br>";
    $produits = $pdo->query("SELECT * FROM produits WHERE actif = 1 ORDER BY nom")->fetchAll();
    echo "Produits: " . count($produits) . "<br>";
    $clients = $pdo->query("SELECT * FROM clients WHERE actif = 1 ORDER BY nom")->fetchAll();
    echo "Clients: " . count($clients) . "<br>";
    echo "Formulaire affiché<br>";
}
echo "Fin du test";
?>
