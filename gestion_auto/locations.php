<?php include 'config.php'; include 'header.php'; 
$db = Database::getInstance();
$conn = $db->getConnection();

// Récupérer les véhicules disponibles à la location
$vehicules_location = $conn->query("
    SELECT v.*, m.nom as modele_nom, mar.nom as marque_nom 
    FROM vehicules v 
    JOIN modeles m ON v.modele_id = m.id 
    JOIN marques mar ON m.marque_id = mar.id 
    WHERE v.statut = 'disponible' AND v.type_vehicule IN ('location', 'mixte')
    ORDER BY mar.nom, m.nom
")->fetchAll();

// Récupérer les clients
$clients = $conn->query("SELECT * FROM clients ORDER BY nom, prenom")->fetchAll();

// Récupérer les locations
$query_locations = $conn->query("
    SELECT l.*, c.nom, c.prenom, c.telephone, 
           ve.immatriculation, ve.prix_location_jour,
           mar.nom as marque_nom, m.nom as modele_nom,
           DATEDIFF(l.date_fin, l.date_debut) as duree,
           (DATEDIFF(l.date_fin, l.date_debut) * l.prix_jour) as montant_total
    FROM locations l 
    JOIN clients c ON l.client_id = c.id 
    JOIN vehicules ve ON l.vehicule_id = ve.id
    JOIN modeles m ON ve.modele_id = m.id
    JOIN marques mar ON m.marque_id = mar.id
    ORDER BY l.date_creation DESC
");
$locations = $query_locations->fetchAll();

// Statistiques
$total_locations = count($locations);
$locations_encours = count(array_filter($locations, function($l) { 
    return $l['statut'] == 'encours'; 
}));
$ca_locations = array_sum(array_column($locations, 'montant_total'));
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
                <i class="bi bi-calendar-check text-warning me-2"></i>Gestion des Locations
            </h1>
            <button class="btn btn-warning text-white" data-bs-toggle="modal" data-bs-target="#ajouterLocationModal">
                <i class="bi bi-plus-circle me-1"></i>Nouvelle Location
            </button>
        </div>
        <p class="text-muted"><?= $total_locations ?> locations enregistrées</p>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= $total_locations ?></h4>
                        <p class="card-text">Total Locations</p>
                    </div>
                    <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= $locations_encours ?></h4>
                        <p class="card-text">En Cours</p>
                    </div>
                    <i class="bi bi-clock fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= number_format($ca_locations, 0, ',', ' ') ?> €</h4>
                        <p class="card-text">CA Locations</p>
                    </div>
                    <i class="bi bi-currency-euro fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= count($vehicules_location) ?></h4>
                        <p class="card-text">Véhicules Disponibles</p>
                    </div>
                    <i class="bi bi-car-front fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="🔍 Rechercher client ou véhicule..." id="searchInput">
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statutFilter">
                    <option value="">Tous statuts</option>
                    <option value="reserve">Réservé</option>
                    <option value="encours">En Cours</option>
                    <option value="termine">Terminé</option>
                    <option value="annule">Annulé</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" class="form-control" id="dateFilter" placeholder="Filtrer par date">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-primary w-100" onclick="appliquerFiltres()">
                    <i class="bi bi-funnel me-1"></i>Filtrer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Liste des Locations -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Historique des Locations</h5>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-success" onclick="exporterLocations()">
                <i class="bi bi-download me-1"></i>Exporter
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Véhicule</th>
                        <th>Période</th>
                        <th>Durée</th>
                        <th>Prix/Jour</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($locations as $location): 
                        $badge_color = match($location['statut']) {
                            'reserve' => 'warning',
                            'encours' => 'success',
                            'termine' => 'info',
                            'annule' => 'danger'
                        };
                    ?>
                    <tr class="location-item" 
                        data-client="<?= htmlspecialchars(strtolower($location['prenom'] . ' ' . $location['nom'])) ?>"
                        data-vehicule="<?= htmlspecialchars(strtolower($location['marque_nom'] . ' ' . $location['modele_nom'])) ?>"
                        data-statut="<?= $location['statut'] ?>"
                        data-date="<?= $location['date_debut'] ?>">
                        <td>
                            <strong><?= $location['prenom'] ?> <?= $location['nom'] ?></strong>
                            <?php if($location['telephone']): ?>
                                <br><small class="text-muted"><?= $location['telephone'] ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= $location['marque_nom'] ?> <?= $location['modele_nom'] ?>
                            <br><small class="text-muted"><?= $location['immatriculation'] ?></small>
                        </td>
                        <td>
                            <?= date('d/m/Y', strtotime($location['date_debut'])) ?>
                            <br>au<br>
                            <?= date('d/m/Y', strtotime($location['date_fin'])) ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary"><?= $location['duree'] ?> jours</span>
                        </td>
                        <td>
                            <?= number_format($location['prix_jour'], 0, ',', ' ') ?> €
                        </td>
                        <td>
                            <strong class="text-success"><?= number_format($location['montant_total'], 0, ',', ' ') ?> €</strong>
                        </td>
                        <td>
                            <span class="badge bg-<?= $badge_color ?>">
                                <?= ucfirst($location['statut']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if($location['statut'] == 'reserve'): ?>
                                    <button class="btn btn-outline-success" title="Débuter location">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                <?php elseif($location['statut'] == 'encours'): ?>
                                    <button class="btn btn-outline-info" title="Clôturer location">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="btn btn-outline-primary" title="Voir détails">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-danger" title="Annuler">
                                    <i class="bi bi-x-circle"></i>
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

<!-- Modal Ajouter Location -->
<div class="modal fade" id="ajouterLocationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nouvelle Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="location_ajouter.php">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Véhicule *</label>
                            <select name="vehicule_id" class="form-select" required id="vehiculeSelect">
                                <option value="">Choisir un véhicule</option>
                                <?php foreach($vehicules_location as $vehicule): ?>
                                <option value="<?= $vehicule['id'] ?>" data-prix="<?= $vehicule['prix_location_jour'] ?>">
                                    <?= $vehicule['marque_nom'] ?> <?= $vehicule['modele_nom'] ?> - 
                                    <?= $vehicule['immatriculation'] ?> - 
                                    <?= number_format($vehicule['prix_location_jour'], 0, ',', ' ') ?> €/jour
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
                        <div class="col-md-6">
                            <label class="form-label">Date de début *</label>
                            <input type="date" name="date_debut" class="form-control" required id="dateDebut">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date de fin *</label>
                            <input type="date" name="date_fin" class="form-control" required id="dateFin">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prix par jour (€) *</label>
                            <input type="number" name="prix_jour" class="form-control" step="0.01" required id="prixJour">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Caution (€)</label>
                            <input type="number" name="caution" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kilométrage départ</label>
                            <input type="number" name="kilometrage_depart" class="form-control" min="0">
                        </div>
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Récapitulatif</h6>
                                    <div id="recapLocation">
                                        <p class="text-muted mb-0">Sélectionnez un véhicule et des dates pour voir le calcul</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Conditions particulières, équipements supplémentaires..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning text-white">Créer la location</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Calcul automatique du prix
function calculerLocation() {
    const vehiculeSelect = document.getElementById('vehiculeSelect');
    const dateDebut = document.getElementById('dateDebut').value;
    const dateFin = document.getElementById('dateFin').value;
    const prixJour = document.getElementById('prixJour');
    const recap = document.getElementById('recapLocation');
    
    if (vehiculeSelect.value && dateDebut && dateFin) {
        const selectedOption = vehiculeSelect.options[vehiculeSelect.selectedIndex];
        const prixBase = selectedOption.getAttribute('data-prix');
        
        // Mettre à jour le prix jour
        if (prixBase && !prixJour.value) {
            prixJour.value = prixBase;
        }
        
        // Calculer la durée et le total
        const debut = new Date(dateDebut);
        const fin = new Date(dateFin);
        const duree = Math.ceil((fin - debut) / (1000 * 60 * 60 * 24));
        const total = duree * prixJour.value;
        
        if (duree > 0) {
            recap.innerHTML = `
                <div class="row">
                    <div class="col-6"><strong>Durée:</strong></div>
                    <div class="col-6">${duree} jours</div>
                    <div class="col-6"><strong>Prix journalier:</strong></div>
                    <div class="col-6">${parseFloat(prixJour.value).toFixed(2)} €</div>
                    <div class="col-6"><strong>Total location:</strong></div>
                    <div class="col-6"><strong class="text-success">${total.toFixed(2)} €</strong></div>
                </div>
            `;
        } else {
            recap.innerHTML = '<p class="text-danger">La date de fin doit être après la date de début</p>';
        }
    }
}

// Filtrage des locations
function appliquerFiltres() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statut = document.getElementById('statutFilter').value;
    const date = document.getElementById('dateFilter').value;
    
    const items = document.querySelectorAll('.location-item');
    
    items.forEach(item => {
        const client = item.getAttribute('data-client');
        const vehicule = item.getAttribute('data-vehicule');
        const itemStatut = item.getAttribute('data-statut');
        const itemDate = item.getAttribute('data-date');
        
        const matchSearch = client.includes(searchTerm) || vehicule.includes(searchTerm);
        const matchStatut = !statut || itemStatut === statut;
        const matchDate = !date || itemDate === date;
        
        item.style.display = (matchSearch && matchStatut && matchDate) ? 'table-row' : 'none';
    });
}

// Événements
document.getElementById('vehiculeSelect').addEventListener('change', calculerLocation);
document.getElementById('dateDebut').addEventListener('change', calculerLocation);
document.getElementById('dateFin').addEventListener('change', calculerLocation);
document.getElementById('prixJour').addEventListener('input', calculerLocation);

document.getElementById('searchInput').addEventListener('input', appliquerFiltres);
document.getElementById('statutFilter').addEventListener('change', appliquerFiltres);
document.getElementById('dateFilter').addEventListener('change', appliquerFiltres);

// Set min date to today
document.getElementById('dateDebut').min = new Date().toISOString().split('T')[0];
document.getElementById('dateFin').min = new Date().toISOString().split('T')[0];

function exporterLocations() {
    alert('Fonction d\'export à implémenter');
}
</script>

<?php include 'footer.php'; ?>
