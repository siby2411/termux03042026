<?php include 'config.php'; include 'header.php'; 
$db = Database::getInstance(); 
$conn = $db->getConnection();

// Récupérer les marques et modèles
$marques = $conn->query("SELECT * FROM marques ORDER BY nom")->fetchAll();
$modeles = $conn->query("SELECT m.*, mar.nom as marque_nom FROM modeles m JOIN marques mar ON m.marque_id = mar.id ORDER BY mar.nom, m.nom")->fetchAll();

// Récupérer tous les véhicules
$query = $conn->query("
    SELECT v.*, m.nom as modele_nom, mar.nom as marque_nom,
           (SELECT nom_fichier FROM vehicule_images WHERE vehicule_id = v.id AND est_principale = 1 LIMIT 1) as image_principale
    FROM vehicules v 
    JOIN modeles m ON v.modele_id = m.id 
    JOIN marques mar ON m.marque_id = mar.id
    ORDER BY v.date_ajout DESC
");
$vehicules = $query->fetchAll();

// Statistiques
$total_vehicules = count($vehicules);
$vehicules_disponibles = count(array_filter($vehicules, function($v) { return $v['statut'] == 'disponible'; }));
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">
                <i class="bi bi-car-front text-primary me-2"></i>Gestion du Parc Auto
            </h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ajouterVehiculeModal">
                <i class="bi bi-plus-circle me-1"></i>Nouveau Véhicule
            </button>
        </div>
        <p class="text-muted"><?= $total_vehicules ?> véhicules dans votre parc</p>
    </div>
</div>

<!-- Statistiques -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= $total_vehicules ?></h4>
                        <p class="card-text">Total Véhicules</p>
                    </div>
                    <i class="bi bi-car-front fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= $vehicules_disponibles ?></h4>
                        <p class="card-text">Disponibles</p>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= count(array_filter($vehicules, function($v) { return $v['type_vehicule'] == 'vente'; })) ?></h4>
                        <p class="card-text">En Vente</p>
                    </div>
                    <i class="bi bi-currency-euro fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title"><?= count(array_filter($vehicules, function($v) { return $v['type_vehicule'] == 'location'; })) ?></h4>
                        <p class="card-text">En Location</p>
                    </div>
                    <i class="bi bi-calendar-check fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" placeholder="🔍 Rechercher..." id="searchInput">
            </div>
            <div class="col-md-2">
                <select class="form-select" id="typeFilter">
                    <option value="">Tous types</option>
                    <option value="vente">En Vente</option>
                    <option value="location">En Location</option>
                    <option value="mixte">Mixte</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="statutFilter">
                    <option value="">Tous statuts</option>
                    <option value="disponible">Disponible</option>
                    <option value="vendu">Vendu</option>
                    <option value="loue">Loué</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" id="carburantFilter">
                    <option value="">Tous carburants</option>
                    <option value="essence">Essence</option>
                    <option value="diesel">Diesel</option>
                    <option value="electrique">Électrique</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-outline-primary w-100" onclick="appliquerFiltres()">
                    <i class="bi bi-funnel me-1"></i>Filtrer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Grille des véhicules -->
<div class="row" id="vehiculesGrid">
    <?php foreach($vehicules as $vehicule): 
        $badge_type = match($vehicule['type_vehicule']) {
            'vente' => 'success',
            'location' => 'info',
            'mixte' => 'warning'
        };
    ?>
    <div class="col-xl-4 col-lg-6 mb-4 vehicule-item"
         data-type="<?= $vehicule['type_vehicule'] ?>"
         data-statut="<?= $vehicule['statut'] ?>"
         data-carburant="<?= $vehicule['carburant'] ?>"
         data-marque="<?= htmlspecialchars(strtolower($vehicule['marque_nom'])) ?>">
        
        <div class="card vehicule-card h-100">
            <!-- Image -->
            <?php if($vehicule['image_principale']): ?>
                <img src="uploads/vehicules/<?= $vehicule['image_principale'] ?>" class="card-img-top vehicule-image" alt="<?= $vehicule['marque_nom'] ?> <?= $vehicule['modele_nom'] ?>">
            <?php else: ?>
                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                    <i class="bi bi-car-front fs-1 text-muted"></i>
                </div>
            <?php endif; ?>
            
            <!-- Badges -->
            <div class="position-absolute top-0 start-0 m-3">
                <span class="badge bg-<?= $badge_type ?>">
                    <?= match($vehicule['type_vehicule']) {
                        'vente' => '💰 Vente',
                        'location' => '📅 Location',
                        'mixte' => '🔄 Mixte'
                    } ?>
                </span>
            </div>
            
            <div class="card-body">
                <!-- En-tête -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title"><?= $vehicule['marque_nom'] ?> <?= $vehicule['modele_nom'] ?></h5>
                    <span class="badge bg-<?= $vehicule['statut'] == 'disponible' ? 'success' : 'secondary' ?>">
                        <?= ucfirst($vehicule['statut']) ?>
                    </span>
                </div>
                
                <!-- Informations -->
                <p class="card-text text-muted small">
                    <i class="bi bi-calendar me-1"></i><?= $vehicule['annee_circulation'] ?> • 
                    <i class="bi bi-speedometer2 me-1"></i><?= number_format($vehicule['kilometrage'], 0, ',', ' ') ?> km • 
                    <i class="bi bi-gear me-1"></i><?= ucfirst($vehicule['carburant']) ?>
                </p>
                
                <!-- Prix -->
                <div class="mb-3">
                    <?php if(in_array($vehicule['type_vehicule'], ['vente', 'mixte']) && $vehicule['prix_vente'] > 0): ?>
                        <div class="price-tag"><?= number_format($vehicule['prix_vente'], 0, ',', ' ') ?> €</div>
                    <?php endif; ?>
                    
                    <?php if(in_array($vehicule['type_vehicule'], ['location', 'mixte']) && $vehicule['prix_location_jour'] > 0): ?>
                        <div class="text-info fw-bold"><?= number_format($vehicule['prix_location_jour'], 0, ',', ' ') ?> €/jour</div>
                    <?php endif; ?>
                </div>
                
                <!-- Actions -->
                <div class="btn-group w-100">
                    <a href="vehicule_detail.php?id=<?= $vehicule['id'] ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye me-1"></i>Voir
                    </a>
                    <?php if($vehicule['type_vehicule'] != 'location' && $vehicule['statut'] == 'disponible'): ?>
                        <a href="ventes.php?vehicule_id=<?= $vehicule['id'] ?>" class="btn btn-outline-success btn-sm">
                            <i class="bi bi-currency-euro me-1"></i>Vendre
                        </a>
                    <?php endif; ?>
                    <?php if($vehicule['type_vehicule'] != 'vente' && $vehicule['statut'] == 'disponible'): ?>
                        <a href="locations.php?vehicule_id=<?= $vehicule['id'] ?>" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-calendar-check me-1"></i>Louer
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal Ajouter Véhicule -->
<div class="modal fade" id="ajouterVehiculeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un Véhicule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="vehicule_ajouter.php" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Modèle *</label>
                            <select name="modele_id" class="form-select" required>
                                <option value="">Choisir un modèle</option>
                                <?php foreach($modeles as $modele): ?>
                                <option value="<?= $modele['id'] ?>"><?= $modele['marque_nom'] ?> <?= $modele['nom'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Immatriculation *</label>
                            <input type="text" name="immatriculation" class="form-control" required placeholder="AB-123-CD">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Année *</label>
                            <input type="number" name="annee_circulation" class="form-control" required min="1990" max="<?= date('Y') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kilométrage</label>
                            <input type="number" name="kilometrage" class="form-control" min="0" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Couleur</label>
                            <input type="text" name="couleur" class="form-control" placeholder="Blanc">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Ajouter le véhicule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function appliquerFiltres() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const type = document.getElementById('typeFilter').value;
    const statut = document.getElementById('statutFilter').value;
    const carburant = document.getElementById('carburantFilter').value;
    
    const items = document.querySelectorAll('.vehicule-item');
    
    items.forEach(item => {
        const marque = item.getAttribute('data-marque');
        const itemType = item.getAttribute('data-type');
        const itemStatut = item.getAttribute('data-statut');
        const itemCarburant = item.getAttribute('data-carburant');
        
        const matchSearch = marque.includes(searchTerm);
        const matchType = !type || itemType === type;
        const matchStatut = !statut || itemStatut === statut;
        const matchCarburant = !carburant || itemCarburant === carburant;
        
        item.style.display = (matchSearch && matchType && matchStatut && matchCarburant) ? 'block' : 'none';
    });
}

// Événements filtres
document.getElementById('searchInput').addEventListener('input', appliquerFiltres);
document.getElementById('typeFilter').addEventListener('change', appliquerFiltres);
document.getElementById('statutFilter').addEventListener('change', appliquerFiltres);
document.getElementById('carburantFilter').addEventListener('change', appliquerFiltres);
</script>

<?php include 'footer.php'; ?>
