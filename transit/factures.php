<?php
require_once 'config.php';
require_once 'header.php';

// Générer numéro facture
function generateInvoiceNumber() {
    return 'FACT-' . date('Ymd') . '-' . rand(1000, 9999);
}

// Traitement CRUD
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                $numero_facture = generateInvoiceNumber();
                $montant_ttc = $_POST['montant_ht'] + ($_POST['montant_ht'] * $_POST['tva'] / 100);
                
                $sql = "INSERT INTO factures (numero_facture, client_id, declaration_id, 
                        date_emission, date_echeance, montant_ht, tva, montant_ttc) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $numero_facture, $_POST['client_id'], $_POST['declaration_id'],
                    $_POST['date_emission'], $_POST['date_echeance'], $_POST['montant_ht'],
                    $_POST['tva'], $montant_ttc
                ]);
                echo '<div class="alert alert-success">Facture créée! N°: ' . $numero_facture . '</div>';
                break;
                
            case 'pay':
                $sql = "UPDATE factures SET statut = 'payee' WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST['id']]);
                
                // Ajouter paiement
                $sql2 = "INSERT INTO paiements (facture_id, montant, mode_paiement, reference_paiement) 
                         VALUES (?, ?, ?, ?)";
                $stmt2 = $pdo->prepare($sql2);
                $stmt2->execute([$_POST['id'], $_POST['montant'], $_POST['mode_paiement'], $_POST['reference']]);
                
                echo '<div class="alert alert-success">Paiement enregistré!</div>';
                break;
        }
    }
}

// Récupérer factures
$factures = $pdo->query("SELECT f.*, c.raison_sociale, d.numero_declaration 
                         FROM factures f 
                         JOIN clients c ON f.client_id = c.id 
                         LEFT JOIN declarations_douane d ON f.declaration_id = d.id 
                         ORDER BY f.date_emission DESC")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestion des Factures</h1>
            <button class="btn btn-omega" data-bs-toggle="modal" data-bs-target="#createFactureModal">
                <i class="fas fa-file-invoice me-2"></i>Nouvelle Facture
            </button>
        </div>
        
        <!-- Liste des factures -->
        <div class="card-modern p-4">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>N° Facture</th>
                            <th>Client</th>
                            <th>Déclaration</th>
                            <th>Date émission</th>
                            <th>Montant TTC</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($factures as $facture): ?>
                        <tr>
                            <td><strong><?php echo $facture['numero_facture']; ?></strong></td>
                            <td><?php echo $facture['raison_sociale']; ?></td>
                            <td><?php echo $facture['numero_declaration'] ?? '-'; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($facture['date_emission'])); ?></td>
                            <td><?php echo number_format($facture['montant_ttc'], 0, ',', ' '); ?> FCFA</td>
                            <td>
                                <span class="badge bg-<?php echo $facture['statut'] == 'payee' ? 'success' : 'warning'; ?>">
                                    <?php echo $facture['statut'] == 'payee' ? 'Payée' : 'En attente'; ?>
                                </span>
                             </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="printFacture(<?php echo $facture['id']; ?>)">
                                    <i class="fas fa-print"></i>
                                </button>
                                <?php if($facture['statut'] != 'payee'): ?>
                                <button class="btn btn-sm btn-success" onclick="enregistrerPaiement(<?php echo $facture['id']; ?>, '<?php echo $facture['montant_ttc']; ?>')">
                                    <i class="fas fa-money-bill"></i> Payer
                                </button>
                                <?php endif; ?>
                             </td>
                         </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Création Facture -->
<div class="modal fade" id="createFactureModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nouvelle Facture</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Client *</label>
                            <select name="client_id" class="form-control" required>
                                <option value="">Sélectionner client</option>
                                <?php
                                $clients = $pdo->query("SELECT id, raison_sociale, code_client FROM clients ORDER BY raison_sociale");
                                foreach($clients as $client):
                                ?>
                                <option value="<?php echo $client['id']; ?>">
                                    <?php echo $client['code_client'] . ' - ' . $client['raison_sociale']; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Déclaration associée</label>
                            <select name="declaration_id" class="form-control">
                                <option value="">Sans déclaration</option>
                                <?php
                                $declarations = $pdo->query("SELECT id, numero_declaration FROM declarations_douane ORDER BY date_depot DESC LIMIT 50");
                                foreach($declarations as $decl):
                                ?>
                                <option value="<?php echo $decl['id']; ?>"><?php echo $decl['numero_declaration']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Date émission *</label>
                            <input type="date" name="date_emission" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Date échéance *</label>
                            <input type="date" name="date_echeance" class="form-control" required value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Montant HT (FCFA)</label>
                            <input type="number" name="montant_ht" class="form-control" id="montant_ht" step="0.01" required oninput="calculTTC()">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>TVA (%)</label>
                            <input type="number" name="tva" class="form-control" id="tva" value="18" step="0.01" oninput="calculTTC()">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Montant TTC (calculé)</label>
                            <input type="text" class="form-control" id="montant_ttc_display" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer Facture</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Paiement -->
<div class="modal fade" id="paiementModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Enregistrer Paiement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="pay">
                    <input type="hidden" name="id" id="facture_id">
                    <div class="mb-3">
                        <label>Montant à payer (FCFA)</label>
                        <input type="number" name="montant" class="form-control" id="montant_paiement" required step="0.01">
                    </div>
                    <div class="mb-3">
                        <label>Mode de paiement</label>
                        <select name="mode_paiement" class="form-control" required>
                            <option value="especes">Espèces</option>
                            <option value="cheque">Chèque</option>
                            <option value="virement">Virement bancaire</option>
                            <option value="mobile_money">Mobile Money (Orange Money/Wave)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Référence de paiement</label>
                        <input type="text" name="reference" class="form-control" placeholder="N° chèque, référence virement...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Valider Paiement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function calculTTC() {
    let ht = parseFloat(document.getElementById('montant_ht').value) || 0;
    let tva = parseFloat(document.getElementById('tva').value) || 0;
    let ttc = ht + (ht * tva / 100);
    document.getElementById('montant_ttc_display').value = ttc.toFixed(2) + ' FCFA';
}

function printFacture(id) {
    window.open('print_facture.php?id=' + id, '_blank');
}

function enregistrerPaiement(id, montant) {
    document.getElementById('facture_id').value = id;
    document.getElementById('montant_paiement').value = montant;
    new bootstrap.Modal(document.getElementById('paiementModal')).show();
}
</script>

<?php require_once 'footer.php'; ?>
