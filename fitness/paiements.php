<?php
require_once 'config/database.php';
include 'header.php';

$database = new Database();
$db = $database->getConnection();

// Traitement ajout paiement
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $query = "INSERT INTO paiements (adherent_id, montant, mode_paiement, reference, observations) 
              VALUES (:adherent_id, :montant, :mode, :ref, :obs)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':adherent_id' => $_POST['adherent_id'],
        ':montant' => $_POST['montant'],
        ':mode' => $_POST['mode_paiement'],
        ':ref' => $_POST['reference'],
        ':obs' => $_POST['observations']
    ]);
    $success = "Paiement enregistré avec succès!";
}

// Récupération des adhérents pour le select
$adherents = $db->query("SELECT id, nom, prenom, numero_licence FROM adherents WHERE statut='actif' ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Récupération des paiements
$query = "SELECT p.*, CONCAT(a.prenom,' ',a.nom) as adherent_nom, a.numero_licence 
          FROM paiements p 
          JOIN adherents a ON p.adherent_id=a.id 
          ORDER BY p.date_paiement DESC LIMIT 50";
$paiements = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-money-bill-wave"></i> Gestion des Paiements</h3>
        </div>
        <div class="card-body">
            <?php if(isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPaiementModal">
                <i class="fas fa-plus"></i> Nouveau Paiement
            </button>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Date</th><th>Licence</th><th>Adhérent</th><th>Montant</th><th>Mode</th><th>Référence</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($paiements as $p): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($p['date_paiement'])) ?></td>
                            <td><?= $p['numero_licence'] ?></td>
                            <td><?= htmlspecialchars($p['adherent_nom']) ?></td>
                            <td><strong><?= number_format($p['montant'], 0, ',', ' ') ?> FCFA</strong></td>
                            <td><span class="badge bg-info"><?= $p['mode_paiement'] ?></span></td>
                            <td><?= $p['reference'] ?></td>
                            <td><span class="badge bg-<?= $p['statut']=='valide'?'success':'warning' ?>"><?= $p['statut'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajout Paiement -->
<div class="modal fade" id="addPaiementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5><i class="fas fa-hand-holding-usd"></i> Enregistrer un Paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label>Adhérent *</label>
                        <select name="adherent_id" class="form-control" required>
                            <option value="">Sélectionner un adhérent</option>
                            <?php foreach($adherents as $a): ?>
                            <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['prenom'] . ' ' . $a['nom']) ?> (<?= $a['numero_licence'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Montant (FCFA) *</label>
                        <input type="number" name="montant" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Mode de paiement *</label>
                        <select name="mode_paiement" class="form-control" required>
                            <option value="especes">Espèces</option>
                            <option value="carte">Carte bancaire</option>
                            <option value="cheque">Chèque</option>
                            <option value="virement">Virement bancaire</option>
                            <option value="mobile_money">Mobile Money</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Référence (optionnel)</label>
                        <input type="text" name="reference" class="form-control" placeholder="N° chèque, transaction...">
                    </div>
                    <div class="mb-3">
                        <label>Observations</label>
                        <textarea name="observations" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Enregistrer le paiement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
