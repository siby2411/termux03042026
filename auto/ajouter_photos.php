<?php
session_start();
include 'db_connect.php';
if (!isset($_SESSION['admin_logged'])) { header("Location: login.php"); exit(); }

$voitures = $conn->query("SELECT id, marque, modele, immatriculation FROM voitures");
$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photos'])) {
    $v_id = $_POST['voiture_id'];
    $target_dir = "uploads/";

    foreach ($_FILES['photos']['name'] as $key => $name) {
        if (!empty($name)) {
            // Nettoyage du nom de fichier pour éviter les erreurs
            $file_ext = pathinfo($name, PATHINFO_EXTENSION);
            $new_filename = "auto_" . time() . "_" . $key . "." . $file_ext;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES['photos']['tmp_name'][$key], $target_file)) {
                $leg = mysqli_real_escape_string($conn, $_POST['legendes'][$key]);
                $path_db = mysqli_real_escape_string($conn, $target_file);
                $conn->query("INSERT INTO galerie (voiture_id, image_url, legende) VALUES ('$v_id', '$path_db', '$leg')");
                $message = "<div class='alert success'>✅ Photos téléchargées avec succès !</div>";
            } else {
                $message = "<div class='alert error'>❌ Échec de l'upload pour $name</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omega Auto | Upload Galerie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; padding: 20px; }
        .card { background: white; max-width: 600px; margin: auto; padding: 30px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .photo-row { background: #f8fafc; padding: 15px; border-radius: 12px; margin-bottom: 15px; border: 1px solid #e2e8f0; }
        input[type="file"] { margin-bottom: 10px; width: 100%; }
        .btn { background: #2563eb; color: white; border: none; padding: 15px; width: 100%; border-radius: 12px; cursor: pointer; font-weight: bold; font-size: 1rem; }
        .alert { padding: 15px; border-radius: 10px; margin-bottom: 20px; text-align: center; font-weight: bold; }
        .success { background: #dcfce7; color: #166534; }
        .error { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="color:#0f172a;"><i class="fas fa-camera-retro text-primary"></i> Upload de Photos</h2>
        <?php echo $message; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <label class="fw-bold">Véhicule concerné :</label>
            <select name="voiture_id" required style="width:100%; padding:12px; border-radius:10px; margin: 10px 0 20px 0;">
                <?php while($v = $voitures->fetch_assoc()): ?>
                    <option value="<?php echo $v['id']; ?>"><?php echo $v['marque']." ".$v['modele']." (".$v['immatriculation'].")"; ?></option>
                <?php endwhile; ?>
            </select>

            <div id="upload-area">
                <?php for($i=0; $i<3; $i++): ?>
                <div class="photo-row">
                    <label class="small text-muted">Photo <?php echo $i+1; ?></label>
                    <input type="file" name="photos[]" accept="image/*">
                    <input type="text" name="legendes[]" placeholder="Légende (ex: Vue intérieure)" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <?php endfor; ?>
            </div>

            <button type="submit" class="btn"><i class="fas fa-cloud-upload-alt me-2"></i> ENVOYER AU SERVEUR</button>
        </form>
        <p align="center" style="margin-top:20px;"><a href="dashboard.php" style="color:#64748b; text-decoration:none;"><i class="fas fa-arrow-left"></i> Retour</a></p>
    </div>
</body>
</html>
