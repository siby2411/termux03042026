<?php include 'config.php'; include 'header.php'; 
$db = new Database();
$conn = $db->getConnection();

// Variables pour le formulaire d'édition
$edition_mode = false;
$plat_a_editer = null;

// Ajouter un plat
if($_POST && isset($_POST['ajouter_plat'])) {
    try {
        $nom = trim($_POST['nom']);
        $description = trim($_POST['description']);
        $prix = floatval($_POST['prix']);
        $categorie_id = intval($_POST['categorie_id']);
        $ingredients = trim($_POST['ingredients']);
        $temps_preparation = intval($_POST['temps_preparation']);
        $vegetarien = isset($_POST['vegetarien']) ? 1 : 0;
        $epice = $_POST['epice'];
        $disponible = isset($_POST['disponible']) ? 1 : 0;
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        // Gestion de l'upload d'image
        $image = null;
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $image = uploadImage($_FILES['image']);
        }
        
        $stmt = $conn->prepare("INSERT INTO plats (nom, description, prix, categorie_id, image, ingredients, temps_preparation, vegetarien, epice, disponible, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $description, $prix, $categorie_id, $image, $ingredients, $temps_preparation, $vegetarien, $epice, $disponible, $featured]);
        
        echo '<div class="alert alert-success">✅ Plat ajouté avec succès!</div>';
    } catch(Exception $e) {
        echo '<div class="alert alert-error">❌ Erreur: ' . $e->getMessage() . '</div>';
    }
}

// Modifier un plat
if($_POST && isset($_POST['modifier_plat'])) {
    try {
        $id = intval($_POST['id']);
        $nom = trim($_POST['nom']);
        $description = trim($_POST['description']);
        $prix = floatval($_POST['prix']);
        $categorie_id = intval($_POST['categorie_id']);
        $ingredients = trim($_POST['ingredients']);
        $temps_preparation = intval($_POST['temps_preparation']);
        $vegetarien = isset($_POST['vegetarien']) ? 1 : 0;
        $epice = $_POST['epice'];
        $disponible = isset($_POST['disponible']) ? 1 : 0;
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        // Gestion de l'upload d'image
        $image = $_POST['image_actuelle']; // Conserver l'image actuelle par défaut
        if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Supprimer l'ancienne image si elle existe
            if($image && file_exists(UPLOAD_DIR . $image)) {
                unlink(UPLOAD_DIR . $image);
            }
            $image = uploadImage($_FILES['image']);
        }
        
        $stmt = $conn->prepare("UPDATE plats SET nom = ?, description = ?, prix = ?, categorie_id = ?, image = ?, ingredients = ?, temps_preparation = ?, vegetarien = ?, epice = ?, disponible = ?, featured = ? WHERE id = ?");
        $stmt->execute([$nom, $description, $prix, $categorie_id, $image, $ingredients, $temps_preparation, $vegetarien, $epice, $disponible, $featured, $id]);
        
        echo '<div class="alert alert-success">✅ Plat modifié avec succès!</div>';
        $edition_mode = false;
    } catch(Exception $e) {
        echo '<div class="alert alert-error">❌ Erreur: ' . $e->getMessage() . '</div>';
    }
}

// Préparation de l'édition
if(isset($_GET['editer'])) {
    $id = intval($_GET['editer']);
    $stmt = $conn->prepare("SELECT p.*, c.nom as categorie_nom FROM plats p LEFT JOIN categories c ON p.categorie_id = c.id WHERE p.id = ?");
    $stmt->execute([$id]);
    $plat_a_editer = $stmt->fetch();
    
    if($plat_a_editer) {
        $edition_mode = true;
    }
}

// Activer/désactiver un plat
if(isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    try {
        $stmt = $conn->prepare("UPDATE plats SET disponible = NOT disponible WHERE id = ?");
        $stmt->execute([$id]);
        echo '<div class="alert alert-success">✅ Statut du plat modifié!</div>';
    } catch(PDOException $e) {
        echo '<div class="alert alert-error">❌ Erreur: ' . $e->getMessage() . '</div>';
    }
}

