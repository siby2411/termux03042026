<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Contrats et fiches de régularisation";
$page_icon = "file-text";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Ajout d'un contrat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'ajouter_contrat') {
        $reference = trim($_POST['reference']);
        $tiers_id = (int)$_POST['tiers_id'];
        $type_contrat = $_POST['type_contrat'];
        $date_debut = $_POST['date_debut'];
        $date_fin = !empty($_POST['date_fin']) ? $_POST['date_fin'] : null;
        $objet = trim($_POST['objet']);
        $montant_total = (float)$_POST['montant_total'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO CONTRATS (reference, tiers_id, type_contrat, date_debut, date_fin, objet, montant_total) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$reference, $tiers_id, $type_contrat, $date_debut, $date_fin, $objet, $montant_total]);
            $message = "✅ Contrat ajouté avec succès.";
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
    
    // Ajout d'une régularisation
    if ($_POST['action'] === 'ajouter_regul') {
        $contrat_id = (int)$_POST['contrat_id'];
        $date_regul = $_POST['date_regul'];
        $type_regul = $_POST['type_regul'];
        $reference_piece = trim($_POST['reference_piece']);
        $montant = (float)$_POST['montant'];
        $description = trim($_POST['description']);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO REGULARISATIONS_CONTRATS (contrat_id, date_regul, type_regul, reference_piece, montant, description) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$contrat_id, $date_regul, $type_regul, $reference_piece, $montant, $description]);
            $message = "✅ Régularisation ajoutée.";
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

$contrats = $pdo->query("SELECT c.*, t.raison_sociale FROM CONTRATS c JOIN TIERS t ON c.tiers_id = t.id ORDER BY c.date_debut DESC")->fetchAll();
$tiers = $pdo->query("SELECT id, raison_sociale, type FROM TIERS")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-file-text"></i> Contrats et fiches de régularisation</h5>
                <small>Suivi contractuel et régularisations comptables</small>
            </div>
            <div class="card-body">
                <?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
                <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

                <ul class="nav nav-tabs" id="contratTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#liste">📋 Liste des contrats</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nouveau">➕ Nouveau contrat</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#regul">📝 Ajouter une régularisation</button></li>
                </ul>

                <div class="tab-content mt-3">
                    <!-- Liste des contrats -->
                    <div class="tab-pane fade show active" id="liste">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Référence</th><th>Tiers</th><th>Type</th><th>Début</th><th>Fin</th><th>Objet</th><th>Montant</th><th>Statut</th><th>Actions</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($contrats as $c): ?>
                                    <tr>
                                        <td class="text-center"><?= htmlspecialchars($c['reference']) ?></td>
                                        <td><?= htmlspecialchars($c['raison_sociale']) ?> (\(<?= $c['type_contrat'] ?>)</td>
                                        <td class="text-center"><?= $c['type_contrat'] ?></td>
                                        <td class="text-center"><?= date('d/m/Y', strtotime($c['date_debut'])) ?></td>
                                        <td class="text-center"><?= $c['date_fin'] ? date('d/m/Y', strtotime($c['date_fin'])) : '-' ?></td>
                                        <td><?= htmlspecialchars($c['objet']) ?></td>
                                        <td class="text-end"><?= number_format($c['montant_total'],0,',',' ') ?> F</td>
                                        <td class="text-center"><span class="badge bg-primary"><?= $c['statut'] ?></span></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info" onclick="voirRegul(<?= $c['id'] ?>, '<?= htmlspecialchars($c['reference']) ?>')">📋 Régularisations</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Nouveau contrat -->
                    <div class="tab-pane fade" id="nouveau">
                        <div class="card bg-light">
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="ajouter_contrat">
                                    <div class="col-md-4"><label>Référence *</label><input type="text" name="reference" class="form-control" required></div>
                                    <div class="col-md-4"><label>Tiers</label><select name="tiers_id" class="form-select" required><?php foreach($tiers as $t): ?><option value="<?= $t['id'] ?>"><?= $t['raison_sociale'] ?></option><?php endforeach; ?></select></div>
                                    <div class="col-md-4"><label>Type contrat</label><select name="type_contrat" class="form-select"><option>CLIENT</option><option>FOURNISSEUR</option><option>AUTRE</option></select></div>
                                    <div class="col-md-4"><label>Date début</label><input type="date" name="date_debut" class="form-control" required></div>
                                    <div class="col-md-4"><label>Date fin</label><input type="date" name="date_fin" class="form-control"></div>
                                    <div class="col-md-6"><label>Objet</label><input type="text" name="objet" class="form-control"></div>
                                    <div class="col-md-2"><label>Montant total</label><input type="number" name="montant_total" class="form-control" step="1000"></div>
                                    <div class="col-12"><button type="submit" class="btn-omega">Enregistrer</button></div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Ajout régularisation -->
                    <div class="tab-pane fade" id="regul">
                        <div class="card bg-light">
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="ajouter_regul">
                                    <div class="col-md-4"><label>Contrat</label><select name="contrat_id" class="form-select" required><?php foreach($contrats as $c): ?><option value="<?= $c['id'] ?>"><?= $c['reference'] ?> - <?= $c['raison_sociale'] ?></option><?php endforeach; ?></select></div>
                                    <div class="col-md-4"><label>Date</label><input type="date" name="date_regul" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
                                    <div class="col-md-4"><label>Type</label><select name="type_regul" class="form-select"><option>FACTURE</option><option>AVOIR</option><option>NOTE_CREDIT</option><option>PAIEMENT</option><option>REGULARISATION</option></select></div>
                                    <div class="col-md-4"><label>Référence pièce</label><input type="text" name="reference_piece" class="form-control"></div>
                                    <div class="col-md-4"><label>Montant (F)</label><input type="number" name="montant" class="form-control" step="100" required></div>
                                    <div class="col-md-12"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                                    <div class="col-12"><button type="submit" class="btn-omega">Ajouter régularisation</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal pour afficher les régularisations -->
<div class="modal fade" id="regulModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white"><h5 class="modal-title">Régularisations du contrat</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body" id="regulContent"></div>
        </div>
    </div>
</div>

<script>
function voirRegul(id, ref) {
    fetch('ajax_regul_contrat.php?contrat_id=' + id)
        .then(r => r.json())
        .then(data => {
            let html = '<h6>Contrat : ' + ref + '</h6><div class="table-responsive"><table class="table table-bordered"><thead class="table-light"><tr><th>Date</th><th>Type</th><th>Référence</th><th>Montant</th><th>Description</th></tr></thead><tbody>';
            data.forEach(r => {
                html += '<tr><td>' + r.date_regul + '</td><td>' + r.type_regul + '</td><td>' + (r.reference_piece || '-') + '</td><td class="text-end">' + new Intl.NumberFormat().format(r.montant) + ' F</td><td>' + (r.description || '-') + '</td></tr>';
            });
            html += '</tbody></table></div>';
            document.getElementById('regulContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('regulModal')).show();
        });
}
</script>

<?php include 'inc_footer.php'; ?>
