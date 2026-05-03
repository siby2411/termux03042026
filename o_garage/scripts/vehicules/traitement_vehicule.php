<?php
require_once '../../includes/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO vehicules (immatriculation, marque, modele, id_client) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([
            $_POST['immatriculation'],
            $_POST['marque'],
            $_POST['modele'],
            $_POST['id_client']
        ]);
        header('Location: liste_vehicules.php?success=1');
    } catch (Exception $e) {
        die("Erreur : " . $e->getMessage());
    }
}
?>
