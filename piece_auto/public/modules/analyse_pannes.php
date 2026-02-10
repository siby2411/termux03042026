<?php
// /var/www/piece_auto/public/modules/analyse_pannes.php
// Module d'analyse de pannes pour identifier les pièces les plus vendues / les plus rentables.

$page_title = "Analyse des Pannes & Popularité des Pièces";
require_once __DIR__ . '/../../config/Database.php';

$message = '';
$top_pannes = [];
$top_pieces_par_panne = [];
$db = null;

try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    $message = "Erreur de connexion à la base de données. (" . $e->getMessage() . ")";
}

// INCLUSION DU HEADER APRES LA TENTATIVE DE CONNEXION BDD
include '../../includes/header.php';

if ($message) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
    include '../../includes/footer.php';
    exit;
}

// =================================================================================
// 2. LOGIQUE D'ANALYSE (Continuation)
// =================================================================================
// NOTE: Cette section nécessite des tables spécifiques aux pannes qui n'ont pas encore été définies.
// Nous laissons cette partie vide pour l'instant.

/*
// Exemple de requête pour trouver les 5 pannes les plus fréquentes (si table existe)
$query_pannes = "SELECT nom_panne, COUNT(*) as total_pannes FROM PANNES GROUP BY nom_panne ORDER BY total_pannes DESC LIMIT 5";
$stmt = $db->prepare($query_pannes);
$stmt->execute();
$top_pannes = $stmt->fetchAll(PDO::FETCH_ASSOC);
*/

?>

<h1><i class="fas fa-chart-bar"></i> <?= $page_title ?></h1>
<p class="lead">Ce module est destiné à l'analyse des tendances de pannes et à l'identification des pièces les plus demandées pour optimiser les achats et le stock.</p>
<hr>

<div class="alert alert-info">Ce module d'analyse n'est pas encore fonctionnel car il nécessite la création de tables spécifiques à l'enregistrement des pannes.</div>

<h3>Top 5 des Pannes (À Développer)</h3>
<table class="table table-striped">
    <thead>
        <tr><th>Panne</th><th>Fréquence</th></tr>
    </thead>
    <tbody>
        <tr><td colspan="2" class="text-center">Données non disponibles</td></tr>
    </tbody>
</table>

<?php include '../../includes/footer.php'; ?>
