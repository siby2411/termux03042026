<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Balance âgée fournisseurs";
$page_icon = "calendar";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$today = date('Y-m-d');
$tranches = [
    '0-30 jours'  => [0, 30],
    '31-60 jours' => [31, 60],
    '61-90 jours' => [61, 90],
    'Plus de 90'  => [91, 9999]
];

$fournisseurs = $pdo->query("SELECT * FROM TIERS WHERE type = 'FOURNISSEUR' ORDER BY raison_sociale")->fetchAll();

$balances = [];
foreach ($fournisseurs as $f) {
    // Solde dû au fournisseur (crédit 401 - débit 401)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(CASE WHEN e.compte_credite_id = 401 THEN e.montant ELSE 0 END),0) - 
               COALESCE(SUM(CASE WHEN e.compte_debite_id = 401 THEN e.montant ELSE 0 END),0) as solde
        FROM ECRITURES_COMPTABLES e
        WHERE e.compte_credite_id = 401 OR e.compte_debite_id = 401
    ");
    $stmt->execute();
    $solde_total = $stmt->fetchColumn();

    // Factures fournisseurs non payées (crédit 401)
    $factures = $pdo->prepare("
        SELECT date_ecriture, montant, reference_piece,
               DATEDIFF(?, date_ecriture) as jours
        FROM ECRITURES_COMPTABLES
        WHERE compte_credite_id = 401 AND (lettrage_id IS NULL OR date_lettrage IS NULL)
        ORDER BY date_ecriture ASC
    ");
    $factures->execute([$today]);
    $list_factures = $factures->fetchAll();

    $tranches_montants = [];
    foreach ($tranches as $label => [$min, $max]) {
        $montant = 0;
        foreach ($list_factures as $fct) {
            if ($fct['jours'] >= $min && $fct['jours'] <= $max) {
                $montant += $fct['montant'];
            }
        }
        $tranches_montants[$label] = $montant;
    }

    $balances[$f['id']] = [
        'fournisseur' => $f,
        'solde_total' => $solde_total,
        'tranches'    => $tranches_montants
    ];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Balance âgée fournisseurs</h5>
                <small>Dettes par tranche d'âge</small>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Fournisseur</th>
                                <th class="text-end">0-30 j</th>
                                <th class="text-end">31-60 j</th>
                                <th class="text-end">61-90 j</th>
                                <th class="text-end">+90 j</th>
                                <th class="text-end">Total dû</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($balances as $b): ?>
                                <tr>
                                    <td><?= htmlspecialchars($b['fournisseur']['raison_sociale']) ?></td>
                                    <td class="text-end"><?= number_format($b['tranches']['0-30 jours'], 0, ',', ' ') ?> F</td>
                                    <td class="text-end"><?= number_format($b['tranches']['31-60 jours'], 0, ',', ' ') ?> F</td>
                                    <td class="text-end"><?= number_format($b['tranches']['61-90 jours'], 0, ',', ' ') ?> F</td>
                                    <td class="text-end"><?= number_format($b['tranches']['Plus de 90'], 0, ',', ' ') ?> F</td>
                                    <td class="text-end fw-bold"><?= number_format($b['solde_total'], 0, ',', ' ') ?> F</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
