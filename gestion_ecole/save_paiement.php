<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['code_etudiant'];
    $type = $_POST['type'];
    $montant = $_POST['montant'];
    $date = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO paiements (code_etudiant, type_paiement, montant_paye, date_paiement) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $code, $type, $montant, $date);
    $stmt->execute();
    
    header("Location: crud_paiements.php?code_etudiant=$code&success=1");
}
?>
