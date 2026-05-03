<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');

// Récupérer la liste des clients pour les listes déroulantes
$expediteurs = $pdo->query("SELECT id, nom, telephone, code_client FROM clients WHERE type IN ('expediteur','both') ORDER BY nom")->fetchAll();
$destinataires = $pdo->query("SELECT id, nom, telephone FROM clients WHERE type IN ('destinataire','both') ORDER BY nom")->fetchAll();
$vols = $pdo->query("SELECT id, numero_vol, depart_ville, arrivee_ville, date_depart FROM vols WHERE statut = 'planifie' ORDER BY date_depart")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expediteur_id = $_POST['expediteur_id'];
    $destinataire_id = $_POST['destinataire_id'];
    $vol_id = $_POST['vol_id'] ?: null;
    $description = $_POST['description'];
    $poids = $_POST['poids_kg'];
    $statut = 'enregistre';

    $stmt = $pdo->prepare("INSERT INTO colis (client_expediteur_id, client_destinataire_id, vol_id, description, poids_kg, statut) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$expediteur_id, $destinataire_id, $vol_id, $description, $poids, $statut]);
    $colis_id = $pdo->lastInsertId();

    // Récupérer le numéro généré automatiquement par le trigger
    $num = $pdo->query("SELECT numero_suivi FROM colis WHERE id = $colis_id")->fetchColumn();
    echo "<div class='alert alert-success'>Colis créé ! N° de suivi : <strong>$num</strong></div>";
}
?>
<div class="card" style="max-width: 600px; margin: auto; padding: 20px;">
    <h2><i class="fas fa-plus-circle"></i> Créer un nouveau colis</h2>
    <form method="post">
        <label>Expéditeur :</label>
        <select name="expediteur_id" required class="form-control">
            <option value="">Choisir</option>
            <?php foreach ($expediteurs as $e): ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nom']) ?> (<?= $e['code_client'] ?>)</option>
            <?php endforeach; ?>
        </select>

        <label>Destinataire :</label>
        <select name="destinataire_id" required class="form-control">
            <option value="">Choisir</option>
            <?php foreach ($destinataires as $d): ?>
                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nom']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Vol (optionnel) :</label>
        <select name="vol_id" class="form-control">
            <option value="">Aucun</option>
            <?php foreach ($vols as $v): ?>
                <option value="<?= $v['id'] ?>"><?= $v['numero_vol'] ?> (<?= $v['depart_ville'] ?> → <?= $v['arrivee_ville'] ?>)</option>
            <?php endforeach; ?>
        </select>

        <label>Description :</label>
        <textarea name="description" rows="2" class="form-control" required></textarea>

        <label>Poids (kg) :</label>
        <input type="number" step="0.1" name="poids_kg" class="form-control" required>

        <button type="submit" class="btn" style="margin-top: 15px;"><i class="fas fa-save"></i> Créer le colis</button>
    </form>
</div>
<?php include('footer.php'); ?>
