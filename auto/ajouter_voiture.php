<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['admin_logged'])) { header("Location: login.php"); exit(); }

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $marque = mysqli_real_escape_string($conn, $_POST['marque']);
    $modele = mysqli_real_escape_string($conn, $_POST['modele']);
    $immat = mysqli_real_escape_string($conn, $_POST['immatriculation']);
    $usage = $_POST['type_usage'];
    $prix = floatval($_POST['prix']);
    $vitesse = $_POST['boite_vitesse'];
    $places = intval($_POST['nb_places']);
    $carburant = $_POST['carburant'];
    
    // Gestion de l'Upload Photo
    $image_path = "uploads/default.jpg";
    if (!empty($_FILES['photo_principale']['name'])) {
        $target_dir = "uploads/";
        $file_ext = pathinfo($_FILES['photo_principale']['name'], PATHINFO_EXTENSION);
        $new_filename = "principal_" . time() . "." . $file_ext;
        if (move_uploaded_file($_FILES['photo_principale']['tmp_name'], $target_dir . $new_filename)) {
            $image_path = $target_dir . $new_filename;
        }
    }

    $sql = "INSERT INTO voitures (marque, modele, immatriculation, type_usage, prix_journalier, boite_vitesse, nb_places, carburant, image_url, statut) 
            VALUES ('$marque', '$modele', '$immat', '$usage', '$prix', '$vitesse', '$places', '$carburant', '$image_path', 'Disponible')";

    if($conn->query($sql)) {
        $message = "<div class='alert alert-success'>✅ Véhicule enregistré avec succès !</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ Erreur : " . $conn->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omega Auto | Nouveau Véhicule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f1f5f9; padding: 20px; }
        .form-card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: none; padding: 30px; }
        .btn-primary { background: #2563eb; border: none; padding: 12px; font-weight: bold; }
    </style>
</head>
<body>
<div class="container" style="max-width: 800px;">
    <div class="card form-card">
        <h3 class="fw-bold text-primary mb-4"><i class="fas fa-plus-circle me-2"></i>Nouveau Véhicule</h3>
        <?php echo $message; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Marque</label>
                    <input type="text" name="marque" class="form-control" placeholder="Toyota" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Modèle</label>
                    <input type="text" name="modele" class="form-control" placeholder="Hilux" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Immatriculation</label>
                    <input type="text" name="immatriculation" class="form-control" placeholder="DK-..." required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Usage</label>
                    <select name="type_usage" class="form-select">
                        <option value="Location">Location</option>
                        <option value="Vente">Vente</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Transmission</label>
                    <select name="boite_vitesse" class="form-select">
                        <option value="Automatique">Automatique</option>
                        <option value="Manuelle">Manuelle</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Places</label>
                    <input type="number" name="nb_places" class="form-control" value="5">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Carburant</label>
                    <select name="carburant" class="form-select">
                        <option value="Diesel">Diesel</option>
                        <option value="Essence">Essence</option>
                        <option value="Hybride">Hybride</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Prix (FCFA)</label>
                    <input type="number" name="prix" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Photo Principale</label>
                    <input type="file" name="photo_principale" class="form-control" accept="image/*">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary w-100">ENREGISTRER</button>
                <a href="dashboard.php" class="btn btn-link w-100 text-muted mt-2 text-decoration-none">Retour Dashboard</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>
