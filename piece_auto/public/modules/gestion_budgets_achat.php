<?php
// /var/www/piece_auto/public/modules/gestion_budgets_achat.php
include_once '../../config/Database.php'; 
include '../../includes/header.php'; 

$page_title = "Gestion des Budgets d'Achat";
$message = "";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Requête corrigée
    $query = "SELECT id_budget, annee_budget, nom_budget, montant_total_cible FROM BUDGETS_ACHAT ORDER BY annee_budget DESC";
    $stmt = $pdo->query($query);
    $budgets = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $message = "Erreur de base de données : " . $e->getMessage();
    $budgets = [];
}
?>
<div class="container-fluid">
    <h1><i class="fas fa-money-bill-alt"></i> <?= $page_title ?></h1>
    <p class="lead">Planification et suivi des budgets alloués aux achats de pièces.</p>
    
    <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID Budget</th>
                    <th>Année</th>
                    <th>Nom</th>
                    <th>Montant Cible (Total)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($budgets as $budget): ?>
                <tr>
                    <td><?= htmlspecialchars($budget['id_budget']) ?></td>
                    <td><?= htmlspecialchars($budget['annee_budget']) ?></td>
                    <td><?= htmlspecialchars($budget['nom_budget']) ?></td>
                    <td><?= number_format($budget['montant_total_cible'], 2, ',', ' ') ?> €</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include '../../includes/footer.php'; ?>
