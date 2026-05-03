<?php
require_once '../../includes/classes/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nom = $_POST['nom_complet'] ?? '';
        $specialite = $_POST['specialite'] ?? 'Mécanicien';
        $telephone = $_POST['telephone'] ?? '';

        // Correction : On utilise bien $db qui est maintenant défini
        $stmt = $db->prepare("INSERT INTO personnel (nom_complet, role, telephone) VALUES (?, ?, ?)");
        $stmt->execute([$nom, $specialite, $telephone]);

        header("Location: ../../index.php?status=mecanicien_ajoute");
        exit();
    }
} catch (Exception $e) {
    die("Erreur fatale : " . $e->getMessage());
}
?>
