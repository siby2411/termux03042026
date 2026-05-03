<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><i class="fas fa-hotel me-2"></i>OMEGA Hôtel</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="../index.php"><i class="fas fa-arrow-left"></i> Retour Portail</a></li>
                <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="chambres/liste.php">Chambres</a></li>
                <li class="nav-item"><a class="nav-link active" href="reservations/liste.php">Réservations</a></li>
                <li class="nav-item"><a class="nav-link" href="clients/liste.php">Clients</a></li>
                <li class="nav-item"><a class="nav-link" href="personnel/liste.php">Personnel</a></li>
            </ul>
            <span class="navbar-text me-3">
                <i class="fas fa-user-circle"></i> <?= isset($_SESSION['username']) ? escape($_SESSION['username']) : 'Invité' ?>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>
</nav>
