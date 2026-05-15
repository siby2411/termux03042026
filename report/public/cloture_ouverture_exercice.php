<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Clôture et ouverture de l'exercice - Passage N → N+1";
$page_icon = "calendar-check";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';
$resultats = [];

// Récupération des données de l'exercice N
$exercice_n = date('Y');
$exercice_n1 = $exercice_n + 1;

// Calcul des soldes par compte
$soldes = [];
$comptes = $pdo->query("SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA ORDER BY compte_id")->fetchAll();

foreach($comptes as $c) {
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(CASE WHEN e.compte_debite_id = ? THEN e.montant ELSE 0 END), 0) - 
               COALESCE(SUM(CASE WHEN e.compte_credite_id = ? THEN e.montant ELSE 0 END), 0) as solde
        FROM ECRITURES_COMPTABLES e
        WHERE YEAR(e.date_ecriture) = ?
    ");
    $stmt->execute([$c['compte_id'], $c['compte_id'], $exercice_n]);
    $solde = $stmt->fetchColumn();
    if($solde != 0) {
        $soldes[$c['compte_id']] = [
            'intitule' => $c['intitule_compte'],
            'solde' => $solde,
            'nature' => ($c['compte_id'] >= 600 && $c['compte_id'] <= 699) ? 'CHARGE' : (($c['compte_id'] >= 700 && $c['compte_id'] <= 799) ? 'PRODUIT' : 'BILAN')
        ];
    }
}

// Calcul du résultat net
$total_produits = 0;
$total_charges = 0;
foreach($soldes as $id => $s) {
    if($s['nature'] == 'PRODUIT') $total_produits += $s['solde'];
    if($s['nature'] == 'CHARGE') $total_charges += $s['solde'];
}
$resultat_net = $total_produits - $total_charges;

// Action de clôture et ouverture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'cloturer_ouvrir') {
        try {
            $pdo->beginTransaction();
            
            // 1. Clôture des comptes de charges et produits
            $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'CLOTURE')");
            
            if($resultat_net > 0) {
                // Bénéfice : clôture des charges (débit des produits, crédit des charges)
                foreach($soldes as $id => $s) {
                    if($s['nature'] == 'PRODUIT') {
                        $stmt->execute([date('Y-m-d'), "Clôture produit - $s[intitule]", $id, 120, $s['solde'], "CLOT-$exercice_n"]);
                    }
                    if($s['nature'] == 'CHARGE') {
                        $stmt->execute([date('Y-m-d'), "Clôture charge - $s[intitule]", 120, $id, $s['solde'], "CLOT-$exercice_n"]);
                    }
                }
            } else {
                // Perte
                foreach($soldes as $id => $s) {
                    if($s['nature'] == 'PRODUIT') {
                        $stmt->execute([date('Y-m-d'), "Clôture produit - $s[intitule]", $id, 121, $s['solde'], "CLOT-$exercice_n"]);
                    }
                    if($s['nature'] == 'CHARGE') {
                        $stmt->execute([date('Y-m-d'), "Clôture charge - $s[intitule]", 121, $id, $s['solde'], "CLOT-$exercice_n"]);
                    }
                }
            }
            
            // 2. Report à nouveau du résultat
            if($resultat_net > 0) {
                $stmt2 = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, 'Report à nouveau bénéfice', 120, 112, ?, 'REP-01', 'REPORT')");
                $stmt2->execute([date('Y-m-d'), $resultat_net]);
            } else {
                $stmt2 = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, 'Report à nouveau perte', 113, 120, ?, 'REP-01', 'REPORT')");
                $stmt2->execute([date('Y-m-d'), abs($resultat_net)]);
            }
            
            // 3. Contrepassation des régularisations
            $stmt3 = $pdo->prepare("SELECT * FROM ECRITURES_COMPTABLES WHERE type_ecriture = 'REGULARISATION' AND YEAR(date_ecriture) = ?");
            $stmt3->execute([$exercice_n]);
            $reguls = $stmt3->fetchAll();
            
            foreach($reguls as $r) {
                $stmt4 = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'CONTREPASSATION')");
                $stmt4->execute([date('Y-m-d', strtotime($exercice_n1 . '-01-01')), "Contrepassation - $r[libelle]", $r['compte_credite_id'], $r['compte_debite_id'], $r['montant'], $r['reference_piece'], 'CONTREPASSATION']);
            }
            
            $pdo->commit();
            $message = "✅ Clôture de l'exercice $exercice_n effectuée et ouverture de l'exercice $exercice_n1 préparée";
            
        } catch(Exception $e) {
            $pdo->rollBack();
            $error = "Erreur: " . $e->getMessage();
        }
    }
}

