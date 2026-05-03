<?php
/**
 * OMEGA AUTO - VEHICULE_DETAILS.PHP 
 * Version Finale : Robuste PHP 8.1+ & Bouton WhatsApp Mr SIBY
 */
require_once 'config.php';
$db = Database::getInstance();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $sql = "SELECT v.*, m.nom as modele_nom, mk.nom as marque_nom 
            FROM vehicules v
            JOIN modeles m ON v.modele_id = m.id
            JOIN marques mk ON m.marque_id = mk.id
            WHERE v.id = :id";
    
    $stmt = $db->getConnection()->prepare($sql);
    $stmt->execute(['id' => $id]);
    $v = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$v) {
        die("<div class='container mt-5 alert alert-warning'>Véhicule introuvable (ID: $id).</div>");
    }

    $sql_img = "SELECT * FROM vehicule_images WHERE vehicule_id = :id ORDER BY est_principale DESC";
    $stmt_img = $db->getConnection()->prepare($sql_img);
    $stmt_img->execute(['id' => $id]);
    $images = $stmt_img->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("<div class='alert alert-danger'>Erreur SQL : " . $e->getMessage() . "</div>");
}

$statut = strtolower($v['statut'] ?? 'disponible');
$badge_class = match($statut) {
    'disponible' => 'bg-success',
    'vendu'      => 'bg-danger',
    'loué'       => 'bg-info',
    default      => 'bg-secondary'
};

// --- CONFIGURATION WHATSAPP MR SIBY ---
$mon_telephone = "221776542803";
$nom_auto = htmlspecialchars(($v['marque_nom'] ?? '') . " " . ($v['modele_nom'] ?? ''));
$prix_auto = number_format((float)($v['prix_vente'] ?? 0), 0, ',', ' ');
$message_wa = urlencode("Bonjour Mr SIBY, je suis intéressé par le véhicule $nom_auto (Immat: {$v['immatriculation']}) affiché à $prix_auto FCFA sur OMEGA AUTO.");
$whatsapp_url = "https://wa.me/$mon_telephone?text=$message_wa";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche <?= $nom_auto ?> - OMEGA AUTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body { background-color: #f4f7f6; }
        .main-img { width: 100%; height: 400px; object-fit: cover; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .price-display { font-size: 2.2rem; font-weight: 800; color: #198754; }
        .btn-whatsapp { background-color: #25D366; color: white; border: none; font-weight: bold; }
        .btn-whatsapp:hover { background-color: #128C7E; color: white; }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <a href="vehicules.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Parc</a>
            <span class="badge <?= $badge_class ?> py-2 px-3"><?= strtoupper($statut) ?></span>
        </div>

        <div class="row">
            <div class="col-lg-7 mb-4">
                <?php if (!empty($images)): ?>
                    <img src="uploads/vehicules/<?= htmlspecialchars($images[0]['nom_fichier']) ?>" class="main-img" alt="Photo">
                <?php else: ?>
                    <div class="main-img bg-dark d-flex align-items-center justify-content-center text-white text-center">
                        <div><i class="bi bi-camera h1 d-block"></i>Aucune photo</div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-5">
                <div class="card border-0 shadow-sm p-4 h-100">
                    <h1 class="display-6 fw-bold"><?= htmlspecialchars($v['marque_nom'] ?? 'Inconnu') ?></h1>
                    <h2 class="h4 text-muted mb-4"><?= htmlspecialchars($v['modele_nom'] ?? 'Inconnu') ?></h2>
                    
                    <div class="price-display mb-4">
                        <?= $prix_auto ?> <small>FCFA</small>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <a href="<?= $whatsapp_url ?>" target="_blank" class="btn btn-whatsapp btn-lg">
                            <i class="bi bi-whatsapp me-2"></i>Commander sur WhatsApp
                        </a>

                        <?php if ($statut === 'disponible'): ?>
                            <a href="vente_enregistrer.php?vehicule_id=<?= $id ?>" class="btn btn-success">
                                <i class="bi bi-cart-plus me-2"></i>Enregistrer la Vente (Admin)
                            </a>
                        <?php endif; ?>
                    </div>

                    <ul class="list-group list-group-flush border-top">
                        <li class="list-group-item d-flex justify-content-between small"><span>Immat.</span> <strong><?= $v['immatriculation'] ?></strong></li>
                        <li class="list-group-item d-flex justify-content-between small"><span>Kilométrage</span> <strong><?= number_format((float)($v['kilometrage'] ?? 0), 0, ',', ' ') ?> km</strong></li>
                        <li class="list-group-item d-flex justify-content-between small"><span>Énergie</span> <strong><?= ucfirst($v['carburant'] ?? 'Non spécifié') ?></strong></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm p-4">
                    <h5><i class="bi bi-info-circle me-2"></i>Description & Options</h5>
                    <p class="mb-0 text-muted"><?= nl2br(htmlspecialchars($v['description'] ?? 'Aucune description disponible.')) ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
