<?php
// index.php - Portail Complet Applications Professionnel
$apps = [
    "Gestion Auto" => [
        "url" => "gestion_auto/",
        "logo" => "🚗",
        "color" => "#e74c3c",
        "description" => "Gestion complète de véhicules, ventes et locations",
        "category" => "business"
    ],
    "Restauration" => [
        "url" => "restau/", 
        "logo" => "🍽️",
        "color" => "#27ae60",
        "description" => "Gestion de restaurant et service en salle",
        "category" => "business"
    ],
    "Pressing" => [
        "url" => "pressing/",
        "logo" => "👔",
        "color" => "#3498db",
        "description" => "Gestion de pressing et blanchisserie",
        "category" => "business"
    ],
    "E-commerce" => [
        "url" => "ecommerce/",
        "logo" => "🛒",
        "color" => "#f39c12",
        "description" => "Plateforme de vente en ligne complète",
        "category" => "commerce"
    ],
    "Gestion Commerciale" => [
        "url" => "gestion_commerciale/",
        "logo" => "📊",
        "color" => "#9b59b6",
        "description" => "Gestion commerciale et relation clients",
        "category" => "business"
    ],
    "Gestion École" => [
        "url" => "gestion_ecole/",
        "logo" => "🎓", 
        "color" => "#1abc9c",
        "description" => "Gestion scolaire et administrative",
        "category" => "education"
    ],
    "Gestion Pointage" => [
        "url" => "gestion_pointage/",
        "logo" => "⏱️",
        "color" => "#e67e22",
        "description" => "Gestion des présences et pointage employés",
        "category" => "rh"
    ],
    "PME" => [
        "url" => "pme/",
        "logo" => "🏢",
        "color" => "#34495e",
        "description" => "Solution complète pour petites et moyennes entreprises",
        "category" => "business"
    ],
    "Blog" => [
        "url" => "blog/",
        "logo" => "📝",
        "color" => "#e84393",
        "description" => "Plateforme de blogging et contenu",
        "category" => "communication"
    ],
    "Auto" => [
        "url" => "auto/",
        "logo" => "⚙️",
        "color" => "#7f8c8d",
        "description" => "Application automobile technique",
        "category" => "technical"
    ],
    "WordPress" => [
        "url" => "wordpress/",
        "logo" => "🌐",
        "color" => "#21759b",
        "description" => "Site WordPress et gestion de contenu",
        "category" => "web"
    ],
    "HTML" => [
        "url" => "html/",
        "logo" => "📄",
        "color" => "#e44d26",
        "description" => "Pages HTML statiques et démonstrations",
        "category" => "web"
    ]
];

