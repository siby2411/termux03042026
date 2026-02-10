#!/bin/bash
# Script de migration du projet SYSCO OHADA
# À exécuter avec : sudo bash migrate_project.sh

echo "================================================================"
echo "MIGRATION DU PROJET SYSCO OHADA - STRUCTURE MODULAIRE"
echo "================================================================"
echo "Ce script va réorganiser votre projet pour une architecture modulaire"
echo "avec index.php comme point d'entrée unique."
echo "================================================================"

# Vérifier que le script est exécuté avec sudo
if [ "$EUID" -ne 0 ]; then 
    echo "ERREUR: Ce script doit être exécuté avec sudo"
    echo "Utilisation: sudo bash migrate_project.sh"
    exit 1
fi

# Définir le répertoire de travail
WORKDIR="/var/www/syscoa"
cd "$WORKDIR" || { echo "ERREUR: Impossible d'accéder à $WORKDIR"; exit 1; }

echo "📁 Répertoire de travail: $WORKDIR"
echo ""

# 1. Créer la structure de dossiers
echo "📂 Création de la structure des dossiers..."
sudo mkdir -p modules
sudo mkdir -p assets/{css,js,images}
sudo mkdir -p api
sudo mkdir -p controllers
sudo mkdir -p partials
sudo mkdir -p exports/{pdf,excel,csv}
sudo mkdir -p backup
echo "✅ Structure créée"
echo ""

# 2. Sauvegarder l'ancien index
echo "💾 Sauvegarde de l'ancien index.php..."
if [ -f "index.php" ]; then
    sudo cp index.php index_old_$(date +%Y%m%d_%H%M%S).php
    echo "✅ Ancien index sauvegardé"
else
    echo "⚠️  index.php non trouvé"
fi
echo ""

# 3. Copier les fichiers vers modules/ avec sudo
echo "📦 Migration des modules..."

# Liste des fichiers à migrer
declare -A modules_map=(
    ["soldes_gestion.php"]="soldes.php"
    ["rapprochement_bancaire.php"]="rapprochement.php"
    ["gestion_articles.php"]="articles.php"
    ["travaux_cloture.php"]="cloture.php"
    ["tableau_flux_tresorerie.php"]="flux.php"
    ["comptabilite.php"]="ecritures.php"
    ["grand_livre.php"]="grand_livre.php"
    ["journal_comptable.php"]="journaux.php"
    ["balance_comptable.php"]="balance.php"
    ["bilan-comptable.php"]="bilans.php"
    ["compte_resultat.php"]="compte_resultat.php"
    ["analyse_financiere.php"]="ratios.php"
    ["administration.php"]="admin.php"
)

# Copier chaque fichier
for source_file in "${!modules_map[@]}"; do
    destination_file="${modules_map[$source_file]}"
    
    if [ -f "$source_file" ]; then
        echo "  Copie de $source_file vers modules/$destination_file"
        sudo cp "$source_file" "modules/$destination_file"
        
        # Ajuster les permissions
        sudo chmod 644 "modules/$destination_file"
        sudo chown www-data:www-data "modules/$destination_file"
    else
        echo "  ⚠️  $source_file non trouvé"
    fi
done
echo "✅ Modules copiés"
echo ""

# 4. Créer les partials
echo "🎨 Création des templates réutilisables..."

