<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Moyens de paiement - Virements, prélèvements";
$page_icon = "credit-card";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';

$echeances_a_payer = $pdo->query("
    SELECT e.*, t.raison_sociale, t.telephone, t.email
    FROM ECHEANCIERS_PAIEMENT e
    JOIN TIERS t ON e.tiers_id = t.id
    WHERE e.statut IN ('EN_ATTENTE', 'PARTIEL')
    AND e.type_echeance IN ('FOURNISSEUR', 'SALAIRE')
    ORDER BY e.date_echeance ASC
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'emettre_virement') {
        $date = date('Y-m-d');
        $montant_total = (float)$_POST['montant_total'];
        $libelle = $_POST['libelle'];
        $echeances = $_POST['echeances'] ?? [];
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO EMISSIONS_PAIEMENT (date_emission, type_emission, libelle, montant_total, statut, created_by) VALUES (?, 'VIREMENT', ?, ?, 'EMIS', ?)");
            $stmt->execute([$date, $libelle, $montant_total, $_SESSION['user_id']]);
            $emission_id = $pdo->lastInsertId();
            
            foreach($echeances as $e_id) {
                $echeance = $pdo->prepare("SELECT * FROM ECHEANCIERS_PAIEMENT WHERE id = ?");
                $echeance->execute([$e_id]);
                $e = $echeance->fetch();
                
                $stmt2 = $pdo->prepare("INSERT INTO EMISSIONS_DETAILS (emission_id, tiers_id, montant, reference, motif) VALUES (?, ?, ?, ?, ?)");
                $stmt2->execute([$emission_id, $e['tiers_id'], $e['montant'], $e['reference_facture'], $e['libelle']]);
                
                $update = $pdo->prepare("UPDATE ECHEANCIERS_PAIEMENT SET statut = 'REGLE', date_reglement = CURDATE(), montant_regle = montant WHERE id = ?");
                $update->execute([$e_id]);
            }
            
            $pdo->commit();
            $message = "✅ Virement émis pour " . count($echeances) . " échéances - Total: " . number_format($montant_total, 0, ',', ' ') . " F";
        } catch(Exception $e) {
            $pdo->rollBack();
            $message = "❌ Erreur: " . $e->getMessage();
        }
    }
}

$emissions = $pdo->query("
    SELECT e.*, u.email as created_by_email
    FROM EMISSIONS_PAIEMENT e
    LEFT JOIN USERS u ON e.created_by = u.user_id
    ORDER BY e.date_emission DESC
")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-credit-card"></i> Moyens de paiement</h5>
                <small>Émission de virements, chèques, prélèvements</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>

                <ul class="nav nav-tabs" id="paiementTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#a_payer">💰 Échéances à payer</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#emissions">📜 Historique émissions</button></li>
                </ul>

                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="a_payer">
                        <form method="POST" id="emissionForm">
                            <input type="hidden" name="action" value="emettre_virement">
                            <input type="hidden" name="montant_total" id="montant_total">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-dark">
                                        <tr><th><input type="checkbox" id="select_all"></th>
                                            <th>Échéance</th><th>Fournisseur</th><th>Libellé</th>
                                            <th class="text-end">Montant (F)</th><th>Référence</th>
                                        </td>
                                    </thead>
                                    <tbody>
                                        <?php foreach($echeances_a_payer as $e): ?>
                                        <tr>
                                            <td class="text-center"><input type="checkbox" name="echeances[]" value="<?= $e['id'] ?>" class="echeance_check" data-montant="<?= $e['montant'] ?>"></td>
                                            <td class="text-center"><?= date('d/m/Y', strtotime($e['date_echeance'])) ?> </td>
                                            <td><?= htmlspecialchars($e['raison_sociale']) ?> </td>
                                            <td><?= htmlspecialchars($e['libelle']) ?> </td>
                                            <td class="text-end"><?= number_format($e['montant'], 0, ',', ' ') ?> F</td>
                                            <td class="text-center"><?= $e['reference_facture'] ?? '-' ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if(empty($echeances_a_payer)): ?>
                                        <tr><td colspan="6" class="text-center">Aucune échéance à payer</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-secondary">
                                            <td colspan="4" class="text-end fw-bold">TOTAL SÉLECTIONNÉ :</td>
                                            <td class="text-end fw-bold" id="total_selection">0 F</td>
                                            <td>
                                                <button type="submit" class="btn btn-success" id="btn_emettre">
                                                    <i class="bi bi-send"></i> Émettre le virement
                                                </button>
                                             </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade" id="emissions">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Date</th><th>Type</th><th>Libellé</th><th class="text-end">Montant</th>
                                        <th>Statut</th><th>Émis par</th><th>Fichier</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($emissions as $e): ?>
                                    <tr>
                                        <td class="text-center"><?= date('d/m/Y', strtotime($e['date_emission'])) ?> </td>
                                        <td class="text-center"><?= $e['type_emission'] ?> </td>
                                        <td><?= htmlspecialchars($e['libelle']) ?> </td>
                                        <td class="text-end"><?= number_format($e['montant_total'], 0, ',', ' ') ?> F</td>
                                        <td class="text-center"><span class="badge bg-success"><?= $e['statut'] ?></span></td>
                                        <td class="text-center"><?= $e['created_by_email'] ?? '-' ?></td>
                                        <td class="text-center">
                                            <?php if($e['fichier_genere']): ?>
                                                <a href="<?= $e['fichier_genere'] ?>" class="btn btn-sm btn-primary">📄 TXT</a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
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
    </div>
</div>

<script>
document.getElementById('select_all').onclick = function() {
    document.querySelectorAll('.echeance_check').forEach(cb => cb.checked = this.checked);
    calculerTotal();
};
document.querySelectorAll('.echeance_check').forEach(cb => cb.onchange = calculerTotal);

function calculerTotal() {
    let total = 0;
    document.querySelectorAll('.echeance_check:checked').forEach(cb => total += parseFloat(cb.dataset.montant));
    document.getElementById('total_selection').innerText = new Intl.NumberFormat().format(total) + ' F';
    document.getElementById('montant_total').value = total;
}
</script>

<?php include 'inc_footer.php'; ?>
