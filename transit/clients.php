<?php
require_once 'config.php';
require_once 'header.php';

// Générer code client unique
function generateClientCode() {
    global $pdo;
    $prefix = 'CLT' . date('Ym');
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM clients WHERE code_client LIKE '$prefix%'");
    $count = $stmt->fetch()['count'] + 1;
    return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
}

// Traitement CRUD
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                $code_client = generateClientCode();
                $sql = "INSERT INTO clients (code_client, raison_sociale, ninea, registre_commerce, 
                        adresse, telephone, email, contact_personne, type_client) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $code_client, $_POST['raison_sociale'], $_POST['ninea'], 
                    $_POST['registre_commerce'], $_POST['adresse'], $_POST['telephone'],
                    $_POST['email'], $_POST['contact_personne'], $_POST['type_client']
                ]);
                echo '<div class="alert alert-success">Client ajouté avec succès! Code: ' . $code_client . '</div>';
                break;
                
            case 'delete':
                $sql = "DELETE FROM clients WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST['id']]);
                echo '<div class="alert alert-success">Client supprimé!</div>';
                break;
        }
    }
}

// Récupérer tous les clients
$clients = $pdo->query("SELECT * FROM clients ORDER BY date_inscription DESC")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestion des Clients</h1>
            <button class="btn btn-omega" data-bs-toggle="modal" data-bs-target="#createClientModal">
                <i class="fas fa-user-plus me-2"></i>Nouveau Client
            </button>
        </div>
        
        <!-- Liste des clients -->
        <div class="card-modern p-4">
            <div class="table-responsive">
                <table class="table table-hover" id="clientsTable">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Raison Sociale</th>
                            <th>NINEA</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($clients as $client): ?>
                        <tr>
                            <td><strong><?php echo $client['code_client']; ?></strong></td>
                            <td><?php echo htmlspecialchars($client['raison_sociale']); ?></td>
                            <td><?php echo $client['ninea']; ?></td>
                            <td><?php echo $client['telephone']; ?></td>
                            <td><?php echo $client['email']; ?></td>
                            <td>
                                <span class="badge bg-info">
                                    <?php echo ucfirst($client['type_client']); ?>
                                </span>
                             </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editClient(<?php echo $client['id']; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteClient(<?php echo $client['id']; ?>)">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn btn-sm btn-success" onclick="voirDeclarations(<?php echo $client['id']; ?>)">
                                    <i class="fas fa-file-alt"></i>
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

<!-- Modal Création Client -->
<div class="modal fade" id="createClientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nouveau Client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Raison Sociale *</label>
                            <input type="text" name="raison_sociale" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Type Client *</label>
                            <select name="type_client" class="form-control" required>
                                <option value="importateur">Importateur</option>
                                <option value="exportateur">Exportateur</option>
                                <option value="transitaire">Transitaire</option>
                                <option value="particulier">Particulier</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>NINEA</label>
                            <input type="text" name="ninea" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Registre Commerce</label>
                            <input type="text" name="registre_commerce" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Téléphone *</label>
                            <input type="tel" name="telephone" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Personne de contact</label>
                            <input type="text" name="contact_personne" class="form-control">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Adresse</label>
                            <textarea name="adresse" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer Client</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editClient(id) {
    // Implémentation édition
    window.location.href = 'edit_client.php?id=' + id;
}

function deleteClient(id) {
    if(confirm('Êtes-vous sûr de vouloir supprimer ce client ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}

function voirDeclarations(id) {
    window.location.href = 'declarations.php?client_id=' + id;
}
</script>

<?php require_once 'footer.php'; ?>
