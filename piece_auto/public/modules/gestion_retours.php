<?php
// /var/www/piece_auto/public/modules/gestion_retours.php
include_once '../../config/Database.php'; 
include '../../includes/header.php'; 

$page_title = "Gestion des Retours et Garanties";
$message = "";

try {
    $db = new Database();
    $pdo = $db->getConnection();
    
    // Requête corrigée
    $query = "
        SELECT 
            R.id_retour, C.nom, R.date_retour, R.statut 
        FROM RETOURS_GARANTIE R
        JOIN CLIENTS C ON R.id_client = C.id_client
        ORDER BY R.date_retour DESC
    ";
    $stmt = $pdo->query($query);
    $retours = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $message = "Erreur de base de données : " . $e->getMessage();
    $retours = [];
}
?>
<div class="container-fluid">
    <h1><i class="fas fa-undo-alt"></i> <?= $page_title ?></h1>
    <p class="lead">Suivi des demandes de retour et des gestions de garantie.</p>
    
    <?php if ($message): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID Retour</th>
                    <th>Client</th>
                    <th>Date Retour</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($retours as $retour): ?>
                <tr>
                    <td><?= htmlspecialchars($retour['id_retour']) ?></td>
                    <td><?= htmlspecialchars($retour['nom']) ?></td>
                    <td><?= htmlspecialchars($retour['date_retour']) ?></td>
                    <td><?= htmlspecialchars($retour['statut']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<?php include '../../includes/footer.php'; ?>
