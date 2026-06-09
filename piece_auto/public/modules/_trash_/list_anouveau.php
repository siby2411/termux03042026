<?php
// public/list_a_nouveaux.php
$page_title = "Liste - À Nouveaux";
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth_check.php';

// normaliser $pdo si nécessaire (fallback)
if (function_exists('getConnection')) {
    $pdo = getConnection();
} elseif (isset($conn) && $conn instanceof PDO) {
    $pdo = $conn;
} elseif (isset($db) && $db instanceof PDO) {
    $pdo = $db;
}

// inclure le layout (présume layout.php dans public/)
require_once __DIR__ . '/layout.php';

// Récupération
$rows = $pdo->query("SELECT * FROM a_nouveaux ORDER BY exercice DESC, id DESC")->fetchAll();
?>

<div class="container-fluid">
    <div class="card p-4 shadow-sm">
        <h5>Liste des À Nouveaux</h5>
        <p class="text-muted">Soldes d'ouverture par exercice</p>

        <table class="table table-striped table-bordered mt-3">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Exercice</th>
                    <th>Résultat exercice</th>
                    <th>Report à nouveau</th>
                    <th>Réserves</th>
                    <th>Date enregistrement</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="6" class="text-center">Aucune donnée.</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td><?= $r['exercice'] ?></td>
                            <td class="text-end"><?= number_format($r['resultat_exercice'],2,',',' ') ?></td>
                            <td class="text-end"><?= number_format($r['report_nouveau'],2,',',' ') ?></td>
                            <td class="text-end"><?= number_format($r['reserves'],2,',',' ') ?></td>
                            <td><?= $r['date_enregistrement'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

