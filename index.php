<?php                     // index.php - Portail Business Suite Pro (Adapté apachewsl2026)
$host = "http://localhost";

$apps = [
    // ========== PÔLE FINANCE & STRATÉGIE (Ports 8094-8098) ==========
    "Ingénierie" => [
        "url" => $host . ":8094/",
        "logo" => "🔧",
        "color" => "#34495e",
        "description" => "Solutions d'ingénierie et gestion de projets",
        "category" => "finance"
    ],
    "Banque" => [
        "url" => $host . ":8095/",
        "logo" => "🏦",
        "color" => "#2ecc71",
        "description" => "Gestion des comptes bancaires et opérations financières",
        "category" => "finance"
    ],
    "SYSCOHADA" => [
        "url" => $host . ":8096/",
        "logo" => "📊",
        "color" => "#8e44ad",
        "description" => "Comptabilité conforme SYSCOHADA",
        "category" => "finance"
    ],
    "SYSCOA" => [
        "url" => $host . ":8097/",
        "logo" => "📈",
        "color" => "#9b59b6",
        "description" => "Système Comptable Ouest Africain",
        "category" => "finance"
    ],
    "Gestion Prévisionnelle" => [
        "url" => $host . ":8098/",
        "logo" => "📅",
        "color" => "#1abc9c",
        "description" => "Budgets, prévisions et tableaux de bord",
        "category" => "finance"
    ],

    // ========== PÔLE GESTION COMMERCIALE & PME (Ports 8100-8104) ==========
    "PME" => [
        "url" => $host . ":8100/",
        "logo" => "🏢",
        "color" => "#34495e",
        "description" => "Solution complète pour petites et moyennes entreprises",
        "category" => "business"
    ],
    "Gestion Commerciale" => [
        "url" => $host . ":8101/",
        "logo" => "📦",
        "color" => "#e67e22",
        "description" => "Ventes, achats et gestion des stocks",
        "category" => "commerce"
    ],
    "E-commerce" => [
        "url" => $host . ":8102/",
        "logo" => "🛒",
        "color" => "#f39c12",
        "description" => "Plateforme de vente en ligne",
        "category" => "commerce"
    ],
    "Gestion E-Commerciale" => [
        "url" => $host . ":8103/",
        "logo" => "🌐",
        "color" => "#e67e22",
        "description" => "Management avancé des activités e-commerce",
        "category" => "commerce"
    ],
    "Restauration" => [
        "url" => $host . ":8104/",
        "logo" => "🍽️",
        "color" => "#27ae60",
        "description" => "Gestion de restaurant et service en salle",
        "category" => "restaurant"
    ],

    // ========== PÔLE AUTOMOBILE (Ports 8110-8112) ==========
    "Auto" => [
        "url" => $host . ":8110/",
        "logo" => "🚘",
        "color" => "#e74c3c",
        "description" => "Gestion générale automobile",
        "category" => "transport"
    ],
    "Gestion Auto" => [
        "url" => $host . ":8111/",
        "logo" => "🚗",
        "color" => "#e74c3c",
        "description" => "Gestion complète de véhicules, ventes et locations",
        "category" => "transport"
    ],
    "Pièces Auto" => [
        "url" => $host . ":8112/",
        "logo" => "🔧",
        "color" => "#d35400",
        "description" => "Gestion de stock de pièces détachées",
        "category" => "commerce"
    ],

    // ========== PÔLE SERVICES & RH (Ports 8091-8093, 8120) ==========
    "Gestion Pointage" => [
        "url" => $host . ":8093/",
        "logo" => "⏱️",
        "color" => "#16a085",
        "description" => "Gestion des présences et horaires",
        "category" => "business"
    ],
    "Gestion École" => [
        "url" => $host . ":8091/",
        "logo" => "🎓",
        "color" => "#1abc9c",
        "description" => "Gestion scolaire et administrative",
        "category" => "education"
    ],
    "Pressing" => [
        "url" => $host . ":8092/",
        "logo" => "👔",
        "color" => "#3498db",
        "description" => "Gestion de pressing et blanchisserie",
        "category" => "business"
    ],
    "Clinique" => [
        "url" => $host . ":8120/",
        "logo" => "🏥",
        "color" => "#e67e22",
        "description" => "Gestion médicale et suivi des patients",
        "category" => "medical"
    ],

    // ========== PÔLE ANALYSE & SYNTHÈSE (Ports 8130-8132) ==========
    "Report" => [
        "url" => $host . ":8130/",
        "logo" => "📋",
        "color" => "#7f8c8d",
        "description" => "Génération de rapports et analyses",
        "category" => "finance"
    ],
    "Reporting" => [
        "url" => $host . ":8131/",
        "logo" => "📊",
        "color" => "#95a5a6",
        "description" => "Tableaux de bord et indicateurs",
        "category" => "finance"
    ],
    "Synthèse Pro" => [
        "url" => $host . ":8132/",
        "logo" => "📈",
        "color" => "#2c3e50",
        "description" => "Synthèse et consolidation de données",
        "category" => "finance"
    ],

    // ========== PREMIERS NOUVEAUX SERVICES (Ports 8140-8145) ==========
    "Centre DIOP" => [
        "url" => $host . ":8140/",
        "logo" => "🏥",
        "color" => "#3498db",
        "description" => "Centre médical spécialisé",
        "category" => "medical"
    ],
    "Charcuterie" => [
        "url" => $host . ":8141/",
        "logo" => "🥩",
        "color" => "#e74c3c",
        "description" => "Gestion de charcuterie artisanale",
        "category" => "commerce"
    ],
    "Foot" => [
        "url" => $host . ":8142/",
        "logo" => "⚽",
        "color" => "#2ecc71",
        "description" => "Gestion de clubs et événements sportifs",
        "category" => "sport"
    ],
    "Librairie" => [
        "url" => $host . ":8143/",
        "logo" => "📚",
        "color" => "#8e44ad",
        "description" => "Gestion de librairie et inventaire",
        "category" => "commerce"
    ],
    "Pharmacie" => [
        "url" => $host . ":8144/",
        "logo" => "💊",
        "color" => "#1abc9c",
        "description" => "Gestion de pharmacie et médicaments",
        "category" => "medical"
    ],
    "Revendeur Médical" => [
        "url" => $host . ":8145/",
        "logo" => "🩺",
        "color" => "#e67e22",
        "description" => "Distribution de matériel médical",
        "category" => "medical"
    ],

    // ========== DEUXIÈME VAGUE (Ports 8150-8154) ==========
    "Analyse Médicale" => [
        "url" => $host . ":8150/",
        "logo" => "🔬",
        "color" => "#3498db",
        "description" => "Laboratoire d'analyses médicales",
        "category" => "medical"
    ],
    "Hôtel" => [
        "url" => $host . ":8151/",
        "logo" => "🏨",
        "color" => "#f39c12",
        "description" => "Gestion hôtelière et réservations",
        "category" => "business"
    ],
    "Cabinet Radiologie" => [
        "url" => $host . ":8153/",
        "logo" => "🩻",
        "color" => "#9b59b6",
        "description" => "Gestion de cabinet radiologique",
        "category" => "medical"
    ],
    "Gestion Immobilière" => [
        "url" => $host . ":8154/",
        "logo" => "🏠",
        "color" => "#16a085",
        "description" => "Gestion de biens immobiliers",
        "category" => "business"
    ],

    // ========== TROISIÈME VAGUE (Ports 8152, 8155, 8156, 8157, 8158, 8159) ==========
    "Portail" => [
        "url" => $host . ":8152/",
        "logo" => "🚪",
        "color" => "#2c3e50",
        "description" => "Portail d'accès aux services",
        "category" => "business"
    ],
    "Couture Sénégal" => [
        "url" => $host . ":8155/",
        "logo" => "👗",
        "color" => "#e74c3c",
        "description" => "Gestion de couture et mode sénégalaise",
        "category" => "commerce"
    ],
    "Génie Civil" => [
        "url" => $host . ":8156/",
        "logo" => "🏗️",
        "color" => "#d35400",
        "description" => "Gestion de projets et chantiers BTP",
        "category" => "business"
    ],
    "Transit" => [
        "url" => $host . ":8157/",
        "logo" => "🚛",
        "color" => "#2980b9",
        "description" => "Gestion de transit et logistique",
        "category" => "transport"
    ],
    "Agence Voyage" => [
        "url" => $host . ":8158/",
        "logo" => "✈️",
        "color" => "#1abc9c",
        "description" => "Réservations et gestion de voyages",
        "category" => "business"
    ],
    "Annuaire" => [
        "url" => $host . ":8159/",
        "logo" => "📇",
        "color" => "#7f8c8d",
        "description" => "Annuaire des entreprises et contacts",
        "category" => "business"
    ],

    // ========== QUATRIÈME VAGUE - NOUVELLES APPLICATIONS (Ports 8160-8163) ==========
    "Fitness" => [
        "url" => $host . ":8160/",
        "logo" => "🏋️",
        "color" => "#00b894",
        "description" => "Gestion de salle de sport, abonnements et coaching",
        "category" => "sport"
    ],
    "Pizzeria" => [
        "url" => $host . ":8161/",
        "logo" => "🍕",
        "color" => "#e17055",
        "description" => "Commandes de pizzas, livraison et gestion des tables",
        "category" => "restaurant"
    ],
    "Scooter" => [
        "url" => $host . ":8162/",
        "logo" => "🛵",
        "color" => "#0984e3",
        "description" => "Location et vente de scooters, gestion de flotte",
        "category" => "transport"
    ],
    "Parfumerie" => [
        "url" => $host . ":8163/",
        "logo" => "🧴",
        "color" => "#6c5ce7",
        "description" => "Parfums et cosmétiques de luxe, gestion des stocks",
        "category" => "commerce"
    ]
];

