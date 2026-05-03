<?php
require_once __DIR__ . '/../../config/database.php';
$database = new Database();
$db = $database->getConnection();
$resultats = [];
if(isset($_GET['q']) && strlen($_GET['q']) >= 2) {
    $q = $_GET['q'];
    $stmt = $db->prepare("SELECT id_eleve, nom_eleve, prenom_eleve, classe FROM eleves WHERE nom_eleve LIKE ? OR prenom_eleve LIKE ? LIMIT 20");
    $param = "%$q%";
    $stmt->execute([$param, $param]);
    $resultats = $stmt->fetchAll();
}
include __DIR__ . '/../../includes/header.php';
?>
<div class="container mt-4">
    <h2><i class="fas fa-search"></i> Recherche avancée</h2>
    <div class="card"><div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-9"><input type="text" name="q" class="form-control form-control-lg" placeholder="Nom, prénom de l'élève..." value="<?= $_GET['q'] ?? '' ?>"></div>
            <div class="col-md-3"><button type="submit" class="btn btn-primary btn-lg w-100"><i class="fas fa-search"></i> Rechercher</button></div>
        </form>
    </div></div>
    <?php if(!empty($resultats)): ?>
    <div class="mt-4"><h4>Résultats (<?= count($resultats) ?> trouvés)</h4>
        <table class="table table-striped"><thead class="table-dark"><tr><th>Nom</th><th>Prénom</th><th>Classe</th></tr></thead>
        <tbody><?php foreach($resultats as $r): ?><tr><td><?= htmlspecialchars($r['nom_eleve']) ?></td><td><?= htmlspecialchars($r['prenom_eleve']) ?></td><td><?= $r['classe'] ?></td></tr><?php endforeach; ?></tbody>
        </table>
    </div>
    <?php elseif(isset($_GET['q'])): ?><div class="alert alert-warning mt-4">Aucun résultat trouvé</div><?php endif; ?>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
