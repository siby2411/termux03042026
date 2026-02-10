<?php include 'config.php'; include 'header.php'; 
$db = new Database();
$conn = $db->getConnection();

// Variables pour le formulaire d'édition
$edition_mode = false;
$service_a_editer = null;

// Ajouter un service
if($_POST && isset($_POST['ajouter_service'])) {
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $duree_moyenne = intval($_POST['duree_moyenne']);
    $categorie = trim($_POST['categorie']);
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    
    try {
        $stmt = $conn->prepare("INSERT INTO services (nom, description, prix, duree_moyenne, categorie, disponible) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $description, $prix, $duree_moyenne, $categorie, $disponible]);
        
        echo '<div class="alert alert-success">✅ Service ajouté avec succès!</div>';
    } catch(PDOException $e) {
        echo '<div class="alert alert-error">❌ Erreur lors de l\'ajout: ' . $e->getMessage() . '</div>';
    }
}

// Modifier un service
if($_POST && isset($_POST['modifier_service'])) {
    $id = intval($_POST['id']);
    $nom = trim($_POST['nom']);
    $description = trim($_POST['description']);
    $prix = floatval($_POST['prix']);
    $duree_moyenne = intval($_POST['duree_moyenne']);
    $categorie = trim($_POST['categorie']);
    $disponible = isset($_POST['disponible']) ? 1 : 0;
    
    try {
        $stmt = $conn->prepare("UPDATE services SET nom = ?, description = ?, prix = ?, duree_moyenne = ?, categorie = ?, disponible = ? WHERE id = ?");
        $stmt->execute([$nom, $description, $prix, $duree_moyenne, $categorie, $disponible, $id]);
        
        echo '<div class="alert alert-success">✅ Service modifié avec succès!</div>';
        $edition_mode = false;
    } catch(PDOException $e) {
        echo '<div class="alert alert-error">❌ Erreur lors de la modification: ' . $e->getMessage() . '</div>';
    }
}

// Activer/désactiver un service
if(isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    
    try {
        $stmt = $conn->prepare("UPDATE services SET disponible = NOT disponible WHERE id = ?");
        $stmt->execute([$id]);
        
        echo '<div class="alert alert-success">✅ Statut du service modifié!</div>';
    } catch(PDOException $e) {
        echo '<div class="alert alert-error">❌ Erreur: ' . $e->getMessage() . '</div>';
    }
}

// Préparation de l'édition
if(isset($_GET['editer'])) {
    $id = intval($_GET['editer']);
    $stmt = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $service_a_editer = $stmt->fetch();
    
    if($service_a_editer) {
        $edition_mode = true;
    }
}

// Supprimer un service
if(isset($_GET['supprimer'])) {
    $id = intval($_GET['supprimer']);
    
    try {
        // Vérifier si le service est utilisé dans des commandes
        $stmt = $conn->prepare("SELECT COUNT(*) as usage_count FROM commande_articles WHERE service_id = ?");
        $stmt->execute([$id]);
        $usage = $stmt->fetch();
        
        if($usage['usage_count'] > 0) {
            echo '<div class="alert alert-warning">⚠️ Impossible de supprimer ce service car il est utilisé dans ' . $usage['usage_count'] . ' commande(s).</div>';
        } else {
            $stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$id]);
            echo '<div class="alert alert-success">✅ Service supprimé avec succès!</div>';
        }
    } catch(PDOException $e) {
        echo '<div class="alert alert-error">❌ Erreur lors de la suppression: ' . $e->getMessage() . '</div>';
    }
}

// Récupérer les catégories existantes
$categories = $conn->query("SELECT DISTINCT categorie FROM services WHERE categorie IS NOT NULL AND categorie != '' ORDER BY categorie")->fetchAll(PDO::FETCH_COLUMN);

// Récupérer tous les services
$query = $conn->query("SELECT * FROM services ORDER BY categorie, nom");
$services = $query->fetchAll();

