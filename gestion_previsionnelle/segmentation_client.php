<?php
$page_title = "Segmentation et Analyse Clients (RFM)";
include_once __DIR__ . '/config/db.php';
include_once __DIR__ . '/includes/header.php'; 

$database = new Database();
$db = $database->getConnection();
$today = date('Y-m-d');
$message_rfm = "";

try {
    // 1. Requête RFM (Récence, Fréquence, Montant)
    $query_rfm = "
        SELECT 
            C.ClientID, 
            C.Nom,
            C.Ville,
            DATEDIFF(:today, MAX(Cmd.DateCommande)) AS Recence,
            COUNT(Cmd.CommandeID) AS Frequence,
            COALESCE(SUM(Cmd.MontantTotal), 0) AS MontantTotal
        FROM Clients C
        LEFT JOIN Commandes Cmd ON C.ClientID = Cmd.ClientID
        GROUP BY C.ClientID, C.Nom, C.Ville
        ORDER BY Recence ASC, MontantTotal DESC, Frequence DESC
    ";
    $stmt_rfm = $db->prepare($query_rfm);
    $stmt_rfm->bindParam(':today', $today);
    $stmt_rfm->execute();
    $rfm_results = $stmt_rfm->fetchAll(PDO::FETCH_ASSOC);

    // 2. Détermination des Quintiles/Score (Simplifié)
    $num_clients = count($rfm_results);
    if ($num_clients > 0) {
        // Pour une segmentation simple, nous utilisons la moyenne comme seuil.
        $avg_recence = array_sum(array_column($rfm_results, 'Recence')) / $num_clients;
        $avg_frequence = array_sum(array_column($rfm_results, 'Frequence')) / $num_clients;
        $avg_montant = array_sum(array_column($rfm_results, 'MontantTotal')) / $num_clients;
    } else {
        $avg_recence = $avg_frequence = $avg_montant = 0;
    }

} catch (PDOException $e) {
    $message_rfm = "<div class='alert alert-danger'>Erreur SQL lors du calcul RFM: " . $e->getMessage() . "</div>";
    $rfm_results = [];
}


// --- Fonction de Segmentation RFM (Simplifiée) ---
function get_rfm_segment($recence, $frequence, $montant, $avg_r, $avg_f, $avg_m) {
    if ($recence <= 10 && $montant > 0) return ['Champions', 'bg-success']; // Récemment acheté, grosse valeur
    if ($recence <= $avg_r && $frequence >= $avg_f) return ['Clients Fidèles', 'bg-primary']; // Récents et fréquents
    if ($recence > 30 && $frequence < $avg_f && $montant > 0) return ['À risque', 'bg-warning']; // Anciens et peu fréquents, mais ont déjà acheté
    if ($montant > 0 && $recence > $avg_r) return ['Hibernants', 'bg-secondary']; // Longtemps sans achat, mais haute valeur
    if ($montant == 0) return ['Nouveaux/Inactifs', 'bg-info']; // Nouveau client sans commande
    return ['Autres', 'bg-light text-dark'];
}
?>

<h1 class="mt-4 text-center"><i class="fas fa-chart-pie me-2"></i> Segmentation des Clients (RFM)</h1>
<p class="text-muted text-center">Analyse comportementale basée sur la Récence, la Fréquence et le Montant des achats.</p>
<hr>

<?= $message_rfm ?>

<div class="row justify-content-center">
    <div class="col-lg-12">
        <div class="card shadow-lg mb-4 border-0">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="fas fa-list me-2"></i> Détail par Client (Seuils: Récence moy: <?= round($avg_recence) ?> j / Fréquence moy: <?= round($avg_frequence, 1) ?> cmd / Montant moy: <?= number_format($avg_montant, 0) ?> F)
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-custom mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th style="width: 25%;">Client</th>
                                <th style="width: 15%;" class="text-center">Récence (jours)</th>
                                <th style="width: 15%;" class="text-center">Fréquence (cmd)</th>
                                <th style="width: 15%;" class="text-end">Montant Total (€)</th>
                                <th style="width: 30%;" class="text-center">Segment RFM</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($rfm_results)): ?>
                            <?php foreach ($rfm_results as $client): 
                                $segment = get_rfm_segment($client['Recence'], $client['Frequence'], $client['MontantTotal'], $avg_recence, $avg_frequence, $avg_montant);
                                $segment_name = $segment[0];
                                $segment_class = $segment[1];
                            ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($client['Nom']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($client['Recence']) ?></td>
                                <td class="text-center"><?= htmlspecialchars($client['Frequence']) ?></td>
                                <td class="text-end"><?= number_format($client['MontantTotal'], 2, ',', ' ') ?></td>
                                <td class="text-center">
                                    <span class="badge <?= $segment_class ?>"><?= $segment_name ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted p-4">Aucune donnée de commande pour effectuer la segmentation.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
