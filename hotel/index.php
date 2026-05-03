<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMEGA Hôtel - Gestion Hôtelière</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3');
            background-size: cover;
            background-position: center;
            height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
        }
        .feature-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s;
            height: 100%;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255,255,255,0.2);
        }
        .btn-omega {
            background: #e94560;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 30px;
            transition: all 0.3s;
        }
        .btn-omega:hover {
            background: #ff6b6b;
            transform: scale(1.05);
        }
        .omega-footer {
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(10px);
            padding: 30px 0;
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-hotel me-2"></i>OMEGA Hôtel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="chambres/liste.php">Chambres</a></li>
                    <li class="nav-item"><a class="nav-link" href="reservations/liste.php">Réservations</a></li>
                    <li class="nav-item"><a class="nav-link" href="clients/liste.php">Clients</a></li>
                    <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container">
            <h1 class="display-3 fw-bold">OMEGA Hôtel</h1>
            <p class="lead">Gestion hôtelière de haute qualité</p>
            <a href="reservations/ajouter.php" class="btn btn-omega btn-lg mt-3"><i class="fas fa-calendar-check me-2"></i>Réserver maintenant</a>
        </div>
    </div>

    <div class="container py-5">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fas fa-bed fa-3x mb-3" style="color: #e94560;"></i>
                    <h3>Chambres Confortables</h3>
                    <p>Des chambres modernes et équipées pour votre confort</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fas fa-utensils fa-3x mb-3" style="color: #e94560;"></i>
                    <h3>Restaurant Gastronomique</h3>
                    <p>Une cuisine raffinée avec des produits locaux</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <i class="fas fa-concierge-bell fa-3x mb-3" style="color: #e94560;"></i>
                    <h3>Service Premium</h3>
                    <p>Un service personnalisé 24h/24</p>
                </div>
            </div>
        </div>
    </div>

    <div class="omega-footer">
        <div class="container text-center">
            <p>&copy; 2026 OMEGA Hôtel - Une solution OMEGA INFORMATIQUE CONSULTING</p>
            <p class="small">Développé par Mohamed Siby - Consultant en Informatique</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
