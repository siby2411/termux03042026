<?php include 'config.php'; include 'header.php'; 
$db = new Database();
$conn = $db->getConnection();

// Statistiques pour le dashboard
$stats = [];
$query = $conn->query("SELECT COUNT(*) as total FROM clients");
$stats['clients'] = $query->fetch()['total'];

$query = $conn->query("SELECT COUNT(*) as total FROM commandes WHERE statut != 'recupere'");
$stats['commandes_encours'] = $query->fetch()['total'];

$query = $conn->query("SELECT SUM(total_ttc) as total FROM commandes WHERE DATE(date_commande) = CURDATE()");
$stats['ca_jour'] = $query->fetch()['total'] ?? 0;

$query = $conn->query("SELECT COUNT(*) as total FROM commandes WHERE statut = 'en_attente'");
$stats['commandes_attente'] = $query->fetch()['total'];

$query = $conn->query("SELECT SUM(total_ttc) as total FROM commandes WHERE MONTH(date_commande) = MONTH(CURDATE())");
$stats['ca_mois'] = $query->fetch()['total'] ?? 0;

$query = $conn->query("SELECT COUNT(*) as total FROM services");
$stats['total_services'] = $query->fetch()['total'];

// Statistiques par catégorie
$query_categories = $conn->query("
    SELECT categorie, COUNT(*) as count, AVG(prix) as prix_moyen 
    FROM services 
    GROUP BY categorie 
    ORDER BY count DESC
");
$categories_stats = $query_categories->fetchAll();

// Top services africains/arabes
$query_top_ethnique = $conn->query("
    SELECT nom, prix, categorie 
    FROM services 
    WHERE categorie IN ('Tenues Africaines', 'Tenues Arabes')
    ORDER BY prix DESC 
    LIMIT 5
");
$top_services_ethnique = $query_top_ethnique->fetchAll();

// Commandes récentes
$query_commandes = $conn->query("
    SELECT c.*, cl.nom, cl.prenom 
    FROM commandes c 
    LEFT JOIN clients cl ON c.client_id = cl.id 
    ORDER BY c.date_commande DESC 
    LIMIT 5
");
$commandes_recentes = $query_commandes->fetchAll();

// Alertes commandes en retard
$query_retard = $conn->query("
    SELECT c.*, cl.nom, cl.prenom 
    FROM commandes c 
    LEFT JOIN clients cl ON c.client_id = cl.id 
    WHERE c.date_recuperation_prevue < CURDATE() 
    AND c.statut != 'recupere'
    ORDER BY c.date_recuperation_prevue ASC
    LIMIT 5
");
$commandes_retard = $query_retard->fetchAll();
?>

<div class="welcome-section">
    <h1>Tableau de Bord - Pressing Pro</h1>
    <p>Bienvenue dans votre système de gestion de pressing professionnel</p>
</div>

<!-- Statistiques principales -->
<div class="stats-grid">
    <div class="stat-card" onclick="location.href='clients.php'" style="cursor: pointer;">
        <div class="stat-icon">👥</div>
        <div class="stat-number"><?= $stats['clients'] ?></div>
        <div class="stat-label">Clients</div>
    </div>
    
    <div class="stat-card" onclick="location.href='commandes.php'" style="cursor: pointer;">
        <div class="stat-icon">📦</div>
        <div class="stat-number"><?= $stats['commandes_encours'] ?></div>
        <div class="stat-label">Commandes en cours</div>
    </div>
    
    <div class="stat-card" onclick="location.href='etat_financier.php'" style="cursor: pointer;">
        <div class="stat-icon">💰</div>
        <div class="stat-number"><?= number_format($stats['ca_jour'], 0, ',', ' ') ?> €</div>
        <div class="stat-label">CA aujourd'hui</div>
    </div>
    
    <div class="stat-card" onclick="location.href='commandes.php'" style="cursor: pointer;">
        <div class="stat-icon">⏱️</div>
        <div class="stat-number"><?= $stats['commandes_attente'] ?></div>
        <div class="stat-label">En attente</div>
    </div>
    
    <div class="stat-card" onclick="location.href='etat_financier.php'" style="cursor: pointer;">
        <div class="stat-icon">📊</div>
        <div class="stat-number"><?= number_format($stats['ca_mois'], 0, ',', ' ') ?> €</div>
        <div class="stat-label">CA ce mois</div>
    </div>
    
    <div class="stat-card" onclick="location.href='services.php'" style="cursor: pointer;">
        <div class="stat-icon">🔧</div>
        <div class="stat-number"><?= $stats['total_services'] ?></div>
        <div class="stat-label">Services</div>
    </div>
</div>

<!-- Répartition par catégories -->
<div class="card">
    <h3>📊 Répartition par Catégories</h3>
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
        <div>
            <canvas id="chartCategories" height="250"></canvas>
        </div>
        <div>
            <h4>👑🌙 Top Services Africains/Arabes</h4>
            <div class="top-services-list">
                <?php foreach($top_services_ethnique as $service): ?>
                <div class="service-item">
                    <span class="service-name"><?= htmlspecialchars($service['nom']) ?></span>
                    <span class="service-price"><?= number_format($service['prix'], 2, ',', ' ') ?> €</span>
                    <span class="service-cat-badge"><?= $service['categorie'] == 'Tenues Africaines' ? '👑' : '🌙' ?></span>
                </div>
                <?php endforeach; ?>
                <?php if(empty($top_services_ethnique)): ?>
                    <div style="text-align: center; padding: 2rem; color: #7f8c8d;">
                        <p>Aucun service africain ou arabe configuré</p>
                        <a href="services.php" class="btn btn-primary">Ajouter des services</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modules de l'application -->
<div class="modules-grid">
    <div class="module-card" onclick="location.href='clients.php'">
        <div class="module-icon">👥</div>
        <h3>Gestion Clients</h3>
        <p>Gérez votre base de clients, ajoutez de nouveaux clients et consultez leurs informations</p>
        <div class="module-actions">
            <span class="btn btn-outline">Voir clients</span>
        </div>
    </div>
    
    <div class="module-card" onclick="location.href='commandes.php'">
        <div class="module-icon">📦</div>
        <h3>Commandes</h3>
        <p>Créez et suivez les commandes, gérez le statut et le planning de récupération</p>
        <div class="module-actions">
            <span class="btn btn-outline">Gérer commandes</span>
        </div>
    </div>
    
    <div class="module-card" onclick="location.href='services.php'">
        <div class="module-icon">🔧</div>
        <h3>Services</h3>
        <p>Configurez vos services, prix et durées de traitement. Inclut tenues africaines et arabes.</p>
        <div class="module-actions">
            <span class="btn btn-outline">Voir services</span>
        </div>
    </div>
    
    <div class="module-card" onclick="location.href='factures.php'">
        <div class="module-icon">🧾</div>
        <h3>Facturation</h3>
        <p>Générez et imprimez des factures professionnelles pour vos clients</p>
        <div class="module-actions">
            <span class="btn btn-outline">Voir factures</span>
        </div>
    </div>
    
    <div class="module-card" onclick="location.href='etat_financier.php'">
        <div class="module-icon">📊</div>
        <h3>État Financier</h3>
        <p>Analyses détaillées, graphiques et rapports financiers avancés</p>
        <div class="module-actions">
            <span class="btn btn-outline">Voir analyses</span>
        </div>
    </div>
    
    <div class="module-card" onclick="location.href='generer_rapport.php'">
        <div class="module-icon">📄</div>
        <h3>Rapports PDF</h3>
        <p>Générez des rapports PDF détaillés pour votre comptabilité</p>
        <div class="module-actions">
            <span class="btn btn-outline">Générer PDF</span>
        </div>
    </div>
</div>

<!-- Alertes et activités récentes -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 2rem;">
    <!-- Commandes récentes -->
    <div class="card">
        <h3>📋 Commandes Récentes</h3>
        <div class="activity-list">
            <?php foreach($commandes_recentes as $commande): ?>
            <div class="activity-item">
                <div class="activity-info">
                    <strong>#<?= $commande['id'] ?> - <?= $commande['prenom'] . ' ' . $commande['nom'] ?></strong>
                    <span><?= number_format($commande['total_ttc'], 2, ',', ' ') ?> €</span>
                </div>
                <div class="activity-meta">
                    <span class="activity-date"><?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></span>
                    <span class="activity-status status-<?= $commande['statut'] ?>">
                        <?= str_replace('_', ' ', $commande['statut']) ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: 1rem;">
            <a href="commandes.php" class="btn btn-primary">Voir toutes les commandes</a>
        </div>
    </div>
    
    <!-- Alertes retards -->
    <div class="card">
        <h3>⚠️ Commandes en Retard</h3>
        <?php if(count($commandes_retard) > 0): ?>
        <div class="alert-list">
            <?php foreach($commandes_retard as $commande): ?>
            <div class="alert-item">
                <div class="alert-info">
                    <strong>#<?= $commande['id'] ?> - <?= $commande['prenom'] . ' ' . $commande['nom'] ?></strong>
                    <span class="alert-date">Échéance: <?= date('d/m/Y', strtotime($commande['date_recuperation_prevue'])) ?></span>
                </div>
                <div class="alert-actions">
                    <a href="commandes.php" class="btn btn-sm btn-danger">Traiter</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="text-align: center; padding: 2rem; color: #27ae60;">
            <div style="font-size: 3rem;">✅</div>
            <p>Aucune commande en retard</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Actions rapides -->
<div class="card" style="margin-top: 2rem;">
    <h3>🚀 Actions Rapides</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
        <a href="commandes.php?action=nouveau" class="btn btn-success btn-block">
            ➕ Nouvelle Commande
        </a>
        <a href="clients.php" class="btn btn-primary btn-block">
            👥 Nouveau Client
        </a>
        <a href="factures.php" class="btn btn-primary btn-block">
            🧾 Générer Facture
        </a>
        <a href="etat_financier.php" class="btn btn-primary btn-block">
            📊 Voir Statistiques
        </a>
    </div>
</div>

<script>
// Données pour le graphique des catégories
const categoriesData = {
    labels: [<?php 
        foreach($categories_stats as $cat) {
            echo "'" . addslashes($cat['categorie']) . "',";
        }
    ?>],
    datasets: [{
        data: [<?php 
            foreach($categories_stats as $cat) {
                echo $cat['count'] . ',';
            }
        ?>],
        backgroundColor: [
            '#FFD700', '#008000', '#3498db', '#9b59b6', '#e74c3c', '#f39c12', '#95a5a6'
        ],
        borderWidth: 2,
        borderColor: '#fff'
    }]
};

// Initialisation du graphique des catégories
document.addEventListener('DOMContentLoaded', function() {
    const ctxCategories = document.getElementById('chartCategories');
    if (ctxCategories) {
        new Chart(ctxCategories, {
            type: 'doughnut',
            data: categoriesData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Services par Catégorie'
                    }
                }
            }
        });
    }
});

// Animation au scroll
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = "1";
                entry.target.style.transform = "translateY(0)";
            }
        });
    });

    document.querySelectorAll('.module-card, .stat-card').forEach(card => {
        card.style.opacity = "0";
        card.style.transform = "translateY(30px)";
        card.style.transition = "all 0.6s ease-out";
        observer.observe(card);
    });
});
</script>

