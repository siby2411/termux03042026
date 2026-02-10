<?php
// /var/www/piece_auto/public/modules/gestion_fournisseurs.php
// Gestion complète (CRUD) des fournisseurs de pièces automobiles.

$page_title = "Gestion des Fournisseurs";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

$action = $_GET['action'] ?? 'list';
$id_fournisseur = (int)($_GET['id'] ?? 0);
$fournisseur_a_editer = [];

// =================================================================================
// 1. GESTION DES ACTIONS (AJOUTER/MODIFIER/SUPPRIMER)
// =================================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_fournisseur = trim($_POST['nom_fournisseur']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);
    $devise_code = trim($_POST['devise_code']);

    if (empty($nom_fournisseur)) {
        $message = '<div class="alert alert-danger">Le nom du fournisseur est obligatoire.</div>';
    } else {
        if (isset($_POST['add'])) {
            // --- C: CREATE (Ajouter) ---
            $query = "INSERT INTO FOURNISSEURS (nom_fournisseur, telephone, email, adresse, devise_code) 
                      VALUES (:nom_fournisseur, :telephone, :email, :adresse, :devise_code)";
            $stmt = $db->prepare($query);
            if ($stmt->execute([
                ':nom_fournisseur' => $nom_fournisseur,
                ':telephone' => $telephone,
                ':email' => $email,
                ':adresse' => $adresse,
                ':devise_code' => $devise_code
            ])) {
                $message = '<div class="alert alert-success">Fournisseur ajouté avec succès.</div>';
                $action = 'list';
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de l\'ajout du fournisseur.</div>';
            }
        } elseif (isset($_POST['edit']) && $id_fournisseur > 0) {
            // --- U: UPDATE (Modifier) ---
            $query = "UPDATE FOURNISSEURS SET nom_fournisseur = :nom_fournisseur, telephone = :telephone, 
                      email = :email, adresse = :adresse, devise_code = :devise_code 
                      WHERE id_fournisseur = :id_fournisseur";
            $stmt = $db->prepare($query);
            if ($stmt->execute([
                ':nom_fournisseur' => $nom_fournisseur,
                ':telephone' => $telephone,
                ':email' => $email,
                ':adresse' => $adresse,
                ':devise_code' => $devise_code,
                ':id_fournisseur' => $id_fournisseur
            ])) {
                $message = '<div class="alert alert-success">Fournisseur mis à jour avec succès.</div>';
                $action = 'list';
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de la mise à jour du fournisseur.</div>';
            }
        }
    }
} elseif ($action === 'delete' && $id_fournisseur > 0) {
    // --- D: DELETE (Supprimer) ---
    try {
        // Optionnel: Vérifier si des pièces sont liées à ce fournisseur avant de supprimer
        $query_check = "SELECT COUNT(*) FROM PIECES WHERE id_fournisseur = :id_fournisseur";
        $stmt_check = $db->prepare($query_check);
        $stmt_check->execute([':id_fournisseur' => $id_fournisseur]);
        $pieces_count = $stmt_check->fetchColumn();

        if ($pieces_count > 0) {
            $message = '<div class="alert alert-warning">Impossible de supprimer ce fournisseur ('.$pieces_count.' pièces lui sont toujours liées). Veuillez réassigner ces pièces ou les supprimer d\'abord.</div>';
            $action = 'list';
        } else {
            $query = "DELETE FROM FOURNISSEURS WHERE id_fournisseur = :id_fournisseur";
            $stmt = $db->prepare($query);
            if ($stmt->execute([':id_fournisseur' => $id_fournisseur])) {
                $message = '<div class="alert alert-success">Fournisseur supprimé avec succès.</div>';
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de la suppression du fournisseur.</div>';
            }
            $action = 'list';
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Erreur lors de la suppression : ' . $e->getMessage() . '</div>';
        $action = 'list';
    }
} elseif ($action === 'edit' && $id_fournisseur > 0) {
    // --- R: READ (Lecture pour le formulaire d'édition) ---
    $query = "SELECT * FROM FOURNISSEURS WHERE id_fournisseur = :id_fournisseur";
    $stmt = $db->prepare($query);
    $stmt->execute([':id_fournisseur' => $id_fournisseur]);
    $fournisseur_a_editer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$fournisseur_a_editer) {
        $message = '<div class="alert alert-warning">Fournisseur non trouvé.</div>';
        $action = 'list';
    }
}

