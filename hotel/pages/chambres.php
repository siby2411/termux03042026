<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sql = "INSERT INTO chambres (numero, type, etage, prix_nuit, capacite) VALUES (?, ?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$_POST['num'], $_POST['type'], $_POST['etage'], $_POST['prix'], $_POST['cap']]);
}
$data = $pdo->query("SELECT * FROM chambres")->fetchAll();
?>
<div class="card">
    <h3>Ajouter une Chambre</h3>
    <form method="POST" class="form-grid">
        <input type="text" name="num" placeholder="N°" required>
        <select name="type">
            <option>Simple</option><option>Double</option><option>Suite</option><option>Présidentielle</option>
        </select>
        <input type="number" name="etage" placeholder="Étage" value="1">
        <input type="number" name="prix" placeholder="Prix/Nuit" required>
        <input type="number" name="cap" placeholder="Capacité" value="1">
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
</div>
<div class="card">
    <table>
        <thead><tr><th>N°</th><th>Type</th><th>Prix</th><th>Statut</th></tr></thead>
        <tbody>
            <?php foreach($data as $row): ?>
            <tr><td><?= $row['numero'] ?></td><td><?= $row['type'] ?></td><td class="color-or"><?= $row['prix_nuit'] ?> F</td><td><?= $row['statut'] ?></td></tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
