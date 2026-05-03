<?php include 'config.php'; include 'header.php'; 
$db = new Database();
$conn = $db->getConnection();

// Ajouter un service
if($_POST && isset($_POST['ajouter_service'])) {
    $stmt = $conn->prepare("INSERT INTO services (nom, prix) VALUES (?, ?)");
    $stmt->execute([$_POST['nom'], $_POST['prix']]);
    header("Location: services.php");
    exit;
}
?>

<div class="card">
    <h2>Ajouter un nouveau service</h2>
    <form method="POST">
        <div style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 1rem; align-items: end;">
            <div class="form-group">
                <label>Nom du service</label>
                <input type="text" name="nom" placeholder="ex: Lavage Veste" required>
            </div>
            <div class="form-group">
                <label>Prix (€)</label>
                <input type="number" step="0.01" name="prix" required>
            </div>
            <button type="submit" name="ajouter_service" class="btn btn-primary">Ajouter</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Liste des services disponibles</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Service</th>
                <th>Prix</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = $conn->query("SELECT * FROM services ORDER BY nom");
            while($s = $query->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= $s['id'] ?></td>
                <td><?= $s['nom'] ?></td>
                <td><?= number_format($s['prix'], 2, ',', ' ') ?> €</td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'footer.php'; ?>
