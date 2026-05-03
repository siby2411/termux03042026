<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="/modules/dashboard/index.php">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/modules/patients/index.php">
                    <i class="fas fa-users"></i> Patients
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/modules/consultations/index.php">
                    <i class="fas fa-stethoscope"></i> Consultations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/modules/queue/index.php">
                    <i class="fas fa-clock"></i> File d'attente
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/modules/payments/index.php">
                    <i class="fas fa-money-bill"></i> Paiements
                </a>
            </li>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="/modules/users/index.php">
                    <i class="fas fa-user-cog"></i> Utilisateurs
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/modules/services/index.php">
                    <i class="fas fa-building"></i> Services
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
