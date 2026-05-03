<?php
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->beginTransaction();

        $immat = strtoupper(trim($_POST['immatriculation']));
        $id_client = $_POST['id_client'];
        $marque = $_POST['marque'] ?? 'Inconnue';
        $modele = $_POST['modele'] ?? 'Inconnu';
        $km = $_POST['kilometrage'] ?? 0;
        $meca = $_POST['id_mecanicien'];

        // 1. Gérer le véhicule
        $check = $db->prepare("SELECT id_vehicule FROM vehicules WHERE immatriculation = ?");
        $check->execute([$immat]);
        $vhc = $check->fetch();

        if (!$vhc) {
            $ins = $db->prepare("INSERT INTO vehicules (immatriculation, marque, modele, id_client, dernier_km) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$immat, $marque, $modele, $id_client, $km]);
            $id_vhc = $db->lastInsertId();
        } else {
            $id_vhc = $vhc['id_vehicule'];
            $up = $db->prepare("UPDATE vehicules SET dernier_km = ?, id_client = ? WHERE id_vehicule = ?");
            $up->execute([$km, $id_client, $id_vhc]);
        }

        // 2. Créer la fiche
        $fiche = $db->prepare("INSERT INTO fiches_intervention (id_vehicule, id_mec_1, statut, date_entree) VALUES (?, ?, 'En cours', NOW())");
        $fiche->execute([$id_vhc, $meca]);

        $db->commit();
        header("Location: ../../index.php?status=ok");
    } catch (Exception $e) {
        if ($db->inTransaction()) $db->rollBack();
        die("Erreur : " . $e->getMessage());
    }
}
?>
