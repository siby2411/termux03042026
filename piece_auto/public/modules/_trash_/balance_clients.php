<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Balance clients - Soldes par client";
$page_icon = "people";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Récupération des clients (table TIERS)
$clients = $pdo->query("SELECT * FROM TIERS WHERE type = 'CLIENT' ORDER BY raison_sociale")->fetchAll();

$soldes = [];
foreach($clients as $c) {
    // Calcul du solde du client (compte 411)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(CASE WHEN compte_debite_id = 411 THEN montant ELSE 0 END),0) - 
               COALESCE(SUM(CASE WHEN compte_credite_id = 411 THEN montant ELSE 0 END),0) as solde
        FROM ECRITURES_COMPTABLES
        WHERE (compte_debite_id = 411 OR compte_credite_id = 411)
    ");
    $stmt->execute();
    $solde = $stmt->fetchColumn();
    
    // Détail des factures impayées (optionnel)
    $factures = $pdo->prepare("
        SELECT date_ecriture, libelle, montant, reference_piece
        FROM ECRITURES_COMPTABLES
        WHERE compte_debite_id = 411 AND lettrage_id IS NULL
        ORDER BY date_ecriture ASC
    ");
    $factures->execute();
    $liste_factures = $factures->fetchAll();
    
    $soldes[$c['id']] = [
        'client' => $c,
        'solde' => $solde,
        'factures' => $liste_factures
    ];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Balance clients - Soldes par client</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Code</th><th>Client</th><th class="text-end">Solde (F)</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($soldes as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['client']['code']) ?></td>
                                <td><?= htmlspecialchars($s['client']['raison_sociale']) ?></td>
                                <td class="text-end <?= $s['solde'] > 0 ? 'text-danger' : ($s['solde'] < 0 ? 'text-success' : '') ?>">
                                    <?= number_format($s['solde'], 0, ',', ' ') ?> F
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-info" onclick="afficherDetails(<?= htmlspecialchars(json_encode($s['factures'])) ?>)">Détails</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function afficherDetails(factures) {
    let txt = "Factures impayées :\n";
    factures.forEach(f => {
        txt += f.date_ecriture + " - " + f.libelle + " : " + new Intl.NumberFormat().format(f.montant) + " F\n";
    });
    alert(txt);
}
</script>

<?php include 'inc_footer.php'; ?>