<style>
.welcome-section {
    text-align: center;
    margin-bottom: 2rem;
    padding: 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
}

.welcome-section h1 {
    margin-bottom: 0.5rem;
    font-size: 2.5rem;
}

.welcome-section p {
    font-size: 1.2rem;
    opacity: 0.9;
}

.modules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
}

.module-card {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.module-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #3498db;
}

.module-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    text-align: center;
}

.module-card h3 {
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.module-card p {
    color: #7f8c8d;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.module-actions {
    text-align: center;
}

.btn-outline {
    background: transparent;
    border: 2px solid #3498db;
    color: #3498db;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.btn-outline:hover {
    background: #3498db;
    color: white;
}

.activity-list, .alert-list {
    max-height: 300px;
    overflow-y: auto;
}

.activity-item, .alert-item {
    padding: 1rem;
    border-bottom: 1px solid #ecf0f1;
    transition: background 0.3s ease;
}

.activity-item:hover, .alert-item:hover {
    background: #f8f9fa;
}

.activity-info, .alert-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.activity-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
    color: #7f8c8d;
}

.activity-status {
    padding: 0.25rem 0.5rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: bold;
}

.status-en_attente { background: #f39c12; color: white; }
.status-en_cours { background: #3498db; color: white; }
.status_termine { background: #27ae60; color: white; }
.status_recupere { background: #95a5a6; color: white; }

.alert-item {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    margin-bottom: 0.5rem;
    border-radius: 4px;
}

.alert-date {
    color: #e74c3c;
    font-weight: bold;
}

.alert-actions {
    text-align: right;
}

.btn-sm {
    padding: 0.25rem 0.75rem;
    font-size: 0.8rem;
}

.btn-block {
    display: block;
    text-align: center;
    padding: 1rem;
    font-size: 1.1rem;
    font-weight: bold;
}

.stat-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    text-align: center;
}

.stat-label {
    text-align: center;
    font-weight: 600;
    color: #2c3e50;
}

.top-services-list {
    max-height: 250px;
    overflow-y: auto;
}

.service-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    margin-bottom: 0.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #3498db;
}

.service-name {
    font-weight: 600;
    flex: 1;
}

.service-price {
    font-weight: bold;
    color: #27ae60;
    margin: 0 1rem;
}

.service-cat-badge {
    font-size: 1.2rem;
}

/* Animation pour les cartes */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.module-card, .stat-card {
    animation: fadeInUp 0.6s ease-out;
}

.module-card:nth-child(1) { animation-delay: 0.1s; }
.module-card:nth-child(2) { animation-delay: 0.2s; }
.module-card:nth-child(3) { animation-delay: 0.3s; }
.module-card:nth-child(4) { animation-delay: 0.4s; }
.module-card:nth-child(5) { animation-delay: 0.5s; }
.module-card:nth-child(6) { animation-delay: 0.6s; }

/* Responsive */
@media (max-width: 768px) {
    .welcome-section h1 {
        font-size: 2rem;
    }
    
    .modules-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
}
</style>

<?php include 'footer.php'; ?>
