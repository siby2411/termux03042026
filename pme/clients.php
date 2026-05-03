<?php
include 'includes/db.php';
include 'includes/header.php';

// Récupération des clients (On s'assure que la table existe)
try {
    $clients = $pdo->query("SELECT * FROM clients ORDER BY nom ASC")->fetchAll();
} catch (Exception $e) {
    // Si la table n'existe pas encore, on la crée à la volée pour le test
    $pdo->exec("CREATE TABLE IF NOT EXISTS clients (id INT AUTO_INCREMENT PRIMARY KEY, nom VARCHAR(100), email VARCHAR(100), telephone VARCHAR(20), ville VARCHAR(50))");
    $clients = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 text-primary"><i class="fas fa-users-rectangle me-2"></i>Portefeuille Clients</h2>
    <button class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nouveau Client</button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nom / Raison Sociale</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Ville</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($clients) > 0): ?>
                    <?php foreach($clients as $c): ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($c['nom']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td><?= htmlspecialchars($c['telephone']) ?></td>
                        <td><?= htmlspecialchars($c['ville']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-info"><i class="fas fa-edit"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">Aucun client enregistré.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
