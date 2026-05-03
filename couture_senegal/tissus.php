<?php
// tissus.php
require_once 'config.php';
include 'includes/header.php';

// Simulation des données de stock
$stocks = [
    ['id' => 1, 'nom' => 'Gagnila (Bleu Roi)', 'categorie' => 'Tissu', 'quantite' => 15.5, 'unite' => 'Mètres', 'alerte' => 5],
    ['id' => 2, 'nom' => 'Bazin Riche (Blanc)', 'categorie' => 'Tissu', 'quantite' => 3.0, 'unite' => 'Mètres', 'alerte' => 10],
    ['id' => 3, 'nom' => 'Fil à coudre (Noir)', 'categorie' => 'Fourniture', 'quantite' => 24, 'unite' => 'Bobines', 'alerte' => 6],
    ['id' => 4, 'nom' => 'Fermetures éclair 50cm', 'categorie' => 'Fourniture', 'quantite' => 45, 'unite' => 'Pièces', 'alerte' => 10],
    ['id' => 5, 'nom' => 'Wax Hollandais (Motifs Or)', 'categorie' => 'Tissu', 'quantite' => 12.0, 'unite' => 'Mètres', 'alerte' => 4],
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Inventaire & Tissus</h4>
        <p class="text-muted small mb-0">Gestion des matières premières et fournitures</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAjuster">
            <i class="bi bi-plus-slash-minus me-1"></i> Ajuster Stock
        </button>
        <button class="btn btn-dark shadow-sm" data-bs-toggle="modal" data-bs-target="#modalNouveauTissu">
            <i class="bi bi-box-seam me-1"></i> Ajouter Article
        </button>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="small opacity-75">Total Tissus</div>
                        <h3 class="fw-bold">30.5 <small>m</small></h3>
                    </div>
                    <i class="bi bi-layers fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="small opacity-75 text-uppercase fw-bold">Alertes Stock Bas</div>
                        <h3 class="fw-bold">1 Article</h3>
                    </div>
                    <i class="bi bi-exclamation-octagon fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body text-center py-3">
                <a href="#" class="text-decoration-none text-dark small fw-bold">
                    <i class="bi bi-file-earmark-pdf me-1 text-danger"></i> Télécharger l'inventaire complet
                </a>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small fw-bold">
                        <tr>
                            <th class="ps-4">Article</th>
                            <th>Catégorie</th>
                            <th>Quantité en Stock</th>
                            <th>Statut</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($stocks as $s): 
                            $isLow = $s['quantite'] <= $s['alerte'];
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold"><?= $s['nom'] ?></div>
                                <div class="text-muted small">REF-INV-00<?= $s['id'] ?></div>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2">
                                    <?= $s['categorie'] ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold <?= $isLow ? 'text-danger' : 'text-dark' ?>">
                                    <?= $s['quantite'] ?> <?= $s['unite'] ?>
                                </div>
                            </td>
                            <td>
                                <?php if($isLow): ?>
                                    <span class="badge bg-danger animate-pulse"><i class="bi bi-arrow-down-circle me-1"></i> Stock Faible</span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success px-2">Correct</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-white border text-primary" title="Modifier"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-white border text-info" title="Historique"><i class="bi bi-clock-history"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNouveauTissu" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Nouvel Article en Stock</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form>
            <div class="mb-3">
                <label class="form-label small fw-bold">Nom du Tissu ou de l'Article</label>
                <input type="text" class="form-control" placeholder="Ex: Bazin, Fil, Boutons...">
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Quantité Initiale</label>
                    <input type="number" step="0.1" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label small fw-bold">Unité</label>
                    <select class="form-select">
                        <option>Mètres</option>
                        <option>Bobines</option>
                        <option>Pièces</option>
                        <option>Paquets</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-bold text-danger">Seuil d'Alerte (Stock Bas)</label>
                <input type="number" class="form-control border-danger" placeholder="Alerter quand il reste moins de...">
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-success">Enregistrer dans le Stock</button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
