<?php
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_client = $_POST['id_client'];
    $id_mecanicien = $_POST['id_mecanicien'];
    $km = $_POST['km'];
    $panne = $_POST['description_panne'];

    try {
        // Recherche multi-colonnes pour garantir de trouver la plaque
        $stC = $db->prepare("SELECT immatriculation, immatriculation_principale, immatriculation_vehicule 
                             FROM clients WHERE id_client = ?");
        $stC->execute([$id_client]);
        $client = $stC->fetch();
        
        // On prend la première valeur non vide trouvée
        $immat = $client['immatriculation'] ?? $client['immatriculation_principale'] ?? $client['immatriculation_vehicule'] ?? 'INCONNU';

        $sql = "INSERT INTO interventions (immatriculation, description_panne, id_mecanicien, km_entree, statut) 
                VALUES (?, ?, ?, ?, 'En cours')";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$immat, $panne, $id_mecanicien, $km]);
        
        header("Location: ../../index.php?status=success&msg=Dossier_Ouvert");
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}
