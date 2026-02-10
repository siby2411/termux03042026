<?php
// Fichier : header_ecole.php (Version Bootstrap 5)

// Assurez-vous que la session est démarrée si ce fichier est inclus directement
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Variables de session
$username = htmlspecialchars($_SESSION['username'] ?? 'Invité');
$role = htmlspecialchars($_SESSION['role'] ?? 'Non connecté'); // Assurez-vous d'avoir une variable 'role' dans la session
$user_id = $_SESSION['user_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion École - <?= $role ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-mortarboard-fill me-2"></i> Gestion École
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if ($role === 'Administrateur'): ?>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="crud_etudiants.php"><i class="bi bi-person-fill me-1"></i> Étudiants</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="crud_paiements.php"><i class="bi bi-currency-euro me-1"></i> Scolarité</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarConfig" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-gear-fill me-1"></i> Configuration
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarConfig">
                            <li><a class="dropdown-item" href="crud_cycles.php">Gérer Cycles</a></li>
                            <li><a class="dropdown-item" href="crud_filieres.php">Gérer Filières</a></li>
                            <li><a class="dropdown-item" href="crud_classes.php">Gérer Classes</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="crud_matieres.php">Gérer Matières</a></li>
                            <li><a class="dropdown-item" href="crud_professeurs.php">Gérer Professeurs</a></li>
                            <li><a class="dropdown-item" href="crud_classe_matiere.php">Affecter Classes/Matières</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($role === 'Professeur' || $role === 'Administrateur'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="notes_edit.php">
                            <i class="bi bi-pencil-square me-1"></i> Saisie Notes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="bulletin_view.php">
                            <i class="bi bi-file-earmark-check-fill me-1"></i> Bulletins
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <span class="navbar-text me-3 text-white">
                Bienvenue, **<?= $username ?>** (<?= $role ?>)
            </span>
            <a href="logout.php" class="btn btn-outline-light">
                <i class="bi bi-box-arrow-right me-1"></i> Déconnexion
            </a>
        </div>
    </div>
</nav>

<main class="container mt-4">
