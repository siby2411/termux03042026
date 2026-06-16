<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $conn->real_escape_string($_POST['etudiant_code']);
    $montant = floatval($_POST['montant_verse']);
    $mois = $conn->real_escape_string($_POST['mois']);
    $mode = $conn->real_escape_string($_POST['mode_paiement']);
    $recu = $conn->real_escape_string($_POST['recu_numero']);

    $etu = $conn->query("SELECT id FROM etudiants WHERE code_etudiant = '$code'")->fetch_assoc();
    
    $stmt = $conn->prepare("INSERT INTO paiements_scolarite (etudiant_id, montant_verse, mois_paye, mode_paiement, recu_numero) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $etu['id'], $montant, $mois, $mode, $recu);
    
    if ($stmt->execute()) {
        header("Location: fiche_suivi.php?code_etudiant=$code&success=1");
    }
}
?>
