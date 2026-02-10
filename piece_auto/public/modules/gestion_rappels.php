<?php
// /var/www/piece_auto/public/modules/gestion_rappels.php
$page_title = "Gestion des Rappels Clients";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

// Traitement de l'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_rappel'])) {
    $id_client = $_POST['id_client'];
    $objet = $_POST['objet_rappel'];
    $date = $_POST['date_rappel'];

    $query = "INSERT INTO RAPPELS (id_client, objet_rappel, date_rappel) VALUES (:cli, :obj, :dat)";
    $stmt = $db->prepare($query);
    if ($stmt->execute([':cli' => $id_client, ':obj' => $objet, ':dat' => $date])) {
        $message = '<div class="alert alert-success">Rappel ajouté avec succès.</div>';
    }
}

// Récupération des rappels
$query = "SELECT r.*, c.nom_client, c.prenom_client 
          FROM RAPPELS r 
          JOIN CLIENTS c ON r.id_client = c.id_client 
          ORDER BY r.date_rappel ASC";
$rappels = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Récupération des clients pour le formulaire
$clients = $db->query("SELECT id_client, nom_client, prenom_client FROM CLIENTS")->fetchAll(PDO::FETCH_ASSOC);
?>

<h1><i class="fas fa-bell"></i> Rappels Clients</h1>
<?= $message ?>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">Programmer un nouveau rappel</div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <div class="col-md-4">
                <select name="id_client" class="form-select" required>
                    <option value="">Choisir un client...</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['id_client'] ?>"><?= $c['nom_client'] ?> <?= $c['prenom_client'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" name="objet_rappel" class="form-control" placeholder="Objet (ex: Relance devis)" required>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_rappel" class="form-control" required>
            </div>
            <div class="col-md-2">
                <button type="submit" name="ajouter_rappel" class="btn btn-success w-100">Ajouter</button>
            </div>
        </form>
    </div>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Date</th>
            <th>Client</th>
            <th>Objet</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rappels as $r): ?>
        <tr>
            <td><?= date('d/m/Y', strtotime($r['date_rappel'])) ?></td>
            <td><?= htmlspecialchars($r['nom_client'] . ' ' . $r['prenom_client']) ?></td>
            <td><?= htmlspecialchars($r['objet_rappel']) ?></td>
            <td><span class="badge bg-<?= $r['statut'] == 'En attente' ? 'warning' : 'success' ?>"><?= $r['statut'] ?></span></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include '../../includes/footer.php'; ?>
