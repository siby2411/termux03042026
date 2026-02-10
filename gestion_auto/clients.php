<?php include 'config.php'; include 'header.php'; 
$db = Database::getInstance();
$conn = $db->getConnection();

// Ajouter un client
if($_POST && isset($_POST['ajouter_client'])) {
    try {
        $stmt = $conn->prepare("INSERT INTO clients (type_client, nom, prenom, societe, telephone, email, adresse, permis, date_naissance, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['type_client'],
            $_POST['nom'],
            $_POST['prenom'],
            $_POST['societe'],
            $_POST['telephone'],
            $_POST['email'],
            $_POST['adresse'],
            $_POST['permis'],
            $_POST['date_naissance'],
            $_POST['notes']
        ]);
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>Client ajouté avec succès!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    } catch(Exception $e) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>Erreur: ' . $e->getMessage() . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    }
}

// Récupérer les clients
$query = $conn->query("SELECT * FROM clients ORDER BY nom, prenom");
$clients = $query->fetchAll();

// Statistiques
$total_clients = count($clients);
$clients_particuliers = count(array_filter($clients, function($c) { return $c['type_client'] == 'particulier'; }));
$clients_pro = count(array_filter($clients, function($c) { return $c['type_client'] == 'professionnel'; }));
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
                <i class="bi bi-people text-primary me-2"></i>Gestion des Clients
            </h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ajouterClientModal">
                <i class="bi bi-person-plus me-1"></i>Nouveau Client
            </button>
        </div>
        <p class="text-muted"><?= $total_clients ?> clients enregistrés</p>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= $total_clients ?></h4>
                        <p class="card-text">Total Clients</p>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= $clients_particuliers ?></h4>
                        <p class="card-text">Particuliers</p>
                    </div>
                    <i class="bi bi-person fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= $clients_pro ?></h4>
                        <p class="card-text">Professionnels</p>
                    </div>
                    <i class="bi bi-building fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= count(array_filter($clients, function($c) { return !empty($c['email']); })) ?></h4>
                        <p class="card-text">Avec Email</p>
                    </div>
                    <i class="bi bi-envelope fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recherche -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-8">
                <input type="text" class="form-control" placeholder="🔍 Rechercher un client..." id="searchInput">
            </div>
            <div class="col-md-2">
                <select class="form-select" id="typeFilter">
                    <option value="">Tous types</option>
                    <option value="particulier">Particulier</option>
                    <option value="professionnel">Professionnel</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100" onclick="appliquerFiltres()">
                    <i class="bi bi-funnel me-1"></i>Filtrer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Liste des Clients -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Liste des Clients</h5>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-success" onclick="exporterClients()">
                <i class="bi bi-download me-1"></i>Exporter
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Nom</th>
                        <th>Contact</th>
                        <th>Adresse</th>
                        <th>Permis</th>
                        <th>Date Inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clients as $client): ?>
                    <tr class="client-item" 
                        data-type="<?= $client['type_client'] ?>"
                        data-nom="<?= htmlspecialchars(strtolower($client['prenom'] . ' ' . $client['nom'] . ' ' . $client['societe'])) ?>">
                        <td>
                            <span class="badge bg-<?= $client['type_client'] == 'particulier' ? 'success' : 'warning' ?>">
                                <?= ucfirst($client['type_client']) ?>
                            </span>
                        </td>
                        <td>
                            <strong><?= $client['prenom'] ?> <?= $client['nom'] ?></strong>
                            <?php if($client['societe']): ?>
                                <br><small class="text-muted"><?= $client['societe'] ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($client['telephone']): ?>
                                <div><i class="bi bi-telephone me-1"></i><?= $client['telephone'] ?></div>
                            <?php endif; ?>
                            <?php if($client['email']): ?>
                                <div><i class="bi bi-envelope me-1"></i><?= $client['email'] ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($client['adresse']): ?>
                                <small><?= substr($client['adresse'], 0, 30) ?><?= strlen($client['adresse']) > 30 ? '...' : '' ?></small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($client['permis']): ?>
                                <span class="badge bg-info"><?= $client['permis'] ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= date('d/m/Y', strtotime($client['date_creation'])) ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-outline-info" title="Voir historique">
                                    <i class="bi bi-clock-history"></i>
                                </button>
                                <button class="btn btn-outline-success" title="Nouvelle transaction">
                                    <i class="bi bi-plus-circle"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajouter Client -->
<div class="modal fade" id="ajouterClientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouveau Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Type de client *</label>
                            <select name="type_client" class="form-select" required id="typeClient">
                                <option value="particulier">Particulier</option>
                                <option value="professionnel">Professionnel</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="societeField" style="display: none;">
                            <label class="form-label">Société</label>
                            <input type="text" name="societe" class="form-control" placeholder="Nom de l'entreprise">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nom *</label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Prénom *</label>
                            <input type="text" name="prenom" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="tel" name="telephone" class="form-control" placeholder="06 12 34 56 78">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="client@email.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Adresse</label>
                            <textarea name="adresse" class="form-control" rows="2" placeholder="Adresse complète..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Numéro de permis</label>
                            <input type="text" name="permis" class="form-control" placeholder="123456789012">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date de naissance</label>
                            <input type="date" name="date_naissance" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Informations supplémentaires..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="ajouter_client" class="btn btn-primary">Ajouter le client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Gestion du type de client
document.getElementById('typeClient').addEventListener('change', function() {
    const societeField = document.getElementById('societeField');
    societeField.style.display = this.value === 'professionnel' ? 'block' : 'none';
});

// Filtrage des clients
function appliquerFiltres() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const type = document.getElementById('typeFilter').value;
    
    const items = document.querySelectorAll('.client-item');
    
    items.forEach(item => {
        const nom = item.getAttribute('data-nom');
        const itemType = item.getAttribute('data-type');
        
        const matchSearch = nom.includes(searchTerm);
        const matchType = !type || itemType === type;
        
        item.style.display = (matchSearch && matchType) ? 'table-row' : 'none';
    });
}

// Événements
document.getElementById('searchInput').addEventListener('input', appliquerFiltres);
document.getElementById('typeFilter').addEventListener('change', appliquerFiltres);

function exporterClients() {
    alert('Fonction d\'export à implémenter');
}
</script>

<?php include 'footer.php'; ?>
