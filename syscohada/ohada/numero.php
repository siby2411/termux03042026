<?php
include('config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$excelFile = '/data/data/com.termux/files/home/storage/shared/comptes_ohada.xlsx';

// Connexion à la base de données PostgreSQL (ou autre)
try {
    $db = new PDO('mysql:host=localhost;dbname=ohada', 'root', '123');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die('Erreur de connexion à la base de données : ' . $e->getMessage());
}

// Vérifier si le fichier existe
if (!file_exists($excelFile)) {
    die("Le fichier Excel n'existe pas : " . $excelFile);
}

try {
    // Charger le fichier Excel
    $spreadsheet = IOFactory::load($excelFile);

    // Sélectionner la première feuille
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray(null, true, true, true); // Récupérer les données sous forme de tableau associatif

    // Extraire les lignes de données à partir de la deuxième ligne (exclure les en-têtes)
    $rows = array_slice($data, 1);

    // Préparation de la requête d'insertion
    $insertQuery = $db->prepare("INSERT INTO numerocompte (numero_compte) VALUES (:numero_compte)");

    // Filtrer et insérer les numéros de compte valides dans la base de données
    foreach ($rows as $row) {
        if (isset($row['A']) && is_numeric($row['A'])) {
            // Insertion du numéro de compte dans la base
            $insertQuery->execute([':numero_compte' => $row['A']]);
        }
    }

    echo "Les numéros de compte ont été insérés avec succès dans la table 'numerocompte'.";
    
} catch (Exception $e) {
    echo "Erreur lors de la lecture du fichier Excel : " . $e->getMessage();
}
?>