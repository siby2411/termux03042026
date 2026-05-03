<?php
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Enregistrement de l'intervention
    $sql = "INSERT INTO fiches_intervention 
            (id_vehicule, id_mec_1, id_mec_2, description_panne, diagnostic_technique, complexite, cout_main_doeuvre) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([
        $_POST['id_vehicule'],
        $_POST['id_mec_1'],
        $_POST['id_mec_2'] ?: null,
        $_POST['description_panne'],
        $_POST['diagnostic_technique'],
        $_POST['complexite'],
        $_POST['cout_main_doeuvre']
    ]);

    // 2. LOGIQUE CRM : Mise à jour du kilométrage du véhicule
    if(isset($_POST['km_actuel']) && !empty($_POST['km_actuel'])) {
        $nouveau_km = intval($_POST['km_actuel']);
        $prochain_km = $nouveau_km + 5000; // Paramètre standard OMEGA TECH
        $id_vehicule = $_POST['id_vehicule'];

        $update_km = $db->prepare("UPDATE vehicules SET dernier_km = ?, prochain_rappel_km = ? WHERE id_vehicule = ?");
        $update_km->execute([$nouveau_km, $prochain_km, $id_vehicule]);
    }

    header('Location: dashboard_ingenieur.php?success=1');
}
