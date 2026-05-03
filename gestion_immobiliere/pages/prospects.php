<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sql = "INSERT INTO prospects (nom, email, telephone, source) VALUES (?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$_POST['nom'], $_POST['email'], $_POST['tel'], $_POST['source']]);
}
$prospects = $pdo->query("SELECT * FROM prospects ORDER BY date_inscription DESC")->fetchAll();
?>
<div class="card">
    <div class="card-title">👤 Nouveau Prospect</div>
    <form method="POST" class="form-grid">
        <input type="text" name="nom" placeholder="Nom complet" required>
        <input type="email" name="email" placeholder="Email">
        <input type="text" name="tel" placeholder="Téléphone">
        <select name="source">
            <option>Facebook</option>
            <option>Site Web</option>
            <option>Recommandation</option>
            <option>WhatsApp</option>
            <option>Autre</option>
        </select>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
</div>
<div class="card">
    <table>
        <thead><tr><th>Nom</th><th>Source</th><th>Tel</th><th>Inscription</th></tr></thead>
        <tbody>
            <?php foreach($prospects as $p): ?>
            <tr>
                <td><b><?= $p['nom'] ?></b></td>
                <td><span class="badge" style="background:#eee;"><?= $p['source'] ?></span></td>
                <td><?= $p['telephone'] ?></td>
                <td><?= date('d/m/Y', strtotime($p['date_inscription'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
