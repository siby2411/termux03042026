<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure le fichier autoload de PhpSpreadsheet (Composer ou via un chemin local)
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Informations de connexion à la base de données
$host = 'localhost'; // Nom d'hôte du serveur MySQL
$dbname = 'ohada'; // Nom de la base de données
$username = 'root'; // Nom d'utilisateur MySQL (root sans mot de passe)
$password = '123'; // Mot de passe

// Chemin vers lequel le fichier XLSX sera exporté (modifier selon vos besoins)
$xlsx_file = '/data/data/com.termux/files/home/storage/shared/comptes_ohada.xlsx';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête pour sélectionner les données de la table comptes_ohada
    $sql = "SELECT * FROM comptes_ohada";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // Créer un nouveau fichier Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Définir l'en-tête des colonnes
    $sheet->setCellValue('A1', 'id');
    $sheet->setCellValue('B1', 'num_compte');
    $sheet->setCellValue('C1', 'intitule');
    $sheet->setCellValue('D1', 'sous_classe_id');
    $sheet->setCellValue('E1', 'description');

    // Remplir le fichier Excel avec les données de la table
    $rowIndex = 2; // Lignes de données commencent à la deuxième ligne (1ère ligne pour les en-têtes)
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sheet->setCellValue('A' . $rowIndex, $row['id']);
        $sheet->setCellValue('B' . $rowIndex, $row['num_compte']);
        $sheet->setCellValue('C' . $rowIndex, $row['intitule']);
        $sheet->setCellValue('D' . $rowIndex, $row['sous_classe_id']);
        $sheet->setCellValue('E' . $rowIndex, $row['description']);
        $rowIndex++;
    }

    // Écrire le fichier Excel sur le disque
    $writer = new Xlsx($spreadsheet);
    $writer->save($xlsx_file);

    echo "Les comptes OHADA ont été exportés dans le fichier $xlsx_file";

} catch (PDOException $e) {
    // Gestion des erreurs de la base de données
    echo "Erreur : " . $e->getMessage();
} catch (Exception $e) {
    // Gestion des erreurs liées à PhpSpreadsheet
    echo "Erreur lors de l'exportation du fichier Excel : " . $e->getMessage();
}
?>