// Statistiques
$total_services = count($services);
$services_actifs = count(array_filter($services, function($s) { return $s['disponible']; }));
$nombre_categories = count(array_unique(array_column($services, 'categorie')));
$prix_moyen = $total_services > 0 ? array_sum(array_column($services, 'prix')) / $total_services : 0;
?>

<div class="card">
    <h2><?= $edition_mode ? '✏️ Modifier le Service' : '➕ Ajouter un Nouveau Service' ?></h2>
    
    <form method="POST" id="formService">
        <?php if($edition_mode): ?>
            <input type="hidden" name="id" value="<?= $service_a_editer['id'] ?>">
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="nom">Nom du Service *</label>
                <input type="text" id="nom" name="nom" 
                       value="<?= $edition_mode ? htmlspecialchars($service_a_editer['nom']) : '' ?>" 
                       required placeholder="Ex: Boubou homme">
            </div>
            
            <div class="form-group">
                <label for="categorie">Catégorie *</label>
                <select id="categorie" name="categorie" required>
                    <option value="">Choisir une catégorie</option>
                    <option value="Tenues Africaines" <?= $edition_mode && $service_a_editer['categorie'] == 'Tenues Africaines' ? 'selected' : '' ?>>👑 Tenues Africaines</option>
                    <option value="Tenues Arabes" <?= $edition_mode && $service_a_editer['categorie'] == 'Tenues Arabes' ? 'selected' : '' ?>>🌙 Tenues Arabes</option>
                    <option value="Vêtements Légers" <?= $edition_mode && $service_a_editer['categorie'] == 'Vêtements Légers' ? 'selected' : '' ?>>👕 Vêtements Légers</option>
                    <option value="Vêtements Élégants" <?= $edition_mode && $service_a_editer['categorie'] == 'Vêtements Élégants' ? 'selected' : '' ?>>👔 Vêtements Élégants</option>
                    <option value="Vêtements Lourds" <?= $edition_mode && $service_a_editer['categorie'] == 'Vêtements Lourds' ? 'selected' : '' ?>>🧥 Vêtements Lourds</option>
                    <option value="Linge de Maison" <?= $edition_mode && $service_a_editer['categorie'] == 'Linge de Maison' ? 'selected' : '' ?>>🏠 Linge de Maison</option>
                    <option value="Autre" <?= $edition_mode && $service_a_editer['categorie'] == 'Autre' ? 'selected' : '' ?>>📦 Autre</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3" 
                      placeholder="Description détaillée du service..."><?= $edition_mode ? htmlspecialchars($service_a_editer['description']) : '' ?></textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="prix">Prix (€) *</label>
                <input type="number" id="prix" name="prix" step="0.01" min="0" 
                       value="<?= $edition_mode ? $service_a_editer['prix'] : '' ?>" 
                       required placeholder="0.00">
            </div>
            
            <div class="form-group">
                <label for="duree_moyenne">Durée moyenne (minutes)</label>
                <input type="number" id="duree_moyenne" name="duree_moyenne" min="0" 
                       value="<?= $edition_mode ? $service_a_editer['duree_moyenne'] : '' ?>" 
                       placeholder="Ex: 30">
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; margin-top: 1.5rem;">
                    <input type="checkbox" name="disponible" value="1" 
                           <?= ($edition_mode && $service_a_editer['disponible']) || !$edition_mode ? 'checked' : '' ?>>
                    Service disponible
                </label>
            </div>
        </div>
        
        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" name="<?= $edition_mode ? 'modifier_service' : 'ajouter_service' ?>" 
                    class="btn btn-success">
                <?= $edition_mode ? '💾 Mettre à jour' : '➕ Ajouter le service' ?>
            </button>
            
            <?php if($edition_mode): ?>
                <a href="services.php" class="btn btn-warning">❌ Annuler</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Statistiques des services -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $total_services ?></div>
        <div>Total Services</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $services_actifs ?></div>
        <div>Services Actifs</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= $nombre_categories ?></div>
        <div>Catégories</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= number_format($prix_moyen, 2, ',', ' ') ?> €</div>
        <div>Prix moyen</div>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2>📋 Liste des Services (<?= $total_services ?>)</h2>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="exporterServices()" class="btn btn-primary">📄 Exporter CSV</button>
            <button onclick="imprimerListe()" class="btn btn-primary">🖨️ Imprimer</button>
        </div>
    </div>

    <!-- Filtres -->
    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
        <div>
            <input type="text" id="searchInput" placeholder="🔍 Rechercher un service..." style="width: 100%;">
        </div>
        <div>
            <select id="categorieFilter" style="width: 100%;">
                <option value="">Toutes catégories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <select id="statutFilter" style="width: 100%;">
                <option value="">Tous statuts</option>
                <option value="actif">🟢 Actifs seulement</option>
                <option value="inactif">🔴 Inactifs seulement</option>
            </select>
        </div>
        <div>
            <button onclick="appliquerFiltres()" class="btn btn-primary">Filtrer</button>
            <button onclick="reinitialiserFiltres()" class="btn btn-warning">Réinitialiser</button>
        </div>
    </div>

    <div class="table-container">
        <table id="tableServices">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Description</th>
                    <th>Prix</th>
                    <th>Durée</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($services as $service): 
                    $badge_class = match($service['categorie']) {
                        'Tenues Africaines' => 'badge-africain',
                        'Tenues Arabes' => 'badge-arabe',
                        'Vêtements Légers' => 'badge-leger',
                        'Vêtements Élégants' => 'badge-elegant',
                        'Vêtements Lourds' => 'badge-lourd',
                        'Linge de Maison' => 'badge-maison',
                        default => 'badge-info'
                    };
                ?>
                <tr class="service-row" 
                    data-categorie="<?= htmlspecialchars($service['categorie']) ?>" 
                    data-statut="<?= $service['disponible'] ? 'actif' : 'inactif' ?>"
                    data-nom="<?= htmlspecialchars(strtolower($service['nom'])) ?>">
                    <td>
                        <strong><?= htmlspecialchars($service['nom']) ?></strong>
                    </td>
                    <td>
                        <span class="badge <?= $badge_class ?>"><?= htmlspecialchars($service['categorie']) ?></span>
                    </td>
                    <td>
                        <?= $service['description'] ? htmlspecialchars(substr($service['description'], 0, 50)) . (strlen($service['description']) > 50 ? '...' : '') : '<span style="color: #95a5a6;">Aucune description</span>' ?>
                    </td>
                    <td>
                        <strong style="color: #27ae60;"><?= number_format($service['prix'], 2, ',', ' ') ?> €</strong>
                    </td>
                    <td>
                        <?= $service['duree_moyenne'] ? $service['duree_moyenne'] . ' min' : '<span style="color: #95a5a6;">-</span>' ?>
                    </td>
                    <td>
                        <span class="badge <?= $service['disponible'] ? 'badge-success' : 'badge-danger' ?>">
                            <?= $service['disponible'] ? '🟢 Actif' : '🔴 Inactif' ?>
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                            <a href="services.php?editer=<?= $service['id'] ?>" class="btn btn-primary btn-sm" title="Modifier">✏️</a>
                            <a href="services.php?toggle=<?= $service['id'] ?>" 
                               class="btn btn-warning btn-sm" 
                               title="<?= $service['disponible'] ? 'Désactiver' : 'Activer' ?>">
                                <?= $service['disponible'] ? '⏸️' : '▶️' ?>
                            </a>
                            <a href="services.php?supprimer=<?= $service['id'] ?>" 
                               class="btn btn-danger btn-sm"
                               title="Supprimer"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer le service \'<?= addslashes($service['nom']) ?>\'?')">
                                🗑️
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if(empty($services)): ?>
        <div style="text-align: center; padding: 3rem; color: #7f8c8d;">
            <div style="font-size: 4rem;">📭</div>
            <h3>Aucun service enregistré</h3>
            <p>Commencez par ajouter votre premier service en utilisant le formulaire ci-dessus.</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Filtrage des services
