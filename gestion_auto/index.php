<?php
include 'config.php';
include 'header.php';
$db = Database::getInstance();
$conn = $db->getConnection();

// Traitement de l'upload rapide depuis la liste
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['quick_upload'])) {
    $v_id = intval($_POST['vehicule_id']);
    $target_dir = "uploads/vehicules/";
    if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
    
    $file_name = time() . "_" . basename($_FILES['quick_upload']['name']);
    if (move_uploaded_file($_FILES['quick_upload']['tmp_name'], $target_dir . $file_name)) {
        $stmt = $conn->prepare("INSERT INTO vehicule_images (vehicule_id, nom_fichier) VALUES (?, ?)");
        $stmt->execute([$v_id, $file_name]);
        echo "<div class='alert alert-success'>Photo ajoutée avec succès au véhicule #$v_id</div>";
    }
}

// Récupération de tout le parc
$query = $conn->query("
    SELECT v.*, m.nom as mod_nom, mar.nom as mar_nom 
    FROM vehicules v
    JOIN modeles m ON v.modele_id = m.id
    JOIN marques mar ON m.marque_id = mar.id
    ORDER BY v.id DESC
");
$parc = $query->fetchAll();
?>

<div class="container-fluid py-4">
    <div class="row g-2 mb-4">
        <div class="col-md-3"><a href="vehicule_ajouter.php" class="btn btn-primary w-100 py-3 fw-bold"><i class="bi bi-plus-circle"></i> AJOUTER VOITURE</a></div>
        <div class="col-md-3"><a href="ventes.php" class="btn btn-success w-100 py-3 fw-bold"><i class="bi bi-cart-check"></i> NOUVELLE VENTE</a></div>
        <div class="col-md-3"><a href="locations.php" class="btn btn-warning w-100 py-3 fw-bold text-white"><i class="bi bi-calendar-event"></i> LOCATION</a></div>
        <div class="col-md-3"><a href="vehicules.php" class="btn btn-dark w-100 py-3 fw-bold"><i class="bi bi-car-front"></i> ÉTAT DU PARC</a></div>
    </div>

    <h2 class="fw-bold mb-4 border-bottom pb-2">GESTION DU PARC AUTOMOBILE</h2>

    <div class="row g-3">
        <?php foreach($parc as $v): 
            // Récupérer l'image principale pour l'affichage ici
            $img_res = $conn->query("SELECT nom_fichier FROM vehicule_images WHERE vehicule_id = {$v['id']} LIMIT 1")->fetch();
            $photo = $img_res ? "uploads/vehicules/".$img_res['nom_fichier'] : "https://via.placeholder.com/300x200?text=Sans+Photo";
        ?>
        <div class="col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                <div class="position-relative">
                    <img src="<?= $photo ?>" class="card-img-top" style="height: 180px; object-fit: cover;">
                    <span class="position-absolute top-0 end-0 m-2 badge bg-<?= $v['statut']=='disponible'?'success':'danger' ?>">
                        <?= strtoupper($v['statut']) ?>
                    </span>
                </div>

                <div class="card-body p-3">
                    <h6 class="fw-bold mb-1"><?= $v['mar_nom'] ?> <?= $v['mod_nom'] ?></h6>
                    <p class="text-primary fw-bold mb-2"><?= number_format($v['prix_vente'], 0, ',', ' ') ?> FCFA</p>
                    <p class="small text-muted mb-3"><i class="bi bi-hash"></i> <?= $v['immatriculation'] ?> | <?= $v['carburant'] ?></p>
                    
                    <div class="bg-light p-2 rounded border mb-3">
                        <label class="small fw-bold text-dark d-block mb-1">Ajouter à la galerie :</label>
                        <form method="POST" enctype="multipart/form-data" class="d-flex gap-1">
                            <input type="hidden" name="vehicule_id" value="<?= $v['id'] ?>">
                            <input type="file" name="quick_upload" class="form-control form-control-sm" required>
                            <button type="submit" class="btn btn-dark btn-sm"><i class="bi bi-upload"></i></button>
                        </form>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="vehicule_detail.php?id=<?= $v['id'] ?>" class="btn btn-outline-primary btn-sm fw-bold">VOIR DÉTAILS & GALERIE</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
