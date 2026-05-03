<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dieynaba GP Holding - Fret & Mode Sénégal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; display: flex; flex-direction: column; min-height: 100vh; }
        main { flex: 1; padding: 20px; }
        .top-bar { background: #0a2b44; color: white; padding: 8px 20px; display: flex; justify-content: space-between; flex-wrap: wrap; font-size: 0.85rem; }
        .logo-area { background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460); padding: 20px 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; border-bottom: 3px solid #ff8c00; }
        .logo-area img { max-height: 100px; width: auto; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .holding-title { font-family: 'Playfair Display', serif; text-align: right; }
        .holding-title .dieynaba { font-size: 2.5rem; font-weight: 900; letter-spacing: 2px; color: #ffaa33; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
        .holding-title .dieynaba .big-d { font-size: 3.2rem; display: inline-block; color: #ff8c00; }
        .holding-title .gp { font-size: 1.8rem; font-weight: 700; color: #e0e0e0; margin: 0 5px; }
        .holding-title .holding { font-size: 1.4rem; font-weight: 600; color: #ffaa66; letter-spacing: 3px; }
        .holding-title .slogan { font-size: 0.75rem; color: #ccc; margin-top: 5px; }
        nav { background: #e65c00; display: flex; flex-wrap: wrap; justify-content: center; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        nav a, nav .dropdown > a { color: white; padding: 12px 18px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 500; transition: all 0.2s; cursor: pointer; }
        nav a:hover, nav .dropdown:hover > a { background: #cc5200; transform: translateY(-2px); }
        .dropdown { position: relative; display: inline-block; }
        .dropdown-content { display: none; position: absolute; background-color: #e65c00; min-width: 220px; z-index: 1; border-radius: 0 0 8px 8px; }
        .dropdown:hover .dropdown-content { display: block; }
        .dropdown-content a { display: block; padding: 10px 16px; font-size: 0.8rem; }
        .dropdown-content a:hover { background: #cc5200; transform: none; }
        footer { background: #0a2b44; color: #ccc; text-align: center; padding: 15px; margin-top: 30px; font-size: 0.8rem; }
        @media (max-width: 768px) {
            .holding-title .dieynaba { font-size: 1.5rem; }
            .holding-title .dieynaba .big-d { font-size: 2rem; }
            .logo-area img { max-height: 60px; }
            nav a, nav .dropdown > a { padding: 8px 12px; font-size: 0.7rem; }
        }
    </style>
</head>
<body>
<div class="top-bar">
    <span><i class="fas fa-phone-alt"></i> Paris : +33 7 58 68 63 48 | Dakar : +221 33 888 88 88</span>
    <span><i class="fas fa-plane-departure"></i> Vols hebdomadaires Paris ↔ Dakar</span>
    <span><i class="fas fa-map-marker-alt"></i> Holding basée à Dakar & Paris</span>
</div>
<div class="logo-area">
    <img src="logo.jpg" alt="Dieynaba GP Holding">
    <div class="holding-title">
        <div class="dieynaba"><span class="big-d">D</span>ieynaba</div>
        <span class="gp">G<span style="font-size:1.2rem;">P</span></span>
        <span class="holding">HOLDING</span>
        <div class="slogan">✈️ Transport international • Mode • Produits du terroir • Joaillerie • Négoce</div>
    </div>
</div>
<nav>
    <!-- Accueil -->
    <a href="index.php"><i class="fas fa-home"></i> Accueil</a>

    <!-- Colis -->
    <div class="dropdown">
        <a href="#"><i class="fas fa-box"></i> Colis ▾</a>
        <div class="dropdown-content">
            <a href="creer_colis_holding.php"><i class="fas fa-exchange-alt"></i> Nouveau colis (bi-directionnel)</a>
            <a href="creer_colis.php"><i class="fas fa-plus-circle"></i> Créer un colis</a>
            <a href="suivi.php"><i class="fas fa-search-location"></i> Suivi colis</a>
            <a href="admin_colis.php"><i class="fas fa-tasks"></i> Admin colis</a>
        </div>
    </div>

    <!-- Boutiques (4 boutiques) -->
    <div class="dropdown">
        <a href="#"><i class="fas fa-store"></i> Boutiques ▾</a>
        <div class="dropdown-content">
            <a href="produits.php"><i class="fas fa-shopping-basket"></i> 🛒 Épicerie sénégalaise</a>
            <a href="vetements.php"><i class="fas fa-tshirt"></i> 👗 Mode traditionnelle</a>
    <a href="mode.php" class="btn btn-outline-secondary btn-sm my-1"><i class="fas fa-shoe-prints"></i> 👠 Mode Luxe</a>
            <a href="bijouterie.php"><i class="fas fa-gem"></i> 💎 Joaillerie & Bijouterie</a>
            <a href="negoce.php"><i class="fas fa-laptop"></i> 🏪 Négoce (High-Tech/Mobilier)</a>
        </div>
    </div>

    <!-- Finance -->
    <div class="dropdown">
        <a href="#"><i class="fas fa-chart-line"></i> Finance ▾</a>
        <div class="dropdown-content">
            <a href="etats_financiers.php"><i class="fas fa-chart-pie"></i> États financiers fret</a>
            <a href="etats_holding.php"><i class="fas fa-chart-line"></i> États consolidés Holding</a>
            <a href="gestion_charges.php"><i class="fas fa-coins"></i> Gestion des charges</a>
            <a href="stats.php"><i class="fas fa-chart-bar"></i> Statistiques</a>
        </div>
    </div>

    <!-- Clients -->
    <div class="dropdown">
        <a href="#"><i class="fas fa-users"></i> Clients ▾</a>
        <div class="dropdown-content">
            <a href="gestion_clients.php"><i class="fas fa-address-book"></i> Clients fret</a>
            <a href="clients_vetements.php"><i class="fas fa-user-friends"></i> Clients mode</a>
            <a href="admin_prospects.php"><i class="fas fa-address-card"></i> Prospects Sénégal</a>
                <a href="agenda.php"><i class="fas fa-calendar-alt"></i> Agenda prospect</a>
                <a href="agenda.php"><i class="fas fa-calendar-alt"></i> Agenda prospect</a>
        </div>
    </div>

    <!-- WhatsApp -->
    <div class="dropdown">
        <a href="#"><i class="fab fa-whatsapp"></i> WhatsApp ▾</a>
        <div class="dropdown-content">
            <a href="api_whatsapp.php?test"><i class="fas fa-vial"></i> Tester WhatsApp</a>
            <a href="admin_colis.php"><i class="fas fa-qrcode"></i> Envoyer QR colis</a>
            <a href="gestion_qrcodes.php"><i class="fas fa-image"></i> Gestion QR codes</a>
        </div>
    </div>

    <!-- Holding -->
    <div class="dropdown">
        <a href="#"><i class="fas fa-globe"></i> Holding ▾</a>
        <div class="dropdown-content">
            <a href="requetes_livraisons.php"><i class="fas fa-search"></i> Requêtes livraisons</a>
            <a href="gestion_geolocalisation.php"><i class="fas fa-map-marker-alt"></i> Géolocalisation</a>
            <a href="vols.php"><i class="fas fa-plane"></i> Vols</a>
            <a href="coordonnees_gps.php"><i class="fas fa-map-marker-alt"></i> Points GPS</a>
            <a href="agenda.php"><i class="fas fa-calendar-alt"></i> 📅 Agenda prospection</a>
            <a href="agenda.php"><i class="fas fa-calendar-alt"></i> 📆 Agenda prospection</a>
            <a href="offres_services.php"><i class="fas fa-file-pdf"></i> Offres de services</a>
            <a href="send_colis_notification.php"><i class="fab fa-whatsapp"></i> 📨 Envoyer notification colis</a>
            <a href="test_whatsapp_direct.php"><i class="fab fa-whatsapp"></i> 📱 Test WhatsApp</a>
        </div>
    </div>

    <!-- Admin (visible uniquement connecté) -->
    <?php if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true): ?>
        <div class="dropdown">
            <a href="#"><i class="fas fa-cog"></i> Administration ▾</a>
            <div class="dropdown-content">
                <a href="admin_colis.php"><i class="fas fa-boxes"></i> Admin colis</a>
                <a href="admin_produits.php"><i class="fas fa-apple-alt"></i> Admin épicerie</a>
                <a href="admin_vetements.php"><i class="fas fa-tshirt"></i> Admin mode</a>
        <a href="admin_mode.php"><i class="fas fa-shoe-prints"></i> Admin Mode Luxe</a>
        <a href="admin_fruits.php"><i class="fas fa-leaf"></i> Admin Fruits</a>
        <a href="admin_sponsors.php"><i class="fas fa-star"></i> Admin Sponsors</a>
                <a href="admin_bijouterie.php"><i class="fas fa-gem"></i> Admin bijouterie</a>
                <a href="admin_negoce.php"><i class="fas fa-laptop"></i> Admin négoce</a>
                <a href="gestion_clients.php"><i class="fas fa-address-book"></i> Clients fret</a>
                <a href="clients_vetements.php"><i class="fas fa-user-friends"></i> Clients mode</a>
                <a href="gestion_charges.php"><i class="fas fa-coins"></i> Gestion charges</a>
            </div>
        </div>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    <?php else: ?>
        <a href="admin_colis.php"><i class="fas fa-lock"></i> Administration</a>
    <?php endif; ?>
</nav>
<main class="container">
