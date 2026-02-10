<?php
include '../../includes/header.php';
include '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Recherche et filtrage
$search = $_GET['search'] ?? '';
$where = 'WHERE p.is_active = 1';
$params = [];

if (!empty($search)) {
    $where .= " AND (p.nom LIKE :search OR p.prenom LIKE :search OR p.code_patient LIKE :search OR p.telephone LIKE :search)";
    $params[':search'] = "%$search%";
}

$query = "SELECT p.* FROM patients p $where ORDER BY p.date_creation DESC";
$stmt = $db->prepare($query);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$patients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques pour les cartes
$query_total = "SELECT COUNT(*) as total FROM patients WHERE is_active = 1";
$stmt_total = $db->prepare($query_total);
$stmt_total->execute();
$total_patients = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];

$query_today = "SELECT COUNT(*) as total FROM patients WHERE DATE(date_creation) = CURDATE() AND is_active = 1";
$stmt_today = $db->prepare($query_today);
$stmt_today->execute();
$today_patients = $stmt_today->fetch(PDO::FETCH_ASSOC)['total'];
?>

<div class="row">
    <div class="col-12">
        <!-- Header avec titre et boutons -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-primary mb-1">
                    <i class="bi bi-people me-2"></i>Gestion des Patients
                </h2>
                <p class="text-muted mb-0">Liste complète des patients actifs de la clinique</p>
            </div>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Nouveau Patient
            </a>
        </div>

        <!-- Cartes de statistiques -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-2">Total Patients Actifs</h5>
                                <h3 class="fw-bold text-primary mb-0"><?php echo $total_patients; ?></h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-people-fill text-primary fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card border-start border-success border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-2">Nouveaux Aujourd'hui</h5>
                                <h3 class="fw-bold text-success mb-0"><?php echo $today_patients; ?></h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-person-plus text-success fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card border-start border-info border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-2">Patients ce Mois</h5>
                                <h3 class="fw-bold text-info mb-0">
                                    <?php
                                    $query_month = "SELECT COUNT(*) as total FROM patients WHERE MONTH(date_creation) = MONTH(CURDATE()) AND YEAR(date_creation) = YEAR(CURDATE()) AND is_active = 1";
                                    $stmt_month = $db->prepare($query_month);
                                    $stmt_month->execute();
                                    echo $stmt_month->fetch(PDO::FETCH_ASSOC)['total'];
                                    ?>
                                </h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-calendar-month text-info fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card border-start border-warning border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-2">Patients Archivés</h5>
                                <h3 class="fw-bold text-warning mb-0">
                                    <?php
                                    $query_archived = "SELECT COUNT(*) as total FROM patients WHERE is_active = 0";
                                    $stmt_archived = $db->prepare($query_archived);
                                    $stmt_archived->execute();
                                    echo $stmt_archived->fetch(PDO::FETCH_ASSOC)['total'];
                                    ?>
                                </h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-archive text-warning fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte principale avec tableau -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-ul me-2"></i>Liste des Patients Actifs
                        </h5>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" placeholder="Rechercher patient..." value="<?php echo htmlspecialchars($search); ?>">
                            <button type="submit" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i>
                            </button>
                            <?php if (!empty($search)): ?>
                                <a href="list.php" class="btn btn-outline-secondary ms-2">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Code Patient</th>
                                <th>Informations Patient</th>
                                <th>Contact</th>
                                <th>Date Naissance</th>
                                <th>Date Inscription</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($patients) > 0): ?>
                                <?php foreach ($patients as $patient): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-primary"><?php echo htmlspecialchars($patient['code_patient']); ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-person text-muted"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0 fw-semibold"><?php echo htmlspecialchars($patient['prenom'] . ' ' . $patient['nom']); ?></h6>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($patient['civilite']); ?> • 
                                                    <?php echo htmlspecialchars($patient['genre']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-telephone me-1"></i>
                                                <?php echo htmlspecialchars($patient['telephone']); ?>
                                            </small>
                                            <?php if (!empty($patient['email'])): ?>
                                                <small class="text-muted">
                                                    <i class="bi bi-envelope me-1"></i>
                                                    <?php echo htmlspecialchars($patient['email']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($patient['date_naissance']): ?>
                                            <span class="text-muted">
                                                <?php echo date('d/m/Y', strtotime($patient['date_naissance'])); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Non spécifié</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($patient['date_creation'])); ?>
                                        </small>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group" role="group">
                                            <a href="dossier.php?id=<?php echo $patient['id']; ?>" class="btn btn-sm btn-outline-primary btn-action" title="Dossier médical">
                                                <i class="bi bi-file-medical"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $patient['id']; ?>" class="btn btn-sm btn-outline-warning btn-action" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="../rendezvous/add.php?id_patient=<?php echo $patient['id']; ?>" class="btn btn-sm btn-outline-success btn-action" title="Nouveau RDV">
                                                <i class="bi bi-calendar-plus"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $patient['id']; ?>" class="btn btn-sm btn-outline-danger btn-action" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce patient?')">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-people display-4 d-block mb-3"></i>
                                            <?php if (!empty($search)): ?>
                                                Aucun patient trouvé pour "<?php echo htmlspecialchars($search); ?>"
                                            <?php else: ?>
                                                Aucun patient actif trouvé
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($search)): ?>
                                            <a href="list.php" class="btn btn-primary mt-3">
                                                <i class="bi bi-arrow-left me-2"></i>Voir tous les patients
                                            </a>
                                        <?php else: ?>
                                            <a href="add.php" class="btn btn-primary mt-3">
                                                <i class="bi bi-plus-circle me-2"></i>Ajouter le premier patient
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (count($patients) > 0): ?>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Affichage de <strong><?php echo count($patients); ?></strong> patient(s) actif(s)
                        <?php if (!empty($search)): ?>
                            pour la recherche "<?php echo htmlspecialchars($search); ?>"
                        <?php endif; ?>
                    </small>
                    <div>
                        <a href="archives.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-archive me-1"></i>Voir les archives
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.btn-action {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 4px;
    transition: all 0.2s;
}

.btn-action:hover {
    transform: translateY(-1px);
}

.table th {
    font-weight: 600;
    color: #374151;
    background-color: #f8fafc;
}

.stat-card {
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
</style>

<script>
// Confirmation de suppression avec message personnalisé
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('a[href*="delete.php"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const patientName = this.closest('tr').querySelector('h6').textContent;
            const patientCode = this.closest('tr').querySelector('.fw-bold').textContent;
            
            if (!confirm(`Êtes-vous sûr de vouloir supprimer le patient :\n${patientName} (${patientCode}) ?\n\nCette action peut être irréversible.`)) {
                e.preventDefault();
            }
        });
    });

    // Auto-focus sur la recherche
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput && !searchInput.value) {
        searchInput.focus();
    }
});
</script>

<?php include '../../includes/footer.php'; ?>
