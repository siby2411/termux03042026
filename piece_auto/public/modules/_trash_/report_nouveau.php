<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Report à nouveau";
$page_icon = "arrow-right-circle";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Calcul du résultat net de l'exercice
$resultat_net = $pdo->query("
    SELECT 
        COALESCE(SUM(CASE WHEN compte_credite_id BETWEEN 700 AND 799 THEN montant ELSE 0 END), 0) -
        COALESCE(SUM(CASE WHEN compte_debite_id BETWEEN 600 AND 699 THEN montant ELSE 0 END), 0) as resultat
    FROM ECRITURES_COMPTABLES
    WHERE YEAR(date_ecriture) = YEAR(CURRENT_DATE)
")->fetchColumn();

// Affichage des reports à nouveau existants
$reports = $pdo->query("
    SELECT r.*, c.intitule_compte
    FROM REPORT_NOUVEAU r
    JOIN PLAN_COMPTABLE_UEMOA c ON r.compte_id = c.compte_id
    ORDER BY r.exercice DESC
")->fetchAll();

$report_total = array_sum(array_column($reports, 'montant'));

// Traitement du report
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exercice = (int)$_POST['exercice'];
    $type_compte = $_POST['type_compte'];
    $affectation = $_POST['affectation'];
    $montant = (float)$_POST['montant'];
    $compte_id = ($type_compte == 'BENEFICE') ? 112 : 113;
    $date_report = $_POST['date_report'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO REPORT_NOUVEAU (exercice, type_compte, compte_id, montant, affectation, date_report) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$exercice, $type_compte, $compte_id, $montant, $affectation, $date_report]);
        
        // Écriture comptable de report
        if ($type_compte == 'BENEFICE') {
            // Bénéfice : Débit 112 (Report) / Crédit 112 (Résultat)
            $stmt2 = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, type_ecriture) VALUES (?, ?, ?, ?, ?, 'CLOTURE')");
            $stmt2->execute([$date_report, "Report à nouveau bénéfice N-$exercice", 112, 112, $montant, 'CLOTURE']);
        }
        
        $message = "✅ Report à nouveau effectué pour l'exercice $exercice";
    } catch (Exception $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-arrow-right-circle"></i> Report à nouveau - Classe 11</h5>
                <small>Conformément aux dispositions SYSCOHADA OHADA</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6>Résultat net de l'exercice <?= date('Y') ?></h6>
                            </div>
                            <div class="card-body text-center">
                                <h2 class="<?= $resultat_net >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format(abs($resultat_net), 0, ',', ' ') ?> FCFA
                                </h2>
                                <p><?= $resultat_net >= 0 ? 'BÉNÉFICE' : 'PERTE' ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6>Reports à nouveau cumulés</h6>
                            </div>
                            <div class="card-body text-center">
                                <h2 class="<?= $report_total >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format(abs($report_total), 0, ',', ' ') ?> FCFA
                                </h2>
                                <p>Total reports antérieurs</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-success text-white">Nouveau report à nouveau</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-4">
                                        <label>Exercice (N-1)</label>
                                        <input type="number" name="exercice" class="form-control" value="<?= date('Y')-1 ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Type</label>
                                        <select name="type_compte" class="form-select" required>
                                            <option value="BENEFICE">Bénéfice à reporter</option>
                                            <option value="PERTE">Perte à reporter</option>
                                            <option value="RESERVE">Affectation en réserve</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Affectation</label>
                                        <select name="affectation" class="form-select" required>
                                            <option value="RESERVE">Mise en réserve</option>
                                            <option value="DISTRIBUTION">Distribution de dividendes</option>
                                            <option value="REPORT">Report à nouveau simple</option>
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label>Montant à reporter</label>
                                        <input type="number" name="montant" class="form-control" value="<?= abs($resultat_net) ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Date de report</label>
                                        <input type="date" name="date_report" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn-omega">Effectuer le report à nouveau</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Historique des reports -->
                <div class="mt-4">
                    <h6><i class="bi bi-clock-history"></i> Historique des reports à nouveau</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr><th>Exercice</th><th>Type</th><th>Montant</th><th>Affectation</th><th>Date report</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($reports as $r): ?>
                                <tr>
                                    <td class="text-center"><?= $r['exercice'] ?> </td>
                                    <td><?= $r['type_compte'] ?></td>
                                    <td class="text-end"><?= number_format($r['montant'], 0, ',', ' ') ?> F</td>
                                    <td><?= $r['affectation'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($r['date_report'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
