<?php 
require_once '../../includes/header.php'; 
require_once '../../includes/classes/Database.php';

$dbObj = new Database();
$pdo = $dbObj->getConnection();

try {
    // Requête simplifiée pour éviter les erreurs de colonnes inexistantes
    $query = $pdo->query("SELECT d.*, c.nom, c.prenom FROM diagnostics d 
                           LEFT JOIN clients c ON d.id_client = c.id_client 
                           ORDER BY d.id_diagnostic DESC LIMIT 50");
    $diagnostics = $query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erreur SQL : " . $e->getMessage() . "</div>";
    $diagnostics = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-microchip text-primary"></i> Diagnostics Techniques</h2>
    <a href="formulaire_diagnostic.php" class="btn btn-primary shadow"><i class="fas fa-plus"></i> Nouveau Diagnostic</a>
</div>

<div class="card shadow border-0 p-3">
    <table class="table table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>Réf</th>
                <th>Client</th>
                <th>Symptômes</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($diagnostics as $d): ?>
            <tr>
                <td>#<?= $d['id_diagnostic'] ?></td>
                <td><i class="fas fa-user-circle me-1"></i> <?= htmlspecialchars(($d['prenom'] ?? 'N/A') . ' ' . ($d['nom'] ?? '')) ?></td>
                <td><?= htmlspecialchars($d['symptomes'] ?? 'Aucun') ?></td>
                <td><span class="badge bg-primary"><?= $d['etat'] ?? 'Inconnu' ?></span></td>
                <td><button class="btn btn-sm btn-outline-dark">Consulter</button></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($diagnostics)): ?>
            <tr><td colspan="5" class="text-center py-4">Aucun diagnostic trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
