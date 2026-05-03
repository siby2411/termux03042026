<?php require_once '../../includes/header.php'; ?>
<div class="container-fluid">
    <h2 class="mb-4"><i class="fas fa-spinner fa-spin me-2 text-primary"></i>Suivi des Réparations en Cours</h2>
    <div class="row g-3">
        <?php
        $query = "SELECT f.*, v.immatriculation, v.marque, v.modele FROM fiches_intervention f 
                  JOIN vehicules v ON f.id_vehicule = v.id_vehicule 
                  WHERE f.statut != 'Terminé' ORDER BY f.date_entree DESC";
        $res = $db->query($query);
        while($f = $res->fetch()):
            $badge_color = ($f['statut'] == 'En cours') ? 'bg-warning' : 'bg-secondary';
        ?>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge <?= $badge_color ?>"><?= $f['statut'] ?></span>
                        <small class="text-muted"><?= $f['date_entree'] ?></small>
                    </div>
                    <h5 class="card-title"><?= $f['immatriculation'] ?></h5>
                    <p class="card-text small"><?= $f['marque'] ?> <?= $f['modele'] ?> - <?= $f['description_panne'] ?></p>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 50%"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<?php require_once '../../includes/footer.php'; ?>
