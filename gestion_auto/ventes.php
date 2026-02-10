<?php include 'config.php'; include 'header.php'; 
$db = Database::getInstance();
$conn = $db->getConnection();

// Récupérer les véhicules disponibles à la vente
$vehicules_vente = $conn->query("
    SELECT v.*, m.nom as modele_nom, mar.nom as marque_nom 
    FROM vehicules v 
    JOIN modeles m ON v.modele_id = m.id 
    JOIN marques mar ON m.marque_id = mar.id 
    WHERE v.statut = 'disponible' AND v.type_vehicule IN ('vente', 'mixte')
    ORDER BY mar.nom, m.nom
")->fetchAll();

// Récupérer les clients
$clients = $conn->query("SELECT * FROM clients ORDER BY nom, prenom")->fetchAll();

// Récupérer les ventes
$query_ventes = $conn->query("
    SELECT v.*, c.nom, c.prenom, ve.immatriculation, ve.prix_vente, mar.nom as marque_nom, m.nom as modele_nom
    FROM ventes v 
    JOIN clients c ON v.client_id = c.id 
    JOIN vehicules ve ON v.vehicule_id = ve.id
    JOIN modeles m ON ve.modele_id = m.id
    JOIN marques mar ON m.marque_id = mar.id
    ORDER BY v.date_vente DESC
");
$ventes = $query_ventes->fetchAll();

// Statistiques
$total_ventes = count($ventes);
$ca_total = array_sum(array_column($ventes, 'prix_vente'));
$ventes_mois = count(array_filter($ventes, function($v) { 
    return date('Y-m', strtotime($v['date_vente'])) == date('Y-m'); 
}));
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
                <i class="bi bi-currency-euro text-success me-2"></i>Gestion des Ventes
            </h1>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#ajouterVenteModal">
                <i class="bi bi-plus-circle me-1"></i>Nouvelle Vente
            </button>
        </div>
        <p class="text-muted"><?= $total_ventes ?> ventes enregistrées</p>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= $total_ventes ?></h4>
                        <p class="card-text">Total Ventes</p>
                    </div>
                    <i class="bi bi-currency-euro fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= number_format($ca_total, 0, ',', ' ') ?> €</h4>
                        <p class="card-text">Chiffre d'Affaires</p>
                    </div>
                    <i class="bi bi-graph-up fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= $ventes_mois ?></h4>
                        <p class="card-text">Ventes ce Mois</p>
                    </div>
                    <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Liste des Ventes -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">Historique des Ventes</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Véhicule</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($ventes as $vente): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($vente['date_vente'])) ?></td>
                        <td>
                            <strong><?= $vente['prenom'] ?> <?= $vente['nom'] ?></strong>
                        </td>
                        <td>
                            <?= $vente['marque_nom'] ?> <?= $vente['modele_nom'] ?>
                            <br><small class="text-muted"><?= $vente['immatriculation'] ?></small>
                        </td>
                        <td>
                            <strong class="text-success"><?= number_format($vente['prix_vente'], 0, ',', ' ') ?> €</strong>
                        </td>
                        <td>
                            <span class="badge bg-<?= $vente['statut'] == 'finalise' ? 'success' : 'warning' ?>">
                                <?= ucfirst($vente['statut']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary">
                                    <i class="bi bi-receipt"></i>
                                </button>
                                <button class="btn btn-outline-info">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Ajouter Vente -->
<div class="modal fade" id="ajouterVenteModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Enregistrer une Vente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="vente_ajouter.php">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Véhicule *</label>
                            <select name="vehicule_id" class="form-select" required>
                                <option value="">Choisir un véhicule</option>
                                <?php foreach($vehicules_vente as $vehicule): ?>
                                <option value="<?= $vehicule['id'] ?>" data-prix="<?= $vehicule['prix_vente'] ?>">
                                    <?= $vehicule['marque_nom'] ?> <?= $vehicule['modele_nom'] ?> - <?= $vehicule['immatriculation'] ?> - <?= number_format($vehicule['prix_vente'], 0, ',', ' ') ?> €
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Client *</label>
                            <select name="client_id" class="form-select" required>
                                <option value="">Choisir un client</option>
                                <?php foreach($clients as $client): ?>
                                <option value="<?= $client['id'] ?>"><?= $client['prenom'] ?> <?= $client['nom'] ?> - <?= $client['telephone'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prix de vente (€) *</label>
                            <input type="number" name="prix_vente" class="form-control" step="0.01" required id="prix_vente">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Frais de dossier</label>
                            <input type="number" name="frais_dossier" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mode de paiement</label>
                            <select name="mode_paiement" class="form-select">
                                <option value="comptant">Comptant</option>
                                <option value="credit">Crédit</option>
                                <option value="leasing">Leasing</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Informations supplémentaires..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Enregistrer la vente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-remplir le prix de vente
document.querySelector('select[name="vehicule_id"]').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const prix = selectedOption.getAttribute('data-prix');
    if (prix) {
        document.getElementById('prix_vente').value = prix;
    }
});
</script>

<?php include 'footer.php'; ?>
