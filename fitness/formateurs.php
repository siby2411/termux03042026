<?php
require_once 'config/database.php';
include 'header.php';

$database = new Database();
$db = $database->getConnection();

// Traitement ajout/modification/suppression
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $matricule = 'FM-' . date('Y') . '-' . rand(1000, 9999);
            $query = "INSERT INTO formateurs (matricule, nom, prenom, email, telephone, specialite, diplomes, salaire_base, statut) 
                      VALUES (:mat, :nom, :prenom, :email, :tel, :specialite, :diplomes, :salaire, :statut)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':mat' => $matricule,
                ':nom' => $_POST['nom'],
                ':prenom' => $_POST['prenom'],
                ':email' => $_POST['email'],
                ':tel' => $_POST['telephone'],
                ':specialite' => $_POST['specialite'],
                ':diplomes' => $_POST['diplomes'],
                ':salaire' => $_POST['salaire_base'],
                ':statut' => $_POST['statut']
            ]);
            $success = "Formateur ajouté avec succès! Matricule: " . $matricule;
        }
        
        if ($_POST['action'] == 'edit' && isset($_POST['id'])) {
            $query = "UPDATE formateurs SET nom=:nom, prenom=:prenom, email=:email, telephone=:tel, 
                      specialite=:specialite, diplomes=:diplomes, salaire_base=:salaire, statut=:statut 
                      WHERE id=:id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':id' => $_POST['id'],
                ':nom' => $_POST['nom'],
                ':prenom' => $_POST['prenom'],
                ':email' => $_POST['email'],
                ':tel' => $_POST['telephone'],
                ':specialite' => $_POST['specialite'],
                ':diplomes' => $_POST['diplomes'],
                ':salaire' => $_POST['salaire_base'],
                ':statut' => $_POST['statut']
            ]);
            $success = "Formateur modifié avec succès!";
        }
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $query = "DELETE FROM formateurs WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $_GET['delete']]);
    $success = "Formateur supprimé!";
}

// Récupération des formateurs
$query = "SELECT * FROM formateurs ORDER BY id DESC";
$formateurs = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-chalkboard-user"></i> Gestion des Formateurs</h3>
        </div>
        <div class="card-body">
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addFormateurModal">
                <i class="fas fa-plus"></i> Nouveau Formateur
            </button>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Matricule</th><th>Nom & Prénom</th><th>Email</th><th>Spécialité</th><th>Salaire</th><th>Statut</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($formateurs as $f): ?>
                        <tr>
                            <td><strong><?= $f['matricule'] ?></strong></td>
                            <td><?= htmlspecialchars($f['prenom'] . ' ' . $f['nom']) ?></td>
                            <td><?= htmlspecialchars($f['email']) ?></td>
                            <td><span class="badge bg-info"><?= $f['specialite'] ?></span></td>
                            <td><?= number_format($f['salaire_base'], 0, ',', ' ') ?> FCFA</td>
                            <td><span class="badge bg-<?= $f['statut']=='actif'?'success':'danger' ?>"><?= $f['statut'] ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editFormateur(<?= htmlspecialchars(json_encode($f)) ?>)"><i class="fas fa-edit"></i></button>
                                <a href="?delete=<?= $f['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce formateur?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajout Formateur -->
<div class="modal fade" id="addFormateurModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5><i class="fas fa-user-plus"></i> Nouveau Formateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="nom" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Prénom *</label><input type="text" name="prenom" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Email *</label><input type="email" name="email" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Téléphone</label><input type="tel" name="telephone" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Spécialité</label><input type="text" name="specialite" class="form-control" placeholder="Ex: Boxe Pro - 5e Dan"></div>
                        <div class="col-md-6 mb-3"><label>Salaire Base (FCFA)</label><input type="number" name="salaire_base" class="form-control"></div>
                        <div class="col-md-12 mb-3"><label>Diplômes / Certifications</label><textarea name="diplomes" class="form-control" rows="2"></textarea></div>
                        <div class="col-md-6 mb-3"><label>Statut</label>
                            <select name="statut" class="form-control">
                                <option value="actif">Actif</option><option value="inactif">Inactif</option><option value="conge">Congé</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Enregistrer</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Édition Formateur -->
<div class="modal fade" id="editFormateurModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5><i class="fas fa-edit"></i> Modifier Formateur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Nom</label><input type="text" name="nom" id="edit_nom" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Prénom</label><input type="text" name="prenom" id="edit_prenom" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="email" id="edit_email" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label>Téléphone</label><input type="tel" name="telephone" id="edit_telephone" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Spécialité</label><input type="text" name="specialite" id="edit_specialite" class="form-control"></div>
                        <div class="col-md-6 mb-3"><label>Salaire Base</label><input type="number" name="salaire_base" id="edit_salaire" class="form-control"></div>
                        <div class="col-md-12 mb-3"><label>Diplômes</label><textarea name="diplomes" id="edit_diplomes" class="form-control" rows="2"></textarea></div>
                        <div class="col-md-6 mb-3"><label>Statut</label>
                            <select name="statut" id="edit_statut" class="form-control">
                                <option value="actif">Actif</option><option value="inactif">Inactif</option><option value="conge">Congé</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-warning">Modifier</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function editFormateur(formateur) {
    document.getElementById('edit_id').value = formateur.id;
    document.getElementById('edit_nom').value = formateur.nom;
    document.getElementById('edit_prenom').value = formateur.prenom;
    document.getElementById('edit_email').value = formateur.email;
    document.getElementById('edit_telephone').value = formateur.telephone;
    document.getElementById('edit_specialite').value = formateur.specialite;
    document.getElementById('edit_diplomes').value = formateur.diplomes;
    document.getElementById('edit_salaire').value = formateur.salaire_base;
    document.getElementById('edit_statut').value = formateur.statut;
    new bootstrap.Modal(document.getElementById('editFormateurModal')).show();
}
</script>

<?php include 'footer.php'; ?>
