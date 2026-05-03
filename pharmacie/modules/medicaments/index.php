<?php
require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Database.php';
Auth::check();

$page_title = "Stock Médicaments";
$active_menu = "medicaments";

// --- DONNÉES ---
$categories = Database::query("SELECT id, libelle FROM categories_medicaments ORDER BY libelle ASC");
$search = $_GET['search'] ?? '';

$sql = "SELECT m.*, c.libelle as categorie_nom 
        FROM medicaments m 
        LEFT JOIN categories_medicaments c ON m.categorie_id = c.id 
        WHERE m.actif = 1";
$params = [];

if ($search) {
    $sql .= " AND (m.denomination LIKE ? OR m.code_barre LIKE ?)";
    $s = "%$search%";
    $params = [$s, $s];
}
$sql .= " ORDER BY m.denomination ASC";
$medicaments = Database::query($sql, $params);

include dirname(__DIR__, 2) . '/templates/partials/layout_head.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Gestion du Stock</h3>
        <p class="text-muted small">Catalogue des produits PharmaSen</p>
    </div>
    <button class="btn-omega" data-bs-toggle="modal" data-bs-target="#modalMed">
        <i class="bi bi-plus-lg"></i> Nouveau Médicament
    </button>
</div>

<div class="omega-card p-3 mb-4 shadow-sm">
    <form class="row g-2">
        <div class="col-md-10">
            <input type="text" name="search" class="form-control" placeholder="Rechercher par nom ou code barre..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-dark w-100">Filtrer</button>
        </div>
    </form>
</div>

<div class="omega-card shadow-sm overflow-hidden">
    <div class="omega-card-head blue-head">LISTE DES PRODUITS EN STOCK</div>
    <div class="bg-white">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-3">Dénomination</th>
                    <th>Forme / Dosage</th>
                    <th>Prix Vente</th>
                    <th>Stock</th>
                    <th class="text-end pe-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($medicaments as $m): ?>
                <tr>
                    <td class="ps-3">
                        <div class="fw-bold"><?= htmlspecialchars($m['denomination']) ?></div>
                        <small class="text-muted"><?= $m['code_barre'] ?: 'Pas de code' ?></small>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border"><?= ucfirst($m['forme']) ?></span>
                        <div class="small text-muted"><?= $m['dosage'] ?></div>
                    </td>
                    <td class="fw-bold text-primary"><?= number_format($m['prix_vente_ttc'], 0, ',', ' ') ?> F</td>
                    <td>
                        <?php $color = ($m['stock_actuel'] <= $m['stock_min']) ? 'danger' : 'success'; ?>
                        <span class="badge bg-<?= $color ?> rounded-pill px-3"><?= $m['stock_actuel'] ?></span>
                    </td>
                    <td class="text-end pe-3">
                        <button class="btn btn-sm btn-light border"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalMed" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold">Nouveau Médicament</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="formMed">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="fw-bold small">Dénomination *</label>
                            <input type="text" name="denomination" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold small">Code Barre</label>
                            <input type="text" name="code_barre" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold small">Catégorie</label>
                            <select name="categorie_id" class="form-select">
                                <?php foreach($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= $c['libelle'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold small">Forme</label>
                            <select name="forme" class="form-select">
                                <option value="comprimé">Comprimé</option>
                                <option value="sirop">Sirop</option>
                                <option value="gélule">Gélule</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold small">Dosage</label>
                            <input type="text" name="dosage" class="form-control" placeholder="ex: 500mg">
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold small">Prix Vente TTC *</label>
                            <input type="number" name="prix_vente_ttc" class="form-control border-success" required>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold small">Stock Initial</label>
                            <input type="number" name="stock_actuel" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold small">Seuil Alerte</label>
                            <input type="number" name="stock_min" class="form-control" value="5">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="submit" class="btn-omega">Enregistrer le produit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include dirname(__DIR__, 2) . '/templates/partials/layout_footer.php'; ?>
