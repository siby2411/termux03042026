<?php
// /var/www/piece_auto/public/modules/gestion_utilisateurs.php
// Gestion complète (CRUD) des utilisateurs et des rôles.

$page_title = "Gestion des Utilisateurs";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

// Vérification de la permission : SEUL l'Admin peut accéder à cette page
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    header('Location: /piece_auto/public/index.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();
$message = '';

$action = $_GET['action'] ?? 'list';
$id_utilisateur = (int)($_GET['id'] ?? 0);
$utilisateur_a_editer = [];

// Définition des rôles disponibles basés sur la structure de la table
$ROLES_DISPONIBLES = ['Admin', 'Stockeur', 'Vendeur', 'Analyse'];

// =================================================================================
// 1. GESTION DES ACTIONS (AJOUTER/MODIFIER/SUPPRIMER)
// =================================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_utilisateur = trim($_POST['nom_utilisateur'] ?? '');
    $role = $_POST['role'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';
    $password_changed = false;

    if (!in_array($role, $ROLES_DISPONIBLES)) {
        $message = '<div class="alert alert-danger">Rôle invalide sélectionné.</div>';
    } elseif (empty($nom_utilisateur) || empty($role)) {
        $message = '<div class="alert alert-danger">Le Nom d\'utilisateur et le Rôle sont obligatoires.</div>';
    } else {
        
        if (isset($_POST['add']) || (isset($_POST['edit']) && !empty($mot_de_passe))) {
            if ($mot_de_passe !== $confirmer_mot_de_passe) {
                $message = '<div class="alert alert-danger">Les mots de passe ne correspondent pas.</div>';
            } elseif (strlen($mot_de_passe) < 6) {
                $message = '<div class="alert alert-danger">Le mot de passe doit contenir au moins 6 caractères.</div>';
            } else {
                $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);
                $password_changed = true;
            }
        }
        
        if (empty($message)) { // Si pas d'erreurs de validation
            
            if (isset($_POST['add'])) {
                // --- C: CREATE (Ajouter) ---
                if (!$password_changed) {
                     $message = '<div class="alert alert-danger">Le mot de passe est obligatoire pour la création.</div>';
                } else {
                    $query = "INSERT INTO UTILISATEURS (nom_utilisateur, mot_de_passe, role) 
                              VALUES (:nom_utilisateur, :mot_de_passe, :role)";
                    $stmt = $db->prepare($query);
                    try {
                        if ($stmt->execute([
                            ':nom_utilisateur' => $nom_utilisateur,
                            ':mot_de_passe' => $mot_de_passe_hache,
                            ':role' => $role
                        ])) {
                            $message = '<div class="alert alert-success">Utilisateur créé avec succès.</div>';
                            $action = 'list';
                        }
                    } catch (PDOException $e) {
                        $message = '<div class="alert alert-danger">Erreur : Le nom d\'utilisateur existe déjà.</div>';
                    }
                }
            } elseif (isset($_POST['edit']) && $id_utilisateur > 0) {
                // --- U: UPDATE (Modifier) ---
                $query = "UPDATE UTILISATEURS SET nom_utilisateur = :nom_utilisateur, role = :role";
                $params = [
                    ':nom_utilisateur' => $nom_utilisateur,
                    ':role' => $role,
                    ':id_utilisateur' => $id_utilisateur
                ];

                if ($password_changed) {
                    $query .= ", mot_de_passe = :mot_de_passe";
                    $params[':mot_de_passe'] = $mot_de_passe_hache;
                }

                $query .= " WHERE id_utilisateur = :id_utilisateur";
                $stmt = $db->prepare($query);
                
                try {
                    if ($stmt->execute($params)) {
                        $message = '<div class="alert alert-success">Utilisateur mis à jour avec succès.' . ($password_changed ? ' (Mot de passe modifié)' : '') . '</div>';
                        $action = 'list';
                    } else {
                        $message = '<div class="alert alert-danger">Erreur lors de la mise à jour de l\'utilisateur.</div>';
                    }
                } catch (PDOException $e) {
                    $message = '<div class="alert alert-danger">Erreur : Le nom d\'utilisateur existe déjà.</div>';
                }
            }
        }
    }
} elseif ($action === 'delete' && $id_utilisateur > 0) {
    // --- D: DELETE (Supprimer) ---
    if ($id_utilisateur === $_SESSION['user_id']) {
         $message = '<div class="alert alert-danger">Vous ne pouvez pas supprimer votre propre compte pendant que vous êtes connecté.</div>';
         $action = 'list';
    } else {
        $query = "DELETE FROM UTILISATEURS WHERE id_utilisateur = :id_utilisateur";
        $stmt = $db->prepare($query);
        if ($stmt->execute([':id_utilisateur' => $id_utilisateur])) {
            $message = '<div class="alert alert-success">Utilisateur supprimé avec succès.</div>';
        } else {
            $message = '<div class="alert alert-danger">Erreur lors de la suppression de l\'utilisateur.</div>';
        }
        $action = 'list';
    }
} elseif ($action === 'edit' && $id_utilisateur > 0) {
    // --- R: READ (Lecture pour le formulaire d'édition) ---
    $query = "SELECT id_utilisateur, nom_utilisateur, role FROM UTILISATEURS WHERE id_utilisateur = :id_utilisateur";
    $stmt = $db->prepare($query);
    $stmt->execute([':id_utilisateur' => $id_utilisateur]);
    $utilisateur_a_editer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$utilisateur_a_editer) {
        $message = '<div class="alert alert-warning">Utilisateur non trouvé.</div>';
        $action = 'list';
    }
}

