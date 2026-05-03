<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conseil Départemental de Velingara</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary: #0B3B2F; --gold: #C6A43F; --islamic: #2E7D32; }
        .navbar { background: var(--primary); box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: bold; font-size: 1.3rem; }
        .navbar-brand small { font-size: 0.7rem; color: var(--gold); }
        .btn-gold { background: var(--gold); color: #000; border: none; }
        .btn-gold:hover { background: #b8942e; color: #000; }
        footer { background: #0a2a1f; color: white; margin-top: 50px; }
        .hero { background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1516026672322-bc52d61a55d5?w=1200'); background-size: cover; background-position: center; }
        .badge-gamou { background: var(--islamic); color: white; }
        .card-gamou { border-left: 5px solid var(--islamic); }
        .nav-link:hover { color: var(--gold) !important; transform: translateY(-2px); transition: all 0.3s; }
        .dropdown-menu { background: var(--primary); }
        .dropdown-item { color: white; }
        .dropdown-item:hover { background: var(--gold); color: #000; }
    </style>
</head>
<body>
    <div class="bg-dark text-white text-center py-2 small">
        <i class="fas fa-map-marker-alt"></i> Carrefour frontalier : Sénégal - Guinée Conakry - Guinée Bissau - Mali
        <span class="mx-3">|</span>
        <i class="fas fa-phone"></i> Directeur: 77 654 28 03
    </div>
    
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-building"></i> Conseil Départemental<br>
                <small>Velingara - Sénégal</small>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="/"><i class="fas fa-home"></i> Accueil</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">🌾 Atouts</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/modules/agriculture/barrage.php">🌊 Barrage Anambe</a></li>
                            <li><a class="dropdown-item" href="/modules/agriculture/agropastoral.php">🐄 Agro-Sylvo-Pastoral</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="/modules/finance/credit.php"><i class="fas fa-hand-holding-usd"></i> Micro-crédits</a></li>
                    <li class="nav-item"><a class="nav-link" href="/modules/sante_sport/infra.php"><i class="fas fa-hospital"></i> Santé & Sport</a></li>
                    <li class="nav-item"><a class="nav-link" href="/modules/culture/festival.php"><i class="fas fa-mask"></i> Culture</a></li>
                    <li class="nav-item"><a class="nav-link btn-gamou rounded-pill px-3" href="/modules/religion/gamou.php" style="background:#2E7D32; color:white;">
                        <i class="fas fa-mosque"></i> 🕌 Gamou Daakaa
                    </a></li>
                    <li class="nav-item"><a class="nav-link" href="/modules/partenariats/cooperation.php"><i class="fas fa-handshake"></i> Coopération</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <main>
