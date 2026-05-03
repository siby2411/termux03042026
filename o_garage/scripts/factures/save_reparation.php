<?php
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $id_intervention = $_POST['id_intervention'];
    $id_type_reparation = $_POST['id_type_reparation'];
    $montant_mo = $_POST['montant_mo'];
    $observations = htmlspecialchars($_POST['observations']);
    $date_cloture = date('Y-m-d H:i:s');

    try {
        // 1. Mise à jour de l'intervention
        // On enregistre le prix, les notes, et on marque comme 'Terminé'
        $sql = "UPDATE interventions SET 
                main_doeuvre = ?, 
                description_panne = CONCAT(description_panne, '\n--- TRAVAUX FAITS ---\n', ?),
                statut = 'Terminé',
                date_reparation = ?
                WHERE id_intervention = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([$montant_mo, $observations, $date_cloture, $id_intervention]);

        // 2. Optionnel : On peut aussi enregistrer une facture officielle ici 
        // ou simplement rediriger vers le profil client pour voir l'historique.
        
        // Récupération de l'id_client pour la redirection
        $stC = $db->prepare("SELECT c.id_client FROM clients c 
                             JOIN interventions i ON c.immatriculation = i.immatriculation 
                             WHERE i.id_intervention = ?");
        $stC->execute([$id_intervention]);
        $client = $stC->fetch();

        header("Location: ../clients/profil.php?id=" . $client['id_client'] . "&status=success&msg=Reparation_Cloturee");

    } catch (PDOException $e) {
        die("Erreur lors de la clôture de la réparation : " . $e->getMessage());
    }
} else {
    header("Location: ../../index.php");
}
