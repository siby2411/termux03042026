<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="resultats_ecole.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Code', 'Nom', 'Prenom', 'Moyenne']);

$rows = $conn->query("SELECT e.code_etudiant, e.nom, e.prenom, b.moyenne_annuelle 
                      FROM etudiants e LEFT JOIN bulletins b ON e.code_etudiant = b.code_etudiant");

while ($row = $rows->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
