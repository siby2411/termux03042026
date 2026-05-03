<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">Laboratoire Médical</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Patients</a>
                    <ul class="dropdown-menu"><li><a class="dropdown-item" href="patients/liste.php">Liste</a></li><li><a class="dropdown-item" href="patients/ajouter.php">Ajouter</a></li></ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Médecins</a>
                    <ul class="dropdown-menu"><li><a class="dropdown-item" href="medecins/liste.php">Liste</a></li><li><a class="dropdown-item" href="medecins/ajouter.php">Ajouter</a></li></ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Analyses</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="categories/liste.php">Catégories</a></li>
                        <li><a class="dropdown-item" href="analyses/liste.php">Analyses</a></li>
                        <li><a class="dropdown-item" href="parametres/liste.php">Paramètres</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Prélèvements</a>
                    <ul class="dropdown-menu"><li><a class="dropdown-item" href="prelevements/liste.php">Liste</a></li><li><a class="dropdown-item" href="prelevements/ajouter.php">Ajouter</a></li></ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="analyses_realisees/liste.php">Analyses réalisées</a></li>
                <li class="nav-item"><a class="nav-link" href="rendezvous/liste.php">Rendez-vous</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Facturation</a>
                    <ul class="dropdown-menu"><li><a class="dropdown-item" href="factures/liste.php">Factures</a></li><li><a class="dropdown-item" href="paiements/liste.php">Paiements</a></li></ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Stock</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="fournisseurs/liste.php">Fournisseurs</a></li>
                        <li><a class="dropdown-item" href="reactifs/liste.php">Réactifs</a></li>
                        <li><a class="dropdown-item" href="mouvements_stock/liste.php">Mouvements</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">RH</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="presences/liste.php">Présences</a></li>
                        <li><a class="dropdown-item" href="contrats/liste.php">Contrats</a></li>
                        <li><a class="dropdown-item" href="feuilles_paie/liste.php">Feuilles de paie</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="statistiques.php">Statistiques</a></li>
            </ul>
            <span class="navbar-text me-3"><?= escape($_SESSION['username']) ?> (<?= escape($_SESSION['role']) ?>)</span>
            <a href="logout.php" class="btn btn-outline-light">Déconnexion</a>
        </div>
    </div>
</nav>
