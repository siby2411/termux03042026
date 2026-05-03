<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$db = (new Database())->getConnection();

$page_title = "Répertoire Clients";
include '../../includes/header.php';

$stmt = $db->query("SELECT * FROM CLIENTS ORDER BY nom ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="fas fa-users text-primary"></i> Répertoire Clients</h3>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addClientModal">
        <i class="fas fa-user-plus me-2"></i> Nouveau Client
    </button>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nom Complet</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($c = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><i class="fas fa-user-circle me-2 text-muted"></i><b><?= strtoupper($c['nom']) ?></b> <?= $c['prenom'] ?></td>
                    <td><?= $c['telephone'] ?: '<span class="text-muted small">Non renseigné</span>' ?></td>
                    <td><?= $c['email'] ?></td>
                    <td class="text-center">
                        <div class="btn-group">
                            <a href="analyse_clients.php?id=<?= $c['id_client'] ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-chart-bar"></i></a>
                            <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addClientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="traitement_client.php" method="POST" class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Enregistrer un nouveau client</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Prénom</label>
                        <input type="text" name="prenom" class="form-control" placeholder="Ex: Moussa" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Nom</label>
                        <input type="text" name="nom" class="form-control" placeholder="Ex: SOW" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Téléphone (WhatsApp)</label>
                        <input type="text" name="telephone" class="form-control" placeholder="+221 ..." required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="client@email.com">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Adresse / Quartier</label>
                        <input type="text" name="adresse" class="form-control" placeholder="Ex: Grand Yoff, Dakar">
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-modal="modal">Annuler</button>
                <button type="submit" class="btn btn-primary px-4">Enregistrer le Client</button>
            </div>
        </form>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
