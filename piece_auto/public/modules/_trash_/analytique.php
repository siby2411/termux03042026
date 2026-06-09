<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Comptabilité analytique";
$page_icon = "pie-chart";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'creer_section') {
        $stmt = $pdo->prepare("INSERT INTO SECTIONS_ANALYTIQUES (code, libelle, type_section) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['code'], $_POST['libelle'], $_POST['type_section']]);
        $message = "✅ Section analytique créée";
    }
}

$sections = $pdo->query("SELECT * FROM SECTIONS_ANALYTIQUES ORDER BY type_section, code")->fetchAll();

// Calcul des résultats par section
$resultats = [];
foreach($sections as $s) {
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(CASE WHEN section_analytique_id = ? AND compte_credite_id BETWEEN 700 AND 799 THEN montant ELSE 0 END), 0) as produits,
               COALESCE(SUM(CASE WHEN section_analytique_id = ? AND compte_debite_id BETWEEN 600 AND 699 THEN montant ELSE 0 END), 0) as charges
        FROM ECRITURES_COMPTABLES
    ");
    $stmt->execute([$s['id'], $s['id']]);
    $res = $stmt->fetch();
    $resultats[$s['id']] = $res['produits'] - $res['charges'];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-pie-chart"></i> Comptabilité analytique</h5>
                <small>Suivi de rentabilité par projet / département</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">➕ Nouvelle section</div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="creer_section">
                                    <div class="mb-2"><label>Code</label><input type="text" name="code" class="form-control" required></div>
                                    <div class="mb-2"><label>Libellé</label><input type="text" name="libelle" class="form-control" required></div>
                                    <div class="mb-2"><label>Type</label>
                                        <select name="type_section" class="form-select">
                                            <option value="PROJET">Projet</option><option value="DEPARTEMENT">Département</option>
                                            <option value="PRODUIT">Produit</option><option value="REGION">Région</option><option value="SERVICE">Service</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn-omega w-100">Créer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h6>Résultats par section analytique</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Code</th><th>Libellé</th><th>Type</th><th class="text-end">Résultat (F)</th><th>Performance</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($sections as $s): ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?= $s['code'] ?> </td>
                                        <td><?= htmlspecialchars($s['libelle']) ?> </td>
                                        <td class="text-center"><?= $s['type_section'] ?> </td>
                                        <td class="text-end <?= $resultats[$s['id']] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format($resultats[$s['id']], 0, ',', ' ') ?> F
                                        </td>
                                        <td class="text-center">
                                            <?php if($resultats[$s['id']] > 1000000): ?>
                                                <span class="badge bg-success">Excellent</span>
                                            <?php elseif($resultats[$s['id']] > 0): ?>
                                                <span class="badge bg-info">Rentable</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Déficitaire</span>
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

<?php include 'inc_footer.php'; ?>
