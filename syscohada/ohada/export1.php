<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclure le fichier autoload de PhpSpreadsheet (assurez-vous que PhpSpreadsheet est installé via Composer)
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Informations de connexion à la base de données
$host = '127.0.0.1'; // Nom d'hôte du serveur MySQL
$dbname = 'ohada'; // Nom de la base de données (à ajuster si nécessaire)
$username = 'root'; // Nom d'utilisateur MySQL
$password = '123'; // Mot de passe MySQL

// Chemin vers lequel le fichier XLSX sera exporté (modifiez-le selon vos besoins)
$xlsx_file = '/data/data/com.termux/files/home/storage/shared/comptes_ohada.xlsx';

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête pour sélectionner toutes les données de la table comptes_ohada
    $sql = "SELECT * FROM comptes_ohada";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Créer un nouveau fichier Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Définir l'en-tête des colonnes
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Numéro de Compte');
    $sheet->setCellValue('C1', 'Intitulé');
    $sheet->setCellValue('D1', 'ID Sous Classe');
    $sheet->setCellValue('E1', 'Description');

    // Remplir le fichier Excel avec les données de la table
    $rowIndex = 2; // Les lignes de données commencent à partir de la deuxième ligne (1ère ligne pour les en-têtes)
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

    echo "Les comptes OHADA ont été exportés dans le fichier : $xlsx_file";

} catch (PDOException $e) {
    // Gestion des erreurs liées à la base de données
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
} catch (Exception $e) {
    // Gestion des erreurs liées à PhpSpreadsheet
    echo "Erreur lors de l'exportation du fichier Excel : " . $e->getMessage();
}
?>