// =================================================================================
// 2. AFFICHAGE DES VUES
// =================================================================================
?>

<h1><i class="fas fa-truck-loading"></i> <?= $page_title ?></h1>
<p class="lead">Gestion complète (CRUD) des fournisseurs de pièces automobiles.</p>
<hr>

<?= $message ?>

<?php if ($action === 'list'): ?>
    
    <a href="?action=add" class="btn btn-primary mb-4">
        <i class="fas fa-plus"></i> Ajouter un Nouveau Fournisseur
    </a>

    <h3>Liste des Fournisseurs</h3>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Adresse</th>
                    <th>Devise</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // --- R: READ (Liste) ---
                $query = "SELECT id_fournisseur, nom_fournisseur, telephone, email, adresse, devise_code FROM FOURNISSEURS ORDER BY id_fournisseur DESC";
                $stmt = $db->query($query);
                $fournisseurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($fournisseurs) {
                    foreach ($fournisseurs as $f): ?>
                    <tr>
                        <td><?= htmlspecialchars($f['id_fournisseur']) ?></td>
                        <td><?= htmlspecialchars($f['nom_fournisseur']) ?></td>
                        <td><?= htmlspecialchars($f['telephone']) ?></td>
                        <td><?= htmlspecialchars($f['email']) ?></td>
                        <td><?= htmlspecialchars(substr($f['adresse'], 0, 50)) . '...' ?></td>
                        <td><?= htmlspecialchars($f['devise_code']) ?></td>
                        <td class="text-center">
                            <a href="?action=edit&id=<?= $f['id_fournisseur'] ?>" class="btn btn-sm btn-info me-1" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?action=delete&id=<?= $f['id_fournisseur'] ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce fournisseur ? Cette action est irréversible.');">
                                <i class="fas fa-trash-alt"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach;
                } else {
                    echo '<tr><td colspan="7" class="text-center">Aucun fournisseur trouvé.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>

    <?php 
    $is_edit = ($action === 'edit');
    $form_title = $is_edit ? "Modifier le Fournisseur (ID: " . $id_fournisseur . ")" : "Ajouter un Nouveau Fournisseur";
    $submit_name = $is_edit ? 'edit' : 'add';
    $f = $fournisseur_a_editer;
    ?>
    
    <h3><?= $form_title ?></h3>
    <form method="POST" action="?action=<?= $action ?><?= $is_edit ? '&id=' . $id_fournisseur : '' ?>">
        
        <div class="mb-3">
            <label for="nom_fournisseur" class="form-label">Nom du Fournisseur</label>
            <input type="text" class="form-control" id="nom_fournisseur" name="nom_fournisseur" value="<?= htmlspecialchars($f['nom_fournisseur'] ?? '') ?>" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="telephone" class="form-label">Téléphone</label>
                <input type="text" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($f['telephone'] ?? '') ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($f['email'] ?? '') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label for="adresse" class="form-label">Adresse Complète</label>
            <textarea class="form-control" id="adresse" name="adresse" rows="3"><?= htmlspecialchars($f['adresse'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label for="devise_code" class="form-label">Code Devise (ex: EUR, USD, CAD)</label>
            <input type="text" class="form-control" id="devise_code" name="devise_code" value="<?= htmlspecialchars($f['devise_code'] ?? 'EUR') ?>" maxlength="3">
        </div>

        <button type="submit" name="<?= $submit_name ?>" class="btn btn-success">
            <i class="fas fa-save"></i> <?= $is_edit ? 'Enregistrer les Modifications' : 'Ajouter le Fournisseur' ?>
        </button>
        <a href="?action=list" class="btn btn-secondary">
            <i class="fas fa-times"></i> Annuler
        </a>
    </form>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
