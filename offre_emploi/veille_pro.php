<?php
// Moteur de veille et d'analyse Big Data sectorielle
function analyserTendanceSecteur($pdo) {
    $stmt = $pdo->query("SELECT secteur, COUNT(*) as volume FROM offres GROUP BY secteur ORDER BY volume DESC LIMIT 5");
    return $stmt->fetchAll();
}

$tendances = analyserTendanceSecteur($pdo);
?>
<div class="container my-4">
    <div class="card shadow-sm border-primary">
        <div class="card-body">
            <h5 class="text-primary"><i class="bi bi-graph-up"></i> Tendance Marché Sénégal</h5>
            <ul class="list-group list-group-flush">
                <?php foreach ($tendances as $t): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <?= htmlspecialchars($t['secteur']) ?> 
                        <span class="badge bg-primary"><?= $t['volume'] ?> offres</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
