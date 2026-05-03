<?php
require_once '../../includes/classes/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $db->beginTransaction();

        $nom = $_POST['nom'];
        $tel = $_POST['tel'];
        $immat = strtoupper(trim($_POST['immat']));

        // 1. Enregistrer le client
        $stmtC = $db->prepare("INSERT INTO clients (nom, telephone, immatriculation_principale) VALUES (?, ?, ?)");
        $stmtC->execute([$nom, $tel, $immat]);
        $id_client = $db->lastInsertId();

        // 2. Créer automatiquement le véhicule lié s'il y a une immatriculation
        if (!empty($immat)) {
            $stmtV = $db->prepare("INSERT INTO vehicules (immatriculation, id_client, marque, modele) VALUES (?, ?, 'A préciser', 'A préciser')");
            $stmtV->execute([$immat, $id_client]);
        }

        $db->commit();
        header("Location: ../../index.php?status=success");
        exit();
    }
} catch (Exception $e) {
    if (isset($db)) $db->rollBack();
    die("Erreur lors de l'ajout : " . $e->getMessage());
}
?>