function appliquerFiltres() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const categorie = document.getElementById('categorieFilter').value;
    const statut = document.getElementById('statutFilter').value;
    
    const rows = document.querySelectorAll('.service-row');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const nom = row.getAttribute('data-nom');
        const rowCategorie = row.getAttribute('data-categorie');
        const rowStatut = row.getAttribute('data-statut');
        
        const matchSearch = nom.includes(searchTerm);
        const matchCategorie = !categorie || rowCategorie === categorie;
        const matchStatut = !statut || rowStatut === statut;
        
        if (matchSearch && matchCategorie && matchStatut) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Mettre à jour le titre avec le nombre de résultats
    const titre = document.querySelector('.card h2');
    if (titre) {
        titre.textContent = `📋 Liste des Services (${visibleCount} résultat${visibleCount > 1 ? 's' : ''})`;
    }
}

function reinitialiserFiltres() {
    document.getElementById('searchInput').value = '';
    document.getElementById('categorieFilter').value = '';
    document.getElementById('statutFilter').value = '';
    appliquerFiltres();
}

// Export des services
function exporterServices() {
    let csv = 'Nom;Catégorie;Description;Prix;Durée;Statut\n';
    
    document.querySelectorAll('.service-row').forEach(row => {
        if (row.style.display !== 'none') {
            const cells = row.querySelectorAll('td');
            const nom = cells[0].textContent.trim();
            const categorie = cells[1].textContent.trim();
            const description = cells[2].textContent.trim();
            const prix = cells[3].textContent.trim().replace(' €', '');
            const duree = cells[4].textContent.trim();
            const statut = cells[5].textContent.trim();
            
            csv += `"${nom}";"${categorie}";"${description}";"${prix}";"${duree}";"${statut}"\n`;
        }
    });
    
    const blob = new Blob(["\uFEFF" + csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'services_pressing_' + new Date().toISOString().split('T')[0] + '.csv');
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Impression de la liste
function imprimerListe() {
    const table = document.getElementById('tableServices').cloneNode(true);
    
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
                <title>Liste des Services - Pressing Pro</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .badge { padding: 2px 6px; border-radius: 3px; font-size: 0.8em; }
                    .badge-africain { background: #FFD700; color: #000; }
                    .badge-arabe { background: #008000; color: white; }
                    .badge-leger { background: #3498db; color: white; }
                    .badge-elegant { background: #9b59b6; color: white; }
                    .badge-lourd { background: #e74c3c; color: white; }
                    .badge-maison { background: #f39c12; color: white; }
                </style>
            </head>
            <body>
                <h1>Liste des Services - Pressing Pro</h1>
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

// Auto-sélection du texte dans le champ prix
document.getElementById('prix')?.addEventListener('focus', function() {
    this.select();
});

// Appliquer les filtres au chargement si des paramètres URL existent
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

.service-row {
    transition: background-color 0.3s ease;
}

.service-row:hover {
    background-color: #f8f9fa !important;
}

/* Styles pour les badges */
.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-success { background: #27ae60; color: white; }
.badge-danger { background: #e74c3c; color: white; }
.badge-warning { background: #f39c12; color: white; }
.badge-info { background: #3498db; color: white; }
.badge-africain { background: linear-gradient(135deg, #FFD700, #FF8C00); color: #000; }
.badge-arabe { background: linear-gradient(135deg, #008000, #006400); color: white; }
.badge-leger { background: #3498db; color: white; }
.badge-elegant { background: #9b59b6; color: white; }
.badge-lourd { background: #e74c3c; color: white; }
.badge-maison { background: #f39c12; color: white; }

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
}
</style>

<?php include 'footer.php'; ?>
