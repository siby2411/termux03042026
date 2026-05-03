<?php
// partials/sidebar.php
?>
<!-- Sidebar -->
<div class="sidebar">
    <!-- Logo -->
    <div class="sidebar-header p-3 border-bottom border-secondary">
        <div class="d-flex align-items-center">
            <div class="logo-icon bg-white text-primary rounded-circle p-2 me-3">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="logo-text">
                <h5 class="mb-0">SYSCO OHADA</h5>
                <small class="text-white-50">v2.0</small>
            </div>
        </div>
    </div>
    
    <!-- User info -->
    <div class="user-info p-3 border-bottom border-secondary">
        <div class="d-flex align-items-center">
            <div class="user-avatar bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?>
            </div>
            <div>
                <p class="mb-0 small"><?php echo $_SESSION['username']; ?></p>
                <small class="text-white-50"><?php echo $_SESSION['user_role']; ?></small>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="sidebar-nav p-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="?module=dashboard" class="nav-link <?php echo $module == 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-home"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-white-50">COMPTABILITÉ</small>
            </li>
            <li class="nav-item">
                <a href="?module=ecritures" class="nav-link <?php echo $module == 'ecritures' ? 'active' : ''; ?>">
                    <i class="fas fa-book"></i>
                    <span>Écritures</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="?module=journaux" class="nav-link <?php echo $module == 'journaux' ? 'active' : ''; ?>">
                    <i class="fas fa-file-invoice"></i>
                    <span>Journaux</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="?module=grand_livre" class="nav-link <?php echo $module == 'grand_livre' ? 'active' : ''; ?>">
                    <i class="fas fa-book-open"></i>
                    <span>Grand livre</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-white-50">BANQUE</small>
            </li>
            <li class="nav-item">
                <a href="?module=rapprochement" class="nav-link <?php echo $module == 'rapprochement' ? 'active' : ''; ?>">
                    <i class="fas fa-university"></i>
                    <span>Rapprochement</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-white-50">ANALYSE</small>
            </li>
            <li class="nav-item">
                <a href="?module=soldes" class="nav-link <?php echo $module == 'soldes' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Soldes intermédiaires</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="?module=bilans" class="nav-link <?php echo $module == 'bilans' ? 'active' : ''; ?>">
                    <i class="fas fa-balance-scale"></i>
                    <span>Bilans</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="?module=flux" class="nav-link <?php echo $module == 'flux' ? 'active' : ''; ?>">
                    <i class="fas fa-exchange-alt"></i>
                    <span>Flux trésorerie</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-white-50">STOCKS</small>
            </li>
            <li class="nav-item">
                <a href="?module=articles" class="nav-link <?php echo $module == 'articles' ? 'active' : ''; ?>">
                    <i class="fas fa-boxes"></i>
                    <span>Gestion articles</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-white-50">CLÔTURE</small>
            </li>
            <li class="nav-item">
                <a href="?module=cloture" class="nav-link <?php echo $module == 'cloture' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-times"></i>
                    <span>Travaux clôture</span>
                </a>
            </li>
            
            <li class="nav-item mt-3">
                <small class="text-white-50">RAPPORTS</small>
            </li>
            <li class="nav-item">
                <a href="?module=rapports" class="nav-link <?php echo $module == 'rapports' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-bar"></i>
                    <span>Rapports</span>
                </a>
            </li>
            
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <li class="nav-item mt-3">
                <small class="text-white-50">ADMINISTRATION</small>
            </li>
            <li class="nav-item">
                <a href="?module=admin" class="nav-link <?php echo $module == 'admin' ? 'active' : ''; ?>">
                    <i class="fas fa-cogs"></i>
                    <span>Administration</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
        
        <!-- Bottom actions -->
        <div class="sidebar-footer position-absolute bottom-0 start-0 end-0 p-3 border-top border-secondary">
            <button class="btn btn-sm btn-outline-light w-100 mb-2" id="toggleSidebar">
                <i class="fas fa-chevron-left"></i>
                <span>Réduire</span>
            </button>
            <a href="logout.php" class="btn btn-sm btn-danger w-100">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </nav>
</div>
