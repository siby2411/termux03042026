<?php
include 'db_connect.php';
if (isset($_GET['id']) && isset($_GET['km'])) {
    $id = intval($_GET['id']);
    $km = intval($_GET['km']);
    
    // On met à jour le KM actuel. 
    // Si on fait une vidange, on pourrait aussi réinitialiser km_prochaine_vidange ici.
    $conn->query("UPDATE voitures SET km_actuel = $km WHERE id = $id");
    
    header("Location: dashboard.php?msg=km_updated");
}
?>
