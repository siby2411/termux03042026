<?php 
include 'config.php'; 
include 'header.php';
$db = Database::getInstance(); $conn = $db->getConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Logique d'envoi de fichier (L'option Upload)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['img_file'])) {
    $target_dir = "uploads/vehicules/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $file_name = time() . "_" . basename($_FILES['img_file']['name']);
    if (move_uploaded_file($_FILES['img_file']['tmp_name'], $target_dir . $file_name)) {
        $stmt = $conn->prepare("INSERT INTO vehicule_images (vehicule_id, nom_fichier) VALUES (?, ?)");
        $stmt->execute([$id, $file_name]);
        echo "<div class='alert alert-success m-2'>Photo ajoutée !</div>";
    }
}

// Récupération des données
$res = $conn->query("SELECT v.*, m.nom as mod_nom, mar.nom as mar_nom FROM vehicules v 
                     JOIN modeles m ON v.modele_id = m.id 
                     JOIN marques mar ON m.marque_id = mar.id WHERE v.id = $id");
$vehicule = $res->fetch();
$images = $conn->query("SELECT * FROM vehicule_images WHERE vehicule_id = $id")->fetchAll();
?>

<div class="container mt-4">
    <div class="card border-primary mb-4 shadow">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="bi bi-camera-fill me-2"></i> AJOUTER UNE PHOTO AU DIAPORAMA
        </div>
        <div class="card-body bg-light">
            <form method="POST" enctype="multipart/form-data" class="row g-2">
                <div class="col-md-9">
                    <input type="file" name="img_file" class="form-control form-control-lg" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-dark btn-lg w-100 fw-bold">UPLOADER MAINTENANT</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="p-3 border rounded bg-white shadow-sm text-center">
                <?php if(!empty($images)): ?>
                    <img src="uploads/vehicules/<?= $images[0]['nom_fichier'] ?>" class="img-fluid rounded mb-3" style="max-height:400px;">
                    <div class="d-flex gap-2 overflow-auto py-2">
                        <?php foreach($images as $img): ?>
                            <img src="uploads/vehicules/<?= $img['nom_fichier'] ?>" width="100" class="img-thumbnail">
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="py-5 bg-light"><i class="bi bi-images display-1 opacity-25"></i><p>Aucune photo dans la galerie</p></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card border-0 shadow-sm p-4">
                <h1 class="fw-bold"><?= $vehicule['mar_nom'] ?> <?= $vehicule['mod_nom'] ?></h1>
                <h2 class="text-primary fw-bold"><?= number_format($vehicule['prix_vente'], 0, ',', ' ') ?> FCFA</h2>
                <hr>
                <div class="mb-3"><strong>Immatriculation :</strong> <span class="badge bg-dark"><?= $vehicule['immatriculation'] ?></span></div>
                <div class="mb-3"><strong>Énergie :</strong> <?= $vehicule['carburant'] ?: 'Non spécifié' ?></div>
                <div class="mb-3"><strong>Kilométrage :</strong> <?= number_format($vehicule['kilometrage'], 0, ',', ' ') ?> km</div>
                
                <div class="d-grid gap-2 mt-4">
                    <a href="index.php" class="btn btn-outline-dark">Retour au Tableau de Bord OMEGA</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
