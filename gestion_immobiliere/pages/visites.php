<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sql = "INSERT INTO rendez_vous (prospect_id, immeuble_id, date_heure, notes) VALUES (?, ?, ?, ?)";
    $pdo->prepare($sql)->execute([$_POST['p_id'], $_POST['i_id'], $_POST['date'], $_POST['notes']]);
}

$prospects = $pdo->query("SELECT * FROM prospects")->fetchAll();
$immeubles = $pdo->query("SELECT * FROM immeubles")->fetchAll();
?>

<div class="card">
    <div class="card-title">📅 Programmer une Visite</div>
    <form method="POST" class="form-grid">
        <select name="p_id" required>
            <option>Choisir un prospect...</option>
            <?php foreach($prospects as $p): ?>
                <option value="<?= $p['id'] ?>"><?= $p['nom'] ?></option>
            <?php endforeach; ?>
        </select>
        <select name="i_id" required>
            <option>Choisir l'immeuble...</option>
            <?php foreach($immeubles as $i): ?>
                <option value="<?= $i['id'] ?>"><?= $i['titre'] ?></option>
            <?php endforeach; ?>
        </select>
        <input type="datetime-local" name="date" required>
        <textarea name="notes" placeholder="Détails (ex: Visite de la terrasse)" class="form-full"></textarea>
        <button type="submit" class="btn btn-primary">Valider le RDV</button>
    </form>
</div>
