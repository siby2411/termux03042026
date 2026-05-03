<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$page_title = "Mon profil - OMEGA Assurance";
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-user"></i> Mon profil</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nom d'utilisateur:</strong> <?php echo $_SESSION['username']; ?></p>
                    <p><strong>Nom complet:</strong> <?php echo $_SESSION['user_name']; ?></p>
                    <p><strong>Rôle:</strong> <?php echo $_SESSION['user_role']; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
