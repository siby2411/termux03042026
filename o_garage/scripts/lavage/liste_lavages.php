<?php require_once '../../includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="fw-bold text-info">Registre Journalier - Lavage</h2>
    <a href="entree_lavage.php" class="btn btn-info text-white"><i class="fas fa-plus"></i> Nouveau Lavage</a>
</div>

<div class="row">
    <?php 
    $res = $db->query("SELECT * FROM lavage_transactions ORDER BY date_lavage DESC");
    while($l = $res->fetch()): ?>
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <span class="badge bg-light text-dark"><?= $l['date_lavage'] ?></span>
                    <i class="fas fa-soap text-info"></i>
                </div>
                <h5 class="mt-2 fw-bold text-primary"><?= $l['immatriculation'] ?></h5>
                <p class="mb-0"><?= $l['type_lavage'] ?></p>
                <h6 class="text-end fw-bold"><?= number_format($l['montant'], 0, ',', ' ') ?> F</h6>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php require_once '../../includes/footer.php'; ?>
