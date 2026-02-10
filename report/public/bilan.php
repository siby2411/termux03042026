<?php
$page_title = "Bilan";
require_once 'layout.php';
require_once __DIR__ . '/../config/database.php';  // FIX




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















// Charger les comptes du bilan (classe 1 à 5)
$sql = "
    SELECT sb.*, pc.intitule_compte 
    FROM SYNTHESES_BALANCE sb
    LEFT JOIN PLAN_COMPTABLE_UEMOA pc ON sb.compte_id = pc.compte_id
    WHERE sb.compte_id BETWEEN 1 AND 5999
    ORDER BY sb.compte_id
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$balances = $stmt->fetchAll();
?>

<div class="container-fluid">
    <h3 class="mb-4">Bilan Comptable</h3>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-bordered table-sm mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Compte</th>
                        <th>Intitulé</th>
                        <th>Débit</th>
                        <th>Crédit</th>
                        <th>Solde Débiteur</th>
                        <th>Solde Créditeur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($balances as $b): ?>
                    <tr>
                        <td><?= $b['compte_id'] ?></td>
                        <td><?= htmlspecialchars($b['intitule_compte']) ?></td>
                        <td><?= number_format($b['mouvement_debit'],2,',',' ') ?></td>
                        <td><?= number_format($b['mouvement_credit'],2,',',' ') ?></td>
                        <td><?= number_format($b['solde_debiteur'],2,',',' ') ?></td>
                        <td><?= number_format($b['solde_crediteur'],2,',',' ') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

