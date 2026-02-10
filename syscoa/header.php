

<?php
// Fichier: includes/header.php
// Contient la structure HTML de base, la navigation et les scripts/styles communs.

// Configuration de la connexion à la base de données SYSCOHADA (à implémenter dans config.php)
// require_once 'config.php'; 

// Démarrage de la session si nécessaire
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Compta Pro-OHADA'; ?></title>
    <!-- Intégration de Tailwind CSS (via CDN pour la rapidité) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Styles personnalisés pour la charte OHADA */
        .navbar-ohada {
            background-color: #007a4d; /* Vert Institutionnel */
        }
        .btn-primary-ohada {
            background-color: #007a4d;
            border-color: #007a4d;
            transition: all 0.2s;
        }
        .btn-primary-ohada:hover {
            background-color: #005f3d;
        }
    </style>
</head>
<body class="bg-gray-50">

<nav class="navbar-ohada p-4 shadow-md">
    <div class="container mx-auto flex justify-between items-center">
        <a class="text-white text-2xl font-bold" href="/index.php">
            <i class="fas fa-chart-line mr-2"></i> COMPTA PRO-OHADA
        </a>
        <div class="space-x-4 text-white">
            <a href="/modules/saisie_comptable/saisie_form.php" class="hover:underline">Saisie</a>
            <a href="/modules/tiers/tiers_gestion_form.php" class="hover:underline">Tiers</a>
            <a href="/modules/immobilisations/immobilisation_form.php" class="hover:underline">Immo.</a>
            <a href="/modules/reporting/reporting_balance.php" class="hover:underline">Reporting</a>
            <a href="#" class="hover:underline"><i class="fas fa-user-circle"></i> User</a>
        </div>
    </div>
</nav>

<main class="container mx-auto mt-8 p-4">
    <!-- Le contenu spécifique du module commencera ici -->








