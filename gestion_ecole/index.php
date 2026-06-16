<?php 
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['role'])) { header("Location: login.php"); exit(); }           
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

// Statistiques
$res_etu = $conn->query("SELECT COUNT(*) as total FROM etudiants");
$total_etudiants = $res_etu->fetch_assoc()['total'];

$res_mois = $conn->query("SELECT SUM(montant_verse) as total FROM paiements_scolarite WHERE MONTH(date_paiement) = MONTH(CURRENT_DATE())");
$total_mois = $res_mois->fetch_assoc()['total'] ?? 0;

$res_global = $conn->query("SELECT SUM(montant_verse) as total FROM paiements_scolarite");
$total_global = $res_global->fetch_assoc()['total'] ?? 0;

include 'header_ecole.php';
?>

<style>
    :root { --omega-blue: #1a2a6c; --omega-gold: #D4AF37; }
    body { background-color: #f4f7f6; }
    .omega-banner { background: linear-gradient(135deg, #1a2a6c, #b21f1f); color: white; padding: 30px; border-radius: 15px; margin-bottom: 30px; box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
    .menu-card { background: white; border-radius: 15px; padding: 20px; text-align: center; text-decoration: none !important; color: #333; transition: all 0.3s ease; display: flex; flex-direction: column; height: 100%; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border: 1px solid #eee; }
    .menu-card:hover { transform: translateY(-8px); background: var(--omega-blue); color: white !important; box-shadow: 0 15px 30px rgba(26, 42, 108, 0.3); }
    .menu-card i { font-size: 2rem; margin-bottom: 10px; }
    .menu-card span { font-weight: 700; text-transform: uppercase; font-size: 0.75rem; }
    .stat-card { border-radius: 10px; padding: 20px; color: white; margin-bottom: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
</style>

<div class="container mt-4">
    <div class="row text-center">
        <div class="col-md-4">
            <div class="stat-card bg-primary">
                <h6>Total Étudiants</h6>
                <h3><?= $total_etudiants ?></h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card bg-success">
                <h6>Encaissé ce mois</h6>
                <h3><?= number_format($total_mois, 0, ' ', ' ') ?> FCFA</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card bg-dark">
                <h6>Total Recettes Globales</h6>
                <h3><?= number_format($total_global, 0, ' ', ' ') ?> FCFA</h3>
            </div>
        </div>
    </div>

    <div class="omega-banner text-center shadow">
        <h2 class="fw-bold">OMEGA INFORMATIQUE <span style="color:var(--omega-gold)">CONSULTING</span></h2>
        <p class="mb-0 opacity-75">Système de Gestion Académique & Financière - Dakar, Sénégal</p>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-6 col-md-3"><a href="crud_etudiants.php" class="menu-card"><i class="bi bi-people-fill text-primary"></i><span>Étudiants</span></a></div>
        <div class="col-6 col-md-3"><a href="paiement_scolarite.php" class="menu-card"><i class="bi bi-cash-stack text-success"></i><span>Scolarité</span></a></div>
        <div class="col-6 col-md-3"><a href="notes_edit.php" class="menu-card"><i class="bi bi-journal-check text-warning"></i><span>Notes</span></a></div>
        <div class="col-6 col-md-3"><a href="generer_bulletin.php" class="menu-card border-danger"><i class="bi bi-file-earmark-bar-graph text-danger"></i><span>Bulletins</span></a></div>
        <div class="col-6 col-md-3"><a href="crud_professeurs.php" class="menu-card"><i class="bi bi-person-badge text-info"></i><span>Professeurs</span></a></div>
        <div class="col-6 col-md-3"><a href="crud_filieres.php" class="menu-card"><i class="bi bi-mortarboard text-primary"></i><span>Filières</span></a></div>
        <div class="col-6 col-md-3"><a href="crud_classes.php" class="menu-card"><i class="bi bi-diagram-3 text-secondary"></i><span>Classes</span></a></div>
        <div class="col-6 col-md-3"><a href="crud_stock.php" class="menu-card"><i class="bi bi-box-seam text-dark"></i><span>Stock</span></a></div>
    </div>
</div>

<?php include 'footer_ecole.php'; ?>
