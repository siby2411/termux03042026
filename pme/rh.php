<?php
include 'includes/db.php';
include 'includes/header.php';

// Liste des employés par service
$sql = "SELECT u.*, s.nom_service FROM users u LEFT JOIN services s ON u.service_id = s.id ORDER BY s.nom_service, u.nom";
$employes = $pdo->query($sql)->fetchAll();
?>

<h1 class="h2 border-bottom pb-2 text-info"><i class="fas fa-users"></i> Annuaire RH</h1>

<div class="row mt-4">
    <?php foreach($employes as $emp): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-user-circle fa-4x text-secondary"></i>
                </div>
                <h5 class="card-title"><?= htmlspecialchars($emp['prenom'] . ' ' . $emp['nom']) ?></h5>
                <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($emp['nom_service']) ?></h6>
                <p class="card-text small">
                    <i class="fas fa-envelope"></i> <?= htmlspecialchars($emp['email']) ?><br>
                    <span class="badge bg-light text-dark border mt-2">Rôle : <?= $emp['role'] ?></span>
                </p>
                <a href="mailto:<?= $emp['email'] ?>" class="btn btn-sm btn-outline-primary">Contacter</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>
