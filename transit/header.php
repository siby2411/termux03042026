<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>OMEGA TRANSIT - Solution de Transit Maritime & Aéroportuaire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        
        /* Header Professionnel */
        .header-top {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: white;
            padding: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .banner-principal {
            background: linear-gradient(135deg, #001f3f, #004080);
            padding: 40px 0;
            position: relative;
            overflow: hidden;
        }
        
        .banner-principal::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: moveBackground 20s linear infinite;
        }
        
        @keyframes moveBackground {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        .logo-omega {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: -1px;
        }
        
        .logo-omega span {
            color: #ffd700;
        }
        
        .sous-titre {
            font-size: 0.9rem;
            opacity: 0.9;
            letter-spacing: 2px;
        }
        
        /* Navigation Moderne */
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            padding: 0;
        }
        
        .navbar-nav {
            margin: 0 auto;
        }
        
        .nav-item {
            position: relative;
        }
        
        .nav-link-custom {
            color: #333;
            font-weight: 500;
            padding: 15px 20px;
            display: inline-block;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .nav-link-custom:hover {
            color: #004080;
            transform: translateY(-2px);
        }
        
        .nav-link-custom.active {
            color: #004080;
            border-bottom: 3px solid #ffd700;
        }
        
        /* Cards Modernes */
        .card-modern {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }
        
        .card-modern:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        /* Dashboard Stats */
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        /* Boutons */
        .btn-omega {
            background: linear-gradient(135deg, #001f3f, #004080);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-omega:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,31,63,0.3);
            color: white;
        }
        
        /* Footer */
        .footer-omega {
            background: #001f3f;
            color: white;
            padding: 40px 0 20px;
            margin-top: 50px;
        }
        
        .footer-omega a {
            color: #ffd700;
            text-decoration: none;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .logo-omega {
                font-size: 1.8rem;
            }
            
            .nav-link-custom {
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Header Top -->
    <div class="header-top">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <i class="fas fa-phone-alt me-2"></i> +221 33 123 45 67
                    <span class="ms-4"><i class="fas fa-envelope me-2"></i> contact@omegatransit.sn</span>
                </div>
                <div class="col-md-6 text-end">
                    <i class="fas fa-clock me-2"></i> Lun-Ven: 8h - 18h | Sam: 9h - 13h
                </div>
            </div>
        </div>
    </div>
    
    <!-- Banner Principal -->
    <div class="banner-principal">
        <div class="container text-center">
            <div class="logo-omega">OMEGA <span>TRANSIT</span></div>
            <div class="sous-titre mt-2">Solution Intégrée de Transit Maritime & Aéroportuaire</div>
            <div class="mt-3">
                <span class="badge bg-warning text-dark me-2">ISO 9001:2024</span>
                <span class="badge bg-info">Certifié OMD</span>
            </div>
            <div class="mt-3">
                <small>OMEGA INFORMATIQUE CONSULTING - Leader en Solutions Digitales</small>
            </div>
        </div>
    </div>
    
    <!-- Navigation -->
    <nav class="navbar-custom">
        <div class="container">
            <div class="navbar-nav">
                <a href="index.php" class="nav-link-custom <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                <a href="clients.php" class="nav-link-custom">Clients</a>
                <a href="declarations.php" class="nav-link-custom">Déclarations</a>
                <a href="factures.php" class="nav-link-custom">Facturation</a>
                <a href="tracking.php" class="nav-link-custom">Tracking</a>
                <a href="prospects.php" class="nav-link-custom">Prospects CRM</a>
                <a href="rapports.php" class="nav-link-custom">États Financiers</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="nav-link-custom text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
