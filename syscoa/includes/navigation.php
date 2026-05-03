<?php
// includes/navigation.php - Navigation complète

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_role = $_SESSION['user_role'] ?? 'visiteur';
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand" href="index.php">
            <i class="fas fa-balance-scale me-2"></i>
            <strong>SYSCOA</strong>
            <small class="opacity-75 ms-2">OHADA</small>
        </a>
        
        <!-- Menu mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <!-- Menu principal -->
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto">
                
                <?php if ($current_role === 'consultant'): ?>
                <!-- Menu Consultant -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'dashboard_consultant.php' ? 'active' : ''; ?>" 
                       href="dashboard_consultant.php">
                        <i class="fas fa-chart-line me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-chart-bar me-1"></i> Analyses
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="compte_resultat.php">Compte de résultat</a></li>
                        <li><a class="dropdown-item" href="bilan-comptable.php">Bilan comptable</a></li>
                        <li><a class="dropdown-item" href="tableau_flux_tresorerie.php">Flux de trésorerie</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="soldes_gestion.php">Soldes intermédiaires</a></li>
                        <li><a class="dropdown-item" href="analyse_ratio.php">Ratios financiers</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'gestion_articles.php' ? 'active' : ''; ?>" 
                       href="gestion_articles.php">
                        <i class="fas fa-boxes me-1"></i> Articles
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="rapports_consultant.php">
                        <i class="fas fa-file-alt me-1"></i> Rapports
                    </a>
                </li>
                
                <?php elseif ($current_role === 'comptable'): ?>
                <!-- Menu Comptable -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'dashboard_comptable.php' ? 'active' : ''; ?>" 
                       href="dashboard_comptable.php">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-edit me-1"></i> Saisie
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="saisie_ecriture.php">Nouvelle écriture</a></li>
                        <li><a class="dropdown-item" href="journal_comptable.php">Journal comptable</a></li>
                        <li><a class="dropdown-item" href="grand_livre.php">Grand livre</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-book me-1"></i> États
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="bilan-comptable.php">Bilan comptable</a></li>
                        <li><a class="dropdown-item" href="compte_resultat.php">Compte de résultat</a></li>
                        <li><a class="dropdown-item" href="balance.php">Balance</a></li>
                        <li><a class="dropdown-item" href="soldes_gestion.php">Soldes de gestion</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'gestion_articles.php' ? 'active' : ''; ?>" 
                       href="gestion_articles.php">
                        <i class="fas fa-boxes me-1"></i> Gestion articles
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="plan_comptable.php">
                        <i class="fas fa-list me-1"></i> Plan comptable
                    </a>
                </li>
                
                <?php elseif ($current_role === 'admin'): ?>
                <!-- Menu Administrateur -->
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'dashboard_admin.php' ? 'active' : ''; ?>" 
                       href="dashboard_admin.php">
                        <i class="fas fa-cogs me-1"></i> Administration
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-database me-1"></i> Gestion
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="gestion_utilisateurs.php">Utilisateurs</a></li>
                        <li><a class="dropdown-item" href="gestion_exercices.php">Exercices</a></li>
                        <li><a class="dropdown-item" href="gestion_articles.php">Articles</a></li>
                        <li><a class="dropdown-item" href="gestion_tiers.php">Tiers</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-chart-line me-1"></i> Rapports
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="compte_resultat.php">Compte de résultat</a></li>
                        <li><a class="dropdown-item" href="bilan-comptable.php">Bilan comptable</a></li>
                        <li><a class="dropdown-item" href="soldes_gestion.php">Soldes de gestion</a></li>
                        <li><a class="dropdown-item" href="rapport_global.php">Rapport global</a></li>
                    </ul>
                </li>
                
                <?php else: ?>
                <!-- Menu par défaut (non connecté) -->
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-home me-1"></i> Accueil
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">
                        <i class="fas fa-sign-in-alt me-1"></i> Connexion
                    </a>
                </li>
                <?php endif; ?>
                
                <!-- Menu commun -->
                <li class="nav-item">
                    <a class="nav-link" href="aide.php">
                        <i class="fas fa-question-circle me-1"></i> Aide
                    </a>
                </li>
            </ul>
            
            <!-- Informations utilisateur -->
            <?php if (isset($_SESSION['user_nom'])): ?>
            <div class="navbar-nav">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                        <div class="me-2">
                            <i class="fas fa-user-circle fa-lg"></i>
                        </div>
                        <div class="d-none d-lg-block">
                            <div class="small"><?php echo $_SESSION['user_nom']; ?></div>
                            <div class="small opacity-75"><?php echo ucfirst($_SESSION['user_role']); ?></div>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profil.php">
                            <i class="fas fa-user me-2"></i> Mon profil
                        </a></li>
                        <li><a class="dropdown-item" href="parametres.php">
                            <i class="fas fa-cog me-2"></i> Paramètres
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                        </a></li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
