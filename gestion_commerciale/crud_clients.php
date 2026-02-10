<?php
// Fichier : crud_clients.php

session_start();
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$conn = db_connect();

// --- LOGIQUE D'AFFICHAGE (READ) ---
$clients = [];
$result = $conn->query("SELECT id_client, nom, adresse, telephone FROM clients ORDER BY nom ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
}

$conn->close();

// Message de succès/erreur après une action CUD
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// 1. INCLUSION DU HEADER
include 'header.php';
?>

<h1 class="mb-4 text-info">👥 Gestion des Clients (CRUD)</h1>
<p><a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Retour au Tableau de Bord</a></p>

<?php if ($message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<hr>

<div class="card shadow mb-5">
    <div class="card-header bg-info text-white">
        <h2 class="h5 mb-0">Ajouter un Nouveau Client</h2>
    </div>
    <div class="card-body">
        <form action="traitement_client.php" method="post">
            <input type="hidden" name="action" value="ajouter">
            
            <div class="row g-3">
                
                <div class="col-md-6">
                    <label for="nom" class="form-label">Nom du Client:</label>
                    <input type="text" class="form-control" id="nom" name="nom" required>
                </div>
                
                <div class="col-md-6">
                    <label for="telephone" class="form-label">Téléphone:</label>
                    <input type="tel" class="form-control" id="telephone" name="telephone">
                </div>
                
                <div class="col-12">
                    <label for="adresse" class="form-label">Adresse:</label>
                    <input type="text" class="form-control" id="adresse" name="adresse">
                </div>
                
            </div>
            
            <button type="submit" class="btn btn-info text-white mt-4">Ajouter Client</button>
        </form>
    </div>
</div>

<h2 class="mb-3">Liste des Clients <span class="badge bg-secondary"><?php echo count($clients); ?></span></h2>

<?php if (count($clients) > 0): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover align-middle shadow-sm">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Adresse</th>
                <th>Téléphone</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $c): ?>
            <tr>
                <td><?php echo $c['id_client']; ?></td>
                <td class="fw-bold"><?php echo htmlspecialchars($c['nom']); ?></td>
                <td><?php echo htmlspecialchars($c['adresse']); ?></td>
                <td><?php echo htmlspecialchars($c['telephone']); ?></td>
                <td class="text-center">
                    <a href="modifier_client.php?id=<?php echo $c['id_client']; ?>" class="btn btn-sm btn-outline-primary me-2">Modifier</a>
                    <a href="traitement_client.php?action=supprimer&id=<?php echo $c['id_client']; ?>" 
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer le client <?php echo htmlspecialchars($c['nom']); ?> ?');">Supprimer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <div class="alert alert-info" role="alert">
        <h4 class="alert-heading">Aucun Client!</h4>
        <p>Veuillez utiliser le formulaire ci-dessus pour enregistrer votre premier client.</p>
    </div>
<?php endif; ?>

<?php
// 2. INCLUSION DU FOOTER
include 'footer.php';
?>
