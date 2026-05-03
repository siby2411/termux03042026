<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role'])) { header("Location: login.php"); exit(); }

require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// On récupère quelques chiffres pour le design
$total_etudiants = 0;
$check_table = $conn->query("SHOW TABLES LIKE 'etudiants'");
if($check_table->num_rows > 0) {
    $res = $conn->query("SELECT COUNT(*) as total FROM etudiants");
    $total_etudiants = $res->fetch_assoc()['total'];
}

include 'header_ecole.php'; 
?>

<style>
    :root {
        --omega-blue: #1a2a6c;
        --omega-gold: #D4AF37;
        --light-gray: #f8f9fa;
    }
    body { background-color: #f4f7f6; font-family: 'Segoe UI', Roboto, sans-serif; }

    /* Navbar Premium */
    .navbar-omega {
        background: var(--omega-blue);
        border-bottom: 3px solid var(--omega-gold);
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .navbar-brand { font-weight: 800; color: var(--omega-gold) !important; text-transform: uppercase; }

    /* Bannière de Bienvenue */
    .omega-banner {
        background: linear-gradient(135deg, #1a2a6c, #b21f1f);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-top: 20px;
        margin-bottom: 30px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    /* Cartes de Navigation (Menu de Grille) */
    .menu-card {
        background: white;
        border: none;
        border-radius: 15px;
        padding: 25px 15px;
        text-align: center;
        text-decoration: none !important;
        color: #333;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .menu-card:hover {
        transform: translateY(-8px);
        background: var(--omega-blue);
        color: white !important;
        box-shadow: 0 15px 30px rgba(26, 42, 108, 0.3);
    }
    .menu-card i { font-size: 2.5rem; margin-bottom: 15px; }
    .menu-card span { font-weight: 700; text-transform: uppercase; font-size: 0.9rem; }
    .menu-card .badge { position: absolute; top: 10px; right: 10px; }
</style>

<nav class="navbar navbar-expand-lg navbar-dark navbar-omega sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">OMEGA ERP V4.0</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link active" href="index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="crud_etudiants.php">Étudiants</a></li>
                <li class="nav-item"><a class="nav-link" href="crud_paiements.php">Scolarité</a></li>
                <li class="nav-item"><a class="nav-link text-warning" href="logout.php"><i class="bi bi-power"></i> Quitter</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <div class="omega-banner text-center">
        <h2 class="fw-bold">OMEGA INFORMATIQUE <span style="color:var(--omega-gold)">CONSULTING</span></h2>
        <p class="mb-0 opacity-75">Système de Gestion Académique & Financière - Dakar, Sénégal</p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-6 col-md-4 col-lg-3">
            <a href="crud_etudiants.php" class="menu-card position-relative">
                <span class="badge bg-primary rounded-pill"><?php echo $total_etudiants; ?></span>
                <i class="bi bi-people-fill text-primary"></i>
                <span>Gestion Étudiants</span>
            </a>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <a href="crud_paiements.php" class="menu-card">
                <i class="bi bi-cash-stack text-success"></i>
                <span>Scolarité & Caisse</span>
            </a>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <a href="notes_edit.php" class="menu-card">
                <i class="bi bi-journal-check text-warning"></i>
                <span>Saisie des Notes</span>
            </a>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <a href="crud_professeurs.php" class="menu-card">
                <i class="bi bi-person-badge text-info"></i>
                <span>Corps Enseignant</span>
            </a>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <a href="crud_classes.php" class="menu-card">
                <i class="bi bi-diagram-3 text-secondary"></i>
                <span>Classes & Filières</span>
            </a>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <a href="crud_matieres.php" class="menu-card">
                <i class="bi bi-book-half text-secondary"></i>
                <span>Matières & UV</span>
            </a>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <a href="bulletin_view.php" class="menu-card">
                <i class="bi bi-file-earmark-bar-graph text-danger"></i>
                <span>Bulletins & Relevés</span>
            </a>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <a href="calcul_moyenne.php" class="menu-card">
                <i class="bi bi-calculator text-dark"></i>
                <span>Calcul Moyennes</span>
            </a>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <a href="crud_stock.php" class="menu-card">
                <i class="bi bi-box-seam text-secondary"></i>
                <span>Gestion de Stock</span>
            </a>
        </div>

        <div class="col-6 col-md-4 col-lg-3">
            <a href="cloture_caisse.php" class="menu-card border-danger">
                <i class="bi bi-lock-fill text-danger"></i>
                <span class="text-danger">Clôture Caisse</span>
            </a>
        </div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
