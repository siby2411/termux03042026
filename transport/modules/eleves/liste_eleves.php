<?php
require_once __DIR__ . '/../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Récupération des écoles et parents pour les selects
$ecoles = $db->query("SELECT id_ecole, nom_ecole FROM ecoles");
$parents = $db->query("SELECT id_parent, nom, prenom, telephone FROM parents");

// Récupération des élèves
$eleves = $db->query("SELECT e.*, ec.nom_ecole, p.nom as parent_nom, p.prenom as parent_prenom 
                      FROM eleves e 
                      JOIN ecoles ec ON e.id_ecole = ec.id_ecole 
                      JOIN parents p ON e.id_parent = p.id_parent 
                      ORDER BY e.id_eleve DESC");

include __DIR__ . '/../../includes/header.php';
?>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-child"></i> Gestion des élèves</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEleveModal">
            <i class="fas fa-plus"></i> Nouvel élève
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-striped table-hover" id="elevesTable">
            <thead class="table-dark">
                <tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Classe</th><th>École</th><th>Parent</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php while($e = $eleves->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= $e['id_eleve'] ?></td>
                    <td><?= htmlspecialchars($e['nom_eleve']) ?></td>
                    <td><?= htmlspecialchars($e['prenom_eleve']) ?></td>
                    <td><?= $e['classe'] ?></td>
                    <td><?= $e['nom_ecole'] ?></td>
                    <td><?= $e['parent_prenom'] . ' ' . $e['parent_nom'] ?></td>
                    <td><span class="badge <?= $e['statut_inscription'] == 'valide' ? 'bg-success' : 'bg-warning' ?>"><?= $e['statut_inscription'] ?></span></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="editEleve(<?= $e['id_eleve'] ?>)"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="deleteEleve(<?= $e['id_eleve'] ?>)"><i class="fas fa-trash"></i></button>
                     </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Ajout Élève -->
<div class="modal fade" id="addEleveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5><i class="fas fa-user-plus"></i> Ajouter un élève</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formAddEleve" action="ajax_eleve.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Nom *</label>
                            <input type="text" name="nom_eleve" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label>Prénom *</label>
                            <input type="text" name="prenom_eleve" class="form-control" required>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label>Classe *</label>
                            <select name="classe" class="form-control" required>
                                <option value="">Sélectionner</option>
                                <option>CI</option><option>CP</option><option>CE1</option><option>CE2</option>
                                <option>CM1</option><option>CM2</option><option>6ème</option><option>5ème</option>
                                <option>4ème</option><option>3ème</option><option>Seconde</option><option>1ère</option><option>Tle</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>École *</label>
                            <select name="id_ecole" class="form-control" required>
                                <option value="">Sélectionner</option>
                                <?php
                                $ecoles = $db->query("SELECT id_ecole, nom_ecole FROM ecoles");
                                while($ec = $ecoles->fetch()): ?>
                                <option value="<?= $ec['id_ecole'] ?>"><?= $ec['nom_ecole'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <label>Parent *</label>
                            <select name="id_parent" class="form-control" required>
                                <option value="">Sélectionner un parent</option>
                                <?php
                                $parents = $db->query("SELECT id_parent, nom, prenom, telephone FROM parents");
                                while($p = $parents->fetch()): ?>
                                <option value="<?= $p['id_parent'] ?>"><?= $p['prenom'] . ' ' . $p['nom'] . ' - ' . $p['telephone'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <label>Point de prise en charge</label>
                            <input type="text" name="point_prise_en_charge" class="form-control" placeholder="Adresse de prise en charge">
                        </div>
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
$('#formAddEleve').on('submit', function(e) {
    e.preventDefault();
    $.post('ajax_eleve.php', $(this).serialize(), function(response) {
        if(response.success) {
            location.reload();
        } else {
            alert('Erreur: ' + response.message);
        }
    }, 'json');
});

function editEleve(id) {
    window.location.href = 'edit_eleve.php?id=' + id;
}

function deleteEleve(id) {
    if(confirm('Supprimer cet élève ?')) {
        $.post('ajax_eleve.php', {action: 'delete', id: id}, function(response) {
            if(response.success) location.reload();
            else alert('Erreur');
        }, 'json');
    }
}
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
