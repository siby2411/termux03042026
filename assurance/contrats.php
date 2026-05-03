<?php
require_once 'config/db.php';
$page_title = "Gestion des contrats - OMEGA Assurance";
require_once 'includes/header.php';

$db = getDB();
$message = '';
$error = '';

// Traitement CRUD
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add':
                $prime_nette = $_POST['prime_nette'];
                $taxe = $prime_nette * 0.10;
                $prime_ttc = $prime_nette + $taxe;
                $numero_contrat = 'CON-' . date('Ymd') . '-' . rand(1000,9999);
                
                $sql = "INSERT INTO contrats (numero_contrat, client_id, vehicule_id, formule, prime_nette, taxe, prime_ttc, mode_paiement, date_effet, date_echeance, statut) 
                        VALUES (:num, :client, :vehicule, :formule, :p_nette, :taxe, :p_ttc, :mode, :debut, :fin, 'actif')";
                $stmt = $db->prepare($sql);
                try {
                    $stmt->execute([
                        ':num' => $numero_contrat,
                        ':client' => $_POST['client_id'],
                        ':vehicule' => $_POST['vehicule_id'],
                        ':formule' => $_POST['formule'],
                        ':p_nette' => $prime_nette,
                        ':taxe' => $taxe,
                        ':p_ttc' => $prime_ttc,
                        ':mode' => $_POST['mode_paiement'],
                        ':debut' => $_POST['date_effet'],
                        ':fin' => $_POST['date_echeance']
                    ]);
                    $message = "Contrat créé avec succès ! Numéro: " . $numero_contrat;
                } catch(PDOException $e) {
                    $error = "Erreur: " . $e->getMessage();
                }
                break;
                
            case 'delete':
                $sql = "UPDATE contrats SET statut='resilie' WHERE id=:id";
                $stmt = $db->prepare($sql);
                $stmt->execute([':id' => $_POST['id']]);
                $message = "Contrat résilié avec succès !";
                break;
        }
    }
}

// Récupération des données
$clients = $db->query("SELECT id, numero_client, nom, prenom, raison_sociale, type_client FROM clients WHERE statut='actif'")->fetchAll();
$vehicules = $db->query("SELECT id, immatriculation, marque, modele FROM vehicules WHERE statut='actif'")->fetchAll();
$contrats = $db->query("SELECT c.*, cl.nom, cl.prenom, cl.raison_sociale, v.immatriculation 
                        FROM contrats c 
                        JOIN clients cl ON c.client_id = cl.id 
                        LEFT JOIN vehicules v ON c.vehicule_id = v.id 
                        ORDER BY c.date_creation DESC")->fetchAll();
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2><i class="fas fa-file-contract"></i> Gestion des contrats</h2>
        </div>
        <div class="col text-end">
            <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addContratModal">
                <i class="fas fa-plus"></i> Nouveau contrat
            </button>
        </div>
    </div>
    
    <?php if($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5>Liste des contrats</h5>
        </div>
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead>
                    <tr><th>N° Contrat</th><th>Client</th><th>Véhicule</th><th>Formule</th><th>Prime TTC</th><th>Début</th><th>Fin</th><th>Statut</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach($contrats as $c): ?>
                    <tr>
                        <td><?php echo $c['numero_contrat']; ?></td>
                        <td><?php echo $c['type_client']=='particulier' ? $c['prenom'].' '.$c['nom'] : $c['raison_sociale']; ?></td>
                        <td><?php echo $c['immatriculation']; ?></td>
                        <td><span class="badge bg-info"><?php echo $c['formule']; ?></span></td>
                        <td><?php echo number_format($c['prime_ttc'], 0, ',', ' '); ?> FCFA</td>
                        <td><?php echo date('d/m/Y', strtotime($c['date_effet'])); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($c['date_echeance'])); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $c['statut']=='actif' ? 'success' : 'danger'; ?>">
                                <?php echo $c['statut']; ?>
                            </span>
                         </td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="deleteContrat(<?php echo $c['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                         </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajout Contrat -->
<div class="modal fade" id="addContratModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient text-white">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Nouveau contrat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Client *</label>
                            <select name="client_id" class="form-control" required>
                                <option value="">Sélectionner</option>
                                <?php foreach($clients as $c): ?>
                                <option value="<?php echo $c['id']; ?>">
                                    <?php echo $c['type_client']=='particulier' ? $c['prenom'].' '.$c['nom'] : $c['raison_sociale']; ?>
                                    (<?php echo $c['numero_client']; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Véhicule *</label>
                            <select name="vehicule_id" class="form-control" required>
                                <option value="">Sélectionner</option>
                                <?php foreach($vehicules as $v): ?>
                                <option value="<?php echo $v['id']; ?>"><?php echo $v['immatriculation']; ?> - <?php echo $v['marque']; ?> <?php echo $v['modele']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Formule *</label>
                            <select name="formule" class="form-control" required onchange="updatePrime(this.value)">
                                <option value="Tiers">Tiers (150 000 - 250 000 FCFA)</option>
                                <option value="Tiers_Plus">Tiers Plus (250 000 - 400 000 FCFA)</option>
                                <option value="Tous_Risques">Tous Risques (400 000 - 600 000 FCFA)</option>
                                <option value="Premium">Premium (600 000 - 1 000 000 FCFA)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Prime nette (FCFA) *</label>
                            <input type="number" name="prime_nette" id="prime_nette" class="form-control" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Mode paiement *</label>
                            <select name="mode_paiement" class="form-control" required>
                                <option value="Annuel">Annuel</option>
                                <option value="Semestriel">Semestriel</option>
                                <option value="Trimestriel">Trimestriel</option>
                                <option value="Mensuel">Mensuel</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Date d'effet *</label>
                            <input type="date" name="date_effet" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Date d'échéance *</label>
                            <input type="date" name="date_echeance" class="form-control" required>
                        </div>
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
function updatePrime(formule) {
    let primes = {'Tiers': 200000, 'Tiers_Plus': 325000, 'Tous_Risques': 500000, 'Premium': 800000};
    document.getElementById('prime_nette').value = primes[formule];
}

function deleteContrat(id) {
    if(confirm('Êtes-vous sûr de vouloir résilier ce contrat ?')) {
        var form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' + id + '">';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
