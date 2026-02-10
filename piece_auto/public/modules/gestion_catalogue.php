<?php
$page_title = "Catalogue des Pièces";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// On sélectionne les colonnes avec des alias pour être sûr de ce qu'on récupère
$query = "SELECT p.*, c.nom_categorie 
          FROM PIECES p 
          LEFT JOIN CATEGORIES c ON p.id_categorie = c.id_categorie";
$pieces = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-book-open"></i> Catalogue Public</h1>
</div>

<div class="row">
    <?php foreach ($pieces as $p): 
        // Détection dynamique du prix pour éviter l'erreur "Undefined array key"
        $prix = $p['prix_vente_unitaire'] ?? $p['prix_vente'] ?? $p['prix'] ?? 0;
        $reference = $p['reference'] ?? 'N/A';
    ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h5 class="card-title text-primary"><?= htmlspecialchars($p['nom_piece']) ?></h5>
                    <span class="badge bg-light text-dark border">#<?= $p['id_piece'] ?></span>
                </div>
                <h6 class="card-subtitle mb-3 text-muted">
                    <i class="fas fa-barcode"></i> Réf: <?= htmlspecialchars($reference) ?>
                </h6>
                <p class="card-text mb-1">
                    <i class="fas fa-tag"></i> <small>Catégorie:</small> 
                    <strong><?= htmlspecialchars($p['nom_categorie'] ?? 'Non classé') ?></strong>
                </p>
                <hr>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="h5 mb-0 text-success fw-bold"><?= number_format($prix, 2, ',', ' ') ?> €</div>
                    <div class="text-end">
                        <small class="d-block text-muted">Disponibilité:</small>
                        <span class="badge <?= ($p['stock_actuel'] > 0) ? 'bg-success' : 'bg-danger' ?>">
                            <?= $p['stock_actuel'] ?> en stock
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include '../../includes/footer.php'; ?>
