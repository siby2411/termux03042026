<?php
if (!isset($page_title)) {
    $page_title = "SynthesePro";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($page_title); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ICONES -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- CSS GLOBAL -->
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div class="d-flex">

    <!-- ========== SIDEBAR ========== -->
    <div class="sidebar bg-dark text-white">

        <!-- LOGO OMEGA -->
        <div class="sidebar-brand text-center mb-4 mt-3">
            <img src="omega.jpg" alt="Omega" class="sidebar-logo">
            <div class="sidebar-title">SynthesePro</div>
        </div>

        <!-- MENU -->
        <ul class="nav flex-column sidebar-menu">

            <li class="nav-item">
                <a href="index.php" class="nav-link text-white">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a href="list_a_nouveaux.php" class="nav-link text-white">
                    <i class="bi bi-layers"></i> À Nouveaux
                </a>
            </li>

            <li class="nav-item">
                <a href="reg_passif.php" class="nav-link text-white">
                    <i class="bi bi-arrow-repeat"></i> Régularisations passif
                </a>
            </li>

            <li class="nav-item">
                <a href="ecritures.php" class="nav-link text-white">
                    <i class="bi bi-journal-text"></i> Saisies
                </a>
            </li>

            <li class="nav-item">
                <a href="logout.php" class="nav-link text-danger fw-bold">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </a>
            </li>

        </ul>

    </div>
    <!-- ========== FIN SIDEBAR ========== -->


    <!-- ========== CONTENU PRINCIPAL ========== -->
    <div class="main-content flex-grow-1">
        
        <!-- TOPBAR -->
        <nav class="navbar navbar-light bg-white shadow-sm px-4">
            <span class="navbar-brand mb-0 h5"><?= htmlspecialchars($page_title) ?></span>

            <div class="d-flex align-items-center">
                <i class="bi bi-person-circle fs-4 me-2"></i>
                <span class="fw-semibold">Utilisateur</span>
            </div>
        </nav>

        <!-- CONTENU -->
        <div class="p-4">

