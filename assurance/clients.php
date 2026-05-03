<?php
require_once 'config/db.php';
$page_title = "Gestion des clients";
require_once 'includes/header.php';

$db = getDB();
$message = '';
$error = '';

// Traitement CRUD
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                $numero_client = 'CLT-' . date('Ymd') . '-' . rand(1000, 9999);
                $sql = "INSERT INTO clients (numero_client, type_client, nom, prenom, raison_sociale, email, telephone, adresse, ville, date_naissance, piece_identite, num_piece) 
                        VALUES (:numero_client, :type_client, :nom, :prenom, :raison_sociale, :email, :telephone, :adresse, :ville, :date_naissance, :piece_identite, :num_piece)";
                $stmt = $db->prepare($sql);
                try {
                    $stmt->execute([
                        ':numero_client' => $numero_client,
                        ':type_client' => $_POST['type_client'],
                        ':nom' => $_POST['nom'],
                        ':prenom' => $_POST['prenom'],
                        ':raison_sociale' => $_POST['raison_sociale'],
                        ':email' => $_POST['email'],
                        ':telephone' => $_POST['telephone'],
                        ':adresse' => $_POST['adresse'],
                        ':ville' => $_POST['ville'],
                        ':date_naissance' => $_POST['date_naissance'],
                        ':piece_identite' => $_POST['piece_identite'],
                        ':num_piece' => $_POST['num_piece']
                    ]);
                    $message = "Client ajouté avec succès ! Numéro: " . $numero_client;
                } catch(PDOException $e) {
                    $error = "Erreur: " . $e->getMessage();
                }
                break;
                
            case 'edit':
                $sql = "UPDATE clients SET type_client=:type_client, nom=:nom, prenom=:prenom, raison_sociale=:raison_sociale, 
                        email=:email, telephone=:telephone, adresse=:adresse, ville=:ville, date_naissance=:date_naissance,
                        piece_identite=:piece_identite, num_piece=:num_piece WHERE id=:id";
                $stmt = $db->prepare($sql);
                try {
                    $stmt->execute([
                        ':id' => $_POST['id'],
                        ':type_client' => $_POST['type_client'],
                        ':nom' => $_POST['nom'],
                        ':prenom' => $_POST['prenom'],
                        ':raison_sociale' => $_POST['raison_sociale'],
                        ':email' => $_POST['email'],
                        ':telephone' => $_POST['telephone'],
                        ':adresse' => $_POST['adresse'],
                        ':ville' => $_POST['ville'],
                        ':date_naissance' => $_POST['date_naissance'],
                        ':piece_identite' => $_POST['piece_identite'],
                        ':num_piece' => $_POST['num_piece']
                    ]);
                    $message = "Client modifié avec succès !";
                } catch(PDOException $e) {
                    $error = "Erreur: " . $e->getMessage();
                }
                break;
                
            case 'delete':
                $sql = "UPDATE clients SET statut='inactif' WHERE id=:id";
                $stmt = $db->prepare($sql);
                $stmt->execute([':id' => $_POST['id']]);
                $message = "Client désactivé avec succès !";
                break;
        }
    }
}

// Récupération des clients
$sql = "SELECT * FROM clients WHERE statut != 'inactif' ORDER BY date_creation DESC";
$clients = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2><i class="fas fa-users"></i> Gestion des clients</h2>
        </div>
        <div class="col text-end">
            <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addClientModal">
                <i class="fas fa-plus"></i> Nouveau client
            </button>
        </div>
    </div>
    
    <?php if($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5>Liste des clients</h5>
        </div>
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead>
                    <tr>
                        <th>N° Client</th>
                        <th>Type</th>
                        <th>Nom complet</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Ville</th>
                        <th>Date création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($clients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['numero_client']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $client['type_client'] == 'particulier' ? 'info' : 'success'; ?>">
                                <?php echo $client['type_client'] == 'particulier' ? 'Particulier' : 'Entreprise'; ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                            if($client['type_client'] == 'particulier') {
                                echo htmlspecialchars($client['prenom'] . ' ' . $client['nom']);
                            } else {
                                echo htmlspecialchars($client['raison_sociale']);
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($client['email']); ?></td>
                        <td><?php echo htmlspecialchars($client['telephone']); ?></td>
                        <td><?php echo htmlspecialchars($client['ville']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($client['date_creation'])); ?></td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="editClient(<?php echo $client['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger btn-delete" data-id="<?php echo $client['id']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                            <a href="contrats.php?client_id=<?php echo $client['id']; ?>" class="btn btn-sm btn-success">
                                <i class="fas fa-file-contract"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajout Client -->
<div class="modal fade" id="addClientModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Nouveau client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Type de client *</label>
                            <select name="type_client" class="form-control" required onchange="toggleClientType(this.value)">
                                <option value="particulier">Particulier</option>
                                <option value="entreprise">Entreprise</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                    </div>
                    
                    <div id="particulier_fields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Nom *</label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Prénom *</label>
                                <input type="text" name="prenom" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Date de naissance</label>
                                <input type="date" name="date_naissance" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div id="entreprise_fields" style="display:none;">
                        <div class="mb-3">
                            <label>Raison sociale *</label>
                            <input type="text" name="raison_sociale" class="form-control">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Téléphone *</label>
                            <input type="tel" name="telephone" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Ville</label>
                            <input type="text" name="ville" class="form-control">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Adresse</label>
                        <textarea name="adresse" class="form-control" rows="2"></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Pièce d'identité</label>
                            <select name="piece_identite" class="form-control">
                                <option value="">Sélectionner</option>
                                <option value="CNI">CNI</option>
                                <option value="Passeport">Passeport</option>
                                <option value="Permis">Permis de conduire</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>N° Pièce</label>
                            <input type="text" name="num_piece" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-gradient">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleClientType(type) {
    if(type == 'particulier') {
        document.getElementById('particulier_fields').style.display = 'block';
        document.getElementById('entreprise_fields').style.display = 'none';
        document.querySelector('input[name="nom"]').required = true;
        document.querySelector('input[name="prenom"]').required = true;
        document.querySelector('input[name="raison_sociale"]').required = false;
    } else {
        document.getElementById('particulier_fields').style.display = 'none';
        document.getElementById('entreprise_fields').style.display = 'block';
        document.querySelector('input[name="nom"]').required = false;
        document.querySelector('input[name="prenom"]').required = false;
        document.querySelector('input[name="raison_sociale"]').required = true;
    }
}

function editClient(id) {
    // Charge les données du client et ouvre modal d'édition
    window.location.href = 'edit_client.php?id=' + id;
}
</script>

<?php require_once 'includes/footer.php'; ?>
