<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Événements postérieurs à la clôture";
$page_icon = "calendar";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Récupération des événements existants
$evenements = $pdo->query("SELECT * FROM EVENEMENTS_POSTERIEURS ORDER BY date_vention DESC")->fetchAll();
$total_impact = array_sum(array_column($evenements, 'impact_financier'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5><i class="bi bi-calendar"></i> Événements postérieurs à la clôture</h5>
                <small>Conformément à l'Acte Uniforme OHADA - Article 17 - Norme IFRS/IAS 10</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Définition :</strong> Événement favorable ou défavorable survenant entre la date de clôture et la date d'arrêté des comptes.<br>
                    <strong>📌 Types :</strong>
                    <ul class="mt-2 mb-0">
                        <li><strong>ADAPTATIFS</strong> : Donnent lieu à comptabilisation (ex: règlement judiciaire, faillite client)</li>
                        <li><strong>NON ADAPTATIFS</strong> : Mention en annexe uniquement (ex: tremblement de terre, guerre)</li>
                    </ul>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white text-center">
                            <div class="card-body">
                                <h4><?= count($evenements) ?></h4>
                                <small>Événements enregistrés</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark text-center">
                            <div class="card-body">
                                <h4><?= number_format($total_impact, 0, ',', ' ') ?> F</h4>
                                <small>Impact financier total</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#newEventModal">+ Nouvel événement</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Date événement</th>
                                <th>Type</th>
                                <th>Libellé</th>
                                <th>Description</th>
                                <th class="text-end">Impact</th>
                                <th>Comptabilisé</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($evenements as $e): ?>
                            <tr>
                                <td class="text-center"><?= date('d/m/Y', strtotime($e['date_vention'])) ?> </td>
                                <td class="text-center">
                                    <span class="badge <?= $e['type_evenement'] == 'ADAPTATIF' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= $e['type_evenement'] ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($e['libelle']) ?></td>
                                <td><?= htmlspecialchars($e['description']) ?></td>
                                <td class="text-end"><?= number_format($e['impact_financier'], 0, ',', ' ') ?> F</td>
                                <td class="text-center"><?= $e['ecriture_id'] ? '✅ Oui' : '❌ Non' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">IMPACT TOTAL :</td>
                                <td class="text-end fw-bold"><?= number_format($total_impact, 0, ',', ' ') ?> F</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nouvel événement -->
<div class="modal fade" id="newEventModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Nouvel événement postérieur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="ajouter_evenement_posterieur.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Date de l'événement</label>
                        <input type="date" name="date_vention" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label>Type d'événement</label>
                        <select name="type_evenement" class="form-select" required>
                            <option value="ADAPTATIF">ADAPTATIF (à comptabiliser)</option>
                            <option value="NON_ADAPTATIF">NON ADAPTATIF (mention annexe)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Libellé</label>
                        <input type="text" name="libelle" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Impact financier (FCFA)</label>
                        <input type="number" name="impact" class="form-control" step="1000" value="0">
                    </div>
                    <div class="mb-3">
                        <label>Compte impacté (si adaptatif)</label>
                        <select name="compte" class="form-select">
                            <option value="0">Aucun</option>
                            <?php
                            $comptes = $pdo->query("SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA ORDER BY compte_id");
                            foreach($comptes as $c): ?>
                                <option value="<?= $c['compte_id'] ?>"><?= $c['compte_id'] ?> - <?= htmlspecialchars($c['intitule_compte']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
