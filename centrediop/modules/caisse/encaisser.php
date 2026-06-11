<?php
require_once '../../includes/db.php';
$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

$stmt = $conn->prepare("UPDATE factures SET statut_paiement = 'payee' WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "Paiement enregistré avec succès. <a href='index.php'>Retour à la caisse</a>";
} else {
    echo "Erreur lors de l'encaissement.";
}
?>
