<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$db = (new Database())->getConnection();

$page_title = "Gestion des Fournisseurs";
include '../../includes/header.php';

$stmt = $db->query("SELECT * FROM FOURNISSEURS ORDER BY nom_fournisseur ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold"><i class="fas fa-truck text-primary me-2"></i> Partenaires Fournisseurs</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFournisseurModal">
        <i class="fas fa-plus-circle me-2"></i> Nouveau Fournisseur
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Raison Sociale</th>
                    <th>Contact Principal</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($f = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td class="fw-bold text-dark"><?= $f['nom_fournisseur'] ?></td>
                    <td><?= $f['contact_nom'] ?: '<span class="text-muted italic">N/A</span>' ?></td>
                    <td><span class="badge bg-light text-dark border"><?= $f['telephone'] ?></span></td>
                    <td><?= $f['email'] ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-light border"><i class="fas fa-edit"></i></button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addFournisseurModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="traitement_fournisseur.php" method="POST" class="modal-content border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title">Nouveau Partenaire</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nom de l'entreprise (Ex: CFAO)</label>
                    <input type="text" name="nom_fournisseur" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nom du contact</label>
                    <input type="text" name="contact_nom" class="form-control" placeholder="M. X...">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label small fw-bold">Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                <button type="submit" class="btn btn-primary">Enregistrer le Partenaire</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