# partials/header.php
sudo tee partials/header.php > /dev/null << 'EOF'
<?php
// partials/header.php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SYSCO OHADA - Système Comptable</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- CSS personnalisé -->
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        .sidebar {
            background: var(--primary);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .stat-card {
            border-radius: 10px;
            border: none;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
EOF

# partials/sidebar.php
sudo tee partials/sidebar.php > /dev/null << 'EOF'
<?php
// partials/sidebar.php
$current_module = isset($_GET['module']) ? $_GET['module'] : 'dashboard';
?>
<div class="sidebar">
    <div class="p-4">
        <h4><i class="fas fa-chart-line"></i> SYSCO OHADA</h4>
        <p class="small text-white-50">v2.0</p>
    </div>
    
    <nav class="nav flex-column px-3">
        <a href="?module=dashboard" class="nav-link <?php echo $current_module == 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-home me-2"></i> Tableau de bord
        </a>
        
        <div class="text-white-50 small mt-3 mb-2 px-3">COMPTABILITÉ</div>
        <a href="?module=ecritures" class="nav-link <?php echo $current_module == 'ecritures' ? 'active' : ''; ?>">
            <i class="fas fa-book me-2"></i> Écritures
        </a>
        <a href="?module=journaux" class="nav-link <?php echo $current_module == 'journaux' ? 'active' : ''; ?>">
            <i class="fas fa-file-invoice me-2"></i> Journaux
        </a>
        
        <div class="text-white-50 small mt-3 mb-2 px-3">ANALYSE</div>
        <a href="?module=soldes" class="nav-link <?php echo $current_module == 'soldes' ? 'active' : ''; ?>">
            <i class="fas fa-chart-line me-2"></i> Soldes intermédiaires
        </a>
        <a href="?module=bilans" class="nav-link <?php echo $current_module == 'bilans' ? 'active' : ''; ?>">
            <i class="fas fa-balance-scale me-2"></i> Bilans
        </a>
        
        <div class="text-white-50 small mt-3 mb-2 px-3">BANQUE</div>
        <a href="?module=rapprochement" class="nav-link <?php echo $current_module == 'rapprochement' ? 'active' : ''; ?>">
            <i class="fas fa-university me-2"></i> Rapprochement
        </a>
        
        <div class="text-white-50 small mt-3 mb-2 px-3">STOCKS</div>
        <a href="?module=articles" class="nav-link <?php echo $current_module == 'articles' ? 'active' : ''; ?>">
            <i class="fas fa-boxes me-2"></i> Gestion articles
        </a>
        
        <div class="text-white-50 small mt-3 mb-2 px-3">CLÔTURE</div>
        <a href="?module=cloture" class="nav-link <?php echo $current_module == 'cloture' ? 'active' : ''; ?>">
            <i class="fas fa-calendar-times me-2"></i> Travaux clôture
        </a>
        
        <div class="text-white-50 small mt-3 mb-2 px-3">RAPPORTS</div>
        <a href="?module=rapports" class="nav-link <?php echo $current_module == 'rapports' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar me-2"></i> Rapports
        </a>
        
        <div class="mt-auto p-3">
            <a href="logout.php" class="btn btn-sm btn-danger w-100">
                <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
            </a>
        </div>
    </nav>
</div>
EOF

# partials/footer.php
sudo tee partials/footer.php > /dev/null << 'EOF'
<?php
// partials/footer.php
?>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
    // Gestion des messages
    <?php if(isset($_SESSION['success'])): ?>
    alert('<?php echo addslashes($_SESSION['success']); ?>');
    <?php unset($_SESSION['success']); endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
    alert('Erreur: <?php echo addslashes($_SESSION['error']); ?>');
    <?php unset($_SESSION['error']); endif; ?>
});
</script>

</body>
</html>
EOF

echo "✅ Templates créés"
echo ""

# 5. Créer le dashboard
echo "📊 Création du module dashboard..."
sudo tee modules/dashboard.php > /dev/null << 'EOF'
<?php
// modules/dashboard.php
$id_exercice = $_SESSION['id_exercice'];

// Statistiques
$total_ecritures = $pdo->query("SELECT COUNT(*) FROM ecritures WHERE id_exercice = $id_exercice")->fetchColumn();
$solde_banque = $pdo->query("SELECT SUM(debit - credit) FROM ecritures WHERE compte_num LIKE '52%'")->fetchColumn();
$total_articles = $pdo->query("SELECT COUNT(*) FROM articles_stock")->fetchColumn();
$total_tiers = $pdo->query("SELECT COUNT(*) FROM tiers")->fetchColumn();
?>
<div class="container-fluid">
    <h1 class="mb-4"><i class="fas fa-tachometer-alt"></i> Tableau de bord</h1>
    
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Écritures</h6>
                    <h2><?php echo number_format($total_ecritures, 0, ',', ' '); ?></h2>
                    <i class="fas fa-file-invoice fa-2x opacity-50 float-end"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Solde bancaire</h6>
                    <h2><?php echo format_montant($solde_banque); ?></h2>
                    <i class="fas fa-university fa-2x opacity-50 float-end"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Articles stock</h6>
                    <h2><?php echo number_format($total_articles, 0, ',', ' '); ?></h2>
                    <i class="fas fa-boxes fa-2x opacity-50 float-end"></i>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Tiers actifs</h6>
                    <h2><?php echo number_format($total_tiers, 0, ',', ' '); ?></h2>
                    <i class="fas fa-users fa-2x opacity-50 float-end"></i>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Accès rapide -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Accès rapide</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="?module=ecritures" class="btn btn-primary w-100">
                                <i class="fas fa-plus-circle"></i> Écriture
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?module=rapprochement" class="btn btn-success w-100">
                                <i class="fas fa-sync-alt"></i> Rapprochement
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?module=articles" class="btn btn-warning w-100">
                                <i class="fas fa-box"></i> Stock
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?module=cloture" class="btn btn-danger w-100">
                                <i class="fas fa-lock"></i> Clôture
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Analyses</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="?module=soldes" class="btn btn-info w-100">
                                <i class="fas fa-chart-pie"></i> Soldes SIG
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?module=bilans" class="btn btn-info w-100">
                                <i class="fas fa-balance-scale"></i> Bilan
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?module=flux" class="btn btn-info w-100">
                                <i class="fas fa-exchange-alt"></i> Flux trésorerie
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="?module=rapports" class="btn btn-info w-100">
                                <i class="fas fa-file-alt"></i> Rapports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dernières activités -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Activités récentes</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Système SYSCO OHADA v2.0 - Architecture modulaire</p>
                    <p>Bienvenue dans la nouvelle interface unifiée !</p>
                </div>
            </div>
        </div>
    </div>
