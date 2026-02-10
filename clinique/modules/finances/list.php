<?php
include '../../includes/header.php';
include '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT f.*, 
                 p.code_patient, p.nom as patient_nom, p.prenom as patient_prenom
          FROM factures f
          JOIN patients p ON f.id_patient = p.id
          ORDER BY f.date_facture DESC
          LIMIT 50";
$stmt = $db->prepare($query);
$stmt->execute();
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-primary mb-1">
                    <i class="bi bi-currency-dollar me-2"></i>Gestion Financière
                </h2>
                <p class="text-muted mb-0">Factures et paiements</p>
            </div>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Nouvelle Facture
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">Liste des Factures</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">N° Facture</th>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($factures) > 0): ?>
                                <?php foreach ($factures as $facture): ?>
                                <tr>
                                    <td class="ps-4">
                                        <strong class="text-primary"><?php echo htmlspecialchars($facture['numero_facture']); ?></strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong class="text-success"><?php echo htmlspecialchars($facture['code_patient']); ?></strong><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($facture['patient_prenom'] . ' ' . $facture['patient_nom']); ?></small>
                                        </div>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($facture['date_facture'])); ?></td>
                                    <td><?php echo number_format($facture['montant_total'], 0, ',', ' '); ?> FCFA</td>
                                    <td>
                                        <?php
                                        $statut_class = '';
                                        $statut_text = '';
                                        switch($facture['statut']) {
                                            case 'payee':
                                                $statut_class = 'bg-success';
                                                $statut_text = 'Payée';
                                                break;
                                            case 'partiel':
                                                $statut_class = 'bg-warning';
                                                $statut_text = 'Paiement partiel';
                                                break;
                                            case 'impayee':
                                                $statut_class = 'bg-danger';
                                                $statut_text = 'Impayée';
                                                break;
                                            default:
                                                $statut_class = 'bg-secondary';
                                                $statut_text = 'Inconnu';
                                        }
                                        ?>
                                        <span class="badge <?php echo $statut_class; ?>"><?php echo $statut_text; ?></span>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group" role="group">
                                            <a href="view.php?id=<?php echo $facture['id']; ?>" class="btn btn-sm btn-outline-primary" title="Voir">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $facture['id']; ?>" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="print.php?id=<?php echo $facture['id']; ?>" class="btn btn-sm btn-outline-info" title="Imprimer" target="_blank">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="confirmDelete(<?php echo $facture['id']; ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox display-4 d-block mb-2"></i>
                                        Aucune facture trouvée
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(factureId) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette facture ? Cette action est irréversible.')) {
        window.location.href = 'delete.php?id=' + factureId;
    }
}
</script>

<?php include '../../includes/footer.php'; ?>
