<?php
// /var/www/piece_auto/public/modules/documentation.php
// Page de référence et de documentation des fonctionnalités de l'ERP Pièces Auto.

$page_title = "Documentation & Référence des Modules";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

// Liste des fonctionnalités pour la documentation
$features = [
    [
        'category' => 'Administration & Base',
        'icon' => 'fas fa-cogs',
        'modules' => [
            ['name' => 'Tableau de Bord Exécutif', 'file' => 'index.php', 'description' => 'Aperçu des KPI (CA, Marge) et alertes de stock critique.'],
            ['name' => 'Gestion Utilisateurs & Rôles', 'file' => 'gestion_utilisateurs.php', 'description' => 'CRUD pour les employés et gestion des permissions (Admin, Vendeur, Stockeur).'],
            ['name' => 'Gestion des Pièces (CRUD)', 'file' => 'gestion_pieces.php', 'description' => 'Ajout, modification et suppression des références, gestion du stock min.'],
            ['name' => 'Gestion des Fournisseurs', 'file' => 'gestion_fournisseurs.php', 'description' => 'Ajout, modification et suppression des partenaires fournisseurs.'],
            ['name' => 'Gestion des Clients', 'file' => 'gestion_clients.php', 'description' => 'Ajout, modification et consultation des fiches clients.'],
        ]
    ],
    [
        'category' => 'Stock & Achats',
        'icon' => 'fas fa-warehouse',
        'modules' => [
            ['name' => 'Enregistrer Réception', 'file' => 'reception_achats.php', 'description' => 'Augmentation du stock, calcul du CUMP et traçabilité du mouvement.'],
            ['name' => 'Alertes Stock Minimum', 'file' => 'alertes_stock.php', 'description' => 'Liste des pièces dont le stock est inférieur au seuil défini.'],
            ['name' => 'Suggestions Réapprovisionnement', 'file' => 'suggestion_reappro.php', 'description' => 'Calcul automatique des quantités à commander basées sur la consommation passée.'],
        ]
    ],
    [
        'category' => 'Ventes & Flux',
        'icon' => 'fas fa-shopping-cart',
        'modules' => [
            ['name' => 'Créer Commande de Vente', 'file' => 'creer_commande_vente.php', 'description' => 'Création d\'une commande (gestion du déstockage et du calcul de marge).'],
            ['name' => 'Liste Commandes & Facturation', 'file' => 'liste_commandes_vente.php', 'description' => 'Consultation de l\'historique des ventes et génération de la facturation.'],
        ]
    ],
    [
        'category' => 'Analyses & Audit',
        'icon' => 'fas fa-chart-bar',
        'modules' => [
            ['name' => 'Tableau de Bord Analytique', 'file' => 'tableau_de_bord.php', 'description' => 'Visualisation des KPI via Chart.js (Ventes mensuelles, Répartition du stock en valeur).'],
            ['name' => 'Mouvements de Stock Détaillés', 'file' => 'historique_mouvements_stock.php', 'description' => 'Historique complet des entrées/sorties pour l\'audit et la traçabilité.'],
        ]
    ],
];
?>

<h1><i class="fas fa-book-open"></i> <?= $page_title ?></h1>
<p class="lead">Ce document sert de référence rapide pour tous les modules de l'application ERP Pièces Auto.</p>
<hr>

<?php foreach ($features as $section): ?>
    <div class="card shadow mb-4">
        <div class="card-header bg-dark text-white">
            <i class="<?= $section['icon'] ?>"></i> **<?= $section['category'] ?>**
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Description</th>
                            <th>Lien Rapide</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($section['modules'] as $module): ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($module['name']) ?></td>
                                <td><?= htmlspecialchars($module['description']) ?></td>
                                <td>
                                    <?php if ($module['file'] === 'index.php'): ?>
                                        <a href="<?= $app_root ?>/index.php" class="btn btn-sm btn-outline-primary">Accéder</a>
                                    <?php else: ?>
                                        <a href="<?= $app_root ?>/modules/<?= htmlspecialchars($module['file']) ?>" class="btn btn-sm btn-outline-primary">Accéder</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<div class="alert alert-info mt-4">
    <i class="fas fa-info-circle"></i> **Rappel :** Les URLs des modules se trouvent dans le dossier <code>/public/modules/</code>.
</div>

<?php include '../../includes/footer.php'; ?>
