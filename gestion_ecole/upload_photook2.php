<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$etudiant_id = intval($_GET['id']);

if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    // 1. Récupérer l'ancien chemin pour le supprimer
    $stmt = $conn->prepare("SELECT photo_path FROM cartes_etudiants WHERE etudiant_id = ?");
    $stmt->bind_param("i", $etudiant_id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    if ($res && file_exists($res['photo_path'])) {
        unlink($res['photo_path']); // Supprime l'ancien fichier
    }

    // 2. Préparer le nouveau fichier
    $target_dir = "uploads/photos/";
    $file_name = "stu_" . $etudiant_id . "_" . time() . ".jpg";
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO cartes_etudiants (etudiant_id, photo_path, date_emission) VALUES (?, ?, NOW()) 
                                ON DUPLICATE KEY UPDATE photo_path = ?, date_emission = NOW()");
        $stmt->bind_param("iss", $etudiant_id, $target_file, $target_file);
        $stmt->execute();
        header("Location: crud_etudiants.php?success=photo");
    }
}
?>
