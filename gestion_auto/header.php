<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .navbar-omega { 
            background: #0f172a; 
            border-bottom: 3px solid #D4AF37; 
            padding: 15px 0;
        }
        .navbar-brand-omega { 
            color: white !important; 
            font-weight: 800; 
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .navbar-brand-omega span { color: #D4AF37; }
        .nav-link-omega { color: rgba(255,255,255,0.8) !important; font-weight: 500; }
        .nav-link-omega:hover { color: #D4AF37 !important; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-omega shadow-sm">
    <div class="container">
        <a class="navbar-brand navbar-brand-omega" href="index.php">
            <i class="bi bi-cpu-fill me-2"></i>OMEGA <span>CONSULTING</span>
        </a>
        <button class="navbar-toggler border-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <i class="bi bi-list text-white"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link nav-link-omega px-3" href="index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link nav-link-omega px-3" href="vehicules.php">Parc Auto</a></li>
                <li class="nav-item"><a class="nav-link nav-link-omega px-3" href="locations.php">Locations</a></li>
                <li class="nav-item"><a class="nav-link nav-link-omega px-3" href="ventes.php">Ventes</a></li>
                <li class="nav-item ms-lg-3">
                    <span class="badge bg-warning text-dark fw-bold">GESTION AUTOMOBILE</span>
                </li>
            </ul>
        </div>
    </div>
</nav>
