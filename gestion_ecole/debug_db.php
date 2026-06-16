<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$code = 'ETU-2026-100'; // Votre code test

echo "<h3>Diagnostic pour $code</h3>";

// 1. Vérifier si l'étudiant existe et a un classe_id
$res = $conn->query("SELECT id, nom, classe_id FROM etudiants WHERE code_etudiant = '$code'");
$etu = $res->fetch_assoc();
echo "Étudiant trouvé : " . ($etu ? "OUI (ID: {$etu['id']}, ClasseID: {$etu['classe_id']})" : "NON") . "<br>";

if ($etu && $etu['classe_id']) {
    // 2. Vérifier si la classe existe et a un montant
    $res2 = $conn->query("SELECT montant_scolarite FROM classes WHERE id = {$etu['classe_id']}");
    $classe = $res2->fetch_assoc();
    echo "Montant trouvé dans classes : " . ($classe ? $classe['montant_scolarite'] : "NULL/VIDE");
} else {
    echo "L'étudiant n'est lié à aucune classe.";
}
?>
