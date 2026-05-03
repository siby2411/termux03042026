<?php
session_start();
if(!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
$page_title = "Paramètres - OMEGA Assurance";
require_once 'includes/header.php';
?>

<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-cog"></i> Paramètres</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Module en construction. Revenez bientôt pour plus d'options.
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
