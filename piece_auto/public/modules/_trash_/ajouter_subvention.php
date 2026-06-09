<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

require_once dirname(__DIR__) . '/config/config.php';

$date = $_POST['date_subvention'];
$libelle = trim($_POST['libelle']);
$montant = (float)$_POST['montant'];
$type = $_POST['type_subvention'];
$compte_subvention = ($type == 'EQUIPEMENT') ? 109 : 131;

try {
    // Écriture comptable : Débit Banque (521) / Crédit compte subvention (109 ou 131)
    $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 521, ?, ?, ?, 'SUBVENTION')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$date, "Subvention - $libelle", $compte_subvention, $montant, "SUB-" . date('Ymd')]);
    
    $_SESSION['message'] = "✅ Subvention enregistrée et écrite dans le Grand Livre";
} catch(Exception $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
}

header("Location: subventions_comptes_liaison.php");
exit();
