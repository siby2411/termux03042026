<?php
require_once 'config.php';
require_once 'header.php';

// Traitement CRUD
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'create':
                $sql = "INSERT INTO declarations_douane (numero_declaration, client_id, type_transport, 
                        regime, date_depot, valeur_fob, fret, assurance, valeur_cif) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['numero_declaration'], $_POST['client_id'], $_POST['type_transport'],
                    $_POST['regime'], $_POST['date_depot'], $_POST['valeur_fob'], 
                    $_POST['fret'], $_POST['assurance'], $_POST['valeur_cif']
                ]);
                echo '<div class="alert alert-success">Déclaration créée avec succès!</div>';
                break;
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestion des Déclarations</h1>
            <button class="btn btn-omega" data-bs-toggle="modal" data-bs-target="#createModal">
                <i class="fas fa-plus me-2"></i>Nouvelle Déclaration
            </button>
        </div>
        
        <!-- Liste des déclarations -->
        <div class="card-modern p-4">
            <div class="table-responsive">
                <table class="table table-striped" id="declarationsTable">
                    <thead>
                        <tr>
                            <th>N° Déclaration</th>
                            <th>Client</th>
                            <th>Transport</th>
                            <th>Régime</th>
                            <th>Date</th>
                            <th>Valeur CIF</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT d.*, c.raison_sociale 
                                            FROM declarations_douane d 
                                            JOIN clients c ON d.client_id = c.id 
                                            ORDER BY d.date_depot DESC");
                        while($row = $stmt->fetch()):
                        ?>
                        <tr>
                            <td><?php echo $row['numero_declaration']; ?></td>
                            <td><?php echo $row['raison_sociale']; ?></td>
                            <td><?php echo ucfirst($row['type_transport']); ?></td>
                            <td><?php echo ucfirst($row['regime']); ?></td>
                            <td><?php echo $row['date_depot']; ?></td>
                            <td><?php echo number_format($row['valeur_cif'], 0, ',', ' '); ?> FCFA</td>
                            <td>
                                <select class="form-select form-select-sm statut-select" data-id="<?php echo $row['id']; ?>">
                                    <option value="brouillon" <?php echo $row['statut'] == 'brouillon' ? 'selected' : ''; ?>>Brouillon</option>
                                    <option value="depose" <?php echo $row['statut'] == 'depose' ? 'selected' : ''; ?>>Déposé</option>
                                    <option value="controle" <?php echo $row['statut'] == 'controle' ? 'selected' : ''; ?>>Contrôle</option>
                                    <option value="acquitte" <?php echo $row['statut'] == 'acquitte' ? 'selected' : ''; ?>>Acquitté</option>
                                    <option value="livre" <?php echo $row['statut'] == 'livre' ? 'selected' : ''; ?>>Livre</option>
                                </select>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="voirDetails(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning" onclick="imprimerDeclaration(<?php echo $row['id']; ?>)">
                                    <i class="fas fa-print"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Création -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Nouvelle Déclaration en Douane</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>N° Déclaration *</label>
                            <input type="text" name="numero_declaration" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Client *</label>
                            <select name="client_id" class="form-control" required>
                                <option value="">Sélectionner un client</option>
                                <?php
                                $clients = $pdo->query("SELECT id, raison_sociale FROM clients");
                                while($c = $clients->fetch()):
                                ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo $c['raison_sociale']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Type Transport *</label>
                            <select name="type_transport" class="form-control" required>
                                <option value="maritime">Maritime</option>
                                <option value="aeroportuaire">Aéroportuaire</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Régime *</label>
                            <select name="regime" class="form-control" required>
                                <option value="import">Import</option>
                                <option value="export">Export</option>
                                <option value="transit">Transit</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Date Dépôt *</label>
                            <input type="date" name="date_depot" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Valeur FOB (FCFA)</label>
                            <input type="number" name="valeur_fob" class="form-control" id="fob" step="0.01">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Fret (FCFA)</label>
                            <input type="number" name="fret" class="form-control" id="fret" step="0.01">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Assurance (FCFA)</label>
                            <input type="number" name="assurance" class="form-control" id="assurance" step="0.01">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Valeur CIF (calculée)</label>
                            <input type="text" name="valeur_cif" class="form-control" id="cif" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer Déclaration</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Calcul automatique CIF
document.getElementById('fob').addEventListener('input', calculCIF);
document.getElementById('fret').addEventListener('input', calculCIF);
document.getElementById('assurance').addEventListener('input', calculCIF);

function calculCIF() {
    let fob = parseFloat(document.getElementById('fob').value) || 0;
    let fret = parseFloat(document.getElementById('fret').value) || 0;
    let assurance = parseFloat(document.getElementById('assurance').value) || 0;
    let cif = fob + fret + assurance;
    document.getElementById('cif').value = cif.toFixed(2);
}

// Mise à jour statut AJAX
document.querySelectorAll('.statut-select').forEach(select => {
    select.addEventListener('change', function() {
        let id = this.dataset.id;
        let statut = this.value;
        
        fetch('update_statut.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id, statut: statut})
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            }
        });
    });
});

function voirDetails(id) {
    window.location.href = 'declaration_details.php?id=' + id;
}

function imprimerDeclaration(id) {
    window.open('print_declaration.php?id=' + id, '_blank');
}
</script>

<?php require_once 'footer.php'; ?>
