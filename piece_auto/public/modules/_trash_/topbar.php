<?php
// topbar.php
?>
<div class="topbar-horizontal d-flex justify-content-between align-items-center p-2 shadow-sm bg-white mb-3">
    <div class="d-flex align-items-center">
        <span class="fw-bold fs-5 text-primary">📘 SynthesePro</span>
    </div>
    <div>
        <a href="profile.php" class="me-3 text-decoration-none"><i class="bi bi-person-circle"></i> Profil</a>
        <a href="logout.php" class="text-decoration-none text-danger"><i class="bi bi-box-arrow-right"></i> Déconnexion</a>
    </div>
</div>

CSS additionnel (layout.php)
.topbar-horizontal {
    position: sticky;
    top: 0;
    z-index: 1050; /* toujours au-dessus */
    background: #ffffff;
}
