<?php
$page_title = "Gestion des Clients";
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php'; 

$database = new Database();
$db = $database->getConnection();

$query = "SELECT ClientID, Nom, Contact, Telephone, Ville FROM Clients ORDER BY Nom ASC";
try {
    $stmt = $db->query($query);
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erreur SQL: Impossible de charger les clients. " . $e->getMessage() . "</div>";
    $clients = [];
}
?>

<h1 class="mt-4 text-center"><i class="fas fa-users me-2"></i> Liste des Clients</h1>
<p class="text-muted text-center">Gestion et coordonnées de vos partenaires commerciaux (Clients).</p>
<hr>

<div class="row justify-content-center">
    <div class="col-lg-12">
        <div class="d-flex justify-content-end mb-3">
            <a href="creer.php" class="btn btn-success"><i class="fas fa-plus me-2"></i> Ajouter un Client</a>
        </div>

        <div class="card shadow-lg mb-4 border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-custom mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 25%;">Nom du Client</th>
                                <th style="width: 20%;">Contact Principal</th>
                                <th style="width: 15%;">Téléphone</th>
                                <th style="width: 20%;">Ville</th>
                                <th style="width: 15%;" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($clients)): ?>
                            <?php foreach ($clients as $client): ?>
                            <tr>
                                <td><?= htmlspecialchars($client['ClientID']) ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($client['Nom']) ?></td>
                                <td><?= htmlspecialchars($client['Contact']) ?></td>
                                <td><?= htmlspecialchars($client['Telephone']) ?></td>
                                <td><?= htmlspecialchars($client['Ville']) ?></td>
                                <td class="text-center">
                                    <a href="modifier.php?id=<?= $client['ClientID'] ?>" class="btn btn-sm btn-outline-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="supprimer.php?id=<?= $client['ClientID'] ?>" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted p-4">Aucun client enregistré.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
