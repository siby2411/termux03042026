<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "États Financiers - Vue d'ensemble";
$page_icon = "file-text";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$exercice = $_GET['exercice'] ?? date('Y');

// Récupération des soldes par compte
$sql = "
    SELECT 
        c.compte_id,
        c.intitule_compte,
        COALESCE(SUM(CASE WHEN e.compte_debite_id = c.compte_id THEN e.montant ELSE 0 END), 0) as total_debit,
        COALESCE(SUM(CASE WHEN e.compte_credite_id = c.compte_id THEN e.montant ELSE 0 END), 0) as total_credit,
        -- Détermination du solde selon la nature du compte (si compte actif = débit - crédit, si passif = crédit - débit)
        CASE 
            WHEN c.compte_id BETWEEN 100 AND 199 THEN COALESCE(SUM(CASE WHEN e.compte_credite_id = c.compte_id THEN e.montant ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN e.compte_debite_id = c.compte_id THEN e.montant ELSE 0 END), 0)
            WHEN c.compte_id BETWEEN 200 AND 599 THEN COALESCE(SUM(CASE WHEN e.compte_debite_id = c.compte_id THEN e.montant ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN e.compte_credite_id = c.compte_id THEN e.montant ELSE 0 END), 0)
            WHEN c.compte_id BETWEEN 600 AND 799 THEN COALESCE(SUM(CASE WHEN e.compte_debite_id = c.compte_id THEN e.montant ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN e.compte_credite_id = c.compte_id THEN e.montant ELSE 0 END), 0)
            ELSE COALESCE(SUM(CASE WHEN e.compte_debite_id = c.compte_id THEN e.montant ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN e.compte_credite_id = c.compte_id THEN e.montant ELSE 0 END), 0)
        END as solde
    FROM PLAN_COMPTABLE_UEMOA c
    LEFT JOIN ECRITURES_COMPTABLES e ON c.compte_id IN (e.compte_debite_id, e.compte_credite_id) AND YEAR(e.date_ecriture) = ?
    GROUP BY c.compte_id, c.intitule_compte
    HAVING solde != 0
    ORDER BY c.compte_id
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$exercice]);
$comptes = $stmt->fetchAll();

// Agrégation par classe
$classes = [
    1 => ['nom' => 'Capitaux propres', 'total' => 0],
    2 => ['nom' => 'Immobilisations', 'total' => 0],
    3 => ['nom' => 'Stocks', 'total' => 0],
    4 => ['nom' => 'Tiers', 'total' => 0],
    5 => ['nom' => 'Trésorerie', 'total' => 0],
    6 => ['nom' => 'Charges', 'total' => 0],
    7 => ['nom' => 'Produits', 'total' => 0]
];

foreach($comptes as $c) {
    $classe = floor($c['compte_id'] / 100);
    if(isset($classes[$classe])) {
        $classes[$classe]['total'] += $c['solde'];
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-file-text"></i> États Financiers - Exercice <?= $exercice ?></h5>
                <small>Vue d'ensemble des comptes et soldes</small>
            </div>
            <div class="card-body">
                <!-- Sélection exercice -->
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label>Exercice</label>
                        <select name="exercice" class="form-select" onchange="this.form.submit()">
                            <option value="2025" <?= $exercice == 2025 ? 'selected' : '' ?>>2025</option>
                            <option value="2026" <?= $exercice == 2026 ? 'selected' : '' ?>>2026</option>
                        </select>
                    </div>
                </form>

                <!-- Synthèse par classe -->
                <div class="row mb-4">
                    <?php foreach($classes as $classe => $data): ?>
                    <div class="col-md-3 col-sm-6 mb-2">
                        <div class="card text-center border-primary">
                            <div class="card-body">
                                <h6>Classe <?= $classe ?> - <?= $data['nom'] ?></h6>
                                <h5 class="<?= $data['total'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format(abs($data['total']), 0, ',', ' ') ?> F
                                </h5>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tableau détaillé -->
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>N° compte</th>
                                <th>Intitulé</th>
                                <th class="text-end">Total débit (F)</th>
                                <th class="text-end">Total crédit (F)</th>
                                <th class="text-end">Solde (F)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($comptes as $c): ?>
                            <tr>
                                <td class="text-center"><?= $c['compte_id'] ?> </td>
                                <td><?= htmlspecialchars($c['intitule_compte']) ?></td>
                                <td class="text-end"><?= number_format($c['total_debit'], 0, ',', ' ') ?></td>
                                <td class="text-end"><?= number_format($c['total_credit'], 0, ',', ' ') ?></td>
                                <td class="text-end <?= $c['solde'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format(abs($c['solde']), 0, ',', ' ') ?> 
                                    <?= $c['solde'] >= 0 ? 'D' : 'C' ?>
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

<?php include 'inc_footer.php'; ?>
