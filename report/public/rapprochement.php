<?php
$page_title = "Rapprochement Bancaire";


require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../includes/auth_check.php'); // si besoin





// normaliser $pdo (compatible avec différents database.php)
if (function_exists('getConnection')) {
    $pdo = getConnection();
} elseif (isset($conn) && $conn instanceof PDO) {
    $pdo = $conn;
} elseif (isset($db) && $db instanceof PDO) {
    $pdo = $db;
} else {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=synthesepro_db;charset=utf8mb4', 'root', '123', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        die("Erreur DB fallback : " . $e->getMessage());
    }
}








require_once "layout.php";

// Ajouter un relevé
if(isset($_POST['submit'])){
    $stmt = $pdo->prepare("INSERT INTO rapprochement_bancaire (date_releve, reference_releve, compte_id, montant_releve) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['date_releve'], $_POST['reference_releve'], $_POST['compte_id'], $_POST['montant_releve']]);
    echo "<div class='alert alert-success'>Relevé ajouté avec succès</div>";
}

// Récupérer les relevés
$releves = $pdo->query("SELECT r.*, p.intitule_compte FROM rapprochement_bancaire r JOIN PLAN_COMPTABLE_UEMOA p ON r.compte_id=p.compte_id ORDER BY r.date_releve DESC")->fetchAll();
?>

<div class="card p-4 shadow-sm mb-4">
    <h5>Ajouter un relevé bancaire</h5>
    <form action="" method="post" class="mt-3">
        <div class="row">
            <div class="col-md-3"><label>Date relevé</label><input type="date" name="date_releve" class="form-control" required></div>
            <div class="col-md-3"><label>Référence</label><input type="text" name="reference_releve" class="form-control" required></div>
            <div class="col-md-3"><label>Compte</label>
                <select name="compte_id" class="form-control" required>
                    <?php
                    $comptes = $pdo->query("SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA")->fetchAll();
                    foreach($comptes as $c) echo "<option value='{$c['compte_id']}'>{$c['intitule_compte']}</option>";
                    ?>
                </select>
            </div>
            <div class="col-md-3"><label>Montant</label><input type="number" step="0.01" name="montant_releve" class="form-control" required></div>
        </div>
        <button type="submit" name="submit" class="btn btn-primary mt-3">Ajouter Relevé</button>
    </form>
</div>

<div class="card p-4 shadow-sm">
    <h5>Relevés bancaires</h5>
    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr><th>Date</th><th>Référence</th><th>Compte</th><th>Montant</th><th>Statut</th></tr>
        </thead>
        <tbody>
            <?php foreach($releves as $r): ?>
            <tr>
                <td><?= $r['date_releve'] ?></td>
                <td><?= $r['reference_releve'] ?></td>
                <td><?= $r['intitule_compte'] ?></td>
                <td><?= number_format($r['montant_releve'],2,',','.') ?></td>
                <td><?= $r['statut'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

