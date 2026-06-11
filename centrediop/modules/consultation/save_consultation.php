<?php
require_once '../../includes/auth.php';
// Remplacez par votre connexion DB réelle
$db = new mysqli('localhost', 'root', 'votre_mdp', 'centrediop');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $db->prepare("INSERT INTO consultations (patient_id, medecin_id, service_id, date_consultation, motif_consultation, diagnostic, traitement_prescrit, type_consultation, statut) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?)");
    
    $stmt->bind_param("iiisssss", 
        $_POST['patient_id'], $_POST['medecin_id'], $_POST['service_id'], 
        $_POST['motif_consultation'], $_POST['diagnostic'], 
        $_POST['traitement_prescrit'], $_POST['type_consultation'], $_POST['statut']
    );

    if ($stmt->execute()) {
        header("Location: index.php?success=1");
    } else {
        echo "Erreur : " . $stmt->error;
    }
}
?>
