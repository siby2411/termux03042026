<?php
require_once 'config/db.php';
$page_title = "Gestion des sinistres - OMEGA Assurance";
require_once 'includes/header.php';

$db = getDB();
$message = '';
$error = '';

// Traitement CRUD
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                $numero_sinistre = 'SIN-' . date('Ymd') . '-' . rand(1000,9999);
                $sql = "INSERT INTO sinistres (numero_sinistre, contrat_id, date_survenance, date_declaration, type_sinistre, montant_estime, statut) 
                        VALUES (:num, :contrat, :date_surv, :date_decl, :type, :montant, 'declare')";
                $stmt = $db->prepare($sql);
                try {
                    $stmt->execute([
                        ':num' => $numero_sinistre,
                        ':contrat' => $_POST['contrat_id'],
                        ':date_surv' => $_POST['date_survenance'],
                        ':date_decl' => $_POST['date_declaration'],
                        ':type' => $_POST['type_sinistre'],
                        ':montant' => $_POST['montant_estime']
                    ]);
                    $message = "Sinistre déclaré avec succès ! Numéro: " . $numero_sinistre;
                } catch(PDOException $e) {
                    $error = "Erreur: " . $e->getMessage();
                }
                break;
                
            case 'update':
                $sql = "UPDATE sinistres SET statut=:statut, montant_indemnise=:montant WHERE id=:id";
                $stmt = $db->prepare($sql);
                $stmt->execute([
                    ':id' => $_POST['id'],
                    ':statut' => $_POST['statut'],
                    ':montant' => $_POST['montant_indemnise']
                ]);
                $message = "Sinistre mis à jour avec succès !";
                break;
        }
    }
}

// Récupération des données
$sinistres = $db->query("SELECT s.*, c.numero_contrat, cl.nom, cl.prenom, cl.raison_sociale 
                         FROM sinistres s 
                         JOIN contrats c ON s.contrat_id = c.id 
                         JOIN clients cl ON c.client_id = cl.id 
                         ORDER BY s.date_survenance DESC")->fetchAll();
$contrats = $db->query("SELECT c.id, c.numero_contrat, cl.nom, cl.prenom, cl.raison_sociale 
                        FROM contrats c 
                        JOIN clients cl ON c.client_id = cl.id 
                        WHERE c.statut='actif'")->fetchAll();
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2><i class="fas fa-exclamation-triangle"></i> Gestion des sinistres</h2>
        </div>
        <div class="col text-end">
            <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addSinistreModal">
                <i class="fas fa-plus"></i> Nouveau sinistre
            </button>
        </div>
    </div>
    
    <?php if($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5>Liste des sinistres</h5>
        </div>
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead>
                    <tr><th>N° Sinistre</th><th>Contrat</th><th>Client</th><th>Date</th><th>Type</th><th>Montant estimé</th><th>Indemnisation</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($sinistres as $s): ?>
                    <tr>
                        <td><?php echo $s['numero_sinistre']; ?></td>
                        <td><?php echo $s['numero_contrat']; ?></td>
                        <td><?php echo $s['type_client']=='particulier' ? $s['prenom'].' '.$s['nom'] : $s['raison_sociale']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($s['date_survenance'])); ?></td>
                        <td><span class="badge bg-warning"><?php echo $s['type_sinistre']; ?></span></td>
                        <td><?php echo number_format($s['montant_estime'], 0, ',', ' '); ?> FCFA</td>
                        <td><?php echo $s['montant_indemnise'] ? number_format($s['montant_indemnise'], 0, ',', ' ').' FCFA' : '-'; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $s['statut']=='cloture' ? 'success' : ($s['statut']=='declare' ? 'danger' : 'info'); ?>">
                                <?php echo $s['statut']; ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editSinistre(<?php echo $s['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                         </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajout Sinistre -->
<div class="modal fade" id="addSinistreModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient text-white">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Nouveau sinistre</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label>Contrat *</label>
                        <select name="contrat_id" class="form-control" required>
                            <option value="">Sélectionner</option>
                            <?php foreach($contrats as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo $c['numero_contrat']; ?> - <?php echo $c['prenom'].' '.$c['nom']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Type de sinistre *</label>
                        <select name="type_sinistre" class="form-control" required>
                            <option value="Accident">Accident</option>
                            <option value="Vol">Vol</option>
                            <option value="Incendie">Incendie</option>
                            <option value="Bris_de_glace">Bris de glace</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Date de survenance *</label>
                        <input type="date" name="date_survenance" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Date de déclaration *</label>
                        <input type="date" name="date_declaration" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Montant estimé (FCFA) *</label>
                        <input type="number" name="montant_estime" class="form-control" required>
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

<script>
function editSinistre(id) {
    // Rediriger vers page d'édition
    window.location.href = 'edit_sinistre.php?id=' + id;
}
</script>

<?php require_once 'includes/footer.php'; ?>
