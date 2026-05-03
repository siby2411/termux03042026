<?php
include 'includes/db.php';
$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM commandes WHERE id = ?");
$stmt->execute([$id]);
$c = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suivi de commande - PME ERP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="col-md-8 mx-auto text-center">
            <h2 class="mb-4 text-primary">Suivi de votre livraison</h2>
            <div class="card shadow border-0">
                <div class="card-body p-5">
                    <?php if ($c && $c['etat'] == 'expediee'): ?>
                        <div class="mb-4"><i class="fas fa-truck-fast fa-4x text-success"></i></div>
                        <h4>Statut : <span class="badge bg-success">En cours de livraison</span></h4>
                        <hr>
                        <p class="lead">Transporteur : <strong><?= $c['transporteur'] ?></strong></p>
                        <p>N° de suivi : <span class="badge bg-dark fs-6"><?= $c['numero_suivi'] ?></span></p>
                        <p class="text-muted small">Expédié le : <?= date('d/m/Y H:i', strtotime($c['date_expedition'])) ?></p>
                    <?php else: ?>
                        <div class="mb-4"><i class="fas fa-box fa-4x text-warning"></i></div>
                        <h4>Statut : <span class="badge bg-warning text-dark">En préparation</span></h4>
                        <p class="mt-3">Votre commande est en cours de traitement dans nos entrepôts.</p>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white py-3">
                    <p class="mb-0 small text-muted">Merci de votre confiance - 2026 ERP Business</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
