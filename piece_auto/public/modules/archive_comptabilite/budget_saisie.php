<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Saisie budgétaire";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$exercice = $_GET['exercice'] ?? date('Y');
$type_budget = $_GET['type'] ?? 'VENTES';

// Sauvegarde des budgets
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    for ($mois = 1; $mois <= 12; $mois++) {
        $montant = (float)($_POST["budget_$mois"] ?? 0);
        $stmt = $pdo->prepare("INSERT INTO BUDGETS (exercice, mois, type_budget, montant_prevu) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE montant_prevu = ?");
        $stmt->execute([$exercice, $mois, $type_budget, $montant, $montant]);
    }
    $message = "✅ Budget $type_budget enregistré pour $exercice";
}

// Récupération des budgets existants
$budgets = [];
$stmt = $pdo->prepare("SELECT mois, montant_prevu FROM BUDGETS WHERE exercice = ? AND type_budget = ?");
$stmt->execute([$exercice, $type_budget]);
while ($row = $stmt->fetch()) {
    $budgets[$row['mois']] = $row['montant_prevu'];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calculator"></i> Saisie budgétaire</h5>
                <small>Exercice <?= $exercice ?> - Type : <?= $type_budget ?></small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label>Exercice</label>
                        <select name="exercice" class="form-select" onchange="this.form.submit()">
                            <option value="2025" <?= $exercice == 2025 ? 'selected' : '' ?>>2025</option>
                            <option value="2026" <?= $exercice == 2026 ? 'selected' : '' ?>>2026</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Type de budget</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="VENTES" <?= $type_budget == 'VENTES' ? 'selected' : '' ?>>Ventes</option>
                            <option value="PRODUCTION" <?= $type_budget == 'PRODUCTION' ? 'selected' : '' ?>>Production</option>
                            <option value="ACHATS" <?= $type_budget == 'ACHATS' ? 'selected' : '' ?>>Achats</option>
                            <option value="CHARGES_PERSO" <?= $type_budget == 'CHARGES_PERSO' ? 'selected' : '' ?>>Charges de personnel</option>
                            <option value="CHARGES_GENERALES" <?= $type_budget == 'CHARGES_GENERALES' ? 'selected' : '' ?>>Charges générales</option>
                            <option value="INVESTISSEMENTS" <?= $type_budget == 'INVESTISSEMENTS' ? 'selected' : '' ?>>Investissements</option>
                        </select>
                    </div>
                </form>

                <form method="POST">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Mois</th><th>Montant prévu (F)</th></tr>
                            </thead>
                            <tbody>
                                <?php
                                $mois_noms = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
                                for ($i = 1; $i <= 12; $i++): ?>
                                    <tr>
                                        <th><?= $mois_noms[$i-1] ?></th>
                                        <td><input type="number" name="budget_<?= $i ?>" class="form-control" step="100000" value="<?= $budgets[$i] ?? 0 ?>"></td>
                                    </tr>
                                <?php endfor; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary">
                                    <td colspan="2" class="text-center"><button type="submit" class="btn-omega">Enregistrer le budget</button></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
