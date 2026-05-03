<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once 'db.php'; // Connexion BDD

// Sécurité : redirection si non connecté
if (!isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) != 'login.php') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intranet PME</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { min-height: 100vh; background: #212529; color: white; }
        .nav-link { color: rgba(255,255,255,0.8); }
        .nav-link:hover { color: white; background: #343a40; }
        .nav-link.active { background: #0d6efd; color: white; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block sidebar p-3">
            <h4 class="text-center mb-4">PME ERP</h4>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link mb-2 rounded" href="index.php"><i class="fas fa-home me-2"></i> Dashboard</a>
                </li>
                
                <?php if ($_SESSION['service_id'] == 2 || $_SESSION['role'] == 'Administrateur'): ?>
                <li class="nav-item">
                    <a class="nav-link mb-2 rounded" href="comptabilite.php"><i class="fas fa-calculator me-2"></i> Comptabilité</a>
                </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link mb-2 rounded" href="stock.php"><i class="fas fa-boxes me-2"></i> Stock & Produits</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link mb-2 rounded" href="clients.php"><i class="fas fa-users me-2"></i> Clients</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link mb-2 rounded" href="logistique.php"><i class="fas fa-truck me-2"></i> Logistique</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link mb-2 rounded" href="documents.php"><i class="fas fa-file-alt me-2"></i> Documents</a>
                </li>

                <?php if ($_SESSION['role'] == 'Administrateur'): ?>
                <li class="nav-item border-top pt-2">
                    <a class="nav-link mb-2 rounded text-info" href="admin_users.php"><i class="fas fa-user-shield me-2"></i> Admin RH</a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item mt-5">
                    <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a>
                </li>
<li class="nav-item"><a class="nav-link mb-2 rounded text-warning" href="reporting.php"><i class="fas fa-chart-line me-2"></i> Analytics BI</a></li>
            </ul>
        </nav>

        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