</div>
EOF

echo "✅ Dashboard créé"
echo ""

# 6. Créer les modules manquants
echo "🛠️  Création des modules manquants..."

# Module rapports
sudo tee modules/rapports.php > /dev/null << 'EOF'
<?php
// modules/rapports.php
?>
<div class="container-fluid">
    <h1 class="mb-4"><i class="fas fa-chart-bar"></i> Rapports Financiers</h1>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Liste des rapports disponibles</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body text-center">
                            <i class="fas fa-file-invoice-dollar fa-3x text-primary mb-3"></i>
                            <h5>Balance générale</h5>
                            <a href="balance.php" class="btn btn-primary">Générer</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-line fa-3x text-success mb-3"></i>
                            <h5>Grand livre</h5>
                            <a href="grand_livre.php" class="btn btn-success">Générer</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body text-center">
                            <i class="fas fa-chart-pie fa-3x text-warning mb-3"></i>
                            <h5>Compte de résultat</h5>
                            <a href="compte_resultat.php" class="btn btn-warning">Générer</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
EOF

# Module inventaire
sudo tee modules/inventaire.php > /dev/null << 'EOF'
<?php
// modules/inventaire.php
?>
<div class="container-fluid">
    <h1 class="mb-4"><i class="fas fa-clipboard-check"></i> Inventaire des Stocks</h1>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Fonctionnalité en développement</h5>
        </div>
        <div class="card-body">
            <p>Ce module est en cours de développement.</p>
            <p>En attendant, vous pouvez utiliser <a href="?module=articles">Gestion des articles</a>.</p>
        </div>
    </div>
</div>
EOF

# Module amortissements
sudo tee modules/amortissements.php > /dev/null << 'EOF'
<?php
// modules/amortissements.php
?>
<div class="container-fluid">
    <h1 class="mb-4"><i class="fas fa-calculator"></i> Amortissements</h1>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Fonctionnalité en développement</h5>
        </div>
        <div class="card-body">
            <p>Ce module est en cours de développement.</p>
            <p>En attendant, vous pouvez utiliser <a href="?module=cloture">Travaux de clôture</a>.</p>
        </div>
    </div>
</div>
EOF

# Module provisions
sudo tee modules/provisions.php > /dev/null << 'EOF'
<?php
// modules/provisions.php
?>
<div class="container-fluid">
    <h1 class="mb-4"><i class="fas fa-shield-alt"></i> Provisions</h1>
    
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Fonctionnalité en développement</h5>
        </div>
        <div class="card-body">
            <p>Ce module est en cours de développement.</p>
            <p>En attendant, vous pouvez utiliser <a href="?module=cloture">Travaux de clôture</a>.</p>
        </div>
    </div>
</div>
EOF

echo "✅ Modules créés"
echo ""

# 7. Créer le nouvel index.php
echo "🚀 Création du nouvel index.php..."
sudo tee index.php > /dev/null << 'EOF'
<?php
// index.php - Point d'entrée unique
session_start();

// Configuration
require_once 'config/database.php';
require_once 'includes/functions.php';

// Vérifier authentification
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Module par défaut
$modules_available = [
    'dashboard' => 'Tableau de bord',
    'soldes' => 'Soldes intermédiaires',
    'rapprochement' => 'Rapprochement bancaire',
    'articles' => 'Gestion articles',
    'cloture' => 'Travaux clôture',
    'flux' => 'Flux trésorerie',
    'ecritures' => 'Écritures comptables',
    'grand_livre' => 'Grand livre',
    'journaux' => 'Journaux comptables',
    'balance' => 'Balance comptable',
    'bilans' => 'Bilans financiers',
    'compte_resultat' => 'Compte de résultat',
    'ratios' => 'Ratios financiers',
    'inventaire' => 'Inventaire stock',
    'amortissements' => 'Amortissements',
    'provisions' => 'Provisions',
    'rapports' => 'Rapports financiers',
    'admin' => 'Administration'
];

