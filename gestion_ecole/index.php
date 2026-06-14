<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role'])) { header("Location: login.php"); exit(); }

require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// Statistiques rapides
$total_etudiants = 0;
$res = $conn->query("SELECT COUNT(*) as total FROM etudiants");
if($res) { $total_etudiants = $res->fetch_assoc()['total']; }

include 'header_ecole.php';
?>

<style>
    :root { --omega-blue: #1a2a6c; --omega-gold: #D4AF37; }
    body { background-color: #f4f7f6; }
    .navbar-omega { background: var(--omega-blue); border-bottom: 3px solid var(--omega-gold); }
    .omega-banner { background: linear-gradient(135deg, #1a2a6c, #b21f1f); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .menu-card { background: white; border-radius: 15px; padding: 25px 15px; text-align: center; text-decoration: none !important; color: #333; transition: all 0.3s ease; display: flex; flex-direction: column; height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid #eee; }
    .menu-card:hover { transform: translateY(-8px); background: var(--omega-blue); color: white !important; box-shadow: 0 15px 30px rgba(26, 42, 108, 0.3); }
    .menu-card i { font-size: 2.5rem; margin-bottom: 15px; }
    .menu-card span { font-weight: 700; text-transform: uppercase; font-size: 0.8rem; }
</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-omega sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">OMEGA ERP V4.0</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="logout.php"><i class="bi bi-power"></i> Quitter</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="omega-banner text-center shadow">
        <h2 class="fw-bold">OMEGA INFORMATIQUE <span style="color:var(--omega-gold)">CONSULTING</span></h2>
        <p class="mb-0 opacity-75">Système de Gestion Académique & Financière - Dakar, Sénégal</p>
    </div>

    <div class="row g-4 mb-5">
        <!-- Étudiants -->
        <div class="col-6 col-md-4 col-lg-3">
            <a href="crud_etudiants.php" class="menu-card">
                <span class="badge bg-primary rounded-pill mb-2"><?php echo $total_etudiants; ?></span>
                <i class="bi bi-people-fill text-primary"></i>
                <span>Étudiants</span>
            </a>
        </div>
        <!-- Paiements -->
        <div class="col-6 col-md-4 col-lg-3">
            <a href="crud_paiements.php" class="menu-card">
                <i class="bi bi-cash-stack text-success"></i>
                <span>Scolarité</span>
            </a>
        </div>
        <!-- Notes -->
        <div class="col-6 col-md-4 col-lg-3">
            <a href="notes_edit.php" class="menu-card">
                <i class="bi bi-journal-check text-warning"></i>
                <span>Notes</span>
            </a>
        </div>
        <!-- Bulletins -->
        <div class="col-6 col-md-4 col-lg-3">
            <a href="generer_bulletin.php" class="menu-card border-danger">
                <i class="bi bi-file-earmark-bar-graph text-danger"></i>
                <span>Gestion Bulletins</span>
            </a>
        </div>
        <!-- Professeurs -->
        <div class="col-6 col-md-4 col-lg-3">
            <a href="crud_professeurs.php" class="menu-card">
                <i class="bi bi-person-badge text-info"></i>
                <span>Professeurs</span>
            </a>
        </div>
        <!-- Filières -->
        <div class="col-6 col-md-4 col-lg-3">
            <a href="crud_filieres.php" class="menu-card">
                <i class="bi bi-mortarboard text-primary"></i>
                <span>Filières</span>
            </a>
        </div>
        <!-- Classes -->
        <div class="col-6 col-md-4 col-lg-3">
            <a href="crud_classes.php" class="menu-card">
                <i class="bi bi-diagram-3 text-secondary"></i>
                <span>Classes</span>
            </a>
        </div>
        <!-- Stock -->
        <div class="col-6 col-md-4 col-lg-3">
            <a href="crud_stock.php" class="menu-card">
                <i class="bi bi-box-seam text-dark"></i>
                <span>Stock</span>
            </a>
        </div>
    </div>

    <div class="text-center text-muted small mb-4">
        &copy; <?php echo date('Y'); ?> Copyright Mr Mohamed Siby Consultant en informatique | Dakar, Sénégal
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
