<?php
// /var/www/piece_auto/public/modules/analyse_clients.php
$page_title = "Analyse Comportement Client";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Requête RFM (Correction : total_commande)
    $query = "SELECT 
                c.id_client, c.nom, c.prenom,
                SUM(cv.total_commande) as ltv,
                COUNT(cv.id_commande_vente) as frequence,
                DATEDIFF(NOW(), MAX(cv.date_commande)) as recence
              FROM CLIENTS c
              JOIN COMMANDE_VENTE cv ON c.id_client = cv.id_client
              GROUP BY c.id_client
              ORDER BY ltv DESC";
    
    $stmt = $db->query($query);
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo '<div class="alert alert-danger">Erreur d\'analyse : ' . $e->getMessage() . '</div>';
    $clients = [];
}

// Fonction simple de segmentation
function getSegment($r, $f, $m) {
    if ($r <= 30 && $f >= 5) return ['label' => 'Champion', 'color' => 'success'];
    if ($f >= 3) return ['label' => 'Fidèle', 'color' => 'primary'];
    if ($r > 90) return ['label' => 'À réactiver', 'color' => 'danger'];
    return ['label' => 'Nouveau / Standard', 'color' => 'info'];
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-users-cog text-primary"></i> Analyse RFM des Clients</h1>
        <span class="text-muted">Segmentation basée sur le comportement d'achat</span>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Client</th>
                            <th>Segment</th>
                            <th class="text-end">LTV (€)</th>
                            <th class="text-center">Fréquence</th>
                            <th class="text-center">Récence (Jours)</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $c): 
                            $segment = getSegment($c['recence'], $c['frequence'], $c['ltv']);
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($c['prenom'] . ' ' . $c['nom']) ?></strong>
                                <br><small class="text-muted">ID: #<?= $c['id_client'] ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?= $segment['color'] ?>">
                                    <?= $segment['label'] ?>
                                </span>
                            </td>
                            <td class="text-end fw-bold"><?= number_format($c['ltv'], 2, ',', ' ') ?> €</td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-light text-dark border">
                                    <?= $c['frequence'] ?> commandes
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($c['recence'] <= 7): ?>
                                    <span class="text-success"><i class="fas fa-bolt"></i> Très récent</span>
                                <?php else: ?>
                                    <?= $c['recence'] ?> j
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="fiche_client.php?id=<?= $c['id_client'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> Dossier
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="alert alert-info border-0 shadow-sm">
                <h5><i class="fas fa-info-circle"></i> Qu'est-ce que la LTV ?</h5>
                <p class="small mb-0">La <strong>Life Time Value</strong> représente la somme totale qu'un client a dépensée dans votre établissement depuis sa création.</p>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card bg-light border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title fw-bold">Stratégie suggérée</h5>
                    <p class="card-text">Utilisez le segment <span class="badge bg-danger">À réactiver</span> pour envoyer des coupons de réduction et faire revenir vos clients qui n'ont plus acheté depuis 3 mois.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
