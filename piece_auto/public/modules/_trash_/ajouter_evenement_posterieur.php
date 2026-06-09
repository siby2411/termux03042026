<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

require_once dirname(__DIR__) . '/config/config.php';

$date_vention = $_POST['date_vention'];
$type = $_POST['type_evenement'];
$libelle = trim($_POST['libelle']);
$description = trim($_POST['description']);
$impact = (float)$_POST['impact'];
$compte = (int)$_POST['compte'];

try {
    $stmt = $pdo->prepare("INSERT INTO EVENEMENTS_POSTERIEURS (date_vention, date_publication_comptes, libelle, type_evenement, description, impact_financier, compte_impacte) VALUES (?, CURDATE(), ?, ?, ?, ?, ?)");
    $stmt->execute([$date_vention, $libelle, $type, $description, $impact, $compte]);

    if($type == 'ADAPTATIF' && $compte > 0 && $impact > 0) {
        $stmt2 = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, 521, ?, ?, 'EVENEMENT_POSTERIEUR')");
        $stmt2->execute([$date_vention, $libelle, $compte, $impact, "POST-" . date('Ymd')]);
    }
    
    $_SESSION['message'] = "✅ Événement enregistré avec succès";
} catch(Exception $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
}

header("Location: evenements_posterieurs.php");
exit();
