<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO ECRITURES_COMPTABLES
        (societe_id, date_operation, libelle_operation,
         compte_debite_id, compte_credite_id, montant)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['societe_id'],
        $_POST['date_operation'],
        $_POST['libelle_operation'],
        $_POST['compte_debite_id'],
        $_POST['compte_credite_id'],
        $_POST['montant']
    ]);

    header("Location: ecriture.php?success=1");
    exit();

} catch (PDOException $e) {
    die("Erreur enregistrement écriture : " . $e->getMessage());
}





