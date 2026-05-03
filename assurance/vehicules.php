<?php
require_once 'config/db.php';
$page_title = "Gestion des véhicules - OMEGA Assurance";
require_once 'includes/header.php';

$db = getDB();
$message = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action']) && $_POST['action'] == 'add') {
        $sql = "INSERT INTO vehicules (immatriculation, marque, modele, annee_fabrication, valeur_venale, proprietaire_id) 
                VALUES (:immat, :marque, :modele, :annee, :valeur, :proprio)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':immat' => $_POST['immatriculation'],
            ':marque' => $_POST['marque'],
            ':modele' => $_POST['modele'],
            ':annee' => $_POST['annee_fabrication'],
            ':valeur' => $_POST['valeur_venale'],
            ':proprio' => $_POST['proprietaire_id']
        ]);
        $message = "Véhicule ajouté avec succès !";
    }
}

$vehicules = $db->query("SELECT v.*, cl.nom, cl.prenom, cl.raison_sociale 
                         FROM vehicules v 
                         LEFT JOIN clients cl ON v.proprietaire_id = cl.id 
                         ORDER BY v.id DESC")->fetchAll();
$clients = $db->query("SELECT id, nom, prenom, raison_sociale, type_client FROM clients WHERE statut='actif'")->fetchAll();
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col"><h2><i class="fas fa-car"></i> Gestion des véhicules</h2></div>
        <div class="col text-end">
            <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addVehiculeModal">
                <i class="fas fa-plus"></i> Nouveau véhicule
            </button>
        </div>
    </div>
    
    <?php if($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <table class="table datatable">
                <thead>
                    <tr><th>Immatriculation</th><th>Marque</th><th>Modèle</th><th>Année</th><th>Valeur</th><th>Propriétaire</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    <?php foreach($vehicules as $v): ?>
                    <tr>
                        <td><?php echo $v['immatriculation']; ?></td>
                        <td><?php echo $v['marque']; ?></td>
                        <td><?php echo $v['modele']; ?></td>
                        <td><?php echo $v['annee_fabrication']; ?></td>
                        <td><?php echo number_format($v['valeur_venale'], 0, ',', ' '); ?> FCFA</td>
                        <td><?php echo $v['type_client']=='particulier' ? $v['prenom'].' '.$v['nom'] : $v['raison_sociale']; ?></td>
                        <td><span class="badge bg-success"><?php echo $v['statut']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajout Véhicule -->
<div class="modal fade" id="addVehiculeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient text-white">
                <h5 class="modal-title">Nouveau véhicule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3"><label>Immatriculation *</label><input type="text" name="immatriculation" class="form-control" required></div>
                    <div class="mb-3"><label>Marque *</label><input type="text" name="marque" class="form-control" required></div>
                    <div class="mb-3"><label>Modèle *</label><input type="text" name="modele" class="form-control" required></div>
                    <div class="mb-3"><label>Année</label><input type="number" name="annee_fabrication" class="form-control"></div>
                    <div class="mb-3"><label>Valeur (FCFA)</label><input type="number" name="valeur_venale" class="form-control"></div>
                    <div class="mb-3"><label>Propriétaire *</label>
                        <select name="proprietaire_id" class="form-control" required>
                            <option value="">Sélectionner</option>
                            <?php foreach($clients as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo $c['type_client']=='particulier' ? $c['prenom'].' '.$c['nom'] : $c['raison_sociale']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-gradient">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
