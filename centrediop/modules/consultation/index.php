<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
// Inclusion de la sidebar unifiée
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php require_once '../../includes/sidebar.php'; ?>
        </div>
        <div class="col-md-10 p-4">
            <h2 class="mb-4">Gestion des Consultations</h2>
            
            <div class="dashboard-card shadow-sm p-4 bg-white rounded">
                <?php 
                    // Votre logique PHP existante pour afficher les consultations
                    // ...
                ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
