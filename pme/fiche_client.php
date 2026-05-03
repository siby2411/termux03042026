<?php
include 'includes/db.php';
include 'includes/header.php';

$id = (int)$_GET['id'];
$client = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$client->execute([$id]);
$c = $client->fetch();

$commandes = $pdo->prepare("SELECT * FROM commandes WHERE client_nom = ? ORDER BY date_commande DESC");
$commandes->execute([$c['nom']]);
$historique = $commandes->fetchAll();
?>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-4">
                <div class="mb-3"><i class="fas fa-user-circle fa-5x text-secondary"></i></div>
                <h4><?= htmlspecialchars($c['nom']) ?></h4>
                <p class="text-muted"><?= $c['email'] ?></p>
                <hr>
                <div class="text-start">
                    <p><strong>Ville:</strong> <?= $c['ville'] ?></p>
                    <p><strong>Tel:</strong> <?= $c['telephone'] ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">Historique d'activité</div>
                <div class="card-body">
                    <table class="table">
                        <thead><tr><th>Date</th><th>Montant</th><th>État</th></tr></thead>
                        <tbody>
                            <?php foreach($historique as $h): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($h['date_commande'])) ?></td>
                                <td><?= number_format($h['total_ht'], 2) ?> €</td>
                                <td><span class="badge bg-info"><?= $h['etat'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
