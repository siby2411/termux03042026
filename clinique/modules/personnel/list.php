<?php
include '../../includes/header.php';
include '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT p.*, d.nom as departement_nom, s.nom as specialite_nom 
          FROM personnel p 
          LEFT JOIN departements d ON p.id_departement = d.id 
          LEFT JOIN specialites s ON p.id_specialite = s.id 
          WHERE p.is_active = 1
          ORDER BY p.role, p.nom, p.prenom";
$stmt = $db->prepare($query);
$stmt->execute();
$personnel = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Compter par rôle
$roles_count = [];
foreach ($personnel as $pers) {
    $role = $pers['role'];
    $roles_count[$role] = ($roles_count[$role] ?? 0) + 1;
}

// Statistiques générales
$query_total = "SELECT COUNT(*) as total FROM personnel WHERE is_active = 1";
$stmt_total = $db->prepare($query_total);
$stmt_total->execute();
$total_personnel = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];

$query_inactive = "SELECT COUNT(*) as total FROM personnel WHERE is_active = 0";
$stmt_inactive = $db->prepare($query_inactive);
$stmt_inactive->execute();
$inactive_personnel = $stmt_inactive->fetch(PDO::FETCH_ASSOC)['total'];
?>

<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="h4 fw-bold text-primary mb-1">
                    <i class="bi bi-person-badge me-2"></i>Gestion du Personnel
                </h2>
                <p class="text-muted mb-0">Médecins, infirmiers et personnel administratif actifs</p>
            </div>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-person-plus me-2"></i>Nouveau Personnel
            </a>
        </div>

        <!-- Cartes de statistiques générales -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card border-start border-primary border-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h5 class="card-title text-muted mb-2">Total Personnel Actif</h5>
                                <h3 class="fw-bold text-primary mb-0"><?php echo $total_personnel; ?></h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-people-fill text-primary fs-1"></i>
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
                                <h5 class="card-title text-muted mb-2">Personnel Inactif</h5>
                                <h3 class="fw-bold text-warning mb-0"><?php echo $inactive_personnel; ?></h3>
                            </div>
                            <div class="flex-shrink-0">
                                <i class="bi bi-person-x text-warning fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques par rôle -->
        <div class="row mb-4">
            <?php foreach ($roles_count as $role => $count): 
                $colors = [
                    'Medecin' => ['bg' => 'primary', 'icon' => 'bi-heart-pulse', 'text' => 'Médecins'],
                    'Infirmier' => ['bg' => 'success', 'icon' => 'bi-bandaid', 'text' => 'Infirmiers'],
                    'Secretaire' => ['bg' => 'info', 'icon' => 'bi-person-lines-fill', 'text' => 'Secrétaires'],
                    'Comptable' => ['bg' => 'warning', 'icon' => 'bi-calculator', 'text' => 'Comptables'],
                    'Admin' => ['bg' => 'danger', 'icon' => 'bi-shield-check', 'text' => 'Administrateurs']
                ];
                $color = $colors[$role] ?? ['bg' => 'secondary', 'icon' => 'bi-person', 'text' => $role];
            ?>
            <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                <div class="card stat-card border-0 bg-<?php echo $color['bg']; ?>-subtle">
                    <div class="card-body text-center p-3">
                        <i class="bi <?php echo $color['icon']; ?> text-<?php echo $color['bg']; ?> fs-1 mb-2"></i>
                        <h4 class="fw-bold text-<?php echo $color['bg']; ?> mb-1"><?php echo $count; ?></h4>
                        <small class="text-muted"><?php echo $color['text']; ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Tableau du personnel -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-ul me-2"></i>Liste du Personnel Actif
                        </h5>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="archives.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-archive me-1"></i>Voir les archives
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Matricule</th>
                                <th>Informations Personnelles</th>
                                <th>Rôle & Spécialité</th>
                                <th>Département</th>
                                <th>Coordonnées</th>
                                <th class="text-center pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($personnel) > 0): ?>
                                <?php foreach ($personnel as $pers): 
                                    $role_colors = [
                                        'Medecin' => 'primary',
                                        'Infirmier' => 'success', 
                                        'Secretaire' => 'info',
                                        'Comptable' => 'warning',
                                        'Admin' => 'danger'
                                    ];
                                    $role_color = $role_colors[$pers['role']] ?? 'secondary';
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($pers['matricule']); ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="bg-<?php echo $role_color; ?>-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-person text-<?php echo $role_color; ?>"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-0 fw-semibold"><?php echo htmlspecialchars($pers['prenom'] . ' ' . $pers['nom']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($pers['civilite']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge bg-<?php echo $role_color; ?> mb-1"><?php echo htmlspecialchars($pers['role']); ?></span>
                                            <?php if ($pers['specialite_nom']): ?>
                                                <br>
                                                <small class="text-muted"><?php echo htmlspecialchars($pers['specialite_nom']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo htmlspecialchars($pers['departement_nom']); ?></span>
                                    </td>
                                    <td>
                                        <div>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-telephone me-1"></i><?php echo htmlspecialchars($pers['telephone']); ?>
                                            </small>
                                            <small class="text-muted">
                                                <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars($pers['email']); ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td class="text-center pe-4">
                                        <div class="btn-group" role="group">
                                            <a href="view.php?id=<?php echo $pers['id']; ?>" class="btn btn-sm btn-outline-primary btn-action" title="Voir le profil">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="edit.php?id=<?php echo $pers['id']; ?>" class="btn btn-sm btn-outline-warning btn-action" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="delete.php?id=<?php echo $pers['id']; ?>" class="btn btn-sm btn-outline-danger btn-action" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce membre du personnel?')">
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
                                            <i class="bi bi-person-x display-4 d-block mb-3"></i>
                                            Aucun personnel actif trouvé
                                        </div>
                                        <a href="add.php" class="btn btn-primary mt-3">
                                            <i class="bi bi-person-plus me-2"></i>Ajouter le premier membre
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if (count($personnel) > 0): ?>
            <div class="card-footer bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Affichage de <strong><?php echo count($personnel); ?></strong> membre(s) du personnel actif(s)
                    </small>
                    <small class="text-muted">
                        <?php echo date('d/m/Y H:i'); ?>
                    </small>
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

.role-badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
}

.stat-card {
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.table th {
    font-weight: 600;
    color: #374151;
    background-color: #f8fafc;
}
</style>

<script>
// Confirmation de suppression avec message personnalisé
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('a[href*="delete.php"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const personnelName = this.closest('tr').querySelector('h6').textContent;
            const personnelMatricule = this.closest('tr').querySelector('.badge').textContent;
            const personnelRole = this.closest('tr').querySelector('.bg-primary, .bg-success, .bg-info, .bg-warning, .bg-danger').textContent;
            
            if (!confirm(`Êtes-vous sûr de vouloir supprimer :\n${personnelName} (${personnelMatricule})\nRôle : ${personnelRole}\n\nCette action peut être irréversible.`)) {
                e.preventDefault();
            }
        });
    });

    // Animation au survol des lignes
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8f9fa';
        });
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
