<?php require_once 'header.php'; ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><i class="fas fa-users"></i> Patients</a>
                    <ul class="dropdown-menu"><li><a class="dropdown-item" href="patients/liste.php">Liste</a></li><li><a class="dropdown-item" href="patients/ajouter.php">Ajouter</a></li></ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><i class="fas fa-user-md"></i> Personnel</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="radiologues/liste.php">Radiologues</a></li>
                        <li><a class="dropdown-item" href="manipulateurs/liste.php">Manipulateurs</a></li>
                        <li><a class="dropdown-item" href="presences/liste.php">Présences</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><i class="fas fa-microscope"></i> Examens</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="examens/liste.php">Examens</a></li>
                        <li><a class="dropdown-item" href="equipements/liste.php">Équipements</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="rendezvous/liste.php"><i class="fas fa-calendar-alt"></i> Rendez-vous</a></li>
                <li class="nav-item"><a class="nav-link" href="comptes_rendus/liste.php"><i class="fas fa-file-alt"></i> Comptes rendus</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"><i class="fas fa-money-bill-wave"></i> Facturation</a>
                    <ul class="dropdown-menu"><li><a class="dropdown-item" href="factures/liste.php">Factures</a></li><li><a class="dropdown-item" href="paiements/liste.php">Paiements</a></li></ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="statistiques.php"><i class="fas fa-chart-line"></i> Statistiques</a></li>
            </ul>
            <span class="navbar-text me-3">
                <i class="fas fa-user-circle"></i>
                <?php if (isset($_SESSION['username'])): ?>
                    <?= escape($_SESSION['username']) ?> (<?= escape($_SESSION['role']) ?>)
                <?php else: ?>
                    Non connecté
                <?php endif; ?>
            </span>
            <a href="logout.php" class="btn btn-outline-light btn-sm"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
        </div>
    </div>
</nav>

<style>
.navbar {
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.navbar .nav-link {
    transition: all 0.3s ease;
}
.navbar .nav-link:hover {
    transform: translateY(-2px);
    color: #e94560 !important;
}
.dropdown-menu {
    background: #2c3e50;
    border: none;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}
.dropdown-item {
    color: #ecf0f1;
    transition: all 0.3s ease;
}
.dropdown-item:hover {
    background: #e94560;
    color: white;
    transform: translateX(5px);
}
.navbar-text i {
    margin-right: 5px;
}
.btn-outline-light:hover {
    background: #e94560;
    border-color: #e94560;
}
</style>
