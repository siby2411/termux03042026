<?php 
include 'config.php'; 
include 'header.php';
$db = Database::getInstance(); $conn = $db->getConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 1. TRAITEMENT UPLOAD
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['img_file'])) {
    $target_dir = "uploads/vehicules/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    $file_name = time() . "_" . basename($_FILES['img_file']['name']);
    if (move_uploaded_file($_FILES['img_file']['tmp_name'], $target_dir . $file_name)) {
        $conn->prepare("INSERT INTO vehicule_images (vehicule_id, nom_fichier) VALUES (?, ?)")->execute([$id, $file_name]);
    }
    header("Location: vehicule_detail.php?id=$id");
    exit;
}

// 2. RÉCUPÉRATION DES DONNÉES
$res = $conn->query("SELECT v.*, m.nom as mod_nom, mar.nom as mar_nom FROM vehicules v 
                     JOIN modeles m ON v.modele_id = m.id 
                     JOIN marques mar ON m.marque_id = mar.id WHERE v.id = $id");
$vehicule = $res->fetch();

if(!$vehicule) { echo "<div class='container mt-5 alert alert-danger'>Véhicule introuvable.</div>"; include 'footer.php'; exit; }

// 3. RÉCUPÉRATION DE LA GALERIE
$images = $conn->query("SELECT * FROM vehicule_images WHERE vehicule_id = $id ORDER BY id DESC")->fetchAll();

// Image principale : Soit la 1ère de la galerie, soit un placeholder OMEGA
$image_principale = (!empty($images)) ? "uploads/vehicules/".$images[0]['nom_fichier'] : "https://via.placeholder.com/600x400/0f172a/D4AF37?text=OMEGA+CONSULTING";

// 4. CONFIGURATION WHATSAPP
$telephone_wa = "221776542803";
$message_wa = urlencode("Bonjour OMEGA CONSULTING, je souhaite commander ce véhicule : " . $vehicule['mar_nom'] . " " . $vehicule['mod_nom'] . " (Prix : " . number_format($vehicule['prix_vente'] ?? 0, 0, ',', ' ') . " FCFA)");
$url_whatsapp = "https://wa.me/" . $telephone_wa . "?text=" . $message_wa;
?>

<div class="container py-4">
    <div class="card border-warning mb-4 shadow-sm bg-light">
        <div class="card-body py-2">
            <form method="POST" enctype="multipart/form-data" class="row g-2 align-items-center">
                <div class="col-md-3 small fw-bold"><i class="bi bi-camera-fill me-1"></i> AJOUTER UNE PHOTO :</div>
                <div class="col-md-6"><input type="file" name="img_file" class="form-control form-control-sm" required></div>
                <div class="col-md-3"><button type="submit" class="btn btn-dark btn-sm w-100 fw-bold">UPLOADER</button></div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="bg-white p-3 rounded shadow-sm border">
                <div class="main-display mb-3 text-center" style="height: 400px; background: #f8f9fa; border-radius: 12px; overflow: hidden; border: 1px solid #eee; display: flex; align-items: center; justify-content: center;">
                    <img src="<?= $image_principale ?>" id="viewBox" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                </div>
                
                <?php if(!empty($images)): ?>
                <div class="d-flex gap-2 overflow-auto py-2">
                    <?php foreach($images as $img): ?>
                        <img src="uploads/vehicules/<?= $img['nom_fichier'] ?>" 
                             class="rounded border shadow-sm" style="width: 80px; height: 60px; object-fit: cover; cursor: pointer;"
                             onclick="document.getElementById('viewBox').src=this.src">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-right: 6px solid #D4AF37 !important;">
                <h6 class="text-muted small fw-bold mb-1">RÉFÉRENCE #<?= $vehicule['id'] ?></h6>
                <h1 class="fw-bold text-dark mb-0"><?= $vehicule['mar_nom'] ?></h1>
                <h2 class="text-secondary fw-light mb-4"><?= $vehicule['mod_nom'] ?></h2>
                
                <div class="bg-dark text-warning p-3 rounded-4 mb-4 text-center shadow-sm">
                    <h2 class="fw-bold mb-0"><?= number_format($vehicule['prix_vente'] ?? 0, 0, ',', ' ') ?> <small class="h6">FCFA</small></h2>
                </div>

                <div class="list-group list-group-flush mb-4 shadow-sm rounded">
                    <div class="list-group-item d-flex justify-content-between p-3">
                        <span class="text-muted"><i class="bi bi-hash me-2"></i>Immatriculation</span>
                        <span class="fw-bold"><?= $vehicule['immatriculation'] ?></span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between p-3">
                        <span class="text-muted"><i class="bi bi-speedometer2 me-2"></i>Kilométrage</span>
                        <span class="fw-bold"><?= number_format($vehicule['kilometrage'] ?? 0, 0, ',', ' ') ?> km</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between p-3">
                        <span class="text-muted"><i class="bi bi-fuel-pump me-2"></i>Carburant</span>
                        <span class="fw-bold"><?= $vehicule['carburant'] ?: 'Essence' ?></span>
                    </div>
                </div>

                <a href="<?= $url_whatsapp ?>" target="_blank" class="btn btn-success btn-lg w-100 py-3 mb-3 fw-bold shadow-sm" style="background-color: #25D366; border:none;">
                    <i class="bi bi-whatsapp me-2"></i> COMMANDER SUR WHATSAPP
                </a>

                <div class="d-grid gap-2">
                    <a href="index.php" class="btn btn-outline-dark fw-bold py-2">← Retour au Parc</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
