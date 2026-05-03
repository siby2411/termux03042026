<?php require_once '../../includes/header.php'; ?>
<div class="row mb-4">
    <div class="col"><h2>Répertoire Fournisseurs</h2></div>
    <div class="col text-end"><button class="btn btn-primary">+ Nouveau Fournisseur</button></div>
</div>
<div class="row">
    <?php 
    // Simulation ou requête réelle si la table existe
    $fournisseurs = [
        ['nom' => 'CFAO Motors', 'type' => 'Pièces Origine', 'contact' => '33 800 00 00'],
        ['nom' => 'Dakar Auto Pièces', 'type' => 'Adaptable', 'contact' => '77 500 00 00'],
        ['nom' => 'TotalEnergies', 'type' => 'Lubrifiants', 'contact' => '33 811 11 11']
    ];
    foreach($fournisseurs as $f): ?>
    <div class="col-md-4 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="fw-bold"><?= $f['nom'] ?></h5>
                <p class="badge bg-info text-dark"><?= $f['type'] ?></p>
                <p class="small mb-0"><i class="fas fa-phone me-2"></i><?= $f['contact'] ?></p>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php require_once '../../includes/footer.php'; ?>
