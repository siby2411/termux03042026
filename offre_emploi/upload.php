<?php
require_once 'includes/db.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['cv'])) {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    
    $file_name = time() . '_' . basename($_FILES['cv']['name']);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES['cv']['tmp_name'], $target_file)) {
        // Insertion avec les nouveaux champs
        $sql = "INSERT INTO candidatures (nom, prenom, adresse, telephone, email, genre, experience, cv_path) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['nom'], 
            $_POST['prenom'], 
            $_POST['adresse'], 
            $_POST['telephone'], 
            $_POST['email'], 
            $_POST['genre'], 
            $_POST['experience'], 
            $target_file
        ]);
        echo "<div class='alert alert-success mt-4'>Candidature enregistrée avec succès !</div>";
    }
}
?>
<div class="container mt-4"><a href="demande.php" class="btn btn-primary">Retour au formulaire</a></div>
<?php include 'includes/footer.php'; ?>
