<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

$pdo = getPDO();
$action = $_GET['action'] ?? 'list';
$search = trim($_GET['q'] ?? '');
$catFilter = (int)($_GET['cat'] ?? 0);

// Définition du chemin des uploads
define('UPLOAD_URL', 'uploads/produits/');

// Créer le dossier d'upload si nécessaire
if (!is_dir('../uploads/produits')) {
    mkdir('../uploads/produits', 0777, true);
}

// Récupérer les catégories
$cats = $pdo->query("SELECT id, nom, icone, couleur FROM categories ORDER BY nom")->fetchAll();

// ==============================================
// GESTION DES ACTIONS
// ==============================================

// Ajouter / Modifier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $id = (int)($_POST['id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $categorie_id = (int)($_POST['categorie_id'] ?? 0) ?: null;
    $description = trim($_POST['description'] ?? '');
    $prix_vente = (float)($_POST['prix_vente'] ?? 0);
    $prix_achat = (float)($_POST['prix_achat'] ?? 0);
    $stock_actuel = (float)($_POST['stock_actuel'] ?? 0);
    $stock_min = (float)($_POST['stock_min'] ?? 1);
    $unite = trim($_POST['unite'] ?? 'kg');
    $actif = isset($_POST['actif']) ? 1 : 0;
    
    // Gestion de l'image
    $image = '';
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (in_array($ext, $allowed) && $_FILES['image']['size'] < 5 * 1024 * 1024) {
            $image = time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], '../uploads/produits/' . $image);
            
            // Supprimer l'ancienne image si modification
            if ($id > 0) {
                $old = $pdo->prepare("SELECT image FROM produits WHERE id = ?")->execute([$id])->fetch();
                if ($old && $old['image'] && file_exists('../uploads/produits/' . $old['image'])) {
                    unlink('../uploads/produits/' . $old['image']);
                }
            }
        }
    } elseif ($id > 0) {
        $stmt = $pdo->prepare("SELECT image FROM produits WHERE id = ?");
        $stmt->execute([$id]);
        $image = $stmt->fetchColumn();
    }
    
    if (!$nom || $prix_vente <= 0) {
        flash("Nom et prix de vente obligatoires", 'danger');
    } else {
        try {
            if ($id > 0) {
                $stmt = $pdo->prepare("UPDATE produits SET nom=?, categorie_id=?, description=?, prix_vente=?, prix_achat=?, stock_actuel=?, stock_min=?, unite=?, image=?, actif=? WHERE id=?");
                $stmt->execute([$nom, $categorie_id, $description, $prix_vente, $prix_achat, $stock_actuel, $stock_min, $unite, $image, $actif, $id]);
                flash("Produit modifié avec succès", 'success');
            } else {
                $stmt = $pdo->prepare("INSERT INTO produits (nom, categorie_id, description, prix_vente, prix_achat, stock_actuel, stock_min, unite, image, actif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $categorie_id, $description, $prix_vente, $prix_achat, $stock_actuel, $stock_min, $unite, $image, $actif]);
                flash("Produit ajouté avec succès", 'success');
            }
            header('Location: produits.php');
            exit;
        } catch (PDOException $e) {
            flash("Erreur: " . $e->getMessage(), 'danger');
        }
    }
}

// Supprimer
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT image FROM produits WHERE id = ?");
        $stmt->execute([$id]);
        $img = $stmt->fetchColumn();
        if ($img && file_exists('../uploads/produits/' . $img)) {
            unlink('../uploads/produits/' . $img);
        }
        $pdo->prepare("DELETE FROM produits WHERE id = ?")->execute([$id]);
        flash("Produit supprimé", 'success');
    } catch (PDOException $e) {
        flash("Erreur: " . $e->getMessage(), 'danger');
    }
    header('Location: produits.php');
    exit;
}

// ==============================================
// RÉCUPÉRATION DES DONNÉES
// ==============================================

if ($action === 'edit' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
    $stmt->execute([$id]);
    $prod = $stmt->fetch();
    if (!$prod) {
        flash("Produit introuvable", 'danger');
        header('Location: produits.php');
        exit;
    }
}

// Liste des produits
$sql = "SELECT p.*, c.nom as cat_nom, c.icone as cat_icone, c.couleur as cat_color 
        FROM produits p 
        LEFT JOIN categories c ON p.categorie_id = c.id 
        WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND p.nom LIKE ?";
    $params[] = "%$search%";
}
if ($catFilter) {
    $sql .= " AND p.categorie_id = ?";
    $params[] = $catFilter;
}
$sql .= " ORDER BY p.nom ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll();

require_once 'header.php';
?>

<div class="page-header">
    <h1><i class="fas fa-boxes"></i> Gestion des produits</h1>
    <p>Gérez votre catalogue de produits charcuterie</p>
</div>

