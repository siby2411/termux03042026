<?php
// /var/www/piece_auto/public/modules/gestion_clients.php
// Gestion complète (CRUD) des clients.

$page_title = "Gestion des Clients";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

$action = $_GET['action'] ?? 'list';
$id_client = (int)($_GET['id'] ?? 0);
$client_a_editer = [];

// =================================================================================
// 1. GESTION DES ACTIONS (AJOUTER/MODIFIER/SUPPRIMER)
// =================================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $adresse = trim($_POST['adresse']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);

    if (empty($nom) || empty($email)) {
        $message = '<div class="alert alert-danger">Le Nom et l\'Email sont obligatoires.</div>';
    } else {
        if (isset($_POST['add'])) {
            // --- C: CREATE (Ajouter) ---
            $query = "INSERT INTO CLIENTS (nom, prenom, adresse, telephone, email) 
                      VALUES (:nom, :prenom, :adresse, :telephone, :email)";
            $stmt = $db->prepare($query);
            if ($stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':adresse' => $adresse,
                ':telephone' => $telephone,
                ':email' => $email
            ])) {
                $message = '<div class="alert alert-success">Client ajouté avec succès.</div>';
                $action = 'list';
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de l\'ajout du client (L\'email est peut-être déjà utilisé).</div>';
            }
        } elseif (isset($_POST['edit']) && $id_client > 0) {
            // --- U: UPDATE (Modifier) ---
            $query = "UPDATE CLIENTS SET nom = :nom, prenom = :prenom, adresse = :adresse, 
                      telephone = :telephone, email = :email 
                      WHERE id_client = :id_client";
            $stmt = $db->prepare($query);
            if ($stmt->execute([
                ':nom' => $nom,
                ':prenom' => $prenom,
                ':adresse' => $adresse,
                ':telephone' => $telephone,
                ':email' => $email,
                ':id_client' => $id_client
            ])) {
                $message = '<div class="alert alert-success">Client mis à jour avec succès.</div>';
                $action = 'list';
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de la mise à jour du client (L\'email est peut-être déjà utilisé).</div>';
            }
        }
    }
} elseif ($action === 'delete' && $id_client > 0) {
    // --- D: DELETE (Supprimer) ---
    try {
        // Optionnel: Vérifier si le client a des commandes avant de supprimer
        $query_check = "SELECT COUNT(*) FROM COMMANDE_VENTE WHERE id_client = :id_client";
        $stmt_check = $db->prepare($query_check);
        $stmt_check->execute([':id_client' => $id_client]);
        $commandes_count = $stmt_check->fetchColumn();

        if ($commandes_count > 0) {
            $message = '<div class="alert alert-warning">Impossible de supprimer ce client ('.$commandes_count.' commandes lui sont liées). Supprimez d\'abord les commandes.</div>';
            $action = 'list';
        } else {
            $query = "DELETE FROM CLIENTS WHERE id_client = :id_client";
            $stmt = $db->prepare($query);
            if ($stmt->execute([':id_client' => $id_client])) {
                $message = '<div class="alert alert-success">Client supprimé avec succès.</div>';
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de la suppression du client.</div>';
            }
            $action = 'list';
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la suppression : ' . $e->getMessage() . '</div>';
        $action = 'list';
    }
} elseif ($action === 'edit' && $id_client > 0) {
    // --- R: READ (Lecture pour le formulaire d'édition) ---
    $query = "SELECT * FROM CLIENTS WHERE id_client = :id_client";
    $stmt = $db->prepare($query);
    $stmt->execute([':id_client' => $id_client]);
    $client_a_editer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$client_a_editer) {
        $message = '<div class="alert alert-warning">Client non trouvé.</div>';
        $action = 'list';
    }
}

// =================================================================================
// 2. AFFICHAGE DES VUES
// =================================================================================
?>

<h1><i class="fas fa-user-friends"></i> <?= $page_title ?></h1>
<p class="lead">Gestion complète (CRUD) de la base de données clients.</p>
<hr>

<?= $message ?>

<?php if ($action === 'list'): ?>
    
    <a href="?action=add" class="btn btn-primary mb-4">
        <i class="fas fa-plus"></i> Ajouter un Nouveau Client
    </a>

    <h3>Liste des Clients</h3>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom & Prénom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Adresse</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // --- R: READ (Liste) ---
                $query = "SELECT id_client, nom, prenom, email, telephone, adresse FROM CLIENTS ORDER BY nom ASC";
                $stmt = $db->query($query);
                $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($clients) {
                    foreach ($clients as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['id_client']) ?></td>
                        <td><?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td><?= htmlspecialchars($c['telephone']) ?></td>
                        <td><?= htmlspecialchars(substr($c['adresse'], 0, 50)) . '...' ?></td>
                        <td class="text-center">
                            <a href="?action=edit&id=<?= $c['id_client'] ?>" class="btn btn-sm btn-info me-1" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?action=delete&id=<?= $c['id_client'] ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce client ? Si des commandes lui sont liées, la suppression échouera.');">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach;
                } else {
                    echo '<tr><td colspan="6" class="text-center">Aucun client trouvé.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>

    <?php 
    $is_edit = ($action === 'edit');
    $form_title = $is_edit ? "Modifier le Client (ID: " . $id_client . ")" : "Ajouter un Nouveau Client";
    $submit_name = $is_edit ? 'edit' : 'add';
    $c = $client_a_editer;
    ?>
    
    <h3><?= $form_title ?></h3>
    <form method="POST" action="?action=<?= $action ?><?= $is_edit ? '&id=' . $id_client : '' ?>">
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="nom" class="form-label">Nom</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($c['nom'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="prenom" class="form-label">Prénom</label>
                <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($c['prenom'] ?? '') ?>">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($c['email'] ?? '') ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="telephone" class="form-label">Téléphone</label>
                <input type="text" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($c['telephone'] ?? '') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="adresse" class="form-label">Adresse Complète</label>
            <textarea class="form-control" id="adresse" name="adresse" rows="3"><?= htmlspecialchars($c['adresse'] ?? '') ?></textarea>
        </div>

        <button type="submit" name="<?= $submit_name ?>" class="btn btn-success">
            <i class="fas fa-save"></i> <?= $is_edit ? 'Enregistrer les Modifications' : 'Ajouter le Client' ?>
        </button>
        <a href="?action=list" class="btn btn-secondary">
            <i class="fas fa-times"></i> Annuler
        </a>
    </form>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
