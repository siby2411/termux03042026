<?php
require_once 'config.php';
require_once 'header.php';

// Configuration API WhatsApp (à remplacer par vos identifiants)
$whatsapp_api_url = "https://api.whatsapp.com/send?phone=";

// Traitement CRUD prospects
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['action'])) {
        switch($_POST['action']) {
            case 'add_prospect':
                $sql = "INSERT INTO prospects (raison_sociale, contact_nom, telephone, email, source, interet, responsable_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $_POST['raison_sociale'], $_POST['contact_nom'], $_POST['telephone'],
                    $_POST['email'], $_POST['source'], $_POST['interet'], $_SESSION['user_id']
                ]);
                echo '<div class="alert alert-success">Prospect ajouté avec succès!</div>';
                break;
                
            case 'update_status':
                $sql = "UPDATE prospects SET statut = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$_POST['statut'], $_POST['id']]);
                echo '<div class="alert alert-success">Statut mis à jour!</div>';
                break;
        }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>CRM - Gestion des Prospects</h1>
            <button class="btn btn-omega" data-bs-toggle="modal" data-bs-target="#addProspectModal">
                <i class="fas fa-user-plus me-2"></i>Nouveau Prospect
            </button>
        </div>
        
        <!-- Feuille de route recherche clients -->
        <div class="card-modern p-4 mb-4">
            <h5><i class="fas fa-route me-2"></i>Feuille de Route - Recherche Clients</h5>
            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Prospection par région</label>
                        <select class="form-control" id="regionFilter">
                            <option value="">Toutes régions</option>
                            <option value="Dakar">Dakar</option>
                            <option value="Thiès">Thiès</option>
                            <option value="Saint-Louis">Saint-Louis</option>
                            <option value="Ziguinchor">Ziguinchor</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Secteur d'activité</label>
                        <select class="form-control" id="secteurFilter">
                            <option value="">Tous secteurs</option>
                            <option value="import_export">Import/Export</option>
                            <option value="industrie">Industrie</option>
                            <option value="commerce">Commerce</option>
                            <option value="agriculture">Agriculture</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Statut prospect</label>
                        <select class="form-control" id="statutFilter">
                            <option value="">Tous statuts</option>
                            <option value="nouveau">Nouveau</option>
                            <option value="contacte">Contacté</option>
                            <option value="rdv">RDV pris</option>
                            <option value="converti">Converti</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button class="btn btn-primary w-100" onclick="exporterProspects()">
                            <i class="fas fa-file-excel me-2"></i>Exporter CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Liste des prospects -->
        <div class="card-modern p-4">
            <div class="table-responsive">
                <table class="table table-hover" id="prospectsTable">
                    <thead>
                        <tr>
                            <th>Raison Sociale</th>
                            <th>Contact</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Source</th>
                            <th>Intérêt</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT p.*, u.nom as responsable 
                                            FROM prospects p 
                                            LEFT JOIN utilisateurs u ON p.responsable_id = u.id 
                                            ORDER BY p.date_creation DESC");
                        while($prospect = $stmt->fetch()):
                        ?>
                        <tr>
                            <td><?php echo $prospect['raison_sociale']; ?></td>
                            <td><?php echo $prospect['contact_nom']; ?></td>
                            <td>
                                <a href="<?php echo $whatsapp_api_url . $prospect['telephone']; ?>" target="_blank">
                                    <i class="fab fa-whatsapp text-success me-1"></i>
                                </a>
                                <?php echo $prospect['telephone']; ?>
                            </td>
                            <td><?php echo $prospect['email']; ?></td>
                            <td><?php echo $prospect['source']; ?></td>
                            <td><?php echo $prospect['interet']; ?></td>
                            <td>
                                <select class="form-select form-select-sm statut-prospect" data-id="<?php echo $prospect['id']; ?>">
                                    <option value="nouveau" <?php echo $prospect['statut'] == 'nouveau' ? 'selected' : ''; ?>>Nouveau</option>
                                    <option value="contacte" <?php echo $prospect['statut'] == 'contacte' ? 'selected' : ''; ?>>Contacté</option>
                                    <option value="rdv" <?php echo $prospect['statut'] == 'rdv' ? 'selected' : ''; ?>>RDV</option>
                                    <option value="converti" <?php echo $prospect['statut'] == 'converti' ? 'selected' : ''; ?>>Converti</option>
                                </select>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-success" onclick="contacterWhatsApp('<?php echo $prospect['telephone']; ?>')">
                                    <i class="fab fa-whatsapp"></i> Contacter
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="convertirEnClient(<?php echo $prospect['id']; ?>)">
                                    <i class="fas fa-exchange-alt"></i> Convertir
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

<!-- Modal Ajout Prospect -->
<div class="modal fade" id="addProspectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Ajouter un Prospect</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_prospect">
                    <div class="mb-3">
                        <label>Raison Sociale *</label>
                        <input type="text" name="raison_sociale" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Nom Contact *</label>
                        <input type="text" name="contact_nom" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Téléphone *</label>
                        <input type="tel" name="telephone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Source</label>
                        <select name="source" class="form-control">
                            <option value="site_web">Site Web</option>
                            <option value="reference">Référence</option>
                            <option value="salon">Salon professionnel</option>
                            <option value="prospection">Prospection directe</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Intérêt</label>
                        <textarea name="interet" class="form-control" rows="3" placeholder="Type de services recherchés..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter Prospect</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Mise à jour statut prospect
document.querySelectorAll('.statut-prospect').forEach(select => {
    select.addEventListener('change', function() {
        let id = this.dataset.id;
        let statut = this.value;
        
        fetch('update_prospect_status.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + id + '&statut=' + statut
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            }
        });
    });
});

function contacterWhatsApp(telephone) {
    window.open('https://api.whatsapp.com/send?phone=' + telephone, '_blank');
}

function convertirEnClient(prospectId) {
    if(confirm('Convertir ce prospect en client ?')) {
        window.location.href = 'convert_prospect.php?id=' + prospectId;
    }
}

function exporterProspects() {
    window.location.href = 'export_prospects.php';
}

// Filtres
document.getElementById('regionFilter').addEventListener('change', filtrerProspects);
document.getElementById('secteurFilter').addEventListener('change', filtrerProspects);
document.getElementById('statutFilter').addEventListener('change', filtrerProspects);

function filtrerProspects() {
    // Implémentation du filtrage
    console.log('Filtrage en cours...');
}
</script>

<?php require_once 'footer.php'; ?>
