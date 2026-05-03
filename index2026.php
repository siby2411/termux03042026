<?php
// index.php - Portail Business Suite Pro (Adapté apachewsl2026)
$host = "http://localhost";

$apps = [
    "Gestion Auto" => [
        "url" => $host . ":8083/",
        "logo" => "🚗",
        "color" => "#e74c3c",
        "description" => "Gestion complète de véhicules, ventes et locations",
        "category" => "business"
    ],
    "Restauration" => [
        "url" => $host . ":8081/", 
        "logo" => "🍽️",
        "color" => "#27ae60",
        "description" => "Gestion de restaurant et service en salle",
        "category" => "business"
    ],
    "Pressing" => [
        "url" => $host . ":8082/",
        "logo" => "👔",
        "color" => "#3498db",
        "description" => "Gestion de pressing et blanchisserie",
        "category" => "business"
    ],
    "E-commerce" => [
        "url" => $host . ":8088/",
        "logo" => "🛒",
        "color" => "#f39c12",
        "description" => "Plateforme de vente en ligne complète",
        "category" => "commerce"
    ],
    "Gestion École" => [
        "url" => $host . ":8087/", 
        "logo" => "🎓", 
        "color" => "#1abc9c",
        "description" => "Gestion scolaire et administrative",
        "category" => "education"
    ],
    "PME" => [
        "url" => $host . ":8089/",
        "logo" => "🏢",
        "color" => "#34495e",
        "description" => "Solution complète pour petites et moyennes entreprises",
        "category" => "business"
    ],
    "Report (SYSCOHADA)" => [
        "url" => $host . ":8085/login.php",
        "logo" => "🧾",
        "color" => "#8e44ad",
        "description" => "Comptabilité, états financiers et rapport SYSCOHADA.",
        "category" => "finance"
    ],
    "Banque & Trésorerie" => [
        "url" => $host . ":8084/login.php",
        "logo" => "🏦",
        "color" => "#2ecc71",
        "description" => "Gestion des comptes bancaires et flux de trésorerie.",
        "category" => "finance"
    ],
    "Clinique" => [
        "url" => $host . ":8086/",
        "logo" => "🏥",
        "color" => "#e67e22",
        "description" => "Gestion médicale et suivi des patients.",
        "category" => "business"
    ]
];

$categories = [
    "business" => ["icon" => "💼", "name" => "Business", "color" => "#2c3e50"],
    "commerce" => ["icon" => "🛍️", "name" => "Commerce", "color" => "#f39c12"],
    "education" => ["icon" => "🎓", "name" => "Éducation", "color" => "#1abc9c"],
    "finance" => ["icon" => "💰", "name" => "Finance & Compta", "color" => "#8e44ad"]
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Suite Pro - apachewsl2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #2c3e50; --accent: #e74c3c; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh; color: #2c3e50;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px; border: none;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        .glass-card:hover { transform: translateY(-5px); }
        .app-card {
            height: 100%; text-decoration: none; color: inherit;
            display: block; padding: 2rem 1.5rem; border-radius: 16px;
            position: relative; overflow: hidden;
        }
        .app-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0;
            height: 4px; background: var(--card-color);
        }
        .app-logo { font-size: 3rem; margin-bottom: 1rem; display: block; }
        .app-title { font-weight: 700; font-size: 1.2rem; color: var(--card-color); }
        .badge-category {
            position: absolute; top: 1rem; left: 1rem;
            background: rgba(0, 0, 0, 0.05); color: var(--card-color);
            padding: 0.25rem 0.5rem; border-radius: 10px; font-size: 0.7rem;
        }
        .header-section { text-align: center; padding: 3rem 0; color: white; }
        .category-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.5rem 1rem; border-radius: 25px;
            background: white; color: var(--primary); font-weight: 600;
            margin: 0.25rem; cursor: pointer; transition: 0.3s;
        }
        .category-badge.active { background: #e74c3c; color: white; }
    </style>
</head>
<body>
    <div class="header-section text-center">
        <h1 class="fw-bold display-4">Business Suite Pro</h1>
        <p class="lead">Gestion Centralisée - Environnement apachewsl2026</p>
    </div>

    <div class="container py-4">
        <div class="bg-white p-3 rounded-4 shadow-sm mb-5 text-center">
            <h5 class="mb-3">Filtrer par Secteur</h5>
            <div class="category-filter">
                <div class="category-badge active" data-category="all"><span>📦</span> Tout</div>
                <?php foreach($categories as $id => $cat): ?>
                    <div class="category-badge" data-category="<?= $id ?>">
                        <span><?= $cat['icon'] ?></span> <?= $cat['name'] ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="row g-4" id="apps-container">
            <?php foreach($apps as $name => $app): ?>
                <div class="col-xl-4 col-md-6 app-item" data-category="<?= $app['category'] ?>">
                    <div class="glass-card h-100">
                        <a href="<?= $app['url'] ?>" target="_blank" class="app-card" style="--card-color: <?= $app['color'] ?>">
                            <span class="badge-category"><?= $categories[$app['category']]['icon'] ?></span>
                            <div class="text-center">
                                <span class="app-logo"><?= $app['logo'] ?></span>
                                <h3 class="app-title"><?= $name ?></h3>
                                <p class="small text-muted"><?= $app['description'] ?></p>
                                <div class="mt-2"><span class="btn btn-sm text-white" style="background:<?= $app['color'] ?>">Lancer l'App</span></div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="text-center py-5 text-white-50">
        <p>&copy; 2026 Business Suite Pro - Serveur PHP/MariaDB Termux</p>
    </footer>

    <script>
        document.querySelectorAll('.category-badge').forEach(badge => {
            badge.addEventListener('click', function() {
                document.querySelectorAll('.category-badge').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const cat = this.dataset.category;
                document.querySelectorAll('.app-item').forEach(item => {
                    item.style.display = (cat === 'all' || item.dataset.category === cat) ? 'block' : 'none';
                });
            });
        });
    </script>
</body>
</html>
