<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - <?php echo $page_title ?? 'Accueil'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #ff6b6b;
            --secondary: #4ecdc4;
            --accent: #ffe66d;
            --dark: #2c3e50;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-size: 1.8rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .navbar-brand small {
            font-size: 0.8rem;
            font-weight: normal;
            display: block;
            color: var(--accent);
        }
        .nav-link {
            color: white !important;
            transition: all 0.3s;
        }
        .nav-link:hover {
            transform: translateY(-2px);
            color: var(--accent) !important;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            font-weight: bold;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        footer {
            background: linear-gradient(135deg, var(--dark) 0%, #1a2a3a 100%);
            color: white;
            margin-top: 50px;
        }
        .footer-link {
            color: var(--accent);
            text-decoration: none;
        }
        .footer-link:hover {
            color: white;
        }
        .banner-africa {
            background: linear-gradient(135deg, #ff6b6b, #4ecdc4, #ffe66d);
            padding: 5px;
            text-align: center;
            color: white;
            font-weight: bold;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="banner-africa">
        <i class="fas fa-globe-africa"></i> Vulgarisation de la littérature et science africaine
    </div>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>dashboard.php">
                <i class="fas fa-book-open"></i> OMEGA JUMTOU SAKOU KHAM KHAM TECH
                <small>Librairie & Éditions</small>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if(isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>dashboard.php"><i class="fas fa-tachometer-alt"></i> Tableau de bord</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="livresDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-book"></i> Livres
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/livres/liste.php">Liste des livres</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/livres/ajouter.php">Ajouter un livre</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/livres/categories.php">Catégories</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="ventesDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-shopping-cart"></i> Ventes
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/ventes/nouvelle.php">Nouvelle vente</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/ventes/liste.php">Historique</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="clientsDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-users"></i> Clients
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/clients/liste.php">Liste des clients</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/clients/ajouter.php">Ajouter un client</a></li>
                            </ul>
                        </li>
                        <?php if(isAdmin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>modules/rapports/financier.php"><i class="fas fa-chart-line"></i> Rapports</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>modules/users/liste.php"><i class="fas fa-user-cog"></i> Utilisateurs</a>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>modules/messages/index.php"><i class="fas fa-envelope"></i> Messagerie</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php include "includes/notifications_widget.php"; ?>
                    <?php if(isLoggedIn()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['user_name'] ?? 'Utilisateur'; ?>
                                <span class="badge bg-info"><?php echo $_SESSION['user_role'] ?? ''; ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>profile.php"><i class="fas fa-user-circle"></i> Mon profil</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>notifications.php"><i class="fas fa-bell"></i> Notifications</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>login.php">Connexion</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main class="container-fluid mt-4">
