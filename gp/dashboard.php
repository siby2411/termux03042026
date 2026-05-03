<?php
require_once 'auth.php';
require_once 'db_connect.php';

// Statistiques globales
$stats = [];
$stats['total_colis'] = $pdo->query("SELECT COUNT(*) FROM colis")->fetchColumn();
$stats['en_transit'] = $pdo->query("SELECT COUNT(*) FROM colis WHERE statut NOT IN ('livre','arrivee')")->fetchColumn();
$stats['livres'] = $pdo->query("SELECT COUNT(*) FROM colis WHERE statut='livre'")->fetchColumn();
$stats['clients_fret'] = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();

// Statistiques des boutiques
$stats['produits'] = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$stats['vetements'] = $pdo->query("SELECT COUNT(*) FROM vetements")->fetchColumn();
$stats['bijoux'] = $pdo->query("SELECT COUNT(*) FROM bijouterie")->fetchColumn();
$stats['negoce'] = $pdo->query("SELECT COUNT(*) FROM negoce")->fetchColumn();
$stats['mode'] = $pdo->query("SELECT COUNT(*) FROM mode_accessoires")->fetchColumn();
$stats['fruits'] = $pdo->query("SELECT COUNT(*) FROM fruits_tropicaux")->fetchColumn();

$derniers = $pdo->query("SELECT c.numero_suivi, c.statut, e.nom as expediteur FROM colis c JOIN clients e ON c.client_expediteur_id = e.id ORDER BY c.derniere_mise_a_jour DESC LIMIT 5")->fetchAll();