// Récupérer les catégories
$categories = $conn->query("SELECT * FROM categories WHERE active = 1 ORDER BY ordre, nom")->fetchAll();

// Récupérer tous les plats avec leurs catégories
$query = $conn->query("
    SELECT p.*, c.nom as categorie_nom 
    FROM plats p 
    LEFT JOIN categories c ON p.categorie_id = c.id 
    ORDER BY c.ordre, p.nom
");
$plats = $query->fetchAll();

// Statistiques
$total_plats = count($plats);
$plats_actifs = count(array_filter($plats, function($p) { return $p['disponible']; }));
$prix_moyen = $total_plats > 0 ? array_sum(array_column($plats, 'prix')) / $total_plats : 0;
$plats_featured = count(array_filter($plats, function($p) { return $p['featured']; }));
?>

<div class="card">
    <h2><?= $edition_mode ? '✏️ Modifier le Plat' : '🍽️ Ajouter un Nouveau Plat' ?></h2>
    
    <form method="POST" id="formPlat" enctype="multipart/form-data">
        <?php if($edition_mode): ?>
            <input type="hidden" name="id" value="<?= $plat_a_editer['id'] ?>">
            <input type="hidden" name="image_actuelle" value="<?= $plat_a_editer['image'] ?>">
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="nom">Nom du Plat *</label>
                <input type="text" id="nom" name="nom" 
                       value="<?= $edition_mode ? htmlspecialchars($plat_a_editer['nom']) : '' ?>" 
                       required placeholder="Ex: Pizza Margherita">
            </div>
            
            <div class="form-group">
                <label for="categorie_id">Catégorie *</label>
                <select id="categorie_id" name="categorie_id" required>
                    <option value="">Choisir une catégorie</option>
                    <?php foreach($categories as $categorie): ?>
                        <option value="<?= $categorie['id'] ?>" 
                            <?= $edition_mode && $plat_a_editer['categorie_id'] == $categorie['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($categorie['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3" 
                      placeholder="Description appétissante du plat..."><?= $edition_mode ? htmlspecialchars($plat_a_editer['description']) : '' ?></textarea>
        </div>

        <div class="form-group">
            <label for="ingredients">Ingrédients</label>
            <textarea id="ingredients" name="ingredients" rows="2" 
                      placeholder="Liste des ingrédients principaux..."><?= $edition_mode ? htmlspecialchars($plat_a_editer['ingredients']) : '' ?></textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="prix">Prix (€) *</label>
                <input type="number" id="prix" name="prix" step="0.01" min="0" 
                       value="<?= $edition_mode ? $plat_a_editer['prix'] : '' ?>" 
                       required placeholder="0.00">
            </div>
            
            <div class="form-group">
                <label for="temps_preparation">Temps préparation (min)</label>
                <input type="number" id="temps_preparation" name="temps_preparation" min="0" 
                       value="<?= $edition_mode ? $plat_a_editer['temps_preparation'] : '' ?>" 
                       placeholder="Ex: 20">
            </div>
            
            <div class="form-group">
                <label for="epice">Niveau d'épice</label>
                <select id="epice" name="epice">
                    <option value="doux" <?= $edition_mode && $plat_a_editer['epice'] == 'doux' ? 'selected' : '' ?>>🌶️ Doux</option>
                    <option value="moyen" <?= $edition_mode && $plat_a_editer['epice'] == 'moyen' ? 'selected' : '' ?>>🌶️🌶️ Moyen</option>
                    <option value="fort" <?= $edition_mode && $plat_a_editer['epice'] == 'fort' ? 'selected' : '' ?>>🌶️🌶️🌶️ Fort</option>
                </select>
            </div>
        </div>

        <!-- Upload d'image -->
        <div class="form-group">
            <label for="image">Image du plat</label>
            <div class="upload-area" onclick="document.getElementById('image').click()">
                <div id="upload-text">
                    <?php if($edition_mode && $plat_a_editer['image']): ?>
                        <img src="uploads/<?= $plat_a_editer['image'] ?>" class="image-preview" id="image-preview">
                        <p>Cliquez pour changer l'image</p>
                    <?php else: ?>
                        <div style="font-size: 3rem;">📷</div>
                        <p>Cliquez pour télécharger une image</p>
                        <small>JPG, PNG, GIF - Max 5MB</small>
                    <?php endif; ?>
                </div>
            </div>
            <input type="file" id="image" name="image" accept="image/*" style="display: none;" onchange="previewImage(this)">
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="vegetarien" value="1" 
                           <?= ($edition_mode && $plat_a_editer['vegetarien']) ? 'checked' : '' ?>>
                    🥕 Végétarien
                </label>
            </div>
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="disponible" value="1" 
                           <?= ($edition_mode && $plat_a_editer['disponible']) || !$edition_mode ? 'checked' : '' ?>>
                    ✅ Disponible
                </label>
            </div>
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" name="featured" value="1" 
                           <?= ($edition_mode && $plat_a_editer['featured']) ? 'checked' : '' ?>>
                    ⭐ Plat du moment
                </label>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" name="<?= $edition_mode ? 'modifier_plat' : 'ajouter_plat' ?>" 
                    class="btn btn-success">
                <?= $edition_mode ? '💾 Mettre à jour' : '➕ Ajouter le plat' ?>
            </button>
            
            <?php if($edition_mode): ?>
                <a href="plats.php" class="btn btn-warning">❌ Annuler</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Statistiques des plats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $total_plats ?></div>
        <div>Total Plats</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $plats_actifs ?></div>
        <div>Plats Actifs</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $plats_featured ?></div>
        <div>Plats du moment</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= number_format($prix_moyen, 2, ',', ' ') ?> €</div>
        <div>Prix moyen</div>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2>📋 Carte des Plats (<?= $total_plats ?>)</h2>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="exporterPlats()" class="btn btn-primary">📄 Exporter CSV</button>
            <button onclick="imprimerCarte()" class="btn btn-primary">🖨️ Imprimer Carte</button>
        </div>
    </div>

    <!-- Filtres -->
    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
        <div>
            <input type="text" id="searchInput" placeholder="🔍 Rechercher un plat..." style="width: 100%;">
        </div>
        <div>
            <select id="categorieFilter" style="width: 100%;">
                <option value="">Toutes catégories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat['nom']) ?>"><?= htmlspecialchars($cat['nom']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <select id="statutFilter" style="width: 100%;">
                <option value="">Tous statuts</option>
                <option value="actif">✅ Actifs</option>
                <option value="inactif">❌ Inactifs</option>
            </select>
        </div>
        <div>
            <select id="specialiteFilter" style="width: 100%;">
                <option value="">Toutes spécialités</option>
                <option value="vegetarien">🥕 Végétarien</option>
                <option value="featured">⭐ Du moment</option>
            </select>
        </div>
        <div>
            <button onclick="appliquerFiltres()" class="btn btn-primary">Filtrer</button>
            <button onclick="reinitialiserFiltres()" class="btn btn-warning">Réinitialiser</button>
        </div>
    </div>

    <div class="table-container">
        <table id="tablePlats">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Description</th>
                    <th>Prix</th>
                    <th>Temps</th>
                    <th>Spécialités</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($plats as $plat): ?>
                <tr class="plat-row" 
                    data-categorie="<?= htmlspecialchars($plat['categorie_nom']) ?>" 
                    data-statut="<?= $plat['disponible'] ? 'actif' : 'inactif' ?>"
                    data-nom="<?= htmlspecialchars(strtolower($plat['nom'])) ?>"
                    data-vegetarien="<?= $plat['vegetarien'] ? 'vegetarien' : '' ?>"
                    data-featured="<?= $plat['featured'] ? 'featured' : '' ?>">
                    <td>
                        <?php if($plat['image']): ?>
                            <img src="uploads/<?= $plat['image'] ?>" class="plat-image" alt="<?= htmlspecialchars($plat['nom']) ?>">
                        <?php else: ?>
                            <div style="width: 80px; height: 80px; background: #f8f9fa; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #95a5a6;">
                                📷
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($plat['nom']) ?></strong>
                        <?php if($plat['featured']): ?>
                            <br><span class="badge badge-featured">⭐ Du moment</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-info"><?= htmlspecialchars($plat['categorie_nom']) ?></span>
                    </td>
                    <td>
                        <?= $plat['description'] ? htmlspecialchars(substr($plat['description'], 0, 50)) . (strlen($plat['description']) > 50 ? '...' : '') : '<span style="color: #95a5a6;">Aucune description</span>' ?>
                        <?php if($plat['ingredients']): ?>
                            <br><small style="color: #7f8c8d;"><?= htmlspecialchars(substr($plat['ingredients'], 0, 30)) ?>...</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong style="color: #27ae60;"><?= number_format($plat['prix'], 2, ',', ' ') ?> €</strong>
                    </td>
                    <td>
                        <?= $plat['temps_preparation'] ? $plat['temps_preparation'] . ' min' : '<span style="color: #95a5a6;">-</span>' ?>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                            <?php if($plat['vegetarien']): ?>
                                <span class="badge badge-vegetarien">🥕 Végétarien</span>
                            <?php endif; ?>
                            <span class="badge badge-epice-<?= $plat['epice'] ?>">
                                <?= str_repeat('🌶️', match($plat['epice']) { 'moyen' => 2, 'fort' => 3, default => 1 }) ?>
                                <?= ucfirst($plat['epice']) ?>
                            </span>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?= $plat['disponible'] ? 'badge-success' : 'badge-danger' ?>">
                            <?= $plat['disponible'] ? '✅ Actif' : '❌ Inactif' ?>
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                            <a href="plats.php?editer=<?= $plat['id'] ?>" class="btn btn-primary btn-sm" title="Modifier">✏️</a>
                            <a href="plats.php?toggle=<?= $plat['id'] ?>" 
                               class="btn btn-warning btn-sm" 
                               title="<?= $plat['disponible'] ? 'Désactiver' : 'Activer' ?>">
                                <?= $plat['disponible'] ? '⏸️' : '▶️' ?>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if(empty($plats)): ?>
        <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
            <div style="font-size: 4rem;">🍽️</div>
            <h3>Aucun plat enregistré</h3>
            <p>Commencez par ajouter votre premier plat en utilisant le formulaire ci-dessus.</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Prévisualisation de l'image
function previewImage(input) {
    const preview = document.getElementById('image-preview');
    const uploadText = document.getElementById('upload-text');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (!preview) {
                uploadText.innerHTML = `<img src="${e.target.result}" class="image-preview" id="image-preview"><p>Cliquez pour changer l'image</p>`;
            } else {
                preview.src = e.target.result;
            }
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Filtrage des plats
function appliquerFiltres() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categorie = document.getElementById('categorieFilter').value;
    const statut = document.getElementById('statutFilter').value;
    const specialite = document.getElementById('specialiteFilter').value;
    
    const rows = document.querySelectorAll('.plat-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const nom = row.getAttribute('data-nom');
        const rowCategorie = row.getAttribute('data-categorie');
        const rowStatut = row.getAttribute('data-statut');
        const rowVegetarien = row.getAttribute('data-vegetarien');
        const rowFeatured = row.getAttribute('data-featured');
        
        const matchSearch = nom.includes(searchTerm);
        const matchCategorie = !categorie || rowCategorie === categorie;
        const matchStatut = !statut || rowStatut === statut;
        const matchSpecialite = !specialite || 
                               (specialite === 'vegetarien' && rowVegetarien === 'vegetarien') ||
                               (specialite === 'featured' && rowFeatured === 'featured');
        
        if (matchSearch && matchCategorie && matchStatut && matchSpecialite) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Mettre à jour le titre avec le nombre de résultats
    const titre = document.querySelector('.card h2');
    if (titre) {
        titre.textContent = `📋 Carte des Plats (${visibleCount} résultat${visibleCount > 1 ? 's' : ''})`;
    }
}

function reinitialiserFiltres() {
    document.getElementById('searchInput').value = '';
    document.getElementById('categorieFilter').value = '';
    document.getElementById('statutFilter').value = '';
    document.getElementById('specialiteFilter').value = '';
    appliquerFiltres();
}

// Export des plats
function exporterPlats() {
    let csv = 'Nom;Catégorie;Description;Ingrédients;Prix;Temps;Végétarien;Épice;Statut\n';
    
    document.querySelectorAll('.plat-row').forEach(row => {
        if (row.style.display !== 'none') {
            const cells = row.querySelectorAll('td');
            const nom = cells[1].textContent.trim();
            const categorie = cells[2].textContent.trim();
            const description = cells[3].textContent.trim();
            const prix = cells[4].textContent.trim().replace(' €', '');
            const temps = cells[5].textContent.trim();
            const vegetarien = cells[6].querySelector('.badge-vegetarien') ? 'Oui' : 'Non';
            const epice = cells[6].querySelector('.badge')?.textContent.trim() || 'Doux';
            const statut = cells[7].textContent.trim();
            
            csv += `"${nom}";"${categorie}";"${description}";"${prix}";"${temps}";"${vegetarien}";"${epice}";"${statut}"\n`;
        }
    });
    
    const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'carte_plats_' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Impression de la carte
function imprimerCarte() {
    const table = document.getElementById('tablePlats').cloneNode(true);
    
    // Supprimer les colonnes d'actions pour l'impression
    const rows = table.querySelectorAll('tr');
    rows.forEach(row => {
        const cells = row.querySelectorAll('td, th');
        if (cells.length > 0) {
            cells[cells.length - 1].remove(); // Supprimer dernière colonne (actions)
        }
    });
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Carte des Plats - La Bonne Cuisine</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .plat-image { width: 60px; height: 60px; object-fit: cover; border-radius: 4px; }
                    .badge { padding: 2px 6px; border-radius: 3px; font-size: 0.8em; }
                </style>
            </head>
            <body>
                <h1>Carte des Plats - La Bonne Cuisine</h1>
                <p>Généré le ${new Date().toLocaleDateString('fr-FR')}</p>
                ${table.outerHTML}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

// Événements pour les filtres
document.getElementById('searchInput').addEventListener('input', appliquerFiltres);
document.getElementById('categorieFilter').addEventListener('change', appliquerFiltres);
document.getElementById('statutFilter').addEventListener('change', appliquerFiltres);
document.getElementById('specialiteFilter').addEventListener('change', appliquerFiltres);

// Appliquer les filtres au chargement
document.addEventListener('DOMContentLoaded', function() {
    appliquerFiltres();
});
</script>

<style>
.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
}

.table-container {
    overflow-x: auto;
}

.plat-row {
    transition: background-color 0.3s ease;
}

.plat-row:hover {
    background-color: #f8f9fa !important;
}

/* Responsive */
@media (max-width: 768px) {
    .table-container {
        font-size: 0.9rem;
    }
    
    .btn-sm {
        padding: 0.3rem 0.6rem;
        font-size: 0.7rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr 1fr;
    }
    
    /* Cacher certaines colonnes sur mobile */
    .table-container td:nth-child(3),
    .table-container th:nth-child(3),
    .table-container td:nth-child(4),
    .table-container th:nth-child(4) {
        display: none;
    }
}
</style>

<?php include 'footer.php'; ?>
