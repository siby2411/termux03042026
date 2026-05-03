<div class="sidebar d-flex flex-column flex-shrink-0 p-3 text-white shadow" 
     style="width: 260px; height: 100vh; position: fixed; top: 0; left: 0; background: #01291a; z-index: 1000;">
    
    <div class="text-center mb-4">
        <h4 class="fw-bold text-warning mb-0">Ω OMEGA PHARMA</h4>
        <small class="opacity-75" style="font-size: 0.7rem;">SYSTEMS BY M. SIBY</small>
    </div>
    
    <hr class="bg-light">
    
    <ul class="nav nav-pills flex-column mb-auto" style="overflow-y: auto;">
        <li class="nav-item">
            <a href="/modules/dashboard/index.php" class="nav-link text-white py-2 mb-1">
                <i class="bi bi-speedometer2 me-2 text-success"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="/modules/caisse/pos.php" class="nav-link text-white py-2 mb-1">
                <i class="bi bi-calculator me-2 text-success"></i> Caisse / Ventes
            </a>
        </li>
        <li>
            <a href="/modules/stock/index.php" class="nav-link text-white py-2 mb-1">
                <i class="bi bi-boxes me-2 text-success"></i> Gestion des Stocks
            </a>
        </li>
        <li>
            <a href="/modules/medicaments/index.php" class="nav-link text-white py-2 mb-1">
                <i class="bi bi-capsule me-2 text-success"></i> Médicaments
            </a>
        </li>
        <li>
            <a href="/modules/achats/index.php" class="nav-link text-white py-2 mb-1">
                <i class="bi bi-cart-plus me-2 text-success"></i> Achats Fournisseurs
            </a>
        </li>
        <li>
            <a href="/modules/fournisseurs/index.php" class="nav-link text-white py-2 mb-1">
                <i class="bi bi-truck me-2 text-success"></i> Partenaires
            </a>
        </li>
        <li>
            <a href="/modules/ordonnances/index.php" class="nav-link text-white py-2 mb-1">
                <i class="bi bi-file-earmark-medical me-2 text-success"></i> Ordonnances
            </a>
        </li>
        <li>
            <a href="/modules/clients/index.php" class="nav-link text-white py-2 mb-1">
                <i class="bi bi-people me-2 text-success"></i> Fichier Clients
            </a>
        </li>
        <li>
            <a href="/modules/rapports/index.php" class="nav-link text-white py-2 mb-1">
                <i class="bi bi-bar-chart-line me-2 text-success"></i> Rapports & Stats
            </a>
        </li>
        <li>
            <a href="/modules/utilisateurs/index.php" class="nav-link text-white py-2 mb-1">
                <i class="bi bi-person-gear me-2 text-success"></i> Utilisateurs
            </a>
        </li>
    </ul>
    
    <hr>
    
    <div class="dropdown">
        <a href="/logout.php" class="d-flex align-items-center text-danger text-decoration-none fw-bold">
            <i class="bi bi-box-arrow-right me-2"></i> DECONNEXION
        </a>
    </div>
</div>

<style>
    .nav-link:hover { background: rgba(255, 255, 255, 0.1); border-radius: 8px; }
    .nav-link.active { background: #00713e !important; }
    /* Personnalisation de la barre de défilement pour la sidebar */
    .nav::-webkit-scrollbar { width: 4px; }
    .nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
</style>
