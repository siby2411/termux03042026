<?php
// /list_grandlivre.php
$page_title = "Grand Livre - Toutes les Écritures";
include_once 'includes/header.php'; 
include_once 'config/db.php'; 
$database = new Database();
$db = $database->getConnection();

// Logique pour récupérer les données du Grand Livre
$query = "SELECT DateComptable, Montant, CompteDebiteur, CompteCrediteur, Libelle FROM GrandLivre ORDER BY DateComptable DESC LIMIT 100";
$stmt = $db->prepare($query);
$stmt->execute();
$resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container my-5">
    <h1 class="mb-4 text-dark"><i class="fas fa-book me-2"></i> Grand Livre (Historique)</h1>
    <a href="form_ecriture.php" class="btn btn-success mb-3"><i class="fas fa-plus-circle me-2"></i> Nouvelle Écriture</a>
    
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Montant (€)</th>
                            <th>Débit (Emploi)</th>
                            <th>Crédit (Ressource)</th>
                            <th>Libellé</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($resultats) > 0): ?>
                            <?php foreach ($resultats as $row): ?>
                            <tr>
                                <td><?= date('d/m/Y', strtotime($row['DateComptable'])) ?></td>
                                <td class="text-end fw-bold"><?= number_format($row['Montant'], 2, ',', ' ') ?></td>
                                <td><?= htmlspecialchars($row['CompteDebiteur']) ?></td>
                                <td><?= htmlspecialchars($row['CompteCrediteur']) ?></td>
                                <td><?= htmlspecialchars($row['Libelle']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center text-muted">Aucune écriture trouvée dans le Grand Livre.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