$categories = [
    "business" => ["icon" => "💼", "name" => "Business", "color" => "#2c3e50"],
    "commerce" => ["icon" => "🛍️", "name" => "Commerce", "color" => "#f39c12"],
    "education" => ["icon" => "🎓", "name" => "Éducation", "color" => "#1abc9c"],
    "finance" => ["icon" => "💰", "name" => "Finance & Compta", "color" => "#8e44ad"],
    "sport" => ["icon" => "⚽", "name" => "Sport & Fitness", "color" => "#00b894"],
    "restaurant" => ["icon" => "🍕", "name" => "Restauration", "color" => "#e17055"],
    "transport" => ["icon" => "🚗", "name" => "Transport & Mobilité", "color" => "#0984e3"],
    "medical" => ["icon" => "🏥", "name" => "Médical & Santé", "color" => "#3498db"]
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
        .new-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #e74c3c;
            color: white;
            font-size: 0.7rem;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            font-weight: bold;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="header-section text-center">
        <h1 class="fw-bold display-4">Business Suite Pro</h1>
        <p class="lead">Gestion Centralisée - Environnement apachewsl2026</p>
        <div class="mt-2">
            <span class="badge bg-success bg-opacity-75 px-3 py-2 rounded-pill">
                🚀 <?= count($apps) ?> applications disponibles
            </span>
        </div>
    </div>

    <div class="container py-4">
        <div class="bg-white p-3 rounded-4 shadow-sm mb-5 text-center">
            <h5 class="mb-3">Filtrer par Secteur</h5>
            <div class="category-filter">
                <div class="category-badge active" data-category="all"><span>📦</span> Tout (<?= count($apps) ?>)</div>
                <?php foreach($categories as $id => $cat): ?>
                    <div class="category-badge" data-category="<?= $id ?>">
                        <span><?= $cat['icon'] ?></span> <?= $cat['name'] ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="row g-4" id="apps-container">
            <?php
            $newApps = ['Fitness', 'Pizzeria', 'Scooter', 'Parfumerie', 'Génie Civil', 'Transit', 'Agence Voyage', 'Annuaire', 'Couture Sénégal'];
            foreach($apps as $name => $app):
                $isNew = in_array($name, $newApps);
            ?>
                <div class="col-xl-4 col-md-6 app-item" data-category="<?= $app['category'] ?>">
                    <div class="glass-card h-100" style="position: relative;">
                        <?php if($isNew): ?>
                            <span class="new-badge">🆕 NOUVEAU</span>
                        <?php endif; ?>
                        <a href="<?= $app['url'] ?>" target="_blank" class="app-card" style="--card-color: <?= $app['color'] ?>">
                            <span class="badge-category"><?= $categories[$app['category']]['icon'] ?></span>
                            <div class="text-center">
                                <span class="app-logo"><?= $app['logo'] ?></span>
                                <h3 class="app-title"><?= htmlspecialchars($name) ?></h3>
                                <p class="small text-muted"><?= htmlspecialchars($app['description']) ?></p>
                                <div class="mt-2">
                                    <span class="btn btn-sm text-white" style="background:<?= $app['color'] ?>">
                                        Lancer l'App <i class="fas fa-arrow-right ms-1"></i>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="text-center py-5 text-white-50">
        <p>&copy; 2026 Business Suite Pro - Serveur PHP/MariaDB</p>
        <p class="small">
            <?php
            $icons = ['🏋️', '🍕', '🛵', '🧴', '🏥', '🚗', '🏢', '📚'];
            foreach($icons as $i => $icon) {
                echo $icon . ($i < count($icons)-1 ? ' · ' : '');
            }
            ?>
        </p>
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
        console.log("🎯 Business Suite Pro - <?= count($apps) ?> applications disponibles !");
    </script>
</body>
</html>
