<?php include 'config.php'; include 'header.php'; 
$db = new Database();
$conn = $db->getConnection();

// Ajouter un client
if($_POST && isset($_POST['ajouter_client'])) {
    $stmt = $conn->prepare("INSERT INTO clients (nom, prenom, telephone, email, adresse) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['nom'], $_POST['prenom'], $_POST['telephone'], $_POST['email'], $_POST['adresse']]);
    header("Location: clients.php");
    exit;
}
?>

<div class="card">
    <h2>Ajouter un client</h2>
    <form method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" required>
            </div>
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="prenom" required>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label>Téléphone</label>
                <input type="text" name="telephone">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email">
            </div>
        </div>
        <div class="form-group">
            <label>Adresse</label>
            <textarea name="adresse" rows="3"></textarea>
        </div>
        <button type="submit" name="ajouter_client" class="btn btn-primary">Ajouter le client</button>
    </form>
</div>

<div class="card">
    <h2>Liste des clients</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Téléphone</th>
                <th>Email</th>
                <th>Date inscription</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = $conn->query("SELECT * FROM clients ORDER BY nom, prenom");
            while($client = $query->fetch(PDO::FETCH_ASSOC)): 
            ?>
            <tr>
                <td><?= $client['id'] ?></td>
                <td><?= $client['nom'] ?></td>
                <td><?= $client['prenom'] ?></td>
                <td><?= $client['telephone'] ?></td>
                <td><?= $client['email'] ?></td>
                <td><?= date('d/m/Y', strtotime($client['date_creation'])) ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
