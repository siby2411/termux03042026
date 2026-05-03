<?php
session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: index.php');
    exit;
}
$page_title = "Gestion utilisateurs - OMEGA Assurance";
require_once 'includes/header.php';
$db = getDB();
$users = $db->query("SELECT * FROM utilisateurs ORDER BY id")->fetchAll();
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-users"></i> Utilisateurs</h5>
        </div>
        <div class="card-body">
            <table class="table datatable">
                <thead>
                    <tr><th>ID</th><th>Nom d'utilisateur</th><th>Nom</th><th>Rôle</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                    <tr>
                        <td><?php echo $u['id']; ?></td>
                        <td><?php echo $u['nom_utilisateur']; ?></td>
                        <td><?php echo $u['prenom'].' '.$u['nom']; ?></td>
                        <td><?php echo $u['role']; ?></td>
                        <td><span class="badge bg-<?php echo $u['actif'] ? 'success' : 'danger'; ?>"><?php echo $u['actif'] ? 'Actif' : 'Inactif'; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
