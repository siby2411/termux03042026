<?php
require_once 'config/database.php';
include 'header.php';

$database = new Database();
$db = $database->getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Récupérer les infos de l'adhérent
$query = "SELECT * FROM adherents WHERE id = :id";
$stmt = $db->prepare($query);
$stmt->execute([':id' => $id]);
$adherent = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$adherent) {
    header('Location: adherents.php');
    exit;
}

// Récupérer les paiements de l'adhérent
$query = "SELECT * FROM paiements WHERE adherent_id = :id ORDER BY date_paiement DESC";
$stmt = $db->prepare($query);
$stmt->execute([':id' => $id]);
$paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-user"></i> Détails Adhérent - <?= htmlspecialchars($adherent['prenom'] . ' ' . $adherent['nom']) ?></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table">
                        <tr><th>Licence</th><td><?= $adherent['numero_licence'] ?></td></tr>
                        <tr><th>Nom complet</th><td><?= htmlspecialchars($adherent['prenom'] . ' ' . $adherent['nom']) ?></td></tr>
                        <tr><th>Email</th><td><?= $adherent['email'] ?></td></tr>
                        <tr><th>Téléphone</th><td><?= $adherent['telephone'] ?></td></tr>
                        <tr><th>Date naissance</th><td><?= $adherent['date_naissance'] ?></td></tr>
                        <tr><th>Discipline</th><td><span class="badge bg-info"><?= $adherent['discipline_principale'] ?></span></td></tr>
                        <tr><th>Statut</th><td><span class="badge bg-<?= $adherent['statut']=='actif'?'success':'danger' ?>"><?= $adherent['statut'] ?></span></td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Historique des paiements</h5>
                    <table class="table table-sm">
                        <thead><tr><th>Date</th><th>Montant</th><th>Mode</th></tr></thead>
                        <tbody>
                            <?php foreach($paiements as $p): ?>
                            <tr><td><?= $p['date_paiement'] ?></td><td><?= number_format($p['montant'],0,',',' ') ?> F</td><td><?= $p['mode_paiement'] ?></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <a href="adherents.php" class="btn btn-secondary">Retour</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
