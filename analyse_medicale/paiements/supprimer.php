<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: liste.php');
    exit;
}

$pdo = getPDO();
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT facture_id FROM paiements WHERE id = ?");
    $stmt->execute([$id]);
    $facture_id = $stmt->fetchColumn();

    $stmt = $pdo->prepare("DELETE FROM paiements WHERE id = ?");
    $stmt->execute([$id]);

    // Recalcul du statut de la facture
    $total_paye = $pdo->prepare("SELECT SUM(montant) FROM paiements WHERE facture_id = ?")->execute([$facture_id])->fetchColumn();
    if ($total_paye > 0) {
        $facture = $pdo->prepare("SELECT total_ttc FROM factures WHERE id = ?")->execute([$facture_id])->fetch();
        if ($total_paye >= $facture['total_ttc']) {
            $pdo->prepare("UPDATE factures SET reglee = 1, date_reglement = CURDATE() WHERE id = ?")->execute([$facture_id]);
        } else {
            $pdo->prepare("UPDATE factures SET reglee = 0, date_reglement = NULL WHERE id = ?")->execute([$facture_id]);
        }
    } else {
        $pdo->prepare("UPDATE factures SET reglee = 0, date_reglement = NULL WHERE id = ?")->execute([$facture_id]);
    }
    $pdo->commit();
    $_SESSION['flash'] = "Paiement supprimé.";
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['flash'] = "Erreur : " . $e->getMessage();
}
header('Location: liste.php');
exit;
