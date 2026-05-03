<?php 
require 'includes/db.php'; 
$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HOTEL OMEGA | Luxury & Comfort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; }
        .navbar { background: #1a1a1a !important; border-bottom: 3px solid #d4af37; }
        .nav-link { color: #fff !important; font-size: 0.9rem; }
        .hero-hotel { height: 400px; object-fit: cover; }
        .promo-banner { background: #d4af37; color: #fff; padding: 10px; text-align: center; font-weight: bold; letter-spacing: 2px; }
        .app-content { margin-top: 30px; }
    </style>
</head>
<body>

    <div class="promo-banner text-uppercase small">Bienvenue à l'Hôtel OMEGA - L'Excellence à Dakar</div>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="?page=dashboard">HOTEL <span style="color:#d4af37">OMEGA</span></a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="?page=dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="?page=reservations">Réservations</a></li>
                    <li class="nav-item"><a class="nav-link" href="?page=personnel">Personnel</a></li>
                    <li class="nav-item"><a class="nav-link" href="?page=paies">Paies</a></li>
                    <li class="nav-item"><a class="nav-link" href="?page=charges">Charges</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if($page == 'dashboard'): ?>
    <div id="hotelCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner shadow">
            <div class="carousel-item active">
                <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?q=80&w=1470" class="d-block w-100 hero-hotel" alt="Luxe">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                    <h2 class="display-6 fw-bold">Suites de Prestige</h2>
                    <p>Un confort inégalé au cœur de la capitale.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?q=80&w=1480" class="d-block w-100 hero-hotel" alt="Spa">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                    <h2 class="display-6 fw-bold">Espace Bien-être</h2>
                    <p>Détendez-vous dans notre Spa & Piscine panoramique.</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#hotelCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#hotelCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
    <?php endif; ?>

    <main class="container app-content">
        <?php 
        if(file_exists("pages/$page.php")) include "pages/$page.php"; 
        else echo "<div class='alert alert-info shadow'>Module en cours d'initialisation...</div>";
        ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
