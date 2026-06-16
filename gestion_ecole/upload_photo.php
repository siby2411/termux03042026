<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo'])) {
    $etudiant_id = intval($_POST['etudiant_id']);
    $upload_dir = 'uploads/photos/';
    
    // Nom unique basé sur l'ID de l'étudiant
    $new_filename = "stu_" . $etudiant_id . ".jpg";
    $target_path = $upload_dir . $new_filename;

    // 1. Déplacer le fichier uploadé
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_path)) {
        
        // 2. Mettre à jour la base de données
        // On utilise REPLACE ou INSERT ... ON DUPLICATE KEY UPDATE pour éviter les doublons
        $stmt = $conn->prepare("REPLACE INTO cartes_etudiants (etudiant_id, photo_path, date_emission) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $etudiant_id, $target_path);
        
        if ($stmt->execute()) {
            echo "Succès ! La photo est mise à jour. <a href='carte_etudiant.php?id=$etudiant_id'>Voir la carte</a>";
        } else {
            echo "Erreur lors de la mise à jour de la base de données.";
        }
    } else {
        echo "Erreur lors de l'upload du fichier.";
    }
}
?>
