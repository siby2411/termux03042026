<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="fas fa-university me-2"></i> Mutuelle Crédit
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-home me-1"></i> Accueil</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="clients.php"><i class="fas fa-users me-1"></i> Clients</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="epargne.php"><i class="fas fa-wallet me-1"></i> Comptes & Opérations</a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link" href="credits.php"><i class="fas fa-hand-holding-usd me-1"></i> Crédits</a>
                </li>
                
                <?php if (in_array($_SESSION['role'], ['Admin', 'Comptable'])) : ?>
                    <li class="nav-item">
                        <a class="nav-link text-warning" href="comptabilite.php"><i class="fas fa-chart-bar me-1"></i> Comptabilité & ALM</a>
                    </li>
                <?php endif; ?>

                <?php if ($_SESSION['role'] === 'Admin') : ?>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="administration.php"><i class="fas fa-cogs me-1"></i> Administration</a>
                    </li>
                <?php endif; ?>
                
            </ul>
            
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars($_SESSION['user_prenom']) ?> (<?= $_SESSION['role'] ?>)
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#">Profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i> Déconnexion</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
