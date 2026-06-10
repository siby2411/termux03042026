<?php
require_once 'includes/db.php'; 
include 'includes/header.php';

// Recherche par secteur ou compétences clés
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM offres WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND (secteur LIKE :s OR competences_cles LIKE :s OR titre LIKE :s)";
}
$sql .= " ORDER BY is_featured DESC, created_at DESC";

$stmt = $pdo->prepare($sql);
if (!empty($search)) $stmt->execute(['s' => "%$search%"]);
else $stmt->execute();
$offres = $stmt->fetchAll();
?>

<div class="fade-in">
    <!-- Bannière de Sponsoring Premium -->
    <div class="marquee-container bg-dark text-white py-2 text-center">
        <small>Partenaires Stratégiques : DANGOTE | WAVE | CISCO | ORANGE SÉNÉGAL</small>
    </div>

    <section class="container-fluid p-0 mb-5">
        <div class="row g-0">
            <div class="col-md-6"><img src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?q=80&w=1200&auto=format" class="img-fluid w-100" style="height: 350px; object-fit: cover;"></div>
            <div class="col-md-6 bg-primary text-white d-flex align-items-center p-5">
                <div>
                    <h2 class="display-5 fw-bold">Omega Informatique CONSULTING</h2>
                    <p class="h5">Mr. Mohamed Siby - Consultant en Informatique</p>
                    <a href="demande.php" class="btn btn-outline-light mt-3">Déposer mon profil</a>
                </div>
            </div>
        </div>
    </section>

    <div class="container my-5">
        <form method="GET" class="mb-5 row justify-content-center">
            <div class="col-md-6">
                <input type="text" name="search" class="form-control form-control-lg" placeholder="Rechercher par métier, compétence ou secteur..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-lg w-100">Explorer</button>
            </div>
        </form>

        <div class="row">
            <?php foreach ($offres as $offre): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 <?= $offre['is_featured'] ? 'border-warning shadow' : '' ?>">
                        <?php if ($offre['is_featured']): ?><div class="card-header bg-warning text-dark text-center fw-bold">SPONSORISÉ</div><?php endif; ?>
                        <div class="card-body">
                            <h5><?= htmlspecialchars($offre['titre']) ?></h5>
                            <span class="badge bg-secondary"><?= htmlspecialchars($offre['secteur']) ?></span>
                            <p class="small text-muted mt-2">Compétences: <?= htmlspecialchars($offre['competences_cles']) ?></p>
                        </div>
                        <div class="card-footer border-0 bg-transparent">
                            <a href="postuler.php?id=<?= $offre['id'] ?>" class="btn btn-outline-primary w-100">Postuler</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
