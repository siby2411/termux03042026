<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Engagements Hors Bilan - Classe 8";
$page_icon = "shield";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Ajout d'un engagement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO ENGAGEMENTS_HORS_BILAN (date_engagement, type, beneficiaire, montant, devise, date_echeance, compte_engagement, document_reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['date_engagement'],
            $_POST['type'],
            $_POST['beneficiaire'],
            $_POST['montant'],
            $_POST['devise'],
            $_POST['date_echeance'] ?: null,
            $_POST['compte_engagement'],
            $_POST['document_reference']
        ]);
        $message = "✅ Engagement enregistré avec succès";
    } catch (Exception $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

// Récupération des engagements
$engagements = $pdo->query("SELECT * FROM ENGAGEMENTS_HORS_BILAN WHERE statut = 'ACTIF' ORDER BY date_echeance")->fetchAll();
$total_engagements = array_sum(array_column($engagements, 'montant'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5><i class="bi bi-shield-shaded"></i> Engagements Hors Bilan - Classe 8 SYSCOHADA</h5>
                <small>Caution, avals, garanties, nantissements, crédit-bail, etc.</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill"></i>
                    <strong>📖 Définition SYSCOHADA :</strong>
                    <p>Les engagements hors bilan sont des obligations potentielles qui ne figurent pas au bilan mais qui peuvent avoir un impact financier significatif.</p>
                </div>
                
                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-danger text-white text-center">
                            <div class="card-body">
                                <h3><?= count($engagements) ?></h3>
                                <small>Engagements actifs</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <h3><?= number_format($total_engagements, 0, ',', ' ') ?> F</h3>
                                <small>Montant total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-secondary text-white text-center">
                            <div class="card-body">
                                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addModal">
                                    <i class="bi bi-plus-lg"></i> Nouvel engagement
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Types d'engagements -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-secondary">
                            <strong>🏷️ Types d'engagements prévus par SYSCOHADA (Classe 8) :</strong>
                            <span class="badge bg-danger">CAUTION</span>
                            <span class="badge bg-warning text-dark">AVAL</span>
                            <span class="badge bg-success">GARANTIE</span>
                            <span class="badge bg-info">NANTISSEMENT</span>
                            <span class="badge bg-primary">CREDIT_BAIL</span>
                            <span class="badge bg-dark">LITIGE</span>
                        </div>
                    </div>
                </div>
                
                <!-- Liste des engagements -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th>Date</th>
                                <th>Type</th>
                                <th>Bénéficiaire</th>
                                <th>Montant</th>
                                <th>Échéance</th>
                                <th>Compte</th>
                                <th>Référence</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($engagements as $e): ?>
                            <tr>
                                <td class="text-center"><?= date('d/m/Y', strtotime($e['date_engagement'])) ?></td>
                                <td class="text-center">
                                    <span class="badge bg-danger"><?= $e['type'] ?></span>
                                </td>
                                <td><?= htmlspecialchars($e['beneficiaire']) ?></td>
                                <td class="text-end"><?= number_format($e['montant'], 0, ',', ' ') ?> <?= $e['devise'] ?></td>
                                <td class="text-center"><?= $e['date_echeance'] ? date('d/m/Y', strtotime($e['date_echeance'])) : '-' ?></td>
                                <td class="text-center"><?= $e['compte_engagement'] ?></td>
                                <td class="text-center"><?= htmlspecialchars($e['document_reference'] ?? '-') ?></td>
                                <td class="text-center"><span class="badge bg-success">ACTIF</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">TOTAL ENGAGEMENTS :</td>
                                <td class="text-end"><?= number_format($total_engagements, 0, ',', ' ') ?> F</td>
                                <td colspan="4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajout Engagement -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-shield-plus"></i> Nouvel engagement hors bilan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label>Date d'engagement</label>
                        <input type="date" name="date_engagement" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Type d'engagement</label>
                        <select name="type" class="form-select" required>
                            <option value="CAUTION">Caution</option>
                            <option value="AVAL">Aval</option>
                            <option value="GARANTIE">Garantie</option>
                            <option value="NANTISSEMENT">Nantissement</option>
                            <option value="CREDIT_BAIL">Crédit-bail</option>
                            <option value="LITIGE">Litige</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Bénéficiaire</label>
                        <input type="text" name="beneficiaire" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Montant</label>
                        <input type="number" name="montant" class="form-control" step="1000" required>
                    </div>
                    <div class="mb-3">
                        <label>Devise</label>
                        <select name="devise" class="form-select">
                            <option value="XOF">FCFA (XOF)</option>
                            <option value="EUR">Euro (EUR)</option>
                            <option value="USD">Dollar (USD)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Date d'échéance</label>
                        <input type="date" name="date_echeance" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Compte engagement (Classe 8)</label>
                        <select name="compte_engagement" class="form-select" required>
                            <option value="80">80 - Engagements donnés</option>
                            <option value="81">81 - Engagements reçus</option>
                            <option value="82">82 - Cautions et avals donnés</option>
                            <option value="83">83 - Cautions et avals reçus</option>
                            <option value="84">84 - Crédit-bail</option>
                            <option value="85">85 - Contrats de location-financement</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Document référence</label>
                        <input type="text" name="document_reference" class="form-control" placeholder="Contrat n°, Jugement n°, etc.">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Enregistrer l'engagement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
