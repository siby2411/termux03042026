<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

require_once dirname(__DIR__) . '/config/config.php';

$id = (int)$_GET['id'];
$date_contrepassation = date('Y-m-d');

try {
    // Récupérer la régularisation
    $regul = $pdo->prepare("SELECT * FROM REGULARISATIONS WHERE id = ?");
    $regul->execute([$id]);
    $r = $regul->fetch();
    
    if ($r && !$r['contrepassation']) {
        // Écriture de contrepassation
        if ($r['compte_charge_id']) {
            // Contrepassation charge : Crédit compte charge / Débit compte tiers
            $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, type_ecriture, reference_piece) VALUES (?, ?, ?, ?, ?, 'CONTREPASSATION', ?)");
            $stmt->execute([$date_contrepassation, "Contrepassation - " . $r['libelle'], 401, $r['compte_charge_id'], $r['montant'], "CTR-" . $id]);
        } elseif ($r['compte_produit_id']) {
            // Contrepassation produit : Débit compte produit / Crédit compte tiers
            $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, type_ecriture, reference_piece) VALUES (?, ?, ?, ?, ?, 'CONTREPASSATION', ?)");
            $stmt->execute([$date_contrepassation, "Contrepassation - " . $r['libelle'], $r['compte_produit_id'], 411, $r['montant'], "CTR-" . $id]);
        }
        
        // Mettre à jour le statut
        $update = $pdo->prepare("UPDATE REGULARISATIONS SET contrepassation = 1, date_contrepassation = ? WHERE id = ?");
        $update->execute([$date_contrepassation, $id]);
        
        $_SESSION['message'] = "✅ Contrepassation effectuée avec succès";
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
}

header("Location: regularisations.php");
exit();
