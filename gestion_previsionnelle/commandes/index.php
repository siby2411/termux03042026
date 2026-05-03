<?php
$page_title = "Gestion des Commandes (Ventes)";
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php'; 

$database = new Database();
$db = $database->getConnection();

$query = "
    SELECT 
        Cmd.CommandeID, 
        C.Nom AS NomClient, 
        Cmd.DateCommande, 
        Cmd.MontantTotal, 
        Cmd.Statut, 
        Cmd.ReferenceFacture
    FROM Commandes Cmd
    JOIN Clients C ON Cmd.ClientID = C.ClientID
    ORDER BY Cmd.DateCommande DESC
";

try {
    $stmt = $db->query($query);
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erreur SQL: Impossible de charger les commandes. " . $e->getMessage() . "</div>";
    $commandes = [];
}
?>

<h1 class="mt-4 text-center"><i class="fas fa-shopping-cart me-2"></i> Liste des Commandes Clients</h1>
<p class="text-muted text-center">Suivi des ventes enregistrées et de leur statut.</p>
<hr>

<div class="row justify-content-center">
    <div class="col-lg-12">

        <div class="d-flex justify-content-end mb-3">
            <a href="creer.php" class="btn btn-success"><i class="fas fa-plus me-2"></i> Enregistrer une Commande</a>
        </div>

        <div class="card shadow-lg mb-4 border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered table-custom mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th>ID</th>
                                <th>Client</th>
                                <th>Date Commande</th>
                                <th>Réf. Facture</th>
                                <th class="text-end">Montant Total</th>
                                <th>Statut</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($commandes)): ?>
                            <?php foreach ($commandes as $cmd): ?>
                            <tr>
                                <td><?= htmlspecialchars($cmd['CommandeID']) ?></td>
                                <td><?= htmlspecialchars($cmd['NomClient']) ?></td>
                                <td><?= date('d/m/Y', strtotime($cmd['DateCommande'])) ?></td>
                                <td><?= htmlspecialchars($cmd['ReferenceFacture'] ?? 'N/A') ?></td>
                                <td class="text-end fw-bold"><?= number_format($cmd['MontantTotal'], 2, ',', ' ') ?> F</td>
                                <td>
                                    <?php 
                                        $badge_class = match($cmd['Statut']) {
                                            'LIVREE' => 'bg-success',
                                            'EN_COURS' => 'bg-info',
                                            'EN_ATTENTE' => 'bg-warning',
                                            'ANNULEE' => 'bg-danger',
                                            default => 'bg-secondary'
                                        };
                                    ?>
                                    <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($cmd['Statut']) ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="voir.php?id=<?= $cmd['CommandeID'] ?>" class="btn btn-sm btn-outline-primary" title="Voir Détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted p-4">Aucune commande enregistrée.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
