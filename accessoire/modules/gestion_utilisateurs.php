<?php
// /var/www/piece_auto/modules/gestion_utilisateurs.php
include_once '../config/Database.php';
include_once '../includes/auth_check.php'; 
include '../includes/header.php';

$page_title = "Gestion des Utilisateurs & Permissions";
$database = new Database();
$db = $database->getConnection();
$message_status = "";

// Redirection si l'utilisateur n'est pas Admin
if ($_SESSION['user_role'] != 'Admin') {
    echo "<div class='alert alert-danger'>Accès refusé. Seuls les administrateurs peuvent gérer les utilisateurs.</div>";
    include '../includes/footer.php';
    exit;
}

// --- LOGIQUE CRUD (Ajout/Modification/Suppression) ---

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $id_user = (int)($_POST['id_user'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'Vendeur';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    try {
        if ($action == 'add' || $action == 'edit') {
            if (empty($username) || empty($email) || ($action == 'add' && empty($password))) {
                throw new Exception("Tous les champs (Username, Email) sont requis, ainsi que le mot de passe pour la création.");
            }

            $fields = "username = :username, email = :email, role = :role, is_active = :is_active";
            $params = [
                ':username' => $username,
                ':email' => $email,
                ':role' => $role,
                ':is_active' => $is_active
            ];

            if (!empty($password)) {
                // Utilisation de PASSWORD() pour la démo
                $fields .= ", password_hash = PASSWORD(:password)";
                $params[':password'] = $password;
            }

            if ($action == 'add') {
                $query = "INSERT INTO USERS SET {$fields}";
                $stmt = $db->prepare($query);
                $stmt->execute($params);
                $message_status = "<div class='alert alert-success'>Utilisateur **{$username}** créé avec succès.</div>";
            } elseif ($action == 'edit') {
                $query = "UPDATE USERS SET {$fields} WHERE id_user = :id_user";
                $params[':id_user'] = $id_user;
                $stmt = $db->prepare($query);
                $stmt->execute($params);
                $message_status = "<div class='alert alert-success'>Utilisateur **{$username}** mis à jour.</div>";
            }
        } elseif ($action == 'delete' && $id_user > 0) {
            $query = "DELETE FROM USERS WHERE id_user = :id_user";
            $stmt = $db->prepare($query);
            $stmt->execute([':id_user' => $id_user]);
            $message_status = "<div class='alert alert-warning'>Utilisateur supprimé.</div>";
        }
    } catch (Exception $e) {
        $message_status = "<div class='alert alert-danger'>Erreur : " . $e->getMessage() . "</div>";
    }
}

// --- RÉCUPÉRATION DES UTILISATEURS ---
$query_users = "SELECT id_user, username, email, role, is_active FROM USERS ORDER BY role, username";
$stmt_users = $db->query($query_users);
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Les rôles disponibles doivent correspondre à l'ENUM dans la base
$roles_options = ['Admin', 'Vendeur', 'Stockeur', 'Analyse']; 
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-users-cog"></i> <?= $page_title ?></h2>
        
        <?= $message_status ?>

        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#userModal" data-action="add">
            <i class="fas fa-user-plus"></i> Ajouter un Utilisateur
        </button>

        <div class="card p-4 shadow">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nom d'utilisateur</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actif</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id_user'] ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><span class="badge bg-<?= $user['role'] == 'Admin' ? 'danger' : ($user['role'] == 'Vendeur' ? 'success' : 'info') ?>"><?= $user['role'] ?></span></td>
                            <td>
                                <i class="fas fa-<?= $user['is_active'] ? 'check-circle text-success' : 'times-circle text-danger' ?>"></i>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-user-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#userModal" 
                                        data-action="edit"
                                        data-id="<?= $user['id_user'] ?>"
                                        data-username="<?= htmlspecialchars($user['username']) ?>"
                                        data-email="<?= htmlspecialchars($user['email']) ?>"
                                        data-role="<?= $user['role'] ?>"
                                        data-active="<?= $user['is_active'] ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="POST" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="userForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Ajouter/Modifier Utilisateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="modalAction">
                    <input type="hidden" name="id_user" id="modalUserId">

                    <div class="mb-3">
                        <label for="username" class="form-label">Nom d'utilisateur</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Rôle</label>
                        <select class="form-select" id="role" name="role">
                            <?php foreach ($roles_options as $role_name): ?>
                                <option value="<?= $role_name ?>"><?= $role_name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe (Laisser vide pour ne pas modifier)</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active">
                        <label class="form-check-label" for="is_active">
                            Compte Actif
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary" id="modalSubmitButton">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userModal = document.getElementById('userModal');
    userModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const action = button.getAttribute('data-action');
        const modalTitle = userModal.querySelector('.modal-title');
        const modalAction = userModal.querySelector('#modalAction');
        const modalUserId = userModal.querySelector('#modalUserId');
        const passwordInput = userModal.querySelector('#password');
        const usernameInput = userModal.querySelector('#username');
        const emailInput = userModal.querySelector('#email');
        const roleInput = userModal.querySelector('#role');
        const activeInput = userModal.querySelector('#is_active');

        modalAction.value = action;
        passwordInput.required = (action === 'add');
        passwordInput.placeholder = (action === 'edit') ? 'Laisser vide pour ne pas modifier' : '';

        if (action === 'add') {
            modalTitle.textContent = 'Ajouter un nouvel Utilisateur';
            modalUserId.value = '';
            usernameInput.value = '';
            emailInput.value = '';
            roleInput.value = 'Vendeur';
            activeInput.checked = true;
            passwordInput.value = '';
        } else if (action === 'edit') {
            modalTitle.textContent = 'Modifier Utilisateur';
            
            modalUserId.value = button.getAttribute('data-id');
            usernameInput.value = button.getAttribute('data-username');
            emailInput.value = button.getAttribute('data-email');
            roleInput.value = button.getAttribute('data-role');
            activeInput.checked = (button.getAttribute('data-active') == 1);
            passwordInput.value = '';
        }
    });
});
</script>

<?php 
include '../includes/footer.php'; 
?>
