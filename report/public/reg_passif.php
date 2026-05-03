<?php
// public/reg_passif.php
$page_title = "Régularisations du passif";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// normaliser $pdo
if (function_exists('getConnection')) {
    $pdo = getConnection();
} elseif (isset($conn) && $conn instanceof PDO) {
    $pdo = $conn;
} elseif (isset($db) && $db instanceof PDO) {
    $pdo = $db;
}

// include layout (public/layout.php)
require_once __DIR__ . '/layout.php';

// handle POST insert
$messages = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insert_reg') {
    $date_reg = $_POST['date_reg'] ?: date('Y-m-d');
    $exercice = (int)($_POST['exercice'] ?? date('Y'));
    $compte_id = (int)$_POST['compte_id'];
    $montant = floatval($_POST['montant']);
    $type_reg = $_POST['type_reg'];
    $description = trim($_POST['description'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO regulations_passif (date_reg, exercice, compte_id, montant, type_reg, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$date_reg, $exercice, $compte_id, $montant, $type_reg, $description]);
    $messages[] = "<div class='alert alert-success'>Régularisation enregistrée.</div>";
}

// fetch list
$list = $pdo->query("SELECT r.*, p.intitule_compte FROM regulations_passif r LEFT JOIN PLAN_COMPTABLE_UEMOA p ON r.compte_id = p.compte_id ORDER BY r.date_reg DESC")->fetchAll();

// comptes pour select
$comptes = $pdo->query("SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA ORDER BY compte_id ASC")->fetchAll();
?>

<div class="container-fluid">
    <?php foreach ($messages as $m) echo $m; ?>

    <div class="card p-4 shadow-sm mb-4">
        <h5>Ajouter une régularisation du passif</h5>
        <form method="post" class="row g-3 mt-2">
            <input type="hidden" name="action" value="insert_reg">
            <div class="col-md-3"><label class="form-label">Date</label><input type="date" name="date_reg" class="form-control" value="<?= date('Y-m-d') ?>"></div>
            <div class="col-md-2"><label class="form-label">Exercice</label><input type="number" name="exercice" class="form-control" value="<?= date('Y') ?>"></div>
            <div class="col-md-3"><label class="form-label">Compte</label>
                <select name="compte_id" class="form-control" required>
                    <?php foreach ($comptes as $c): ?>
                        <option value="<?= $c['compte_id'] ?>"><?= $c['compte_id'] ?> - <?= htmlspecialchars($c['intitule_compte']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><label class="form-label">Montant</label><input type="number" step="0.01" name="montant" class="form-control" required></div>
            <div class="col-md-2"><label class="form-label">Type</label>
                <select name="type_reg" class="form-control">
                    <option value="produit_constate_avance">Produit constaté d'avance</option>
                    <option value="charge_constate_avance">Charge constatée d'avance</option>
                </select>
            </div>
            <div class="col-12"><label class="form-label">Description</label>
                <input name="description" class="form-control">
            </div>
            <div class="col-12"><button class="btn btn-primary">Enregistrer</button></div>
        </form>
    </div>

    <div class="card p-3 shadow-sm">
        <h5>Liste des régularisations</h5>
        <table class="table table-striped mt-3">
            <thead class="table-dark">
                <tr><th>Date</th><th>Exercice</th><th>Compte</th><th>Montant</th><th>Type</th><th>Description</th></tr>
            </thead>
            <tbody>
                <?php if (empty($list)) echo '<tr><td colspan="6" class="text-center">Aucune régularisation.</td></tr>'; ?>
                <?php foreach ($list as $r): ?>
                    <tr>
                        <td><?= $r['date_reg'] ?></td>
                        <td><?= $r['exercice'] ?></td>
                        <td><?= $r['compte_id'] ?> - <?= htmlspecialchars($r['intitule_compte']) ?></td>
                        <td class="text-end"><?= number_format($r['montant'],2,',',' ') ?></td>
                        <td><?= $r['type_reg'] ?></td>
                        <td><?= htmlspecialchars($r['description']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

