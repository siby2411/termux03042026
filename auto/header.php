<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --omega-gold: #D4AF37; --omega-dark: #0f172a; }
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .omega-banner {
            background: linear-gradient(135deg, var(--omega-dark) 0%, #1e293b 100%);
            color: white;
            padding: 20px 0;
            border-bottom: 4px solid var(--omega-gold);
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .omega-logo { font-weight: 800; letter-spacing: 2px; color: var(--omega-gold); }
        .sub-text { font-size: 0.85rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 1px; }
        .nav-link { color: #f8fafc !important; font-weight: 500; transition: 0.3s; }
        .nav-link:hover { color: var(--omega-gold) !important; }
        .btn-gold { background-color: var(--omega-gold); color: var(--omega-dark); font-weight: bold; border: none; }
        .btn-gold:hover { background-color: #b8962e; color: white; }
    </style>
</head>
<body>
    <div class="omega-banner mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <h2 class="omega-logo mb-0">OMEGA INFORMATIQUE <span style="color:white;">CONSULTING</span></h2>
                <div class="sub-text"><i class="fas fa-car-side me-2"></i>Expertise & Gestion Automobile - Dakar, Sénégal</div>
            </div>
            <nav class="d-none d-md-block">
                <a href="index.php" class="nav-link d-inline me-3"><i class="fas fa-home"></i> Accueil</a>
                <a href="dashboard.php" class="nav-link d-inline"><i class="fas fa-user-shield"></i> Admin</a>
            </nav>
        </div>
    </div>
