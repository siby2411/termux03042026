<?php
require_once 'config/database.php';

$table = $_GET['table'] ?? '';

if (empty($table)) {
    die('Table non spécifiée');
}

// Vérifier que la table existe
$stmt = $pdo->query("SHOW TABLES LIKE '$table'");
if (!$stmt->fetch()) {
    die('Table non trouvée');
}

// Récupérer les données
$stmt = $pdo->query("SELECT * FROM $table");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Définir les headers pour le téléchargement CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $table . '_export_' . date('Y-m-d') . '.csv');

// Créer le fichier CSV
$output = fopen('php://output', 'w');

// En-têtes
if (!empty($data)) {
    fputcsv($output, array_keys($data[0]));
}

// Données
foreach ($data as $row) {
    fputcsv($output, $row);
}

fclose($output);
