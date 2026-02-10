<?php include 'config.php'; include 'header.php'; 
$db = Database::getInstance();
$conn = $db->getConnection();

if(!isset($_GET['id'])) {
    header('Location: vehicules.php');
    exit;
}

$vehicule_id = intval($_GET['id']);

// Récupérer les infos du véhicule
$query = $conn->prepare("
    SELECT v.*, m.nom as modele_nom, mar.nom as marque_nom, mar.logo as marque_logo
    FROM vehicules v 
    JOIN modeles m ON v.modele_id = m.id 
    JOIN marques mar ON m.marque_id = mar.id
    WHERE v.id = ?
");
$query->execute([$vehicule_id]);
$vehicule = $query->fetch();

if(!$vehicule) {
    echo '<div class="alert alert-danger">Véhicule non trouvé</div>';
    include 'footer.php';
    exit;
}

// Récupérer les images
$query_images = $conn->prepare("SELECT * FROM vehicule_images WHERE vehicule_id = ? ORDER BY est_principale DESC, ordre ASC");
$query_images->execute([$vehicule_id]);
$images = $query_images->fetchAll();

// Image principale (ou première image)
$image_principale = $images[0] ?? null;

// Gestion de l'upload d'images
if($_POST && isset($_FILES['nouvelles_images'])) {
    try {
        $conn->beginTransaction();
        
        $ordre = count($images);
        foreach($_FILES['nouvelles_images']['tmp_name'] as $key => $tmp_name) {
            if($_FILES['nouvelles_images']['error'][$key] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['nouvelles_images']['name'][$key],
                    'type' => $_FILES['nouvelles_images']['type'][$key],
                    'tmp_name' => $tmp_name,
                    'error' => $_FILES['nouvelles_images']['error'][$key],
                    'size' => $_FILES['nouvelles_images']['size'][$key]
                ];
                
                $nom_fichier = uploadImage($file, $vehicule_id);
                $est_principale = ($ordre === 0 && empty($images)) ? 1 : 0;
                
                $stmt_img = $conn->prepare("INSERT INTO vehicule_images (vehicule_id, nom_fichier, est_principale, ordre) VALUES (?, ?, ?, ?)");
                $stmt_img->execute([$vehicule_id, $nom_fichier, $est_principale, $ordre]);
                $ordre++;
            }
        }
        
        $conn->commit();
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>Images ajoutées avec succès!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
        
        // Recharger les images
        $query_images->execute([$vehicule_id]);
        $images = $query_images->fetchAll();
        $image_principale = $images[0] ?? null;
        
    } catch(Exception $e) {
        $conn->rollBack();
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>Erreur: ' . $e->getMessage() . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    }
}

// Définir image principale
if(isset($_GET['set_principal'])) {
    $image_id = intval($_GET['set_principal']);
    
    try {
        $conn->beginTransaction();
        
        // Réinitialiser toutes les images
        $stmt_reset = $conn->prepare("UPDATE vehicule_images SET est_principale = 0 WHERE vehicule_id = ?");
        $stmt_reset->execute([$vehicule_id]);
        
        // Définir la nouvelle image principale
        $stmt_set = $conn->prepare("UPDATE vehicule_images SET est_principale = 1 WHERE id = ? AND vehicule_id = ?");
        $stmt_set->execute([$image_id, $vehicule_id]);
        
        $conn->commit();
        header('Location: vehicule_detail.php?id=' . $vehicule_id);
        exit;
        
    } catch(Exception $e) {
        $conn->rollBack();
        echo '<div class="alert alert-danger">Erreur: ' . $e->getMessage() . '</div>';
    }
}

// Supprimer une image
if(isset($_GET['delete_image'])) {
    $image_id = intval($_GET['delete_image']);
    
    try {
        // Récupérer le nom du fichier
        $stmt = $conn->prepare("SELECT nom_fichier FROM vehicule_images WHERE id = ? AND vehicule_id = ?");
        $stmt->execute([$image_id, $vehicule_id]);
        $image = $stmt->fetch();
        
        if($image) {
            // Supprimer le fichier physique
            deleteImage($image['nom_fichier']);
            
            // Supprimer de la base
            $stmt_delete = $conn->prepare("DELETE FROM vehicule_images WHERE id = ?");
            $stmt_delete->execute([$image_id]);
            
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>Image supprimée avec succès!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  </div>';
            
            // Recharger les images
            $query_images->execute([$vehicule_id]);
            $images = $query_images->fetchAll();
            $image_principale = $images[0] ?? null;
        }
        
    } catch(Exception $e) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>Erreur: ' . $e->getMessage() . '
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
              </div>';
    }
}
?>

