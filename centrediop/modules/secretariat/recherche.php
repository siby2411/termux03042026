<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'secretariat' && $_SESSION['user_role'] !== 'admin')) { header('Location: /login.php'); exit(); }
require_once '../../config/database.php';
$db = getPDO();
$q = $_GET['q'] ?? '';
$results = [];

if ($q) {
    $stmt = $db->prepare("SELECT * FROM patients WHERE nom LIKE ? OR prenom LIKE ? OR code_patient_unique LIKE ?");
    $stmt->execute(["%$q%", "%$q%", "%$q%"]);
    $results = $stmt->fetchAll();
}
?>
<div class="p-4">
    <h4>Recherche & Orientation</h4>
    <form method="GET" class="d-flex mb-3">
        <input type="text" name="q" class="form-control" placeholder="Rechercher patient..." value="<?= htmlspecialchars($q) ?>">
        <button class="btn btn-dark">Chercher</button>
    </form>
    <table class="table">
        <?php foreach($results as $p): ?>
        <tr><td><?= $p['nom'] ?> <?= $p['prenom'] ?></td><td><a href="suivi.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Orienter</a></td></tr>
        <?php endforeach; ?>
    </table>
</div>
