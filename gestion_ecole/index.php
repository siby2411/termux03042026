<?php
include 'header_ecole.php';
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
?>

<div class="container mt-4">
    <div class="p-5 mb-5 rounded shadow-lg text-white" style="background: linear-gradient(135deg, #2c3e50, #34495e);">
        <h1 class="display-5 fw-bold">OMEGA INFORMATIQUE CONSULTING</h1>
        <p class="lead">Progiciel de Gestion pour École de Formation - Système LMD</p>
        <hr class="my-4 border-light">
        <p class="mb-0 text-white-50"><i class="bi bi-shield-check"></i> Environnement Client-Serveur Sécurisé</p>
    </div>

    <h5 class="mb-3 text-dark"><i class="bi bi-gear-fill me-2"></i>Administration</h5>
    <div class="row g-4 mb-5">
        <?php
        $admin_modules = [
            ['url' => 'crud_etudiants.php', 'icon' => 'bi-people', 'title' => 'Étudiants', 'color' => '#2980b9'],
            ['url' => 'classes.php', 'icon' => 'bi-building', 'title' => 'Classes', 'color' => '#7f8c8d'],
            ['url' => 'filieres.php', 'icon' => 'bi-book', 'title' => 'Filières', 'color' => '#16a085'],
            ['url' => 'generer_bulletin.php', 'icon' => 'bi-file-earmark-bar-graph', 'title' => 'Bulletins', 'color' => '#c0392b']
        ];
        foreach($admin_modules as $mod): ?>
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 shadow-sm" style="border-top: 5px solid <?= $mod['color'] ?>;">
                    <div class="card-body text-center">
                        <i class="bi <?= $mod['icon'] ?> fs-1 mb-2" style="color: <?= $mod['color'] ?>;"></i>
                        <h6 class="card-title fw-bold text-dark"><?= $mod['title'] ?></h6>
                        <a href="<?= $mod['url'] ?>" class="stretched-link"></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h5 class="mb-3 text-dark"><i class="bi bi-mortarboard-fill me-2"></i>Programmes Académiques</h5>
    <div class="row g-4 mb-5">
        <?php
        $academic_modules = [
            ['url' => 'crud_programmes.php', 'icon' => 'bi-journals', 'title' => 'Programmes', 'color' => '#d35400'],
            ['url' => 'crud_uv.php', 'icon' => 'bi-list-check', 'title' => 'Unités de Valeur', 'color' => '#8e44ad']
        ];
        foreach($academic_modules as $mod): ?>
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 shadow-sm" style="border-top: 5px solid <?= $mod['color'] ?>;">
                    <div class="card-body text-center">
                        <i class="bi <?= $mod['icon'] ?> fs-1 mb-2" style="color: <?= $mod['color'] ?>;"></i>
                        <h6 class="card-title fw-bold text-dark"><?= $mod['title'] ?></h6>
                        <a href="<?= $mod['url'] ?>" class="stretched-link"></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h5 class="mb-3 text-dark"><i class="bi bi-cash-stack me-2"></i>Gestion Financière</h5>
    <div class="row g-4">
        <?php
        $finance_modules = [
            ['url' => 'paiement_scolarite.php', 'icon' => 'bi-wallet2', 'title' => 'Scolarité', 'color' => '#2980b9'],
            ['url' => 'paiement_inscription.php', 'icon' => 'bi-pencil-square', 'title' => 'Inscription', 'color' => '#27ae60'],
            ['url' => 'recherche_fiche.php', 'icon' => 'bi-search', 'title' => 'Fiche Suivi', 'color' => '#8e44ad'],
            ['url' => 'etat_classe.php?classe_id=1', 'icon' => 'bi-graph-up', 'title' => 'État Financier', 'color' => '#f39c12']
        ];
        foreach($finance_modules as $mod): ?>
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 shadow-sm" style="border-top: 5px solid <?= $mod['color'] ?>;">
                    <div class="card-body text-center">
                        <i class="bi <?= $mod['icon'] ?> fs-1 mb-2" style="color: <?= $mod['color'] ?>;"></i>
                        <h6 class="card-title fw-bold text-dark"><?= $mod['title'] ?></h6>
                        <a href="<?= $mod['url'] ?>" class="stretched-link"></a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include 'footer_ecole.php'; ?>