<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="vehicules.php"><i class="bi bi-car-front"></i> Véhicules</a></li>
                <li class="breadcrumb-item active"><?= $vehicule['marque_nom'] ?> <?= $vehicule['modele_nom'] ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <!-- Galerie Photos -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Galerie Photos</h5>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#uploadImagesModal">
                    <i class="bi bi-cloud-upload me-1"></i>Ajouter des photos
                </button>
            </div>
            <div class="card-body">
                <?php if(!empty($images)): ?>
                <!-- Diaporama Principal -->
                <div id="vehiculeCarousel" class="carousel slide" data-bs-ride="carousel">
                    <!-- Indicateurs -->
                    <div class="carousel-indicators">
                        <?php foreach($images as $index => $image): ?>
                        <button type="button" data-bs-target="#vehiculeCarousel" data-bs-slide-to="<?= $index ?>" 
                                class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Slides -->
                    <div class="carousel-inner rounded" style="max-height: 500px; overflow: hidden;">
                        <?php foreach($images as $index => $image): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>" style="height: 500px;">
                            <img src="uploads/vehicules/<?= $image['nom_fichier'] ?>" 
                                 class="d-block w-100 h-100" 
                                 style="object-fit: cover;"
                                 alt="<?= $vehicule['marque_nom'] ?> <?= $vehicule['modele_nom'] ?> - Photo <?= $index + 1 ?>">
                            <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-2">
                                <h6>Photo <?= $index + 1 ?></h6>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Contrôles -->
                    <button class="carousel-control-prev" type="button" data-bs-target="#vehiculeCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Précédent</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#vehiculeCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Suivant</span>
                    </button>
                </div>
                
                <!-- Miniatures -->
                <div class="row mt-3 g-2" id="thumbnails">
                    <?php foreach($images as $index => $image): ?>
                    <div class="col-3 col-sm-2">
                        <div class="position-relative">
                            <img src="uploads/vehicules/<?= $image['nom_fichier'] ?>" 
                                 class="img-thumbnail w-100 cursor-pointer"
                                 style="height: 80px; object-fit: cover; cursor: pointer;"
                                 onclick="showSlide(<?= $index ?>)"
                                 alt="Miniature">
                            
                            <!-- Badge image principale -->
                            <?php if($image['est_principale']): ?>
                                <span class="position-absolute top-0 start-0 badge bg-success" title="Image principale">
                                    <i class="bi bi-star-fill"></i>
                                </span>
                            <?php endif; ?>
                            
                            <!-- Menu actions -->
                            <div class="position-absolute top-0 end-0">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light bg-white bg-opacity-75 border-0 p-1" 
                                            type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <?php if(!$image['est_principale']): ?>
                                        <li>
                                            <a class="dropdown-item" href="?id=<?= $vehicule_id ?>&set_principal=<?= $image['id'] ?>">
                                                <i class="bi bi-star me-2"></i>Définir comme principale
                                            </a>
                                        </li>
                                        <?php endif; ?>
                                        <li>
                                            <a class="dropdown-item text-danger" 
                                               href="?id=<?= $vehicule_id ?>&delete_image=<?= $image['id'] ?>" 
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette image ?')">
                                                <i class="bi bi-trash me-2"></i>Supprimer
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <!-- Aucune image -->
                <div class="text-center py-5">
                    <i class="bi bi-image display-1 text-muted"></i>
                    <h5 class="text-muted mt-3">Aucune photo disponible</h5>
                    <p class="text-muted">Ajoutez des photos pour mettre en valeur ce véhicule</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadImagesModal">
                        <i class="bi bi-cloud-upload me-1"></i>Ajouter des photos
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Informations Véhicule -->
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">
                    <?= $vehicule['marque_nom'] ?> <?= $vehicule['modele_nom'] ?>
                </h5>
            </div>
            <div class="card-body">
                <!-- Badges -->
                <div class="d-flex gap-2 mb-3">
                    <span class="badge bg-<?= $vehicule['statut'] == 'disponible' ? 'success' : 'secondary' ?>">
                        <?= ucfirst($vehicule['statut']) ?>
                    </span>
                    <span class="badge bg-<?= match($vehicule['type_vehicule']) {
                        'vente' => 'success',
                        'location' => 'warning',
                        'mixte' => 'info'
                    } ?>">
                        <?= match($vehicule['type_vehicule']) {
                            'vente' => '💰 Vente',
                            'location' => '📅 Location',
                            'mixte' => '🔄 Mixte'
                        } ?>
                    </span>
                </div>
                
                <!-- Immatriculation -->
                <div class="mb-3">
                    <strong>Immatriculation:</strong>
                    <span class="badge bg-dark"><?= $vehicule['immatriculation'] ?></span>
                </div>
                
                <!-- Prix -->
                <div class="mb-4">
                    <?php if(in_array($vehicule['type_vehicule'], ['vente', 'mixte']) && $vehicule['prix_vente'] > 0): ?>
                        <div class="price-tag display-6"><?= number_format($vehicule['prix_vente'], 0, ',', ' ') ?> €</div>
                    <?php endif; ?>
                    
                    <?php if(in_array($vehicule['type_vehicule'], ['location', 'mixte']) && $vehicule['prix_location_jour'] > 0): ?>
                        <div class="text-warning fw-bold fs-5">
                            <?= number_format($vehicule['prix_location_jour'], 0, ',', ' ') ?> €/jour
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Caractéristiques -->
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="card bg-light">
                            <div class="card-body text-center p-2">
                                <i class="bi bi-calendar text-primary"></i>
                                <small class="d-block">Année</small>
                                <strong><?= $vehicule['annee_circulation'] ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-light">
                            <div class="card-body text-center p-2">
                                <i class="bi bi-speedometer2 text-primary"></i>
                                <small class="d-block">Kilométrage</small>
                                <strong><?= number_format($vehicule['kilometrage'], 0, ',', ' ') ?> km</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-light">
                            <div class="card-body text-center p-2">
                                <i class="bi bi-fuel-pump text-primary"></i>
                                <small class="d-block">Carburant</small>
                                <strong><?= ucfirst($vehicule['carburant']) ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card bg-light">
                            <div class="card-body text-center p-2">
                                <i class="bi bi-gear text-primary"></i>
                                <small class="d-block">Boîte</small>
                                <strong><?= ucfirst($vehicule['boite_vitesse']) ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="d-grid gap-2">
                    <?php if(in_array($vehicule['type_vehicule'], ['vente', 'mixte']) && $vehicule['statut'] == 'disponible'): ?>
                        <a href="ventes.php?vehicule_id=<?= $vehicule['id'] ?>" class="btn btn-success">
                            <i class="bi bi-currency-euro me-1"></i>Procéder à la vente
                        </a>
                    <?php endif; ?>
                    
                    <?php if(in_array($vehicule['type_vehicule'], ['location', 'mixte']) && $vehicule['statut'] == 'disponible'): ?>
                        <a href="locations.php?vehicule_id=<?= $vehicule['id'] ?>" class="btn btn-warning text-white">
                            <i class="bi bi-calendar-check me-1"></i>Louer ce véhicule
                        </a>
                    <?php endif; ?>
                    
                    <a href="vehicules.php" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-left me-1"></i>Retour aux véhicules
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Informations détaillées -->
        <div class="card mt-4">
            <div class="card-header bg-white">
                <h6 class="card-title mb-0">Caractéristiques détaillées</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6"><small><strong>Couleur:</strong></small></div>
                    <div class="col-6"><small><?= $vehicule['couleur'] ?: 'Non spécifiée' ?></small></div>
                    
                    <div class="col-6"><small><strong>Portes:</strong></small></div>
                    <div class="col-6"><small><?= $vehicule['portes'] ?></small></div>
                    
                    <div class="col-6"><small><strong>Places:</strong></small></div>
                    <div class="col-6"><small><?= $vehicule['places'] ?></small></div>
                    
                    <div class="col-6"><small><strong>Puissance:</strong></small></div>
                    <div class="col-6"><small><?= $vehicule['puissance'] ?> CV</small></div>
                </div>
                
                <?php if($vehicule['options']): ?>
                <hr>
                <h6 class="mb-2">Équipements</h6>
                <div class="row g-1">
                    <?php 
                    $options = explode(',', $vehicule['options']);
                    foreach($options as $option): 
                        if(trim($option)):
                    ?>
                    <div class="col-6">
                        <small><i class="bi bi-check text-success me-1"></i><?= trim($option) ?></small>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Description -->
