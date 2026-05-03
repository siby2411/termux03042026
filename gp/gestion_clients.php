<?php
require_once 'auth.php';
require_once 'db_connect.php';
require_once 'generer_qr.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save') {
    $id = $_POST['id'] ?? null;
    $nom = $_POST['nom'];
    $tel = $_POST['telephone'];
    $email = $_POST['email'];
    $adresse = $_POST['adresse'];
    $type = $_POST['type'];
    if ($id) {
        $pdo->prepare("UPDATE clients SET nom=?, telephone=?, email=?, adresse=?, type=? WHERE id=?")->execute([$nom, $tel, $email, $adresse, $type, $id]);
    } else {
        $pdo->prepare("INSERT INTO clients (nom, telephone, email, adresse, type) VALUES (?,?,?,?,?)")->execute([$nom, $tel, $email, $adresse, $type]);
        $id = $pdo->lastInsertId();
        $code = 'CLT-' . str_pad($id, 5, '0', STR_PAD_LEFT);
        $pdo->prepare("UPDATE clients SET code_client = ? WHERE id = ?")->execute([$code, $id]);
        generer_qr_client($id, $tel);
    }
    echo "<div style='background:#d4edda; padding:10px; border-radius:8px; margin:10px 0;'>Client enregistré avec succès !</div>";
}
$clients = $pdo->query("SELECT * FROM clients ORDER BY nom")->fetchAll();
include('header.php');
?>
<h2><i class="fas fa-user-plus"></i> Ajouter un client</h2>
<form method="post" class="card" style="background:#f9f9f9; padding:25px; border-radius:20px; margin-bottom:30px;">
    <input type="hidden" name="id">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
        <input type="text" name="nom" placeholder="Nom complet" required>
        <input type="tel" name="telephone" placeholder="+33..." required>
        <input type="email" name="email" placeholder="Email">
        <select name="type">
            <option value="expediteur">Expéditeur</option>
            <option value="destinataire">Destinataire</option>
            <option value="both">Les deux</option>
        </select>
    </div>
    <textarea name="adresse" placeholder="Adresse complète" rows="2" style="width:100%; margin-top:10px;"></textarea>
    <button type="submit" name="action" value="save" class="btn" style="margin-top:15px;"><i class="fas fa-save"></i> Enregistrer le client</button>
</form>

<h3><i class="fas fa-list"></i> Liste des clients</h3>
<table class="card" style="width:100%; border-collapse:collapse;">
    <tr style="background:#ff8c00; color:white;"><th>Code</th><th>Nom</th><th>Téléphone</th><th>Type</th><th>QR</th><th>Action</th></tr>
    <?php foreach ($clients as $c): ?>
    <tr>
        <td><?= htmlspecialchars($c['code_client'] ?? '') ?></td>
        <td><?= htmlspecialchars($c['nom']) ?></td>
        <td><?= $c['telephone'] ?></td>
        <td><?= $c['type'] ?></td>
        <td><?php if(file_exists("qrcodes/client_{$c['id']}.png")) echo '<img src="qrcodes/client_'.$c['id'].'.png" width="40">'; else echo '-'; ?></td>
        <td><a href="gestion_clients.php?edit=<?= $c['id'] ?>">Modifier</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include('footer.php'); ?>
