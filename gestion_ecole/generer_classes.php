<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

$filiere_id = 4; // ID du Génie Logiciel
$niveaux = ['L1', 'L2', 'L3'];
$filiere_code = 'GL';

foreach ($niveaux as $n) {
    $nom_class = $n . '-' . $filiere_code;
    $sql = "INSERT IGNORE INTO classes (nom_class, filiere_id, annee_academique) VALUES ('$nom_class', $filiere_id, '2026-2027')";
    $conn->query($sql);
}
echo "Classes générées avec succès selon la norme de concaténation.";
?>
