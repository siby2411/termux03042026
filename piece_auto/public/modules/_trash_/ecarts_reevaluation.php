<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Écarts de Réévaluation - Classe 1";
$page_icon = "arrow-repeat";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';

// Enregistrement d'un écart de réévaluation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'reevaluer') {
        $immobilisation_id = (int)$_POST['immobilisation_id'];
        $valeur_reevaluee = (float)$_POST['valeur_reevaluee'];
        $justificatif = trim($_POST['justificatif']);
        
        // Récupérer l'immobilisation
        $stmt = $pdo->prepare("SELECT a.*, c.intitule_compte FROM AMORTISSEMENTS a JOIN PLAN_COMPTABLE_UEMOA c ON a.compte_immobilisation = c.compte_id WHERE a.id = ?");
        $stmt->execute([$immobilisation_id]);
        $immo = $stmt->fetch();
        
        if ($immo) {
            $valeur_comptable = $immo['valeur_originale'] - $immo['amortissement_cumule'];
            $ecart = $valeur_reevaluee - $valeur_comptable;
            
            if ($ecart != 0) {
                // Enregistrement dans ECARTS_REEVALUATION
                $stmt = $pdo->prepare("INSERT INTO ECARTS_REEVALUATION (immobilisation_id, date_reevaluation, valeur_comptable_ancienne, valeur_reevaluee, compte_immobilisation, compte_ecart, justificatif, statut) VALUES (?, ?, ?, ?, ?, ?, ?, 'DEFINITIF')");
                $stmt->execute([$immobilisation_id, date('Y-m-d'), $valeur_comptable, $valeur_reevaluee, $immo['compte_immobilisation'], 1061, $justificatif]);
                
                // Création des écritures comptables
                if ($ecart > 0) {
                    // Augmentation de valeur
                    $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'REEVALUATION')";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([date('Y-m-d'), "Réévaluation - Augmentation valeur " . $immo['libelle'], $immo['compte_immobilisation'], 1061, $ecart, "REEVAL-" . date('Ymd')]);
                } else {
                    // Diminution de valeur
                    $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'REEVALUATION')";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([date('Y-m-d'), "Réévaluation - Diminution valeur " . $immo['libelle'], 1061, $immo['compte_immobilisation'], abs($ecart), "REEVAL-" . date('Ymd')]);
                }
                
                $message = "✅ Réévaluation enregistrée : Écart de " . number_format($ecart, 0, ',', ' ') . " FCFA";
            } else {
                $message = "⚠️ Aucun écart constaté, valeur identique";
            }
        }
    }
}

