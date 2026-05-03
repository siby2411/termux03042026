<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMEGA ERP - Gestion École</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --omega-blue: #1a2a6c; --omega-gold: #D4AF37; }
        .navbar-omega { background: var(--omega-blue); border-bottom: 3px solid var(--omega-gold); }
        .nav-link { color: rgba(255,255,255,0.8) !important; font-weight: 500; }
        .nav-link:hover { color: var(--omega-gold) !important; }
        footer { background: #f8f9fa; border-top: 1px solid #dee2e6; padding: 20px 0; margin-top: 50px; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-omega shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-mortarboard-fill me-2"></i>OMEGA ERP <span style="color:var(--omega-gold)">GOLD</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Tableau de Bord</a></li>
                <li class="nav-item"><a class="nav-link" href="crud_etudiants.php">Étudiants</a></li>
                <li class="nav-item"><a class="nav-link" href="crud_paiements.php">Scolarité</a></li>
                <li class="nav-item"><a class="nav-link text-danger fw-bold" href="logout.php"><i class="bi bi-power"></i></a></li>
            </ul>
        </div>
    </div>
</nav>
