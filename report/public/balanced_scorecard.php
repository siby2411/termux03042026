<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Balanced Scorecard";
$page_icon = "grid";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$exercice = $_GET['exercice'] ?? date('Y');

// Ajout / mise à jour d'un objectif
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $perspective = $_POST['perspective'];
    $objectif = trim($_POST['objectif']);
    $indicateur = trim($_POST['indicateur']);
    $cible = (float)$_POST['cible'];
    $realise = (float)$_POST['realise'];
    $poids = (int)$_POST['poids'];

    $stmt = $pdo->prepare("INSERT INTO BALANCED_SCORECARD (id, exercice, perspective, objectif, indicateur, cible, realise, poids) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE perspective=?, objectif=?, indicateur=?, cible=?, realise=?, poids=?");
    $stmt->execute([$id, $exercice, $perspective, $objectif, $indicateur, $cible, $realise, $poids,
                    $perspective, $objectif, $indicateur, $cible, $realise, $poids]);
    $message = "✅ Objectif enregistré";
}

$objectifs = $pdo->prepare("SELECT * FROM BALANCED_SCORECARD WHERE exercice = ? ORDER BY perspective");
$objectifs->execute([$exercice]);
$list_objectifs = $objectifs->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Balanced Scorecard - Exercice <?= $exercice ?></h5>
                <small>Déclinaison stratégique de la performance</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <td><th>Perspective</th><th>Objectif</th><th>Indicateur</th><th>Cible</th><th>Réalisé</th><th>Taux (%)</th><th>Poids</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($list_objectifs as $obj): 
                                $taux = $obj['cible'] > 0 ? ($obj['realise'] / $obj['cible']) * 100 : 0;
                                $couleur = $taux >= 100 ? 'success' : ($taux >= 80 ? 'warning' : 'danger');
                            ?>
                            <tr>
                                <td><?= $obj['perspective'] ?> </td>
                                <td><?= htmlspecialchars($obj['objectif']) ?> </td>
                                <td><?= htmlspecialchars($obj['indicateur']) ?> </td>
                                <td class="text-end"><?= number_format($obj['cible'],0,',',' ') ?> <?= $obj['indicateur'] == 'Taux de satisfaction' ? '%' : 'F' ?></td>
                                <td class="text-end"><?= number_format($obj['realise'],0,',',' ') ?> <?= $obj['indicateur'] == 'Taux de satisfaction' ? '%' : 'F' ?></td>
                                <td class="text-center"><span class="badge bg-<?= $couleur ?>"><?= number_format($taux,1) ?>%</span></td>
                                <td class="text-center"><?= $obj['poids'] ?>%</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal"
                                        data-id="<?= $obj['id'] ?>"
                                        data-perspective="<?= htmlspecialchars($obj['perspective']) ?>"
                                        data-objectif="<?= htmlspecialchars($obj['objectif']) ?>"
                                        data-indicateur="<?= htmlspecialchars($obj['indicateur']) ?>"
                                        data-cible="<?= $obj['cible'] ?>"
                                        data-realise="<?= $obj['realise'] ?>"
                                        data-poids="<?= $obj['poids'] ?>">✏️</button>
                                 </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-omega mt-3" data-bs-toggle="modal" data-bs-target="#newModal">+ Nouvel objectif</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajout -->
<div class="modal fade" id="newModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST"><div class="modal-body">
            <input type="hidden" name="id" value="0">
            <div class="mb-2"><label>Perspective</label><select name="perspective" class="form-select"><option>FINANCIERE</option><option>CLIENT</option><option>PROCESSUS_INTERNES</option><option>APPRENTISSAGE</option></select></div>
            <div class="mb-2"><label>Objectif</label><input type="text" name="objectif" class="form-control" required></div>
            <div class="mb-2"><label>Indicateur</label><input type="text" name="indicateur" class="form-control"></div>
            <div class="mb-2"><label>Cible</label><input type="number" name="cible" class="form-control" step="1000"></div>
            <div class="mb-2"><label>Réalisé</label><input type="number" name="realise" class="form-control" step="1000"></div>
            <div class="mb-2"><label>Poids (%)</label><input type="number" name="poids" class="form-control" value="1"></div>
        </div><div class="modal-footer"><button type="submit" class="btn btn-primary">Enregistrer</button></div></form>
    </div></div>
</div>

<script>
document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target="#editModal"]').forEach(btn => {
    btn.addEventListener('click', function() {
        let id = this.dataset.id;
        let perspective = this.dataset.perspective;
        let objectif = this.dataset.objectif;
        let indicateur = this.dataset.indicateur;
        let cible = this.dataset.cible;
        let realise = this.dataset.realise;
        let poids = this.dataset.poids;
        let form = document.querySelector('#editModal form');
        form.querySelector('[name="id"]').value = id;
        form.querySelector('[name="perspective"]').value = perspective;
        form.querySelector('[name="objectif"]').value = objectif;
        form.querySelector('[name="indicateur"]').value = indicateur;
        form.querySelector('[name="cible"]').value = cible;
        form.querySelector('[name="realise"]').value = realise;
        form.querySelector('[name="poids"]').value = poids;
    });
});
</script>

<!-- Modal Edition -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST"><div class="modal-body">
            <input type="hidden" name="id">
            <div class="mb-2"><label>Perspective</label><select name="perspective" class="form-select"><option>FINANCIERE</option><option>CLIENT</option><option>PROCESSUS_INTERNES</option><option>APPRENTISSAGE</option></select></div>
            <div class="mb-2"><label>Objectif</label><input type="text" name="objectif" class="form-control" required></div>
            <div class="mb-2"><label>Indicateur</label><input type="text" name="indicateur" class="form-control"></div>
            <div class="mb-2"><label>Cible</label><input type="number" name="cible" class="form-control" step="1000"></div>
            <div class="mb-2"><label>Réalisé</label><input type="number" name="realise" class="form-control" step="1000"></div>
            <div class="mb-2"><label>Poids (%)</label><input type="number" name="poids" class="form-control" value="1"></div>
        </div><div class="modal-footer"><button type="submit" class="btn btn-primary">Mettre à jour</button></div></form>
    </div></div>
</div>

<?php include 'inc_footer.php'; ?>
