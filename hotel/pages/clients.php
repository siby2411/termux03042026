<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sql = "INSERT INTO clients (nom, prenom, email, telephone) VALUES (?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$_POST['nom'], $_POST['prenom'], $_POST['email'], $_POST['tel']]);
}
$data = $pdo->query("SELECT * FROM clients")->fetchAll();
?>
<div class="card">
    <h3>Nouveau Client</h3>
    <form method="POST" class="form-grid">
        <input type="text" name="nom" placeholder="Nom" required>
        <input type="text" name="prenom" placeholder="Prénom" required>
        <input type="email" name="email" placeholder="Email">
        <input type="text" name="tel" placeholder="Téléphone">
        <button type="submit" class="btn btn-primary">Ajouter</button>
    </form>
</div>
<div class="card">
    <table>
        <thead><tr><th>Client</th><th>Contact</th><th>Inscrit le</th></tr></thead>
        <tbody>
            <?php foreach($data as $row): ?>
            <tr><td><?= $row['nom'] ?> <?= $row['prenom'] ?></td><td><?= $row['telephone'] ?></td><td><?= $row['date_inscription'] ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
