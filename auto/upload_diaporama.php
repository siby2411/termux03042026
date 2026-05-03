<?php
session_start();
include_once "db_connect.php";
include_once "header.php";

if (!isset($_SESSION['admin_logged'])) { header("Location: login.php"); exit(); }

$id_voiture = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['voiture_id']) ? intval($_POST['voiture_id']) : null);
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && $id_voiture) {
    $upload_dir = "uploads/galerie/" . $id_voiture . "/";
    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }

    if (!empty($_FILES['diaporama_images']['name'][0])) {
        foreach ($_FILES['diaporama_images']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['diaporama_images']['name'][$key];
            $ext = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_name = "slide_" . uniqid() . "." . $ext;
            $target = $upload_dir . $new_name;
            
            if (move_uploaded_file($tmp_name, $target)) {
                $conn->query("INSERT INTO images_voitures (voiture_id, chemin_image) VALUES ($id_voiture, '$target')");
            }
        }
        $message = "<div class='alert alert-success shadow-sm'>✅ Photos ajoutées avec succès !</div>";
    }
}

$nom_v = "";
if($id_voiture) {
    $res = $conn->query("SELECT marque, modele FROM voitures WHERE id = $id_voiture");
    $row = $res->fetch_assoc();
    $nom_v = $row['marque'] . " " . $row['modele'];
}
?>

<div class="container my-5">
    <div class="card border-0 shadow-lg rounded-4 p-4 mx-auto" style="max-width: 550px;">
        <h3 class="fw-bold text-primary mb-4 text-center"><i class="fas fa-camera-retro me-2"></i>Diaporama : <?php echo $nom_v; ?></h3>
        <?php echo $message; ?>

        <?php if ($id_voiture): ?>
        <form method="POST" enctype="multipart/form-data" class="p-3 bg-light rounded-3 border">
            <input type="hidden" name="voiture_id" value="<?php echo $id_voiture; ?>">
            <div class="mb-4">
                <label class="form-label fw-bold">Sélectionner les images</label>
                <input type="file" name="diaporama_images[]" class="form-control form-control-lg" multiple accept="image/*" required>
                <small class="text-muted">Vous pouvez sélectionner plusieurs photos à la fois.</small>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold">🚀 ENVOYER AU SERVEUR</button>
        </form>
        <?php else: ?>
            <div class="alert alert-warning">ID Voiture manquant. Retournez au dashboard.</div>
        <?php endif; ?>
        <a href="dashboard.php" class="btn btn-link w-100 mt-3 text-muted text-decoration-none">← Retour Dashboard</a>
    </div>
</div>
<?php include_once "footer.php"; ?>
