<?php
require_once __DIR__ . '/../../config/database.php';
$database = new Database();
$db = $database->getConnection();

$buses = $db->query("SELECT * FROM bus ORDER BY id_bus DESC");
include __DIR__ . '/../../includes/header.php';
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-bus"></i> Gestion du Parc Automobile</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBusModal">
            <i class="fas fa-plus"></i> Ajouter un véhicule
        </button>
    </div>
    
    <div class="slogan text-center mb-4" style="background:#ff9900; color:#003366; padding:15px; border-radius:10px;">
        <i class="fas fa-shield-alt"></i> ASSIDUITÉ • SÉCURITÉ • FIABILITÉ • COÛT ABORDABLE
    </div>
    
    <div class="row">
        <?php while($b = $buses->fetch(PDO::FETCH_ASSOC)): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <i class="fas fa-bus" style="font-size: 60px; color: #003366;"></i>
                    <h4 class="mt-2"><?= $b['immatriculation'] ?></h4>
                    <p><strong>Modèle:</strong> <?= $b['modele'] ?? 'Non spécifié' ?></p>
                    <p><strong>Capacité:</strong> <?= $b['capacite_max'] ?> élèves</p>
                    <p><strong>Consommation:</strong> <?= $b['consommation_moyenne'] ?? 'N/A' ?> L/100km</p>
                    <span class="badge <?= $b['statut_bus'] == 'operationnel' ? 'bg-success' : 'bg-warning' ?>"><?= $b['statut_bus'] ?></span>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-warning" onclick="editBus(<?= $b['id_bus'] ?>)"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="deleteBus(<?= $b['id_bus'] ?>)"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Modal Ajout Véhicule -->
<div class="modal fade" id="addBusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5><i class="fas fa-truck"></i> Ajouter un véhicule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddBus" action="ajax_bus.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mt-2">
                        <label>Immatriculation *</label>
                        <input type="text" name="immatriculation" class="form-control" placeholder="DK-123-AB" required>
                    </div>
                    <div class="mt-2">
                        <label>Modèle</label>
                        <input type="text" name="modele" class="form-control" placeholder="Toyota Coaster, Mercedes Sprinter...">
                    </div>
                    <div class="mt-2">
                        <label>Capacité (max 20 élèves) *</label>
                        <input type="number" name="capacite_max" class="form-control" value="20" max="20" required>
                    </div>
                    <div class="mt-2">
                        <label>Consommation moyenne (L/100km)</label>
                        <input type="number" step="0.1" name="consommation_moyenne" class="form-control" placeholder="12.5">
                    </div>
                    <div class="mt-2">
                        <label>Statut</label>
                        <select name="statut_bus" class="form-control">
                            <option value="operationnel">Opérationnel</option>
                            <option value="maintenance">En maintenance</option>
                            <option value="hors_service">Hors service</option>
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
$('#formAddBus').on('submit', function(e) {
    e.preventDefault();
    $.post('ajax_bus.php', $(this).serialize(), function(response) {
        if(response.success) location.reload();
        else alert('Erreur: ' + response.message);
    }, 'json');
});

function editBus(id) {
    window.location.href = 'edit_bus.php?id=' + id;
}

function deleteBus(id) {
    if(confirm('Supprimer ce véhicule ?')) {
        $.post('ajax_bus.php', {action: 'delete', id: id}, function(response) {
            if(response.success) location.reload();
        }, 'json');
    }
}
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
