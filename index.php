<?php
$host = "http://localhost";     
$apps = [
    // ========== PÔLE FINANCE & STRATÉGIE ==========
    "Ingénierie" => ["url" => $host . ":8094/", "logo" => "🔧", "color" => "#34495e", "description" => "Solutions d'ingénierie et gestion de projets", "category" => "finance"],
    "Banque" => ["url" => $host . ":8095/", "logo" => "🏦", "color" => "#2ecc71", "description" => "Gestion des comptes bancaires et opérations financières", "category" => "finance"],
    "GP" => ["url" => $host . ":8096/", "logo" => "📊", "color" => "#8e44ad", "description" => "Gestion Prévisionnelle, budgets et tableaux de bord", "category" => "finance"],
    "Assurance" => ["url" => $host . ":8098/", "logo" => "🛡️", "color" => "#1abc9c", "description" => "Gestion des assurances, contrats et sinistres", "category" => "finance"],

    // ========== PÔLE GESTION COMMERCIALE & PME ==========
    "PME" => ["url" => $host . ":8100/", "logo" => "🏢", "color" => "#34495e", "description" => "Solution complète pour petites et moyennes entreprises", "category" => "business"],
    "Gestion Commerciale" => ["url" => $host . ":8101/", "logo" => "📦", "color" => "#e67e22", "description" => "Ventes, achats et gestion des stocks", "category" => "commerce"],
    "E-commerce" => ["url" => $host . ":8102/", "logo" => "🛒", "color" => "#f39c12", "description" => "Plateforme de vente en ligne", "category" => "commerce"],
    "Gestion E-Commerciale" => ["url" => $host . ":8103/", "logo" => "🌐", "color" => "#e67e22", "description" => "Management avancé des activités e-commerce", "category" => "commerce"],
    "Restauration" => ["url" => $host . ":8104/", "logo" => "🍽️", "color" => "#27ae60", "description" => "Gestion de restaurant et service en salle", "category" => "restaurant"],

    // ========== PÔLE AUTOMOBILE ==========
    "Auto" => ["url" => $host . ":8110/", "logo" => "🚘", "color" => "#e74c3c", "description" => "Gestion générale automobile", "category" => "transport"],
    "Gestion Auto" => ["url" => $host . ":8111/", "logo" => "🚗", "color" => "#e74c3c", "description" => "Gestion complète de véhicules", "category" => "transport"],
    "Pièces Auto" => ["url" => $host . ":8112/", "logo" => "🔧", "color" => "#d35400", "description" => "Gestion de stock de pièces détachées", "category" => "commerce"],
    "O_Garage" => ["url" => $host . ":8113/", "logo" => "🛠️", "color" => "#c0392b", "description" => "Gestion complète d'atelier mécanique", "category" => "transport"],

    // ========== PÔLE SERVICES & RH ==========
    "Gestion Pointage" => ["url" => $host . ":8093/", "logo" => "⏱️", "color" => "#16a085", "description" => "Gestion des présences et horaires", "category" => "business"],
    "Gestion École" => ["url" => $host . ":8091/", "logo" => "🎓", "color" => "#1abc9c", "description" => "Gestion scolaire et administrative", "category" => "education"],
    "Pressing" => ["url" => $host . ":8092/", "logo" => "👔", "color" => "#3498db", "description" => "Gestion de pressing et blanchisserie", "category" => "business"],
    "Clinique" => ["url" => $host . ":8120/", "logo" => "🏥", "color" => "#e67e22", "description" => "Gestion médicale et suivi des patients", "category" => "medical"],

    // ========== PÔLE ANALYSE & SYNTHÈSE ==========
    "Report" => ["url" => $host . ":8130/", "logo" => "📋", "color" => "#7f8c8d", "description" => "Génération de rapports et analyses", "category" => "finance"],
    "Reporting" => ["url" => $host . ":8131/", "logo" => "📊", "color" => "#95a5a6", "description" => "Tableaux de bord et indicateurs", "category" => "finance"],

    // ========== PREMIERS NOUVEAUX SERVICES ==========
    "Centre DIOP" => ["url" => $host . ":8140/", "logo" => "🏥", "color" => "#3498db", "description" => "Centre médical spécialisé", "category" => "medical"],
    "Charcuterie" => ["url" => $host . ":8141/", "logo" => "🥩", "color" => "#e74c3c", "description" => "Gestion de charcuterie artisanale", "category" => "commerce"],
    "Foot" => ["url" => $host . ":8142/", "logo" => "⚽", "color" => "#2ecc71", "description" => "Gestion de clubs et événements sportifs", "category" => "sport"],
    "Librairie" => ["url" => $host . ":8143/", "logo" => "📚", "color" => "#8e44ad", "description" => "Gestion de librairie et inventaire", "category" => "commerce"],
    "Pharmacie" => ["url" => $host . ":8144/", "logo" => "💊", "color" => "#1abc9c", "description" => "Gestion de pharmacie et médicaments", "category" => "medical"],
    
    // ========== VAGUES APPLICATIONS ==========
    "Analyse Médicale" => ["url" => $host . ":8150/", "logo" => "🔬", "color" => "#3498db", "description" => "Laboratoire d'analyses médicales", "category" => "medical"],
    "Hôtel" => ["url" => $host . ":8151/", "logo" => "🏨", "color" => "#f39c12", "description" => "Gestion hôtelière et réservations", "category" => "business"],
    "Cabinet Radiologie" => ["url" => $host . ":8153/", "logo" => "🩻", "color" => "#9b59b6", "description" => "Gestion de cabinet radiologique", "category" => "medical"],
    "Gestion Immobilière" => ["url" => $host . ":8154/", "logo" => "🏠", "color" => "#16a085", "description" => "Gestion de biens immobiliers", "category" => "business"],
    "Portail" => ["url" => $host . ":8152/", "logo" => "🚪", "color" => "#2c3e50", "description" => "Portail d'accès aux services", "category" => "business"],
    "Couture Sénégal" => ["url" => $host . ":8155/", "logo" => "👗", "color" => "#e74c3c", "description" => "Gestion de couture et mode", "category" => "commerce"],
    "Génie Civil" => ["url" => $host . ":8156/", "logo" => "🏗️", "color" => "#d35400", "description" => "Gestion de projets et chantiers BTP", "category" => "business"],
    "Transit" => ["url" => $host . ":8157/", "logo" => "🚛", "color" => "#2980b9", "description" => "Gestion de transit et logistique", "category" => "transport"],
    "Agence Voyage" => ["url" => $host . ":8158/", "logo" => "✈️", "color" => "#1abc9c", "description" => "Réservations et gestion de voyages", "category" => "business"],
    "Annuaire" => ["url" => $host . ":8159/", "logo" => "📇", "color" => "#7f8c8d", "description" => "Annuaire des entreprises", "category" => "business"],
    "Fitness" => ["url" => $host . ":8160/", "logo" => "🏋️", "color" => "#00b894", "description" => "Gestion de salle de sport", "category" => "sport"],
    "Pizzeria" => ["url" => $host . ":8161/", "logo" => "🍕", "color" => "#e17055", "description" => "Commandes de pizzas et gestion", "category" => "restaurant"],
    "Scooter" => ["url" => $host . ":8162/", "logo" => "🛵", "color" => "#0984e3", "description" => "Location et vente de scooters", "category" => "transport"],
    "Parfumerie" => ["url" => $host . ":8163/", "logo" => "🧴", "color" => "#6c5ce7", "description" => "Parfums et cosmétiques de luxe", "category" => "commerce"]
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
    <title>Business Suite Pro - 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); min-height: 100vh; color: #2c3e50; }
        .glass-card { background: rgba(255, 255, 255, 0.95); border-radius: 20px; box-shadow: 0 8px 32px rgba(0,0,0,0.2); transition: 0.3s; }
        .app-card { display: block; padding: 2rem; text-decoration: none; color: inherit; }
    </style>
</head>
<body>
    <div class="container py-5 text-center text-white">
        <h1>Business Suite Pro</h1>
        <p class="lead">Plateforme de Gestion Centralisée</p>
    </div>
    <div class="container">
        <div class="row g-4">
            <?php foreach($apps as $name => $app): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="glass-card">
                        <a href="<?= $app['url'] ?>" target="_blank" class="app-card">
                            <div style="font-size: 3rem;"><?= $app['logo'] ?></div>
                            <h5><?= $name ?></h5>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
