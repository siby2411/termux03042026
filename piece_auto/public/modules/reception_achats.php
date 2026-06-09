<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

$page_title = "Réception Achats";
$db = (new Database())->getConnection();

// Récupération des commandes d'achat non reçues
$query = "SELECT * FROM COMMANDES_ACHAT WHERE statut = 'En attente' ORDER BY date_commande DESC";
$commandes = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3"><h5><i class="fas fa-truck-loading me-2"></i> Réception de Marchandises</h5></div>
    <div class="card-body p-0">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr><th>Date</th><th>Fournisseur</th><th>Statut</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($commandes as $c): ?>
                <tr>
                    <td><?= $c['date_commande'] ?></td>
                    <td><?= htmlspecialchars($c['fournisseur']) ?></td>
                    <td><span class="badge bg-warning">En attente</span></td>
                    <td>
                        <form action="traitement_achat.php" method="POST">
                            <input type="hidden" name="id_commande" value="<?= $c['id_commande'] ?>">
                            <button type="submit" name="valider_reception" class="btn btn-sm btn-success">Valider Réception</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/footer.php'; ?>
