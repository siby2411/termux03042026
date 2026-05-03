<?php
require_once '../../includes/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO diagnostics (id_client, id_vehicule, symptomes, diagnostic, etat, cout_estime) VALUES (?, ?, ?, ?, 'En attente', ?)";
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([
            $_POST['id_client'],
            $_POST['id_vehicule'],
            $_POST['symptomes'],
            $_POST['constat_technique'],
            $_POST['cout_estime']
        ]);
        header('Location: liste_diagnostics.php?success=1');
    } catch (Exception $e) {
        die("Erreur : " . $e->getMessage());
    }
}
?>
