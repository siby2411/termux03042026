<?php
if (!isset($_GET['id'])) die("ID du bien manquant.");

$stmt = $pdo->prepare("SELECT i.*, z.nom as zone_nom FROM immeubles i JOIN zones_geographiques z ON i.zone_id = z.id WHERE i.id = ?");
$stmt->execute([$_GET['id']]);
$bien = $stmt->fetch();

if (!$bien) die("Bien introuvable.");
?>

<div class="no-print mb-4">
    <button onclick="window.print()" class="btn btn-warning shadow"><i class="bi bi-file-pdf"></i> Télécharger la Fiche PDF</button>
    <a href="?page=immeubles" class="btn btn-outline-secondary">Retour</a>
</div>

<div class="fiche-pdf-container p-5 bg-white shadow-lg mx-auto" style="max-width: 800px; border-top: 10px solid #D4AF37;">
    <div class="row mb-4">
        <div class="col-8">
            <h1 class="display-5 fw-bold text-uppercase" style="font-family: 'Playfair Display', serif;"><?= $bien['titre'] ?></h1>
            <p class="fs-5 text-muted"><i class="bi bi-geo-alt-fill"></i> Dakar, <?= $bien['zone_nom'] ?></p>
        </div>
        <div class="col-4 text-end">
            <h4 class="text-warning fw-bold">OMEGA IMMO</h4>
            <small class="text-muted">Expertise Informatique Consulting</small>
        </div>
    </div>

    <img src="<?= $bien['image_url'] ?>" class="img-fluid rounded mb-4 shadow" style="width: 100%; height: 400px; object-fit: cover;">

    <div class="row g-4 mb-5">
        <div class="col-4 border-end">
            <h6 class="text-muted small text-uppercase">Prix de vente</h6>
            <h3 class="fw-bold text-dark"><?= number_format($bien['prix'], 0, ',', ' ') ?> <small class="fs-6">FCFA</small></h3>
        </div>
        <div class="col-4 border-end text-center">
            <h6 class="text-muted small text-uppercase">Surface habitable</h6>
            <h3 class="fw-bold"><?= $bien['surface'] ?> m²</h3>
        </div>
        <div class="col-4 text-center">
            <h6 class="text-muted small text-uppercase">Statut actuel</h6>
            <h3 class="badge bg-<?= $bien['statut'] == 'Disponible' ? 'success' : 'danger' ?> fs-5"><?= $bien['statut'] ?></h3>
        </div>
    </div>

    <div class="p-4 bg-light rounded mb-5">
        <h5 class="fw-bold border-bottom pb-2 mb-3">Description technique</h5>
        <p class="text-secondary">Ce bien immobilier situé dans la zone prisée de <b><?= $bien['zone_nom'] ?></b> répond aux standards de qualité OMEGA. Idéal pour un investissement ou une résidence principale, il offre une surface optimisée de <?= $bien['surface'] ?> m².</p>
    </div>

    <div class="row mt-5 pt-5 border-top">
        <div class="col-6">
            <p class="small text-muted mb-0">Contact Agent : <b>+221 77 XXX XX XX</b></p>
            <p class="small text-muted">Email : contact@omega-consulting.sn</p>
        </div>
        <div class="col-6 text-end">
            <p class="small text-muted italic">Généré le <?= date('d/m/Y') ?> par OMEGA IMMO System</p>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, .navbar, .omega-top, footer, .theme-switch { display: none !important; }
    body { background: white !important; padding: 0 !important; }
    .fiche-pdf-container { box-shadow: none !important; border: none !important; width: 100% !important; max-width: 100% !important; padding: 0 !important; }
    .main { margin: 0 !important; padding: 0 !important; }
}
</style>
