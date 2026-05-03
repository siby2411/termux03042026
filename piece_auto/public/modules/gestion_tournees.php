<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../includes/auth_check.php';
// /var/www/piece_auto/public/modules/gestion_tournees.php
// Ce module est en cours de développement. Il est minimaliste pour l'instant.

$page_title = "Optimisation des Tournées de Livraison";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php'; 

$database = new Database();
$db = $database->getConnection();

$message = '';
$tournees = [];
$ventes_a_affecter = []; // Ventes non encore affectées à une tournée
$chauffeurs = ['Pierre D.', 'Julie L.', 'Express Ext.']; // Simuler la liste des chauffeurs

try {
    // Le code pour récupérer les ventes, les chauffeurs et gérer les tournées irait ici.

    // Exemple de récupération des ventes non livrées :
    $query_ventes = "SELECT cv.id_commande_vente, cv.montant_total, c.adresse
                     FROM COMMANDE_VENTE cv
                     LEFT JOIN CLIENTS c ON cv.id_client = c.id_client
                     WHERE cv.statut_livraison = 'En attente' 
                     ORDER BY cv.date_commande DESC";
                     
    // NOTE: Il faudrait ajouter la colonne statut_livraison à COMMANDE_VENTE pour que cela fonctionne.
    // Si la colonne n'existe pas, cette requête provoquera une erreur.
    // Pour l'instant, on laisse la requête en commentaire pour éviter un nouveau bug SQL.
    
    // $stmt_ventes = $db->prepare($query_ventes);
    // $stmt_ventes->execute();
    // $ventes_a_affecter = $stmt_ventes->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $message = '<div class="alert alert-danger">Erreur de chargement des données : ' . $e->getMessage() . '</div>';
}

?>

<h1><i class="fas fa-route"></i> <?= $page_title ?></h1>
<p class="lead">Ce module permet d'affecter les commandes clients à livrer aux chauffeurs et d'optimiser les tournées.</p>
<hr>

<?= $message ?>

<div class="alert alert-info">Ce module est en cours de construction. Prochaines étapes : Affichage des ventes à livrer et affectation aux chauffeurs.</div>

<?php include '../../includes/footer.php'; ?>
