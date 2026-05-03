<?php
/**
 * OMEGA AUTO - VEHICULES.PHP
 * Liste du parc avec protection contre les UnhandledMatchError
 */
require_once 'config.php';
$db = Database::getInstance();

try {
    // Requête pour récupérer les véhicules avec leur marque et modèle
    $sql = "SELECT v.*, m.nom as modele_nom, mk.nom as marque_nom, 
            (SELECT nom_fichier FROM vehicule_images WHERE vehicule_id = v.id AND est_principale = 1 LIMIT 1) as photo
            FROM vehicules v
            JOIN modeles m ON v.modele_id = m.id
            JOIN marques mk ON m.marque_id = mk.id
            ORDER BY v.date_ajout DESC";
    
    $stmt = $db->query($sql);
    $vehicules = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Erreur : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Parc Automobile - OMEGA AUTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .card-img-top { height: 200px; object-fit: cover; background: #eee; }
        .badge-status { position: absolute; top: 10px; right: 10px; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3"><i class="bi bi-speedometer2 me-2"></i>Gestion du Parc Auto</h1>
            <a href="vehicule_ajouter.php" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Nouveau</a>
        </div>

        <div class="row g-4">
            <?php foreach ($vehicules as $v): 
                // PROTECTION CRITIQUE : Match avec valeur par défaut
                $status_class = match($v['statut'] ?? 'disponible') {
                    'disponible' => 'bg-success',
                    'vendu'      => 'bg-danger',
                    'loué'       => 'bg-info',
                    default      => 'bg-secondary'
                };
            ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <span class="badge badge-status <?= $status_class ?>"><?= strtoupper($v['statut'] ?? 'Inconnu') ?></span>
                    <img src="<?= $v['photo'] ? 'uploads/vehicules/'.$v['photo'] : 'https://via.placeholder.com/400x250?text=Pas+de+Photo' ?>" class="card-img-top">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($v['marque_nom'] . ' ' . $v['modele_nom']) ?></h5>
                        <p class="text-muted small"><?= $v['immatriculation'] ?> | <?= $v['carburant'] ?> | <?= $v['kilometrage'] ?> km</p>
                        <h6 class="text-primary"><?= number_format($v['prix_vente'], 0, ',', ' ') ?> FCFA</h6>
                    </div>
                    <div class="card-footer bg-white border-0 d-flex gap-2">
                        <a href="vehicule_details.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-dark w-100">Détails</a>
                        <a href="vehicule_edit.php?id=<?= $v['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
