<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Liasses fiscales - ECF";
$page_icon = "file-text";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$exercice = $_GET['exercice'] ?? date('Y');

// Calcul des montants pour les liasses
$ca = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 700 AND 799");
$ca->execute([$exercice]);
$chiffre_affaires = $ca->fetchColumn();

$tva_collectee = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id = 4451");
$tva_collectee->execute([$exercice]);
$tva_collectee = $tva_collectee->fetchColumn();

$tva_deductible = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id = 4454");
$tva_deductible->execute([$exercice]);
$tva_deductible = $tva_deductible->fetchColumn();

$tva_a_payer = $tva_collectee - $tva_deductible;

$resultat = $pdo->prepare("
    SELECT COALESCE(SUM(CASE WHEN compte_credite_id BETWEEN 700 AND 799 THEN montant ELSE 0 END), 0) - 
           COALESCE(SUM(CASE WHEN compte_debite_id BETWEEN 600 AND 699 THEN montant ELSE 0 END), 0) 
    FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ?
");
$resultat->execute([$exercice]);
$resultat_net = $resultat->fetchColumn();
$is_a_payer = max(0, $resultat_net * 0.25);
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-file-text"></i> Liasses fiscales (ECF)</h5>
                <small>Déclarations fiscales - IS, TVA, IR</small>
            </div>
            <div class="card-body">
                
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label>Exercice</label>
                        <select name="exercice" class="form-select" onchange="this.form.submit()">
                            <option value="2025" <?= $exercice == 2025 ? 'selected' : '' ?>>2025</option>
                            <option value="2026" <?= $exercice == 2026 ? 'selected' : '' ?>>2026</option>
                        </select>
                    </div>
                </form>

                <div class="row">
                    <!-- TVA -->
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-warning text-dark">📄 Déclaration de TVA</div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr><td>TVA collectée (18%)</td><td class="text-end"><?= number_format($tva_collectee, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>TVA déductible (18%)</td><td class="text-end"><?= number_format($tva_deductible, 0, ',', ' ') ?> F</td></tr>
                                    <tr class="table-primary fw-bold"><td>TVA à payer</td><td class="text-end text-danger"><?= number_format($tva_a_payer, 0, ',', ' ') ?> F</td></tr>
                                </table>
                                <button class="btn btn-sm btn-warning" onclick="genererFichier('TVA', <?= $tva_a_payer ?>)">
                                    <i class="bi bi-download"></i> Générer la déclaration TVA
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- IS -->
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-danger text-white">📄 Impôt sur les Sociétés (IS)</div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr><td>Résultat fiscal estimé (25%)</td>
                                        <td class="text-end"><?= number_format($resultat_net, 0, ',', ' ') ?> F</td>
                                    </tr>
                                    <tr class="table-primary fw-bold"><td>IS à payer (25%)</td>
                                        <td class="text-end text-danger"><?= number_format($is_a_payer, 0, ',', ' ') ?> F</td>
                                    </tr>
                                </table>
                                <button class="btn btn-sm btn-danger" onclick="genererFichier('IS', <?= $is_a_payer ?>)">
                                    <i class="bi bi-download"></i> Générer la liasse IS
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Récapitulatif des paiements -->
                <div class="card mt-3">
                    <div class="card-header bg-success text-white">📋 Récapitulatif des déclarations</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Date</th><th>Type</th><th>Montant</th><th>Fichier</th><th>Statut</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $liasses = $pdo->prepare("SELECT * FROM LIASSES_FISCALES WHERE exercice = ?");
                                    $liasses->execute([$exercice]);
                                    foreach($liasses as $l): ?>
                                    <tr>
                                        <td class="text-center"><?= date('d/m/Y', strtotime($l['date_generation'])) ?> </td>
                                        <td class="text-center"><?= $l['type_liasse'] ?> </td>
                                        <td class="text-end"><?= number_format($l['montant'], 0, ',', ' ') ?> F</td>
                                        <td><?= $l['fichier_edi'] ? '<a href="'.$l['fichier_edi'].'">📄 EDI</a>' : '-' ?> </td>
                                        <td class="text-center"><span class="badge <?= $l['statut'] == 'GENERE' ? 'bg-warning' : 'bg-success' ?>"><?= $l['statut'] ?></span></td>
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
</div>

<script>
function genererFichier(type, montant) {
    if(confirm(`Générer la déclaration ${type} pour un montant de ${montant.toLocaleString()} F ?`)) {
        fetch(`generer_liasse.php?type=${type}&montant=${montant}&exercice=<?= $exercice ?>`)
            .then(r => r.json())
            .then(data => {
                if(data.success) location.reload();
                else alert(data.error);
            });
    }
}
</script>

<?php include 'inc_footer.php'; ?>
