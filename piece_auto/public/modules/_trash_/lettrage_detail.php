<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Détail du lettrage";
$page_icon = "link";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$factures = $_SESSION['factures'] ?? [];
$reglements = $_SESSION['reglements'] ?? [];
$tiers_id = $_SESSION['tiers_id'] ?? 0;
$type = $_SESSION['type_lettrage'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_factures = $_POST['factures'] ?? [];
    $selected_reglements = $_POST['reglements'] ?? [];
    $montant_factures = array_sum(array_column($selected_factures, 'montant'));
    $montant_reglements = array_sum(array_column($selected_reglements, 'montant'));
    
    if(abs($montant_factures - $montant_reglements) > 1) {
        $error = "Le montant des factures et des règlements ne correspond pas !";
    } else {
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO LETTRAGES (date_lettrage, tiers_id, type_lettrage, montant_total, montant_lettre, statut) VALUES (?, ?, ?, ?, ?, 'TOTAL')");
            $stmt->execute([date('Y-m-d'), $tiers_id, $type, $montant_factures, $montant_reglements]);
            $lettrage_id = $pdo->lastInsertId();
            
            foreach($selected_factures as $f) {
                $stmt2 = $pdo->prepare("INSERT INTO LETTRAGES_DETAILS (lettrage_id, ecriture_id, type_ecriture, montant) VALUES (?, ?, 'FACTURE', ?)");
                $stmt2->execute([$lettrage_id, $f['id'], $f['montant']]);
                $stmt3 = $pdo->prepare("UPDATE ECRITURES_COMPTABLES SET lettrage_id = ?, date_lettrage = CURDATE() WHERE id = ?");
                $stmt3->execute([$lettrage_id, $f['id']]);
            }
            foreach($selected_reglements as $r) {
                $stmt2 = $pdo->prepare("INSERT INTO LETTRAGES_DETAILS (lettrage_id, ecriture_id, type_ecriture, montant) VALUES (?, ?, 'REGLEMENT', ?)");
                $stmt2->execute([$lettrage_id, $r['id'], $r['montant']]);
                $stmt3 = $pdo->prepare("UPDATE ECRITURES_COMPTABLES SET lettrage_id = ?, date_lettrage = CURDATE() WHERE id = ?");
                $stmt3->execute([$lettrage_id, $r['id']]);
            }
            
            $pdo->commit();
            $message = "✅ Lettrage effectué avec succès";
        } catch(Exception $e) {
            $pdo->rollBack();
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-link"></i> Lettrage - <?= $type ?> </h5>
            </div>
            <div class="card-body">
                <form method="POST" class="row">
                    <div class="col-md-6">
                        <h6>📄 Factures à lettrer</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light"><td><th><input type="checkbox" id="select_all_factures"></th><th>Date</th><th>Libellé</th><th class="text-end">Montant</th></tr></thead>
                                <tbody>
                                    <?php foreach($factures as $f): ?>
                                    <tr>
                                        <td><input type="checkbox" name="factures[<?= $f['id'] ?>][id]" class="facture_check" value="<?= $f['id'] ?>" data-montant="<?= $f['montant'] ?>"></td>
                                        <td><?= $f['date_ecriture'] ?> </td><td><?= $f['libelle'] ?> </td>
                                        <td class="text-end"><?= number_format($f['montant'], 0, ',', ' ') ?> F</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            <tr>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>💵 Règlements à lettrer</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light"><tr><th><input type="checkbox" id="select_all_reglements"></th><th>Date</th><th>Libellé</th><th class="text-end">Montant</th></tr></thead>
                                <tbody>
                                    <?php foreach($reglements as $r): ?>
                                    <tr>
                                        <td><input type="checkbox" name="reglements[<?= $r['id'] ?>][id]" class="reglement_check" data-montant="<?= $r['montant'] ?>"> </td>
                                        <td><?= $r['date_ecriture'] ?> </td><td><?= $r['libelle'] ?> </td>
                                        <td class="text-end"><?= number_format($r['montant'], 0, ',', ' ') ?> F</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 text-center mt-3">
                        <div id="diff_alerte" class="alert alert-info">Sélectionnez factures et règlements pour lettrage</div>
                        <button type="submit" class="btn-omega">Confirmer le lettrage</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('select_all_factures').onclick = function() {
    document.querySelectorAll('.facture_check').forEach(cb => cb.checked = this.checked);
    calculerDiff();
};
document.getElementById('select_all_reglements').onclick = function() {
    document.querySelectorAll('.reglement_check').forEach(cb => cb.checked = this.checked);
    calculerDiff();
};
document.querySelectorAll('.facture_check, .reglement_check').forEach(cb => cb.onchange = calculerDiff);

function calculerDiff() {
    let totalFactures = 0, totalReglements = 0;
    document.querySelectorAll('.facture_check:checked').forEach(cb => totalFactures += parseFloat(cb.dataset.montant));
    document.querySelectorAll('.reglement_check:checked').forEach(cb => totalReglements += parseFloat(cb.dataset.montant));
    let diff = totalFactures - totalReglements;
    let div = document.getElementById('diff_alerte');
    if(Math.abs(diff) < 1) {
        div.innerHTML = '✅ Équilibre parfait : ' + new Intl.NumberFormat().format(totalFactures) + ' F';
        div.className = 'alert alert-success';
    } else if(diff > 0) {
        div.innerHTML = '⚠️ Différence : ' + new Intl.NumberFormat().format(diff) + ' F (factures > règlements)';
        div.className = 'alert alert-warning';
    } else {
        div.innerHTML = '⚠️ Différence : ' + new Intl.NumberFormat().format(Math.abs(diff)) + ' F (règlements > factures)';
        div.className = 'alert alert-danger';
    }
}
</script>

<?php include 'inc_footer.php'; ?>