include('header.php');
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
    .dashboard-card { transition: transform 0.2s, box-shadow 0.2s; border-radius: 15px; }
    .dashboard-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
    .section-title { background: linear-gradient(135deg, #ff8c00, #ffaa33); color: #1a1a2e; padding: 10px 15px; border-radius: 10px; margin: 20px 0 15px 0; }
    .whatsapp-card { background: linear-gradient(135deg, #075E54, #128C7E); color: white; }
    .whatsapp-card:hover { background: linear-gradient(135deg, #128C7E, #075E54); }
</style>

<h2><i class="fas fa-tachometer-alt"></i> Tableau de bord - Dieynaba GP Holding</h2>

<!-- Cartes statistiques globales -->
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card text-white bg-primary dashboard-card"><div class="card-body"><i class="fas fa-box fa-2x"></i><h3><?= $stats['total_colis'] ?></h3><p>Colis expédiés</p></div></div></div>
    <div class="col-md-3"><div class="card text-white bg-warning dashboard-card"><div class="card-body"><i class="fas fa-truck fa-2x"></i><h3><?= $stats['en_transit'] ?></h3><p>En transit</p></div></div></div>
    <div class="col-md-3"><div class="card text-white bg-success dashboard-card"><div class="card-body"><i class="fas fa-check-circle fa-2x"></i><h3><?= $stats['livres'] ?></h3><p>Colis livrés</p></div></div></div>
    <div class="col-md-3"><div class="card text-white bg-info dashboard-card"><div class="card-body"><i class="fas fa-users fa-2x"></i><h3><?= $stats['clients_fret'] ?></h3><p>Clients fret</p></div></div></div>
</div>

<!-- ==================== SECTION WHATSAPP (EN HAUT) ==================== -->
<div class="section-title"><i class="fab fa-whatsapp"></i> 📱 WhatsApp Business - Notifications automatiques</div>
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card whatsapp-card dashboard-card h-100">
            <div class="card-body text-center">
                <i class="fab fa-whatsapp fa-3x mb-2"></i>
                <h5>Notification simple</h5>
                <p>Tester l'envoi de message sur votre numéro</p>
                <a href="test_whatsapp_direct.php" class="btn btn-light btn-sm">📱 Tester</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card whatsapp-card dashboard-card h-100">
            <div class="card-body text-center">
                <i class="fas fa-paper-plane fa-3x mb-2"></i>
                <h5>Notification colis</h5>
                <p>Envoyer une notification personnalisée pour un colis</p>
                <a href="send_colis_notification.php" class="btn btn-light btn-sm">📦 Envoyer</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card whatsapp-card dashboard-card h-100">
            <div class="card-body text-center">
                <i class="fas fa-tasks fa-3x mb-2"></i>
                <h5>Admin colis</h5>
                <p>Changer un statut déclenche une notification auto</p>
                <a href="admin_colis.php" class="btn btn-light btn-sm">⚙️ Gérer</a>
            </div>
        </div>
    </div>
</div>

<!-- ==================== SECTION BOUTIQUES ==================== -->
<div class="section-title"><i class="fas fa-store"></i> 🛍️ Nos Boutiques en ligne</div>
<div class="row g-4 mb-5">
    <div class="col-md-2"><div class="card text-center border-success dashboard-card h-100"><div class="card-body"><i class="fas fa-shopping-basket fa-2x text-success"></i><h6>Épicerie</h6><small><?= $stats['produits'] ?> produits</small><br><a href="produits.php" class="btn btn-sm btn-success mt-2">Voir</a> <a href="admin_produits.php" class="btn btn-sm btn-outline-success">Admin</a></div></div></div>
    <div class="col-md-2"><div class="card text-center border-primary dashboard-card h-100"><div class="card-body"><i class="fas fa-tshirt fa-2x text-primary"></i><h6>Mode</h6><small><?= $stats['vetements'] ?> articles</small><br><a href="vetements.php" class="btn btn-sm btn-primary mt-2">Voir</a> <a href="admin_vetements.php" class="btn btn-sm btn-outline-primary">Admin</a></div></div></div>
    <div class="col-md-2"><div class="card text-center border-warning dashboard-card h-100"><div class="card-body"><i class="fas fa-gem fa-2x text-warning"></i><h6>Joaillerie</h6><small><?= $stats['bijoux'] ?> refs</small><br><a href="bijouterie.php" class="btn btn-sm btn-warning mt-2">Voir</a> <a href="admin_bijouterie.php" class="btn btn-sm btn-outline-warning">Admin</a></div></div></div>
    <div class="col-md-2"><div class="card text-center border-info dashboard-card h-100"><div class="card-body"><i class="fas fa-laptop fa-2x text-info"></i><h6>Négoce</h6><small><?= $stats['negoce'] ?> produits</small><br><a href="negoce.php" class="btn btn-sm btn-info mt-2">Voir</a> <a href="admin_negoce.php" class="btn btn-sm btn-outline-info">Admin</a></div></div></div>
    <div class="col-md-2"><div class="card text-center border-secondary dashboard-card h-100"><div class="card-body"><i class="fas fa-shoe-prints fa-2x text-secondary"></i><h6>Mode Luxe</h6><small><?= $stats['mode'] ?> articles</small><br><a href="mode.php" class="btn btn-sm btn-secondary mt-2">Voir</a> <a href="admin_mode.php" class="btn btn-sm btn-outline-secondary">Admin</a></div></div></div>
    <div class="col-md-2"><div class="card text-center border-success dashboard-card h-100"><div class="card-body"><i class="fas fa-leaf fa-2x text-success"></i><h6>Fruits</h6><small><?= $stats['fruits'] ?> produits</small><br><a href="fruits_tropicaux.php" class="btn btn-sm btn-success mt-2">Voir</a> <a href="admin_fruits.php" class="btn btn-sm btn-outline-success">Admin</a></div></div></div>
</div>

<!-- ==================== SECTION GESTION DES COLIS ==================== -->
<div class="section-title"><i class="fas fa-box"></i> 📦 Gestion des colis</div>
<div class="row g-3 mb-4">
    <div class="col-md-3"><a href="creer_colis_holding.php" class="btn btn-primary w-100"><i class="fas fa-exchange-alt"></i> ➕ Nouveau colis bi-directionnel</a></div>
    <div class="col-md-3"><a href="admin_colis.php" class="btn btn-warning w-100"><i class="fas fa-tasks"></i> ⚙️ Gestion des colis</a></div>
    <div class="col-md-3"><a href="gestion_geolocalisation.php" class="btn btn-info w-100"><i class="fas fa-map-marker-alt"></i> 📍 Géolocalisation</a></div>
    <div class="col-md-3"><a href="update_location.php" class="btn btn-secondary w-100"><i class="fas fa-map-pin"></i> 🗺️ Mettre à jour GPS</a></div>
</div>

<!-- ==================== SECTION DERNIERS COLIS ==================== -->
<div class="card mt-4">
    <div class="card-header bg-dark text-white">📦 Derniers colis enregistrés</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark"><tr><th>N° suivi</th><th>Expéditeur</th><th>Statut</th><th>Action</th></tr></thead>
                <tbody><?php foreach ($derniers as $d): ?>
                <tr><td><?= htmlspecialchars($d['numero_suivi']) ?></td><td><?= htmlspecialchars($d['expediteur']) ?></td><td><span class="badge bg-secondary"><?= $d['statut'] ?></span></td><td><a href="suivi_carte.php?numero=<?= urlencode($d['numero_suivi']) ?>" class="btn btn-sm btn-info">🗺️ Carte</a></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==================== SECTION SERVICES RAPIDES ==================== -->
<div class="row mt-4">
    <div class="col-md-3"><div class="card bg-light dashboard-card"><div class="card-body text-center"><a href="vols.php" class="stretched-link"><i class="fas fa-plane fa-2x"></i><br>✈️ Vols</a></div></div></div>
    <div class="col-md-3"><div class="card bg-light dashboard-card"><div class="card-body text-center"><a href="offres_services.php" class="stretched-link"><i class="fas fa-file-pdf fa-2x"></i><br>📄 Offres services</a></div></div></div>
    <div class="col-md-3"><div class="card bg-light dashboard-card"><div class="card-body text-center"><a href="generer_offre_pdf.php?client_id=1" class="stretched-link"><i class="fas fa-download fa-2x"></i><br>📑 Offre PDF</a></div></div></div>
    <div class="col-md-3"><div class="card bg-light dashboard-card"><div class="card-body text-center"><a href="etats_holding.php" class="stretched-link"><i class="fas fa-chart-line fa-2x"></i><br>📊 États Holding</a></div></div></div>
</div>

<?php include('footer.php'); ?>
