<?php
$page_title = "Contrôle de Gestion";




  
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

// Ajouter indicateur
if(isset($_POST['submit'])){
    $stmt = $pdo->prepare("INSERT INTO controle_gestion (indicateur, valeur_cible, valeur_reelle, date_mesure) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['indicateur'], $_POST['valeur_cible'], $_POST['valeur_reelle'] ?? 0, $_POST['date_mesure']]);
    echo "<div class='alert alert-success'>Indicateur ajouté</div>";
}

// Récupérer indicateurs
$indicateurs = $pdo->query("SELECT * FROM controle_gestion ORDER BY date_mesure DESC")->fetchAll();
?>

<div class="card p-4 shadow-sm mb-4">
    <h5>Ajouter un indicateur</h5>
    <form action="" method="post" class="mt-3">
        <div class="row">
            <div class="col-md-4"><label>Indicateur</label><input type="text" name="indicateur" class="form-control" required></div>
            <div class="col-md-4"><label>Valeur cible</label><input type="number" step="0.01" name="valeur_cible" class="form-control" required></div>
            <div class="col-md-4"><label>Valeur réelle</label><input type="number" step="0.01" name="valeur_reelle" class="form-control"></div>
        </div>
        <div class="row mt-2">
            <div class="col-md-4"><label>Date mesure</label><input type="date" name="date_mesure" class="form-control" required></div>
        </div>
        <button type="submit" name="submit" class="btn btn-info mt-3">Ajouter</button>
    </form>
</div>

<div class="card p-4 shadow-sm">
    <h5>Indicateurs</h5>
    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr><th>Indicateur</th><th>Valeur cible</th><th>Valeur réelle</th><th>Ecart</th><th>Date</th></tr>
        </thead>
        <tbody>
            <?php foreach($indicateurs as $i): ?>
            <tr>
                <td><?= $i['indicateur'] ?></td>
                <td><?= number_format($i['valeur_cible'],2,',','.') ?></td>
                <td><?= number_format($i['valeur_reelle'],2,',','.') ?></td>
                <td><?= number_format($i['ecart'],2,',','.') ?></td>
                <td><?= $i['date_mesure'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

