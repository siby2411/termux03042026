<?php
$page_title = "Contrôle Budgétaire";



  
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

// Ajouter budget
if(isset($_POST['submit'])){
    $stmt = $pdo->prepare("INSERT INTO budget_previsionnel (compte_id, exercice, montant_budget, montant_realise) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['compte_id'], $_POST['exercice'], $_POST['montant_budget'], $_POST['montant_realise'] ?? 0]);
    echo "<div class='alert alert-success'>Budget enregistré</div>";
}

// Récupérer budgets
$budgets = $pdo->query("SELECT b.*, p.intitule_compte FROM budget_previsionnel b JOIN PLAN_COMPTABLE_UEMOA p ON b.compte_id=p.compte_id ORDER BY b.exercice DESC")->fetchAll();
?>

<div class="card p-4 shadow-sm mb-4">
    <h5>Ajouter un budget prévisionnel</h5>
    <form action="" method="post" class="mt-3">
        <div class="row">
            <div class="col-md-3"><label>Exercice</label><input type="number" name="exercice" class="form-control" required></div>
            <div class="col-md-3"><label>Compte</label>
                <select name="compte_id" class="form-control" required>
                    <?php
                    $comptes = $pdo->query("SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA")->fetchAll();
                    foreach($comptes as $c) echo "<option value='{$c['compte_id']}'>{$c['intitule_compte']}</option>";
                    ?>
                </select>
            </div>
            <div class="col-md-3"><label>Montant Budget</label><input type="number" step="0.01" name="montant_budget" class="form-control" required></div>
            <div class="col-md-3"><label>Montant Réalisé</label><input type="number" step="0.01" name="montant_realise" class="form-control"></div>
        </div>
        <button type="submit" name="submit" class="btn btn-warning mt-3">Enregistrer</button>
    </form>
</div>

<div class="card p-4 shadow-sm">
    <h5>Budgets prévisionnels</h5>
    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr><th>Exercice</th><th>Compte</th><th>Budget</th><th>Réalisé</th><th>Ecart</th></tr>
        </thead>
        <tbody>
            <?php foreach($budgets as $b): ?>
            <tr>
                <td><?= $b['exercice'] ?></td>
                <td><?= $b['intitule_compte'] ?></td>
                <td><?= number_format($b['montant_budget'],2,',','.') ?></td>
                <td><?= number_format($b['montant_realise'],2,',','.') ?></td>
                <td><?= number_format($b['ecart'],2,',','.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

