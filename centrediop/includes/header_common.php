<?php
/**
 * En-tête commun pour tous les dashboards
 */
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <i class="fas fa-hospital me-2"></i>
            Centre Mamadou Diop
        </a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/modules/medical/edition_dossier.php">
                        <i class="fas fa-edit"></i> Édition dossier
                    </a>
                </li>
                <li class="nav-item">
                    <span class="nav-link">
                        <i class="fas fa-user"></i>
                        <?= $_SESSION['user_prenom'] ?? '' ?> <?= $_SESSION['user_nom'] ?? '' ?>
                        (<?= $_SESSION['user_role'] ?? '' ?>)
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
