<?php
require_once __DIR__ . '/../includes/db.php'; // Vérifiez que ce chemin est correct !
$page_title = "Journal des Écritures - OMEGA";
include "layout.php";

try {
    $query = "SELECT e.*, p1.intitule_compte as nom_debit, p2.intitule_compte as nom_credit 
              FROM ECRITURES_COMPTABLES e
              LEFT JOIN PLAN_COMPTABLE_UEMOA p1 ON e.compte_debite_id = p1.compte_id
              LEFT JOIN PLAN_COMPTABLE_UEMOA p2 ON e.compte_credite_id = p2.compte_id
              ORDER BY e.date_ecriture DESC";
    $ecritures = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Erreur SQL : " . $e->getMessage() . "</div>";
    $ecritures = [];
}
?>

<div class="form-centered">
    <div class="card card-omega">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0 text-primary">Journal Général</h4>
            <a href="ecriture.php" class="btn btn-omega btn-sm"><i class="bi bi-plus-circle"></i> Nouvelle Saisie</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-omega">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Libellé</th>
                            <th>Débit (Compte)</th>
                            <th>Crédit (Compte)</th>
                            <th>Montant (F CFA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ecritures as $e): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($e['date_ecriture'])) ?></td>
                            <td><?= htmlspecialchars($e['libelle']) ?></td>
                            <td><span class="badge bg-light text-dark"><?= $e['compte_debite_id'] ?></span> <?= $e['nom_debit'] ?></td>
                            <td><span class="badge bg-light text-dark"><?= $e['compte_credite_id'] ?></span> <?= $e['nom_credit'] ?></td>
                            <td class="text-end fw-bold"><?= number_format($e['montant'], 0, ',', ' ') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include "footer.php"; ?>