<?php if($vehicule['description']): ?>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Description</h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?= nl2br(htmlspecialchars($vehicule['description'])) ?></p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Upload Images -->
<div class="modal fade" id="uploadImagesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter des photos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Sélectionnez les images</label>
                        <input type="file" class="form-control" name="nouvelles_images[]" multiple 
                               accept="image/*" required>
                        <div class="form-text">
                            Formats acceptés: JPG, PNG, GIF, WEBP (max 10MB par image)
                        </div>
                    </div>
                    <div id="imagePreviews" class="row g-2 mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Uploader les images</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Navigation du diaporama via les miniatures
function showSlide(index) {
    const carousel = new bootstrap.Carousel(document.getElementById('vehiculeCarousel'));
    carousel.to(index);
}

// Prévisualisation des images avant upload
document.querySelector('input[name="nouvelles_images[]"]').addEventListener('change', function(e) {
    const previews = document.getElementById('imagePreviews');
    previews.innerHTML = '';
    
    if (this.files.length > 0) {
        for (let file of this.files) {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-4';
                    col.innerHTML = `
                        <img src="${e.target.result}" class="img-thumbnail w-100" style="height: 80px; object-fit: cover;">
                    `;
                    previews.appendChild(col);
                };
                reader.readAsDataURL(file);
            }
        }
    }
});

// Auto-play du diaporama
document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('vehiculeCarousel');
    if (carousel) {
        // Démarrer l'auto-play
        const bsCarousel = new bootstrap.Carousel(carousel, {
            interval: 5000, // 5 secondes
            ride: 'carousel'
        });
    }
});
</script>

<style>
.carousel-item {
    transition: transform 0.6s ease-in-out;
}

.carousel-control-prev,
.carousel-control-next {
    width: 5%;
}

.cursor-pointer {
    cursor: pointer;
}

.img-thumbnail {
    transition: transform 0.2s ease;
}

.img-thumbnail:hover {
    transform: scale(1.05);
}

.price-tag {
    color: #27ae60;
    font-weight: 700;
}
</style>

<?php include 'footer.php'; ?>
