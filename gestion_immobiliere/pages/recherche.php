<?php
$zones = $pdo->query("SELECT * FROM zones_geographiques")->fetchAll();
$results = [];

if (isset($_GET['search'])) {
    // FILTRE AUTOMATIQUE : On ne cherche que le 'Disponible'
    $sql = "SELECT i.*, z.nom as zone_nom FROM immeubles i 
            JOIN zones_geographiques z ON i.zone_id = z.id 
            WHERE i.statut = 'Disponible'";
    $params = [];

    if (!empty($_GET['zone'])) {
        $sql .= " AND i.zone_id = ?";
        $params[] = $_GET['zone'];
    }
    if (!empty($_GET['prix_max'])) {
        $sql .= " AND i.prix <= ?";
        $params[] = $_GET['prix_max'];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}
?>

<div class="card">
    <div class="card-title">🔍 Recherche de biens DISPONIBLES</div>
    <form method="GET" class="form-grid">
        <input type="hidden" name="page" value="recherche">
        <select name="zone">
            <option value="">Toutes les zones (Dakar)</option>
            <?php foreach($zones as $z): ?>
                <option value="<?= $z['id'] ?>"><?= $z['nom'] ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="prix_max" placeholder="Budget Max (FCFA)">
        <button type="submit" name="search" class="btn btn-primary">Lancer la recherche</button>
    </form>
</div>

<?php if (isset($_GET['search'])): ?>
<div class="card">
    <div class="card-title">📍 <?= count($results) ?> Biens prêts pour visite</div>
    <table>
        <thead><tr><th>Titre</th><th>Zone</th><th>Prix</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach($results as $res): ?>
            <tr>
                <td><b><?= $res['titre'] ?></b></td>
                <td><?= $res['zone_nom'] ?></td>
                <td class="color-or"><?= number_format($res['prix'], 0) ?> F</td>
                <td><a href="?page=visites&i_id=<?= $res['id'] ?>" class="badge badge-ok">Programmer Visite</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