// Catégories avec icônes
$categories = [
    "business" => ["icon" => "💼", "name" => "Business", "color" => "#2c3e50"],
    "commerce" => ["icon" => "🛍️", "name" => "Commerce", "color" => "#f39c12"],
    "education" => ["icon" => "🎓", "name" => "Éducation", "color" => "#1abc9c"],
    "rh" => ["icon" => "👥", "name" => "Ressources Humaines", "color" => "#e67e22"],
    "communication" => ["icon" => "📢", "name" => "Communication", "color" => "#e84393"],
    "web" => ["icon" => "🌐", "name" => "Web", "color" => "#3498db"],
    "technical" => ["icon" => "⚙️", "name" => "Technique", "color" => "#7f8c8d"]
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Suite Pro - Portail Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --accent: #e74c3c;
            --success: #27ae60;
            --info: #3498db;
            --warning: #f39c12;
            --light: #ecf0f1;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #2c3e50;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .app-logo {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
            transition: transform 0.3s ease;
        }
        
        .glass-card:hover .app-logo {
            transform: scale(1.1);
        }
        
        .app-card {
            height: 100%;
            text-decoration: none;
            color: inherit;
            display: block;
            padding: 2rem 1.5rem;
            border-radius: 16px;
            position: relative;
            overflow: hidden;
        }
        
        .app-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--card-color);
        }
        
        .app-title {
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--card-color);
        }
        
        .app-description {
            font-size: 0.9rem;
            color: #7f8c8d;
            line-height: 1.5;
        }
        
        .badge-popular {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--accent);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-category {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: rgba(0, 0, 0, 0.1);
            color: var(--card-color);
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .header-section {
            text-align: center;
            padding: 3rem 0;
            color: white;
        }
        
        .header-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .header-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .stats-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 1.5rem;
            color: white;
            text-align: center;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--primary);
        }
        
        .section-title {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .category-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary);
            font-weight: 600;
            margin: 0.25rem;
            transition: all 0.3s ease;
        }
        
        .category-badge:hover {
            transform: scale(1.05);
            text-decoration: none;
            color: var(--primary);
        }
        
        .category-badge.active {
            background: var(--primary);
            color: white;
        }
        
        .filter-section {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .app-counter {
            font-size: 0.9rem;
            color: #7f8c8d;
            text-align: center;
            margin-top: 1rem;
        }
        
        @media (max-width: 768px) {
            .header-title {
                font-size: 2rem;
            }
            
            .app-logo {
                font-size: 2.5rem;
            }
            
            .category-badge {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header-section">
        <div class="container">
            <h1 class="header-title">Business Suite Pro</h1>
            <p class="header-subtitle">Ecosystème complet de solutions professionnelles</p>
            
            <div class="row justify-content-center mt-5">
                <div class="col-lg-10">
                    <div class="row">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stats-card">
                                <div class="stats-number"><?php echo count($apps); ?></div>
                                <div class="stats-label">Applications</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stats-card">
                                <div class="stats-number"><?php echo count($categories); ?></div>
                                <div class="stats-label">Catégories</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stats-card">
                                <div class="stats-number">24/7</div>
                                <div class="stats-label">Disponibilité</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="stats-card">
                                <div class="stats-number">100%</div>
                                <div class="stats-label">Cloud</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Grid -->
    <div class="container py-5">
        <div class="filter-section">
            <h3 class="text-center mb-4">Filtrer par catégorie</h3>
            <div class="text-center">
                <a href="#" class="category-badge active" data-category="all">
                    <span>📦</span> Toutes les applications
                </a>
                <?php foreach($categories as $catKey => $category): ?>
                    <a href="#" class="category-badge" data-category="<?= $catKey ?>">
                        <span><?= $category['icon'] ?></span> <?= $category['name'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <h2 class="section-title">Toutes les Applications</h2>
        
        <div class="row g-4" id="apps-container">
            <?php foreach($apps as $name => $app): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 app-item" data-category="<?= $app['category'] ?>">
                    <div class="glass-card h-100">
                        <a href="<?= $app['url'] ?>" class="app-card" style="--card-color: <?= $app['color'] ?>">
                            <?php if(in_array($name, ['Gestion Auto', 'Restauration', 'Pressing', 'E-commerce'])): ?>
                                <span class="badge-popular">POPULAIRE</span>
                            <?php endif; ?>
                            
                            <span class="badge-category"><?= $categories[$app['category']]['icon'] ?></span>
                            
                            <div class="text-center">
                                <span class="app-logo"><?= $app['logo'] ?></span>
                                <h3 class="app-title"><?= $name ?></h3>
                                <p class="app-description"><?= $app['description'] ?></p>
                                
                                <div class="mt-3">
                                    <span class="btn btn-sm" style="background: <?= $app['color'] ?>; color: white;">
                                        Ouvrir <i class="fas fa-arrow-right ms-1"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="app-counter" id="app-counter">
            Affichage de <?php echo count($apps); ?> applications
        </div>
    </div>

    <!-- Features Section -->
    <div class="container py-5">
        <div class="glass-card p-5">
            <h2 class="section-title">Une Suite Complète pour Votre Business</h2>
            
            <div class="row g-4">
                <div class="col-md-3 col-6 text-center">
                    <div class="feature-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h5>Performance</h5>
                    <p class="text-muted small">Applications optimisées pour une expérience fluide</p>
                </div>
                
                <div class="col-md-3 col-6 text-center">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5>Sécurité</h5>
                    <p class="text-muted small">Protection avancée de vos données sensibles</p>
                </div>
                
                <div class="col-md-3 col-6 text-center">
                    <div class="feature-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h5>Sync</h5>
                    <p class="text-muted small">Synchronisation en temps réel multi-appareils</p>
                </div>
                
                <div class="col-md-3 col-6 text-center">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5>Analytics</h5>
                    <p class="text-muted small">Tableaux de bord et analyses en temps réel</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 text-white">
        <div class="container">
            <p class="mb-0">&copy; 2024 Business Suite Pro. Écosystème complet de solutions.</p>
            <p class="text-white-50 small">Développé pour les professionnels exigeants</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filtrage par catégorie
        document.addEventListener('DOMContentLoaded', function() {
            const categoryBadges = document.querySelectorAll('.category-badge');
            const appItems = document.querySelectorAll('.app-item');
            const appCounter = document.getElementById('app-counter');
            
            categoryBadges.forEach(badge => {
                badge.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Active le badge cliqué
                    categoryBadges.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    const category = this.getAttribute('data-category');
                    
                    // Filtre les applications
                    let visibleCount = 0;
                    appItems.forEach(item => {
                        if (category === 'all' || item.getAttribute('data-category') === category) {
                            item.style.display = 'block';
                            visibleCount++;
                        } else {
                            item.style.display = 'none';
                        }
                    });
                    
                    // Met à jour le compteur
                    appCounter.textContent = `Affichage de ${visibleCount} application${visibleCount > 1 ? 's' : ''}`;
                });
            });
            
            // Animation au scroll
            const cards = document.querySelectorAll('.glass-card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, { threshold: 0.1 });
            
            cards.forEach(card => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>
