<?php 
require 'includes/db.php'; 
$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMEGA IMMO | Excellence Immobilière & Consulting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        :root { --gold: #D4AF37; --dark-bg: #121212; }
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        
        /* Navigation Premium */
        .navbar { background: rgba(255,255,255,0.95) !important; backdrop-filter: blur(10px); border-bottom: 2px solid var(--gold); }
        .nav-link { font-weight: 500; color: #333 !important; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 1px; }
        .nav-link.active { color: var(--gold) !important; font-weight: 700; }
        
        /* Hero Section */
        .hero {
            height: 60vh;
            background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), 
                        url('https://images.unsplash.com/photo-1512917774080-9991f1c4c750?q=80&w=1470&auto=format&fit=crop');
            background-size: cover; background-position: center;
            display: flex; align-items: center; justify-content: center; color: white;
        }

        /* Section Marketing */
        .marketing-intro { background: white; padding: 60px 0; border-bottom: 1px solid #eee; }
        .feature-icon { font-size: 2.5rem; color: var(--gold); margin-bottom: 1rem; }

        /* Conteneur Dynamique */
        .app-content { margin-top: -40px; position: relative; z-index: 100; padding-bottom: 60px; }
        
        /* Bouton Theme */
        .theme-switch { position: fixed; bottom: 20px; right: 20px; z-index: 1000; border-radius: 50%; width: 50px; height: 50px; background: var(--gold); border: none; color: white; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="?page=dashboard">
                <span class="fw-bold fs-3">OMEGA</span><span class="fs-3 text-warning ms-1">IMMO</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#omegaNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="omegaNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link <?= $page=='dashboard'?'active':'' ?>" href="?page=dashboard">📊 Dashboard</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">🏠 Parc Immobilier</a>
                        <ul class="dropdown-menu border-0 shadow">
                            <li><a class="dropdown-item" href="?page=immeubles">Voir les Biens</a></li>
                            <li><a class="dropdown-item" href="?page=ajouter_immeuble">Ajouter un Immeuble</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">👥 Clients</a>
                        <ul class="dropdown-menu border-0 shadow">
                            <li><a class="dropdown-item" href="?page=prospects">Liste des Prospects</a></li>
                            <li><a class="dropdown-item" href="?page=visites">Planning Visites</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link <?= $page=='finances'?'active':'' ?>" href="?page=finances">💰 Finances</a></li>
                    <li class="nav-item"><a class="nav-link <?= $page=='rapports'?'active':'' ?>" href="?page=rapports text-warning">📈 Rapports</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if($page == 'dashboard'): ?>
    <section class="hero text-center">
        <div class="container">
            <h1 class="display-3 fw-bold mb-3" style="font-family: 'Playfair Display';">L'Art de Vivre à Dakar</h1>
            <p class="lead mb-4">Expertise Immobilière & Consulting Informatique de pointe.</p>
            <a href="#app" class="btn btn-warning btn-lg px-5 rounded-pill text-white fw-bold shadow">Gérer mon Agence</a>
        </div>
    </section>

    <section class="marketing-intro">
        <div class="container">
            <div class="row align-items-center text-center">
                <div class="col-md-4 mb-4">
                    <i class="bi bi-shield-check feature-icon"></i>
                    <h5>Sécurité & Rigueur</h5>
                    <p class="text-muted small">Chaque transaction est vérifiée par notre protocole OMEGA pour une totale sérénité.</p>
                </div>
                <div class="col-md-4 mb-4 border-start border-end">
                    <i class="bi bi-cpu feature-icon"></i>
                    <h5>Digitalisation 2.0</h5>
                    <p class="text-muted small">Nous utilisons l'IA et le Cloud pour optimiser la visibilité de vos biens immobiliers.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <i class="bi bi-graph-up-arrow feature-icon"></i>
                    <h5>Rentabilité Maximisée</h5>
                    <p class="text-muted small">Notre approche conseil garantit le meilleur rendement locatif sur le marché dakarois.</p>
                </div>
            </div>
            <div class="text-center mt-4">
                <h3 class="fw-light" style="font-family: 'Playfair Display';">"Votre confiance est notre seul actif."</h3>
                <p class="text-warning fw-bold small text-uppercase mb-0">Omega Informatique Consulting</p>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <main class="container app-content" id="app">
        <?php 
        if(file_exists("pages/$page.php")) include "pages/$page.php"; 
        else echo "<div class='card p-5 text-center shadow-sm'><h3>Module en construction</h3><p>Accès réservé aux administrateurs OMEGA.</p></div>";
        ?>
    </main>

    <footer class="bg-dark text-white py-5 mt-5">
        <div class="container text-center">
            <p class="mb-2 fw-bold text-warning">OMEGA INFORMATIQUE CONSULTING</p>
            <p class="small text-muted mb-0">Siège Social : Dakar Plateau, Sénégal</p>
            <p class="small text-muted mb-0">Expertise IT & Real Estate Management System</p>
        </div>
    </footer>

    <button class="theme-switch" id="themeBtn"><i class="bi bi-moon"></i></button>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const btn = document.getElementById('themeBtn');
        const html = document.documentElement;
        btn.onclick = () => {
            const current = html.getAttribute('data-bs-theme');
            const target = current === 'light' ? 'dark' : 'light';
            html.setAttribute('data-bs-theme', target);
            btn.innerHTML = target === 'light' ? '<i class="bi bi-moon"></i>' : '<i class="bi bi-sun"></i>';
        };
    </script>
</body>
</html>
