<?php
session_start();
if(!isset($_SESSION['user_role'])) { header('Location: login_parent.php'); exit(); }
include __DIR__ . '/../../includes/header.php';
?>
<div class="container mt-4">
    <h2>Dashboard Parent</h2>
    <div class="alert alert-success">Bienvenue <?= $_SESSION['user_name'] ?></div>
    <div class="row">
        <div class="col-md-4"><div class="card text-center p-3"><i class="fas fa-child fa-3x text-primary"></i><h3>2</h3><p>Enfants inscrits</p></div></div>
        <div class="col-md-4"><div class="card text-center p-3"><i class="fas fa-credit-card fa-3x text-success"></i><h3>1</h3><p>Payé / 1 impayé</p></div></div>
        <div class="col-md-4"><div class="card text-center p-3"><i class="fas fa-bus fa-3x text-warning"></i><h3>Bus DK-123-AB</h3><p>Affecté</p></div></div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
