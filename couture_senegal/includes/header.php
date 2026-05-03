<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> | OMEGA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { 
            --sidebar-width: 280px; 
            --omega-gold: #d4af37; 
            --omega-dark: #0f172a;
            --omega-bg: #1e293b;
        }
        body { background: #f1f5f9; display: flex; min-height: 100vh; font-family: 'Segoe UI', Roboto, sans-serif; }
        
        .sidebar { 
            width: var(--sidebar-width); 
            background: var(--omega-dark); 
            color: white; 
            position: fixed; 
            height: 100vh; 
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .omega-banner {
            background: linear-gradient(135deg, #000 0%, #1e293b 100%);
            padding: 25px 15px;
            text-align: center;
            border-bottom: 2px solid var(--omega-gold);
        }
        .omega-logo-text {
            color: var(--omega-gold);
            font-weight: 800;
            letter-spacing: 1px;
            font-size: 1.1rem;
            margin-bottom: 0;
        }
        .omega-sub {
            font-size: 0.65rem;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 2px;
        }

        .main-content { flex: 1; margin-left: var(--sidebar-width); padding: 40px; }
        
        .nav-link { 
            color: #94a3b8; 
            padding: 12px 25px; 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            font-weight: 500;
            transition: all 0.3s;
            border-radius: 0;
        }
        .nav-link i { font-size: 1.2rem; }
        .nav-link:hover { 
            background: rgba(212, 175, 55, 0.1); 
            color: var(--omega-gold); 
        }
        .nav-link.active { 
            background: var(--omega-gold); 
            color: black !important; 
            font-weight: 600;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="omega-banner">
        <p class="omega-logo-text">OMEGA INFORMATIQUE</p>
        <p class="omega-sub">Consulting & Couture</p>
    </div>
    
    <div class="py-3">
        <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>">
            <i class="bi bi-grid-1x2-fill"></i> Tableau de bord
        </a>
        <a href="clients.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='clients.php'?'active':'' ?>">
            <i class="bi bi-people-fill"></i> Clients & Mesures
        </a>
        <a href="commandes.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='commandes.php'?'active':'' ?>">
            <i class="bi bi-scissors"></i> Commandes
        </a>
        <a href="paiements.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='paiements.php'?'active':'' ?>">
            <i class="bi bi-cash-stack"></i> Paiements
        </a>
        <a href="factures.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='factures.php'?'active':'' ?>">
            <i class="bi bi-file-earmark-ruled"></i> Factures
        </a>
        <a href="stocks.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='stocks.php'?'active':'' ?>">
            <i class="bi bi-box-seam"></i> Stocks Tissus
        </a>
        <a href="depenses.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='depenses.php'?'active':'' ?>">
            <i class="bi bi-wallet2"></i> Suivi des Charges
        </a>
        <a href="finances.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])=='finances.php'?'active':'' ?>">
            <i class="bi bi-pie-chart-fill"></i> États Financiers
        </a>
    </div>
</div>
<div class="main-content">
    <?php if ($f = getFlash()): ?>
        <div class="alert alert-<?= $f['type'] ?> border-0 shadow-sm alert-dismissible fade show">
            <?= $f['msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
