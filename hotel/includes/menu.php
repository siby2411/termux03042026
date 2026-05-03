<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><i class="fas fa-hotel me-2"></i>OMEGA Hôtel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="../index.php"><i class="fas fa-arrow-left"></i> Retour Portail</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="chambres/liste.php"><i class="fas fa-bed"></i> Chambres</a></li>
                <li class="nav-item"><a class="nav-link" href="reservations/liste.php"><i class="fas fa-calendar-alt"></i> Réservations</a></li>
                <li class="nav-item"><a class="nav-link" href="clients/liste.php"><i class="fas fa-users"></i> Clients</a></li>
                <li class="nav-item"><a class="nav-link" href="personnel/liste.php"><i class="fas fa-user-tie"></i> Personnel</a></li>
                <li class="nav-item"><a class="nav-link" href="paie/liste.php"><i class="fas fa-money-bill-wave"></i> Paie</a></li>
                <li class="nav-item"><a class="nav-link" href="charges/liste.php"><i class="fas fa-chart-line"></i> Charges</a></li>
                <li class="nav-item"><a class="nav-link active" href="statistiques/index.php"><i class="fas fa-chart-pie"></i> Statistiques</a></li>
            </ul>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    <i class="fas fa-user-circle"></i> <?= isset($_SESSION['username']) ? escape($_SESSION['username']) : 'Invité' ?>
                </span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </div>
</nav>
