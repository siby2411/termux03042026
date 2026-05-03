<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $tel = $_POST['telephone'];
    $adr = $_POST['adresse'];

    try {
        $stmt = $pdo->prepare("INSERT INTO clients (nom, telephone, adresse) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $tel, $adr]);
        header("Location: clients.php?success=Client ajouté !");
    } catch (PDOException $e) {
        die("❌ Erreur OMEGA : " . $e->getMessage());
    }
}
?>