// =================================================================================
// 2. AFFICHAGE DES VUES
// =================================================================================
?>

<h1><i class="fas fa-users-cog"></i> <?= $page_title ?></h1>
<p class="lead">Création, édition et gestion des droits d'accès des employés.</p>
<hr>

<?= $message ?>

<?php if ($action === 'list'): ?>
    
    <a href="?action=add" class="btn btn-primary mb-4">
        <i class="fas fa-plus"></i> Ajouter un Nouvel Utilisateur
    </a>

    <h3>Liste des Utilisateurs</h3>
    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom d'utilisateur</th>
                    <th>Rôle</th>
                    <th>Date de Création</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // --- R: READ (Liste) ---
                $query = "SELECT id_utilisateur, nom_utilisateur, role, date_creation FROM UTILISATEURS ORDER BY id_utilisateur ASC";
                $stmt = $db->query($query);
                $utilisateurs = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($utilisateurs) {
                    foreach ($utilisateurs as $u): ?>
                    <tr class="<?= $u['id_utilisateur'] === $_SESSION['user_id'] ? 'table-info' : '' ?>">
                        <td><?= htmlspecialchars($u['id_utilisateur']) ?></td>
                        <td><?= htmlspecialchars($u['nom_utilisateur']) ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($u['role']) ?></span></td>
                        <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($u['date_creation']))) ?></td>
                        <td class="text-center">
                            <a href="?action=edit&id=<?= $u['id_utilisateur'] ?>" class="btn btn-sm btn-info me-1" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($u['id_utilisateur'] !== $_SESSION['user_id']): ?>
                                <a href="?action=delete&id=<?= $u['id_utilisateur'] ?>" class="btn btn-sm btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer l\'utilisateur <?= htmlspecialchars($u['nom_utilisateur']) ?> ?');">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            <?php else: ?>
                                <button class="btn btn-sm btn-secondary" disabled title="Vous ne pouvez pas vous supprimer.">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach;
                } else {
                    echo '<tr><td colspan="5" class="text-center">Aucun utilisateur trouvé.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

<?php elseif ($action === 'add' || $action === 'edit'): ?>

    <?php 
    $is_edit = ($action === 'edit');
    $form_title = $is_edit ? "Modifier l'Utilisateur (ID: " . $id_utilisateur . ")" : "Ajouter un Nouvel Utilisateur";
    $submit_name = $is_edit ? 'edit' : 'add';
    $u = $utilisateur_a_editer;
    ?>
    
    <h3><?= $form_title ?></h3>
    <form method="POST" action="?action=<?= $action ?><?= $is_edit ? '&id=' . $id_utilisateur : '' ?>">
        
        <div class="mb-3">
            <label for="nom_utilisateur" class="form-label">Nom d'utilisateur (Login)</label>
            <input type="text" class="form-control" id="nom_utilisateur" name="nom_utilisateur" value="<?= htmlspecialchars($u['nom_utilisateur'] ?? '') ?>" required>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Rôle</label>
            <select class="form-select" id="role" name="role" required>
                <?php foreach ($ROLES_DISPONIBLES as $r): ?>
                    <option value="<?= $r ?>" <?= (isset($u['role']) && $u['role'] === $r) ? 'selected' : '' ?>>
                        <?= $r ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <hr>
        
        <h4><?= $is_edit ? 'Changer le Mot de Passe (Laissez vide pour ne pas changer)' : 'Mot de Passe Initial' ?></h4>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="mot_de_passe" class="form-label">Mot de Passe <?= !$is_edit ? '<span class="text-danger">*</span>' : '' ?></label>
                <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" <?= !$is_edit ? 'required' : '' ?>>
                <small class="form-text text-muted">Minimum 6 caractères.</small>
            </div>
            <div class="col-md-6 mb-3">
                <label for="confirmer_mot_de_passe" class="form-label">Confirmer Mot de Passe <?= !$is_edit ? '<span class="text-danger">*</span>' : '' ?></label>
                <input type="password" class="form-control" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" <?= !$is_edit ? 'required' : '' ?>>
            </div>
        </div>
        

        <button type="submit" name="<?= $submit_name ?>" class="btn btn-success mt-3">
            <i class="fas fa-save"></i> <?= $is_edit ? 'Enregistrer les Modifications' : 'Ajouter l\'Utilisateur' ?>
        </button>
        <a href="?action=list" class="btn btn-secondary mt-3">
            <i class="fas fa-times"></i> Annuler
        </a>
    </form>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>