// Récupération des immobilisations
$immobilisations = $pdo->query("
    SELECT a.*, c.intitule_compte,
           (a.valeur_originale - a.amortissement_cumule) as valeur_nette_comptable
    FROM AMORTISSEMENTS a
    JOIN PLAN_COMPTABLE_UEMOA c ON a.compte_immobilisation = c.compte_id
    WHERE a.statut = 'ACTIF'
")->fetchAll();

// Récupération des écarts existants
$ecarts = $pdo->query("
    SELECT e.*, a.libelle, a.compte_immobilisation
    FROM ECARTS_REEVALUATION e
    JOIN AMORTISSEMENTS a ON e.immobilisation_id = a.id
    ORDER BY e.date_reevaluation DESC
")->fetchAll();

$total_ecarts = array_sum(array_column($ecarts, 'ecart_reevaluation'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-arrow-repeat"></i> Écarts de Réévaluation (Classe 1 - Fonds Propres)</h5>
                <small>Réévaluation économique des actifs selon SYSCOHADA UEMOA</small>
            </div>
            <div class="card-body">
                
                <!-- Explication technique -->
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>📖 PRINCIPE DE LA RÉÉVALUATION SYSCOHADA :</strong>
                    <p class="mt-2">La réévaluation permet d'ajuster la valeur des immobilisations à leur valeur économique réelle. L'écart de réévaluation est intégré aux fonds propres (compte 1061).</p>
                    <hr>
                    <p><strong>Formule :</strong> Écart = Valeur économique - Valeur nette comptable</p>
                    <p><strong> Condition de crédit :</strong> Un crédit peut être octroyé lorsque les fonds propres sont au moins égaux aux dettes.</p>
                    <ul>
                        <li>Si écart > 0 → Augmentation de l'actif et des fonds propres</li>
                        <li>Si écart < 0 → Diminution (à comptabiliser en charge exceptionnelle)</li>
                    </ul>
                </div>
                
                <!-- Liste des immobilisations -->
                <h6><i class="bi bi-building"></i> Immobilisations éligibles à la réévaluation</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>N° compte</th><th>Libellé</th><th>Valeur brute</th><th>Amort. cumulé</th><th>VNC actuelle</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($immobilisations as $i): ?>
                            <tr>
                                <td class="text-center"><?= $i['compte_immobilisation'] ?></td>
                                <td><?= htmlspecialchars($i['libelle']) ?></td>
                                <td class="text-end"><?= number_format($i['valeur_originale'], 0, ',', ' ') ?> F</td>
                                <td class="text-end text-danger"><?= number_format($i['amortissement_cumule'], 0, ',', ' ') ?> F</td>
                                <td class="text-end fw-bold text-primary"><?= number_format($i['valeur_nette_comptable'], 0, ',', ' ') ?> F</td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#reevalModal" onclick="preparerReeval(<?= htmlspecialchars(json_encode($i)) ?>)">
                                        <i class="bi bi-arrow-repeat"></i> Réévaluer
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Historique des écarts -->
                <h6><i class="bi bi-clock-history"></i> Historique des écarts de réévaluation</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Date</th><th>Immobilisation</th><th>VNC ancienne</th><th>Valeur réévaluée</th><th>Écart</th><th>Justificatif</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($ecarts as $e): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($e['date_reevaluation'])) ?></td>
                                <td><?= htmlspecialchars($e['libelle']) ?></td>
                                <td class="text-end"><?= number_format($e['valeur_comptable_ancienne'], 0, ',', ' ') ?> F</td>
                                <td class="text-end"><?= number_format($e['valeur_reevaluee'], 0, ',', ' ') ?> F</td>
                                <td class="text-end <?= $e['ecart_reevaluation'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format(abs($e['ecart_reevaluation']), 0, ',', ' ') ?> F
                                    <?= $e['ecart_reevaluation'] >= 0 ? '(+)' : '(-)' ?>
                                </td>
                                <td><?= htmlspecialchars($e['justificatif']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr><td colspan="4" class="text-end fw-bold">TOTAL ÉCART DE RÉÉVALUATION :</td>
                                <td class="text-end fw-bold <?= $total_ecarts >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format(abs($total_ecarts), 0, ',', ' ') ?> F
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Impact sur les fonds propres -->
                <div class="alert alert-success mt-3">
                    <i class="bi bi-piggy-bank"></i>
                    <strong>Impact sur les fonds propres :</strong>
                    L'écart de réévaluation total de <strong><?= number_format(abs($total_ecarts), 0, ',', ' ') ?> FCFA</strong> est intégré au compte 1061 "Écart de réévaluation positive" dans les fonds propres.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Réévaluation -->
<div class="modal fade" id="reevalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-arrow-repeat"></i> Réévaluation d'immobilisation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="reevaluer">
                    <input type="hidden" name="immobilisation_id" id="immobilisation_id">
                    
                    <div class="mb-3">
                        <label>Immobilisation</label>
                        <input type="text" id="libelle_immo" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Valeur nette comptable actuelle (FCFA)</label>
                        <input type="text" id="vnc_actuelle" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Nouvelle valeur économique (FCFA)</label>
                        <input type="number" name="valeur_reevaluee" id="valeur_reevaluee" class="form-control" required>
                        <small>Valeur d'expertise ou de marché</small>
                    </div>
                    <div class="mb-3">
                        <label>Justificatif (rapport d'expertise, étude de marché, etc.)</label>
                        <textarea name="justificatif" class="form-control" rows="3" required></textarea>
                    </div>
                    
                    <div id="ecart_preview" class="alert alert-info"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Valider la réévaluation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function preparerReeval(immo) {
    document.getElementById('immobilisation_id').value = immo.id;
    document.getElementById('libelle_immo').value = immo.libelle + ' (' + immo.compte_immobilisation + ')';
    document.getElementById('vnc_actuelle').value = new Intl.NumberFormat().format(immo.valeur_nette_comptable) + ' F';
    
    const vnc = immo.valeur_nette_comptable;
    const champValeur = document.getElementById('valeur_reevaluee');
    const preview = document.getElementById('ecart_preview');
    
    champValeur.oninput = function() {
        let nouvelle = parseFloat(this.value) || 0;
        let ecart = nouvelle - vnc;
        if(ecart > 0) {
            preview.innerHTML = '<i class="bi bi-arrow-up-circle"></i> Écart positif : +' + new Intl.NumberFormat().format(ecart) + ' F<br>Impact : Augmentation des fonds propres (compte 1061)';
            preview.className = 'alert alert-success';
        } else if(ecart < 0) {
            preview.innerHTML = '<i class="bi bi-arrow-down-circle"></i> Écart négatif : ' + new Intl.NumberFormat().format(ecart) + ' F<br>Impact : Diminution des fonds propres';
            preview.className = 'alert alert-warning';
        } else {
            preview.innerHTML = 'Aucun écart constaté';
            preview.className = 'alert alert-secondary';
        }
    };
}
</script>

<?php include 'inc_footer.php'; ?>