$module = isset($_GET['module']) ? $_GET['module'] : 'dashboard';
if (!array_key_exists($module, $modules_available)) {
    $module = 'dashboard';
}

// Inclure le header
include 'partials/header.php';

// Inclure la sidebar
include 'partials/sidebar.php';

// Contenu principal
echo '<main class="main-content">';

// Charger le module
$module_file = 'modules/' . $module . '.php';
if (file_exists($module_file)) {
    include $module_file;
} else {
    echo '<div class="container-fluid mt-4">';
    echo '<div class="alert alert-warning">';
    echo '<h4><i class="fas fa-exclamation-triangle"></i> Module non disponible</h4>';
    echo '<p>Le module "' . $modules_available[$module] . '" n\'est pas encore implémenté.</p>';
    echo '<p>Modules disponibles: ' . implode(', ', array_values($modules_available)) . '</p>';
    echo '</div>';
    echo '</div>';
}

echo '</main>';

// Inclure le footer
include 'partials/footer.php';
?>
EOF

echo "✅ Index créé"
echo ""

# 8. Créer le CSS minimal
echo "🎨 Création des assets CSS..."
sudo tee assets/css/main.css > /dev/null << 'EOF'
/* assets/css/main.css */

/* Styles de base */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8f9fa;
}

/* Sidebar */
.sidebar {
    background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
    color: white;
    min-height: 100vh;
    position: fixed;
    width: 250px;
    z-index: 1000;
}

.main-content {
    margin-left: 250px;
    padding: 20px;
    transition: margin-left 0.3s;
}

/* Navigation */
.nav-link {
    color: rgba(255, 255, 255, 0.8);
    padding: 12px 20px;
    border-radius: 5px;
    margin-bottom: 5px;
    transition: all 0.3s;
}

.nav-link:hover, .nav-link.active {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    text-decoration: none;
}

/* Cartes */
.card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.stat-card {
    color: white;
    border-radius: 10px;
    padding: 20px;
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

/* Responsive */
@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        position: relative;
        min-height: auto;
    }
    
    .main-content {
        margin-left: 0;
    }
}
EOF

echo "✅ CSS créé"
echo ""

# 9. Ajuster les permissions
echo "🔒 Ajustement des permissions..."
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
sudo chmod 644 $(find . -type f -name "*.php")
sudo chmod 644 $(find . -type f -name "*.css")
sudo chmod 644 $(find . -type f -name "*.js")
echo "✅ Permissions ajustées"
echo ""

# 10. Message final
echo "================================================================"
echo "✅ MIGRATION TERMINÉE AVEC SUCCÈS !"
echo "================================================================"
echo ""
echo "📋 RÉCAPITULATIF :"
echo "------------------"
echo "✓ Structure créée : modules/, partials/, assets/"
echo "✓ Ancien index sauvegardé : index_old_*.php"
echo "✓ Nouvel index.php créé"
echo "✓ Modules migrés : " $(ls modules/*.php 2>/dev/null | wc -l) " fichiers"
echo ""
echo "🔗 ACCÈS :"
echo "----------"
echo "URL principale : http://localhost/syscoa/"
echo "Modules disponibles :"
echo "  • dashboard      - Tableau de bord"
echo "  • soldes         - Soldes intermédiaires"
echo "  • rapprochement  - Rapprochement bancaire"
echo "  • articles       - Gestion des articles"
echo "  • cloture        - Travaux de clôture"
echo "  • flux           - Flux de trésorerie"
echo "  • ecritures      - Écritures comptables"
echo "  • bilans         - Bilans financiers"
echo ""
echo "⚠️  NOTES IMPORTANTES :"
echo "----------------------"
echo "1. Les fichiers existants ont été copiés dans modules/"
echo "2. L'ancien index.php a été sauvegardé"
echo "3. Tous les modules sont accessibles via : index.php?module=NOM"
echo "4. Vous devrez peut-être ajuster certains chemins dans les modules"
echo ""
echo "🔄 Pour revenir à l'ancienne version :"
echo "   sudo cp index_old_*.php index.php"
echo ""
echo "================================================================"
