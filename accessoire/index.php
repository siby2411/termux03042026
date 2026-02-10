<?php
// /var/www/piece_auto/index.php
// Point d'entrée principal après le login

$page_title = "Accueil du Tableau de Bord";
include 'includes/header.php'; 

// Les variables $username et $user_role sont définies dans includes/header.php
?>

<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Bienvenue, <?= htmlspecialchars($username) ?>!</h1>
        <p class="lead">Votre rôle actuel est : **<?= htmlspecialchars($user_role) ?>**.</p>
        <p>Utilisez le menu de navigation à gauche pour accéder aux différents modules de l'ERP.</p>

        <div class="alert alert-info mt-5">
            <h4 class="alert-heading">Démarrer rapidement :</h4>
            <ul>
                <?php 
                // Utilisation de la variable $user_role définie dans header.php
                if (in_array($user_role, ['Admin', 'Stockeur'])): 
                ?>
                    <li><i class="fas fa-box"></i> **Référentiel Pièces** : <a href="<?= $GLOBALS['app_root'] ?>/modules/gestion_pieces.php">Gérer les produits et les prix</a></li>
                    <li><i class="fas fa-truck-loading"></i> **Stock** : <a href="<?= $GLOBALS['app_root'] ?>/modules/gestion_stock.php">Consulter/Ajuster l'inventaire</a></li>
                <?php 
                endif;
                
                if (in_array($user_role, ['Admin', 'Vendeur'])): 
                ?>
                    <li><i class="fas fa-file-invoice-dollar"></i> **Ventes** : <a href="<?= $GLOBALS['app_root'] ?>/modules/creation_vente.php">Créer une nouvelle transaction de vente</a></li>
                <?php 
                endif;
                
                if (in_array($user_role, ['Admin', 'Analyse'])): 
                ?>
                    <li><i class="fas fa-chart-pie"></i> **Analyse** : <a href="<?= $GLOBALS['app_root'] ?>/modules/reporting_strategique.php">Consulter le tableau de bord de performance</a></li>
                <?php 
                endif;
                
                if ($user_role == 'Admin'): 
                ?>
                    <li><i class="fas fa-users-cog"></i> **Admin** : <a href="<?= $GLOBALS['app_root'] ?>/modules/gestion_utilisateurs.php">Gérer les accès et les rôles utilisateurs</a></li>
                <?php 
                endif;
                ?>
            </ul>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
?>