// Récupération du bilan d'ouverture
$bilan_ouverture = $pdo->prepare("
    SELECT compte_id, intitule_compte, 
           SUM(CASE WHEN e.compte_debite_id = c.compte_id THEN e.montant ELSE 0 END) as total_debit,
           SUM(CASE WHEN e.compte_credite_id = c.compte_id THEN e.montant ELSE 0 END) as total_credit
    FROM PLAN_COMPTABLE_UEMOA c
    LEFT JOIN ECRITURES_COMPTABLES e ON c.compte_id IN (e.compte_debite_id, e.compte_credite_id) AND YEAR(e.date_ecriture) = ?
    GROUP BY c.compte_id, c.intitule_compte
    HAVING total_debit != 0 OR total_credit != 0
    ORDER BY c.compte_id
");
$bilan_ouverture->execute([$exercice_n]);
$bilan_data = $bilan_ouverture->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calendar-check"></i> Clôture et ouverture de l'exercice</h5>
                <small>Passage de l'exercice <?= $exercice_n ?> à l'exercice <?= $exercice_n1 ?></small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <!-- Synthèse de l'exercice N -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_produits, 0, ',', ' ') ?> F</h4>
                                <small>Total produits</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-danger text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_charges, 0, ',', ' ') ?> F</h4>
                                <small>Total charges</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format(abs($resultat_net), 0, ',', ' ') ?> F</h4>
                                <small><?= $resultat_net >= 0 ? 'BÉNÉFICE' : 'PERTE' ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bouton de clôture -->
                <div class="text-center mb-4">
                    <form method="POST" onsubmit="return confirm('Confirmer la clôture de l\'exercice <?= $exercice_n ?> ?')">
                        <input type="hidden" name="action" value="cloturer_ouvrir">
                        <button type="submit" class="btn-omega btn-lg">
                            <i class="bi bi-lock"></i> Clôturer l'exercice <?= $exercice_n ?> et ouvrir <?= $exercice_n1 ?>
                        </button>
                    </form>
                </div>

                <!-- Bilan de l'exercice N -->
                <h6>📊 Bilan de l'exercice <?= $exercice_n ?> (avant clôture)</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-dark">
                            <tr><th>Compte</th><th>Intitulé</th><th class="text-end">Débit (F)</th><th class="text-end">Crédit (F)</th><th class="text-end">Solde</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($bilan_data as $b): 
                                $solde = $b['total_debit'] - $b['total_credit'];
                                $nature = $solde >= 0 ? 'Débiteur' : 'Créditeur';
                            ?>
                            <tr>
                                <td class="text-center fw-bold"><?= $b['compte_id'] ?> </td>
                                <td><?= htmlspecialchars($b['intitule_compte']) ?> </td>
                                <td class="text-end"><?= number_format($b['total_debit'], 0, ',', ' ') ?> F</td>
                                <td class="text-end"><?= number_format($b['total_credit'], 0, ',', ' ') ?> F</td>
                                <td class="text-end <?= $solde >= 0 ? 'text-danger' : 'text-success' ?>">
                                    <?= number_format(abs($solde), 0, ',', ' ') ?> F <?= $nature ?>
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
