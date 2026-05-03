<?php
require_once 'config/database.php';
include 'header.php';

$database = new Database();
$db = $database->getConnection();

$results = [];
$search_type = isset($_GET['type']) ? $_GET['type'] : 'adherent';

if(isset($_GET['search'])) {
    $keyword = '%' . $_GET['keyword'] . '%';
    
    if($search_type == 'adherent') {
        $query = "SELECT * FROM adherents WHERE nom LIKE :keyword OR prenom LIKE :keyword OR email LIKE :keyword OR numero_licence LIKE :keyword";
        $stmt = $db->prepare($query);
        $stmt->execute([':keyword' => $keyword]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } elseif($search_type == 'discipline') {
        $query = "SELECT * FROM adherents WHERE discipline_principale LIKE :keyword";
        $stmt = $db->prepare($query);
        $stmt->execute([':keyword' => $keyword]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-search"></i> Recherche Avancée</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <select name="type" class="form-control">
                            <option value="adherent">Par Adhérent</option>
                            <option value="discipline">Par Discipline</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" name="keyword" class="form-control" placeholder="Mot-clé..." required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="search" class="btn btn-primary w-100">Rechercher</button>
                    </div>
                </div>
            </form>
            
            <?php if(isset($_GET['search'])): ?>
                <h5>Résultats: <?= count($results) ?> trouvé(s)</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr><th>Licence</th><th>Nom</th><th>Email</th><th>Discipline</th><th>Statut</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($results as $r): ?>
                            <tr>
                                <td><?= $r['numero_licence'] ?></td>
                                <td><?= htmlspecialchars($r['prenom'] . ' ' . $r['nom']) ?></td>
                                <td><?= $r['email'] ?></td>
                                <td><?= $r['discipline_principale'] ?></td>
                                <td><span class="badge bg-<?= $r['statut']=='actif'?'success':'danger' ?>"><?= $r['statut'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
