<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Engagements Hors Bilan - Classe 8 SYSCOHADA";
$page_icon = "shield";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'ajouter_engagement') {
        $date_engagement = $_POST['date_engagement'];
        $type = $_POST['type'];
        $beneficiaire = trim($_POST['beneficiaire']);
        $montant = (float)$_POST['montant'];
        $devise = $_POST['devise'];
        $date_echeance = $_POST['date_echeance'] ?: null;
        $compte_engagement = (int)$_POST['compte_engagement'];
        $document_reference = trim($_POST['document_reference']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO ENGAGEMENTS_HORS_BILAN (date_engagement, type, beneficiaire, montant, devise, date_echeance, compte_engagement, document_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$date_engagement, $type, $beneficiaire, $montant, $devise, $date_echeance, $compte_engagement, $document_reference]);
            
            // Écriture comptable (classe 8 - hors bilan)
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 80, $compte_engagement, ?, ?, 'ENGAGEMENT')";
            $stmt2 = $pdo->prepare($sql);
            $stmt2->execute([$date_engagement, "Engagement - $type - $beneficiaire", $montant, "ENG-" . date('Ymd')]);
            
            $message = "✅ Engagement enregistré - Montant: " . number_format($montant, 0, ',', ' ') . " FCFA";
        } catch (Exception $e) {
            $error = "❌ Erreur: " . $e->getMessage();
        }
    }
}

$engagements = $pdo->query("SELECT * FROM ENGAGEMENTS_HORS_BILAN WHERE statut = 'ACTIF' ORDER BY date_echeance ASC")->fetchAll();
$total_engagements = array_sum(array_column($engagements, 'montant'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5><i class="bi bi-shield-shaded"></i> Engagements Hors Bilan - Classe 8 (SYSCOHADA)</h5>
                <small>Caution, avals, garanties, crédit-bail, litiges</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Norme SYSCOHADA - Classe 8 :</strong><br>
                    Les engagements hors bilan représentent des obligations potentielles non inscrites au bilan.
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-danger text-white text-center">
                            <div class="card-body">
                                <h4><?= count($engagements) ?></h4>
                                <small>Engagements actifs</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_engagements, 0, ',', ' ') ?> F</h4>
                                <small>Montant total engagé</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-secondary text-white text-center">
                            <div class="card-body">
                                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#newEngagementModal">+ Nouvel engagement</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>Date</th><th>Type</th><th>Bénéficiaire</th><th>Montant</th><th>Échéance</th><th>Référence</th><th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($engagements as $e): ?>
                            <tr>
                                <td class="text-center"><?= date('d/m/Y', strtotime($e['date_engagement'])) ?> </td>
                                <td class="text-center"><span class="badge bg-danger"><?= $e['type'] ?></span></td>
                                <td><?= htmlspecialchars($e['beneficiaire']) ?></td>
                                <td class="text-end"><?= number_format($e['montant'], 0, ',', ' ') ?> <?= $e['devise'] ?></td>
                                <td class="text-center"><?= $e['date_echeance'] ? date('d/m/Y', strtotime($e['date_echeance'])) : '-' ?></td>
                                <td class="text-center"><?= htmlspecialchars($e['document_reference'] ?? '-') ?></td>
                                <td class="text-center"><span class="badge bg-success">ACTIF</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr><td colspan="3" class="text-end fw-bold">TOTAL :</td>
                            <td class="text-end fw-bold"><?= number_format($total_engagements, 0, ',', ' ') ?> F</td>
                            <td colspan="3"></td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="newEngagementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5>Nouvel engagement hors bilan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="ajouter_engagement">
                    <div class="mb-2"><label>Date</label><input type="date" name="date_engagement" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                    <div class="mb-2"><label>Type</label><select name="type" class="form-select" required>
                        <option value="CAUTION">Caution</option><option value="AVAL">Aval</option>
                        <option value="GARANTIE">Garantie</option><option value="NANTISSEMENT">Nantissement</option>
                        <option value="CREDIT_BAIL">Crédit-bail</option><option value="LITIGE">Litige</option>
                    </select></div>
                    <div class="mb-2"><label>Bénéficiaire</label><input type="text" name="beneficiaire" class="form-control" required></div>
                    <div class="mb-2"><label>Montant</label><input type="number" name="montant" class="form-control" step="1000" required></div>
                    <div class="mb-2"><label>Devise</label><select name="devise" class="form-select"><option value="XOF">FCFA</option><option value="EUR">Euro</option><option value="USD">Dollar</option></select></div>
                    <div class="mb-2"><label>Échéance</label><input type="date" name="date_echeance" class="form-control"></div>
                    <div class="mb-2"><label>Compte classe 8</label><select name="compte_engagement" class="form-select">
                        <option value="80">80 - Engagements donnés</option><option value="81">81 - Engagements reçus</option>
                        <option value="82">82 - Cautions et avals donnés</option><option value="83">83 - Cautions et avals reçus</option>
                        <option value="84">84 - Crédit-bail</option>
                    </select></div>
                    <div class="mb-2"><label>Référence document</label><input type="text" name="document_reference" class="form-control"></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-danger">Enregistrer</button></div>
            </form>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
