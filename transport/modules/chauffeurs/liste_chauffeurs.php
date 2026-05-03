<?php
require_once __DIR__ . '/../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$chauffeurs = $db->query("SELECT * FROM chauffeurs ORDER BY id_chauffeur DESC");
include __DIR__ . '/../../includes/header.php';
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users"></i> Gestion des chauffeurs</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addChauffeurModal">
            <i class="fas fa-plus"></i> Nouveau chauffeur
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Téléphone</th><th>Permis</th><th>Date embauche</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while($c = $chauffeurs->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= $c['id_chauffeur'] ?></td>
                    <td><?= htmlspecialchars($c['nom']) ?></td>
                    <td><?= htmlspecialchars($c['prenom']) ?></td>
                    <td><?= $c['telephone'] ?></td>
                    <td><?= $c['permis_conduire'] ?></td>
                    <td><?= $c['date_embauche'] ?></td>
                    <td><span class="badge <?= $c['statut_chauffeur'] == 'actif' ? 'bg-success' : 'bg-danger' ?>"><?= $c['statut_chauffeur'] ?></span></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="toggleStatus(<?= $c['id_chauffeur'] ?>, '<?= $c['statut_chauffeur'] ?>')">
                            <i class="fas fa-exchange-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteChauffeur(<?= $c['id_chauffeur'] ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                     </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajout Chauffeur -->
<div class="modal fade" id="addChauffeurModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5><i class="fas fa-user-plus"></i> Ajouter un chauffeur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddChauffeur" action="ajax_chauffeur.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Nom *</label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Prénom *</label>
                            <input type="text" name="prenom" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label>Téléphone *</label>
                        <input type="tel" name="telephone" class="form-control" placeholder="77 123 45 67" required>
                    </div>
                    <div class="mt-2">
                        <label>Numéro de permis *</label>
                        <input type="text" name="permis_conduire" class="form-control" required>
                    </div>
                    <div class="mt-2">
                        <label>Date d'embauche</label>
                        <input type="date" name="date_embauche" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mt-2">
                        <label>Statut</label>
                        <select name="statut_chauffeur" class="form-control">
                            <option value="actif">Actif</option>
                            <option value="conge">En congé</option>
                            <option value="suspendu">Suspendu</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$('#formAddChauffeur').on('submit', function(e) {
    e.preventDefault();
    $.post('ajax_chauffeur.php', $(this).serialize(), function(response) {
        if(response.success) location.reload();
        else alert('Erreur: ' + response.message);
    }, 'json');
});

function toggleStatus(id, current) {
    let newStatus = current == 'actif' ? 'suspendu' : 'actif';
    if(confirm('Changer le statut ?')) {
        $.post('ajax_chauffeur.php', {action: 'toggle', id: id, status: newStatus}, function(response) {
            if(response.success) location.reload();
        }, 'json');
    }
}

function deleteChauffeur(id) {
    if(confirm('Supprimer ce chauffeur ?')) {
        $.post('ajax_chauffeur.php', {action: 'delete', id: id}, function(response) {
            if(response.success) location.reload();
        }, 'json');
    }
}
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
