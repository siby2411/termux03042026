<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') { header("Location: login.php"); exit(); }

require_once dirname(__DIR__) . '/config/config.php';

try {
    // Contrepassation des charges constatées d'avance
    $stmt = $pdo->query("SELECT * FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 481 AND YEAR(date_ecriture) = ? AND type_ecriture = 'REGULARISATION'");
    $stmt->execute([date('Y')-1]);
    $reguls = $stmt->fetchAll();
    
    foreach($reguls as $r) {
        $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'CONTREPASSATION')";
        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute([date('Y-m-d'), "Contrepassation - " . $r['libelle'], $r['compte_credite_id'], $r['compte_debite_id'], $r['montant'], "CTR-" . $r['reference_piece']]);
    }
    
    $_SESSION['message'] = "✅ Contrepassation des régularisations effectuée avec succès";
} catch(Exception $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
}

header("Location: travaux_fin_exercice.php");
exit();
