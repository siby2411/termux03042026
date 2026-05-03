<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit;
}

$page_title = 'Liste des clients';

// Récupérer les clients
$clients = $pdo->query("SELECT * FROM clients ORDER BY nom, prenom")->fetchAll();

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-users"></i> Gestion des clients</h4>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <a href="ajouter.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter un client
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped" id="table_clients">
                        <thead>
                             <tr>
                                <th>ID</th>
                                <th>Nom complet</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Points fidélité</th>
                                <th>Date inscription</th>
                                <th>Statut</th>
                                <th>Actions</th>
                             </tr>
                        </thead>
                        <tbody>
                            <?php foreach($clients as $client): ?>
                             <tr>
                                <td><?php echo $client['id']; ?></td>
                                <td><?php echo htmlspecialchars($client['prenom'] . ' ' . $client['nom']); ?></td>
                                <td><?php echo htmlspecialchars($client['email']); ?></td>
                                <td><?php echo $client['telephone']; ?></td>
                                <td>
                                    <span class="badge bg-warning"><?php echo $client['points_fidelite']; ?> pts</span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($client['date_inscription'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $client['statut'] == 'actif' ? 'success' : 'danger'; ?>">
                                        <?php echo $client['statut']; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="modifier.php?id=<?php echo $client['id']; ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" onclick="supprimerClient(<?php echo $client['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                             </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function supprimerClient(id) {
    if(confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
        window.location.href = 'supprimer.php?id=' + id;
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