<?php if ($flash = getFlash()): ?>
<div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
    <?= $flash['message'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($action === 'add' || $action === 'edit'): ?>
<!-- FORMULAIRE D'AJOUT / MODIFICATION -->
<div class="card-omega">
    <div class="card-head">
        <h4><i class="fas fa-<?= $id ?? 0 ? 'edit' : 'plus' ?>"></i> <?= $id ?? 0 ? 'Modifier' : 'Ajouter' ?> un produit</h4>
        <a href="produits.php" class="btn-omega btn-omega-outline">← Retour</a>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="form-omega">
            <input type="hidden" name="id" value="<?= $id ?? 0 ?>">
            <input type="hidden" name="save_product" value="1">
            
            <div class="row">
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nom du produit *</label>
                            <input type="text" name="nom" class="form-control" required 
                                   value="<?= htmlspecialchars($prod['nom'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Catégorie</label>
                            <select name="categorie_id" class="form-select">
                                <option value="">-- Choisir une catégorie --</option>
                                <?php foreach ($cats as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= (($prod['categorie_id'] ?? 0) == $c['id']) ? 'selected' : '' ?>>
                                    <?= !empty($c['icone']) ? $c['icone'] . ' ' : '' ?><?= htmlspecialchars($c['nom']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($prod['description'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Prix de vente (FCFA) *</label>
                            <input type="number" name="prix_vente" class="form-control" step="1" min="0" required 
                                   value="<?= $prod['prix_vente'] ?? '' ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Prix d'achat (FCFA)</label>
                            <input type="number" name="prix_achat" class="form-control" step="1" min="0" 
                                   value="<?= $prod['prix_achat'] ?? '' ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Stock actuel</label>
                            <input type="number" name="stock_actuel" class="form-control" step="0.001" min="0" 
                                   value="<?= $prod['stock_actuel'] ?? '0' ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Stock minimum</label>
                            <input type="number" name="stock_min" class="form-control" step="0.001" min="0" 
                                   value="<?= $prod['stock_min'] ?? '1' ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Unité</label>
                            <select name="unite" class="form-select">
                                <?php foreach (['kg', 'g', 'litre', 'pièce', 'boîte', 'pot', 'plaquette', 'rouleau', 'pack', 'bocal', 'bouteille'] as $u): ?>
                                <option <?= (($prod['unite'] ?? 'kg') == $u) ? 'selected' : '' ?>><?= $u ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="actif" id="actif" <?= (($prod['actif'] ?? 1) ? 'checked' : '') ?>>
                                <label class="form-check-label" for="actif">Produit actif</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Image du produit</label>
                    <div class="image-upload-area" onclick="document.getElementById('imgInput').click()">
                        <?php if (!empty($prod['image'])): ?>
                            <img src="../uploads/produits/<?= htmlspecialchars($prod['image']) ?>" id="imgPreview" class="img-preview">
                        <?php else: ?>
                            <img id="imgPreview" class="img-preview" style="display: none;">
                            <div id="imgPlaceholder" class="img-placeholder-large">📷</div>
                        <?php endif; ?>
                        <p class="upload-hint">Cliquez pour choisir une image (JPG, PNG, WEBP - max 5Mo)</p>
                    </div>
                    <input type="file" id="imgInput" name="image" accept="image/*" style="display: none" onchange="previewImage(this)">
                    
                    <div class="marge-info mt-3">
                        <strong>💡 Marge bénéficiaire :</strong><br>
                        <span id="margeInfo">Renseignez les prix pour calculer</span>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-omega btn-omega-primary">
                    <i class="fas fa-save"></i> <?= $id ?? 0 ? 'Mettre à jour' : 'Enregistrer' ?>
                </button>
                <a href="produits.php" class="btn-omega btn-omega-outline">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('imgPreview');
            const placeholder = document.getElementById('imgPlaceholder');
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function calculerMarge() {
    const prixVente = parseFloat(document.querySelector('[name="prix_vente"]').value) || 0;
    const prixAchat = parseFloat(document.querySelector('[name="prix_achat"]').value) || 0;
    const margeSpan = document.getElementById('margeInfo');
    
    if (prixVente > 0 && prixAchat > 0) {
        const marge = prixVente - prixAchat;
        const pourcentage = ((marge / prixAchat) * 100).toFixed(1);
        margeSpan.innerHTML = `Marge: <strong style="color: ${marge >= 0 ? '#27ae60' : '#e74c3c'}">${marge.toLocaleString()} FCFA (${pourcentage}%)</strong>`;
    } else if (prixVente > 0) {
        margeSpan.innerHTML = `Marge: en attente du prix d'achat`;
    } else {
        margeSpan.innerHTML = `Renseignez les prix pour calculer`;
    }
}

document.querySelector('[name="prix_vente"]').addEventListener('input', calculerMarge);
document.querySelector('[name="prix_achat"]').addEventListener('input', calculerMarge);
calculerMarge();
</script>

<?php else: ?>
<!-- LISTE DES PRODUITS -->
<div class="card-omega">
    <div class="card-head">
        <h4><i class="fas fa-list"></i> Liste des produits</h4>
        <a href="produits.php?action=add" class="btn-omega btn-omega-primary">
            <i class="fas fa-plus"></i> Nouveau produit
        </a>
    </div>
    
    <div class="card-body">
        <form method="GET" class="filter-form">
            <input type="text" name="q" class="form-control" placeholder="🔍 Rechercher un produit..." value="<?= htmlspecialchars($search) ?>">
            <select name="cat" class="form-select">
                <option value="">Toutes catégories</option>
                <?php foreach ($cats as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $catFilter == $c['id'] ? 'selected' : '' ?>>
                    <?= !empty($c['icone']) ? $c['icone'] . ' ' : '' ?><?= htmlspecialchars($c['nom']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-omega btn-omega-gold">Filtrer</button>
            <a href="produits.php" class="btn-omega btn-omega-outline">Réinitialiser</a>
        </form>
    </div>
    
    <div class="table-responsive">
        <table class="table-omega">
            <thead>
                <tr><th>Image</th><th>Produit</th><th>Catégorie</th><th>Prix vente</th><th>Prix achat</th><th>Marge</th><th>Stock</th><th>Unité</th><th>Statut</th><th>Actions</th> </thead>
            <tbody>
                <?php if (empty($produits)): ?>
                <tr><td colspan="10" class="text-center">Aucun produit trouvé</td></tr>
                <?php else: foreach ($produits as $p):
                    $marge = $p['prix_vente'] - $p['prix_achat'];
                    $margePct = $p['prix_achat'] > 0 ? ($marge / $p['prix_achat']) * 100 : 0;
                    $stockClass = $p['stock_actuel'] <= 0 ? 'text-danger' : ($p['stock_actuel'] <= $p['stock_min'] ? 'text-warning' : 'text-success');
                ?>
                <tr>
                    <td>
                        <?php if ($p['image']): ?>
                            <img src="../uploads/produits/<?= htmlspecialchars($p['image']) ?>" class="img-thumb" onerror="this.outerHTML='<div class=img-placeholder>🥩</div>'">
                        <?php else: ?>
                            <div class="img-placeholder">🥩</div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?= htmlspecialchars($p['nom']) ?></strong><br><small><?= htmlspecialchars(mb_substr($p['description'] ?? '', 0, 50)) ?>...</small></td>
                    <td>
                        <?php if ($p['cat_nom']): ?>
                            <span style="color: <?= htmlspecialchars($p['cat_color'] ?? '#888') ?>">
                                <?= !empty($p['cat_icone']) ? $p['cat_icone'] . ' ' : '' ?><?= htmlspecialchars($p['cat_nom']) ?>
                            </span>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td class="text-orange"><strong><?= number_format($p['prix_vente'], 0, ',', ' ') ?></strong></td>
                    <td><?= number_format($p['prix_achat'], 0, ',', ' ') ?></td>
                    <td class="<?= $marge >= 0 ? 'text-success' : 'text-danger' ?>">
                        <?= number_format($marge, 0, ',', ' ') ?><br><small>(<?= number_format($margePct, 1) ?>%)</small>
                    </td>
                    <td class="<?= $stockClass ?>">
                        <?= number_format($p['stock_actuel'], 2) ?>
                        <?php if ($p['stock_actuel'] <= $p['stock_min'] && $p['stock_actuel'] > 0): ?>
                            <br><small class="text-warning">⚠️ Alerte</small>
                        <?php elseif ($p['stock_actuel'] <= 0): ?>
                            <br><small class="text-danger">Rupture</small>
                        <?php endif; ?>
                    </td>
                    <td><small><?= htmlspecialchars($p['unite']) ?></small></td>
                    <td>
                        <?php if ($p['actif']): ?>
                            <span class="badge-stat b-success">✓ Actif</span>
                        <?php else: ?>
                            <span class="badge-stat b-muted">✗ Inactif</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="produits.php?action=edit&id=<?= $p['id'] ?>" class="btn-omega btn-omega-gold btn-sm" title="Modifier">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="produits.php?action=delete&id=<?= $p['id'] ?>" class="btn-omega btn-omega-danger btn-sm btn-delete" title="Supprimer" onclick="return confirm('Supprimer ce produit ?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<style>
.text-orange { color: #e67e22; font-weight: 600; }
.img-thumb { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
.img-placeholder { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: #f0f0f0; border-radius: 8px; font-size: 1.5rem; }
.img-preview { max-width: 100%; max-height: 200px; border-radius: 8px; margin-bottom: 10px; }
.img-placeholder-large { font-size: 4rem; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px; }
.image-upload-area { border: 2px dashed #ddd; border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all 0.3s; }
.image-upload-area:hover { border-color: #e67e22; background: rgba(230, 126, 34, 0.05); }
.upload-hint { font-size: 0.75rem; color: #7f8c8d; margin-top: 8px; }
.marge-info { background: rgba(230, 126, 34, 0.1); border: 1px solid rgba(230, 126, 34, 0.2); border-radius: 10px; padding: 12px; font-size: 0.85rem; }
.filter-form { display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; }
.filter-form .form-control, .filter-form .form-select { width: auto; min-width: 200px; }
@media (max-width: 768px) {
    .filter-form .form-control, .filter-form .form-select { width: 100%; }
}
</style>

<?php require_once 'footer.php'; ?>
