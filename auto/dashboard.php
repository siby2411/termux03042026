<?php
session_start();
include_once "db_connect.php";
include_once "header.php";

if (!isset($_SESSION['admin_logged'])) { header("Location: login.php"); exit(); }

// 1. Calcul des Recettes (Somme de cout_total)
$q_rec = $conn->query("SELECT SUM(cout_total) as total FROM locations");
$total_recettes = ($q_rec) ? $q_rec->fetch_assoc()['total'] : 0;

// 2. Nombre de Véhicules
$q_veh = $conn->query("SELECT COUNT(*) as nb FROM voitures");
$nb_vehicules = ($q_veh) ? $q_veh->fetch_assoc()['nb'] : 0;

// 3. Locations Actives (Statut 'En cours')
$q_act = $conn->query("SELECT COUNT(*) as nb FROM locations WHERE statut = 'En cours'");
$nb_actives = ($q_act) ? $q_act->fetch_assoc()['nb'] : 0;
?>

<div class="container my-5">
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 opacity-75">Parc Automobile</h6>
                        <h2 class="fw-bold mb-0"><?php echo $nb_vehicules; ?></h2>
                    </div>
                    <i class="fas fa-car fa-3x opacity-25"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-dark text-white border-bottom border-warning border-5">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 opacity-75">Contrats En cours</h6>
                        <h2 class="fw-bold mb-0"><?php echo $nb_actives; ?></h2>
                    </div>
                    <i class="fas fa-file-signature fa-3x opacity-25 text-warning"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 opacity-75">Recettes (FCFA)</h6>
                        <h2 class="fw-bold mb-0"><?php echo number_format($total_recettes, 0, ',', ' '); ?></h2>
                    </div>
                    <i class="fas fa-money-bill-wave fa-3x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <h5 class="fw-bold mb-4 border-bottom pb-2">Actions OMEGA CONSULTING</h5>
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <a href="liste_voitures.php" class="btn btn-outline-primary w-100 py-3 rounded-3 border-2">
                    <i class="fas fa-list d-block mb-2 fa-lg"></i> Liste Voitures
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="ajouter_location.php" class="btn btn-outline-success w-100 py-3 rounded-3 border-2">
                    <i class="fas fa-plus-circle d-block mb-2 fa-lg"></i> Nouveau Contrat
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="liste_clients.php" class="btn btn-outline-info w-100 py-3 rounded-3 border-2">
                    <i class="fas fa-users d-block mb-2 fa-lg"></i> Clients
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="upload_diaporama.php" class="btn btn-outline-dark w-100 py-3 rounded-3 border-2">
                    <i class="fas fa-camera-retro d-block mb-2 fa-lg"></i> Diaporama
                </a>
            </div>
        </div>
    </div>
</div>

<?php include_once "footer.php"; ?>
