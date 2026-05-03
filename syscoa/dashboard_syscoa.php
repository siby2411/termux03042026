<?php
// dashboard_ohada.php
// Tableau de bord visuel pour le système comptable OHADA
// Conforme aux normes SYSCOHADA UEMOA

// Connexion à la base de données
$host = '127.0.0.1';
$dbname = 'sysco_ohada';
$username = 'root'; // À adapter
$password = '123'; // À adapter

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion: " . $e->getMessage());
}

// Fonction pour récupérer les données d'une table
function getTableData($pdo, $table) {
    $stmt = $pdo->query("SELECT * FROM $table");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour compter les enregistrements
function countRecords($pdo, $table) {
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
    return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
}

// Récupération des données principales
$tables = [
    'classes_ohada' => 'Classes OHADA',
    'comptes_ohada' => 'Plan Comptable',
    'journaux' => 'Journaux Comptables',
    'exercices_comptables' => 'Exercices Comptables',
    'tiers' => 'Tiers (Clients/Fournisseurs)',
    'operations_comptables' => 'Opérations Comptables'
];

$stats = [];
foreach ($tables as $table => $name) {
    $stats[$table] = countRecords($pdo, $table);
}

// Calcul des statistiques financières
$total_debit = $pdo->query("SELECT SUM(montant) as total FROM operations_comptables WHERE compte_debit IS NOT NULL")->fetch(PDO::FETCH_ASSOC)['total'];
$total_credit = $pdo->query("SELECT SUM(montant) as total FROM operations_comptables WHERE compte_credit IS NOT NULL")->fetch(PDO::FETCH_ASSOC)['total'];
$nombre_operations = $stats['operations_comptables'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Système Comptable OHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card {
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        .module-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .bg-ohada {
            background-color: #2c3e50;
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-ohada">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-balance-scale"></i> SYSCOHADA UEMOA
            </a>
            <span class="navbar-text">
                Système Comptable OHADA Harmonisé
            </span>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- En-tête -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-body text-center">
                        <h1><i class="fas fa-chart-line"></i> Tableau de Bord Comptable</h1>
                        <p class="lead">Système conforme aux normes OHADA - Version UEMOA</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cartes de statistiques -->
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-book module-icon"></i>
                        <h4><?php echo $stats['classes_ohada']; ?> Classes</h4>
                        <p>Structure OHADA</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body text-center">
                        <i class="fas fa-file-invoice-dollar module-icon"></i>
                        <h4><?php echo $stats['comptes_ohada']; ?> Comptes</h4>
                        <p>Plan Comptable</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-exchange-alt module-icon"></i>
                        <h4><?php echo $nombre_operations; ?> Opérations</h4>
                        <p>Écritures Comptables</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body text-center">
                        <i class="fas fa-users module-icon"></i>
                        <h4><?php echo $stats['tiers']; ?> Tiers</h4>
                        <p>Clients/Fournisseurs</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Équilibre Comptable -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card <?php echo ($total_debit == $total_credit) ? 'bg-success' : 'bg-danger'; ?> text-white">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-balance-scale-left"></i> ÉQUILIBRE COMPTABLE</h5>
                        <h3>Débit: <?php echo number_format($total_debit, 2, ',', ' '); ?> FCFA</h3>
                        <h3>Crédit: <?php echo number_format($total_credit, 2, ',', ' '); ?> FCFA</h3>
                        <h4>Statut: <?php echo ($total_debit == $total_credit) ? 'ÉQUILIBRÉ ✅' : 'DÉSÉQUILIBRÉ ❌'; ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-dark text-white">
                    <div class="card-body text-center">
                        <h5><i class="fas fa-cogs"></i> PROCHAINES FONCTIONNALITÉS</h5>
                        <ul class="list-unstyled">
                            <li>✓ États Financiers Automatisés</li>
                            <li>✓ Module de Trésorerie</li>
                            <li>✓ Rapports Fiscaux</li>
                            <li>✓ Interface Mobile</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modules du Système -->
        <?php foreach ($tables as $table => $title): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-ohada text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-table"></i> 
                            <?php echo $title; ?> 
                            <span class="badge bg-secondary float-end"><?php echo $stats[$table]; ?> enregistrements</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <?php
                                        $data = getTableData($pdo, $table);
                                        if (!empty($data)) {
                                            foreach (array_keys($data[0]) as $column) {
                                                echo "<th>" . htmlspecialchars($column) . "</th>";
                                            }
                                        }
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($data as $row) {
                                        echo "<tr>";
                                        foreach ($row as $value) {
                                            echo "<td>" . htmlspecialchars($value ?? 'NULL') . "</td>";
                                        }
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Description des Modules -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5><i class="fas fa-info-circle"></i> DESCRIPTION DES MODULES IMPLÉMENTÉS</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6><i class="fas fa-sitemap text-primary"></i> CLASSES OHADA</h6>
                                        <p class="small">Structure hiérarchique des 9 classes du système comptable OHADA. Base de la normalisation comptable.</p>
                                        <span class="badge bg-primary">Fondamental</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6><i class="fas fa-file-invoice-dollar text-success"></i> PLAN COMPTABLE</h6>
                                        <p class="small">Liste exhaustive des comptes avec leur numérotation standard SYSCOHADA et leur nature.</p>
                                        <span class="badge bg-success">Opérationnel</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6><i class="fas fa-book text-warning"></i> JOURNAUX COMPTABLES</h6>
                                        <p class="small">Journaux spécialisés (Achats, Ventes, Banque, Caisse) pour l'organisation des écritures.</p>
                                        <span class="badge bg-warning">Organisation</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6><i class="fas fa-calendar-alt text-info"></i> EXERCICES COMPTABLES</h6>
                                        <p class="small">Gestion des périodes comptables avec dates d'ouverture et de clôture.</p>
                                        <span class="badge bg-info">Temporalité</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6><i class="fas fa-users text-secondary"></i> GESTION DES TIERS</h6>
                                        <p class="small">Clients, fournisseurs et autres partenaires avec leurs comptes collectifs.</p>
                                        <span class="badge bg-secondary">Relations</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h6><i class="fas fa-exchange-alt text-danger"></i> OPÉRATIONS COMPTABLES</h6>
                                        <p class="small">Saisie, validation et suivi des écritures comptables avec intégrité référentielle.</p>
                                        <span class="badge bg-danger">Transactionnel</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Roadmap du Projet -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5><i class="fas fa-road"></i> FEUILLE DE ROUTE DU PROJET</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <span class="badge bg-success rounded-circle p-2 mb-2">✓</span>
                                        <h6>PHASE 1</h6>
                                        <p class="small">Structure de Base</p>
                                        <ul class="list-unstyled small text-start">
                                            <li>✓ Modèle de données</li>
                                            <li>✓ Tables principales</li>
                                            <li>✓ Intégrité OHADA</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-primary">
                                    <div class="card-body text-center">
                                        <span class="badge bg-primary rounded-circle p-2 mb-2">2</span>
                                        <h6>PHASE 2</h6>
                                        <p class="small">États Financiers</p>
                                        <ul class="list-unstyled small text-start">
                                            <li>• Bilan Automatique</li>
                                            <li>• Compte de Résultat</li>
                                            <li>• Annexes Légales</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-info">
                                    <div class="card-body text-center">
                                        <span class="badge bg-info rounded-circle p-2 mb-2">3</span>
                                        <h6>PHASE 3</h6>
                                        <p class="small">Modules Avancés</p>
                                        <ul class="list-unstyled small text-start">
                                            <li>• Immobilisations</li>
                                            <li>• Trésorerie</li>
                                            <li>• Fiscalité</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-secondary">
                                    <div class="card-body text-center">
                                        <span class="badge bg-secondary rounded-circle p-2 mb-2">4</span>
                                        <h6>PHASE 4</h6>
                                        <p class="small">Optimisation</p>
                                        <ul class="list-unstyled small text-start">
                                            <li>• Interface Web</li>
                                            <li>• Rapports PDF</li>
                                            <li>• API REST</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pied de page -->
        <footer class="mt-4 mb-4 text-center text-muted">
            <hr>
            <p>Système Comptable OHADA - Conforme SYSCOHADA UEMOA | Développé pour les cabinets comptables d'Afrique de l'Ouest</p>
            <p><small>© 2024 - Solution Intégrée de Gestion Comptable</small></p>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation simple pour les cartes
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>
