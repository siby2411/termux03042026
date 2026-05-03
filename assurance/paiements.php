<?php
require_once 'config/db.php';
$page_title = "Gestion des paiements - OMEGA Assurance";
require_once 'includes/header.php';

$db = getDB();
$message = '';

// Traitement
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $quittance = 'Q-' . date('Ymd') . '-' . rand(1000,9999);
    $sql = "INSERT INTO paiements (contrat_id, numero_quittance, date_paiement, montant, mode_reglement, statut) 
            VALUES (:contrat, :quittance, :date, :montant, :mode, 'valide')";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':contrat' => $_POST['contrat_id'],
        ':quittance' => $quittance,
        ':date' => $_POST['date_paiement'],
        ':montant' => $_POST['montant'],
        ':mode' => $_POST['mode_reglement']
    ]);
    $message = "Paiement enregistré ! Quittance: " . $quittance;
}

$paiements = $db->query("SELECT p.*, c.numero_contrat, cl.nom, cl.prenom, cl.raison_sociale 
                         FROM paiements p 
                         JOIN contrats c ON p.contrat_id = c.id 
                         JOIN clients cl ON c.client_id = cl.id 
                         ORDER BY p.date_paiement DESC")->fetchAll();
$contrats = $db->query("SELECT id, numero_contrat FROM contrats WHERE statut='actif'")->fetchAll();
?>

<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h2><i class="fas fa-money-bill-wave"></i> Gestion des paiements</h2>
        </div>
        <div class="col text-end">
            <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addPaiementModal">
                <i class="fas fa-plus"></i> Nouveau paiement
            </button>
        </div>
    </div>
    
    <?php if($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5>Historique des paiements</h5>
        </div>
        <div class="card-body">
            <table class="table table-hover datatable">
                <thead>
                    <tr><th>Quittance</th><th>Contrat</th><th>Client</th><th>Date</th><th>Montant</th><th>Mode</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    <?php foreach($paiements as $p): ?>
                    <tr>
                        <td><?php echo $p['numero_quittance']; ?></td>
                        <td><?php echo $p['numero_contrat']; ?></td>
                        <td><?php echo $p['type_client']=='particulier' ? $p['prenom'].' '.$p['nom'] : $p['raison_sociale']; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($p['date_paiement'])); ?></td>
                        <td><?php echo number_format($p['montant'], 0, ',', ' '); ?> FCFA</td>
                        <td><?php echo $p['mode_reglement']; ?></td>
                        <td><span class="badge bg-success"><?php echo $p['statut']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajout Paiement -->
<div class="modal fade" id="addPaiementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-gradient text-white">
                <h5 class="modal-title">Nouveau paiement</h5>
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
                            <option value="<?php echo $c['id']; ?>"><?php echo $c['numero_contrat']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Montant (FCFA) *</label>
                        <input type="number" name="montant" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Date de paiement *</label>
                        <input type="date" name="date_paiement" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Mode de règlement</label>
                        <select name="mode_reglement" class="form-control">
                            <option value="Virement">Virement bancaire</option>
                            <option value="Especes">Espèces</option>
                            <option value="Orange_Money">Orange Money</option>
                            <option value="Wave">Wave</option>
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
