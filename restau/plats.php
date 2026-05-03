<?php include 'config.php'; include 'header.php'; 
$db = new Database();
$conn = $db->getConnection();

// Ajouter un plat
if($_POST && isset($_POST['ajouter_plat'])) {
    $stmt = $conn->prepare("INSERT INTO plats (nom, prix, disponible) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['nom'], $_POST['prix'], isset($_POST['disponible']) ? 1 : 0]);
    echo '<div class="alert alert-success">✅ Plat ajouté au menu !</div>';
}

// Changer disponibilité
if(isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $conn->query("UPDATE plats SET disponible = 1 - disponible WHERE id = $id");
    header("Location: plats.php");
    exit;
}

// Récupérer les plats
$plats = $conn->query("SELECT * FROM plats ORDER BY nom")->fetchAll();
?>

<div class="card">
    <h2>🍔 Ajouter au Menu</h2>
    <form method="POST">
        <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 1rem; align-items: end;">
            <div class="form-group">
                <label>Nom du plat / boisson</label>
                <input type="text" name="nom" placeholder="ex: Pizza Royale" required>
            </div>
            <div class="form-group">
                <label>Prix (€)</label>
                <input type="number" step="0.01" name="prix" required>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="disponible" checked> Disponible</label>
            </div>
            <button type="submit" name="ajouter_plat" class="btn btn-primary">➕ Ajouter</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>📋 Carte du Restaurant</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Plat</th>
                <th>Prix</th>
                <th>Statut</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($plats as $p): ?>
            <tr>
                <td>#<?= $p['id'] ?></td>
                <td><strong><?= htmlspecialchars($p['nom']) ?></strong></td>
                <td><?= number_format($p['prix'], 2, ',', ' ') ?> €</td>
                <td>
                    <span class="badge <?= $p['disponible'] ? 'badge-success' : 'badge-danger' ?>">
                        <?= $p['disponible'] ? 'En vente' : 'Épuisé' ?>
                    </span>
                </td>
                <td>
                    <a href="plats.php?toggle=<?= $p['id'] ?>" class="btn btn-sm">
                        <?= $p['disponible'] ? '❌ Retirer' : '✅ Remettre' ?>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
