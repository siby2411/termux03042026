<?php
require_once dirname(__DIR__,2).'/core/Auth.php';
require_once dirname(__DIR__,2).'/core/Database.php';
Auth::check();
$cats = Database::query("SELECT * FROM categories_medicaments");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>POS Catégories — Omega Pharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { --sidebar-w: 260px; }
        .main-wrapper { margin-left: var(--sidebar-w); display: flex; height: 100vh; }
        .cat-list { width: 200px; background: #222; color: #fff; overflow-y: auto; }
        .cat-item { padding: 15px; border-bottom: 1px solid #444; cursor: pointer; font-size: 0.8rem; text-transform: uppercase; }
        .cat-item:hover { background: #00713e; }
        .prod-grid { flex: 1; padding: 20px; overflow-y: auto; background: #fff; }
        .cart-panel { width: 350px; background: #f8f9fa; border-left: 1px solid #ddd; }
    </style>
</head>
<body>
<?php include dirname(__DIR__,2).'/templates/partials/sidebar.php'; ?>
<div class="main-wrapper">
    <div class="cat-list">
        <div class="p-3 fw-bold border-bottom">RAYONS</div>
        <?php foreach($cats as $c): ?>
            <div class="cat-item" onclick="loadByCat(<?= $c['id'] ?>)"><?= $c['libelle'] ?></div>
        <?php endforeach; ?>
    </div>
    <div class="prod-grid" id="productGrid">
        <h3 class="text-muted">Sélectionnez une catégorie</h3>
    </div>
    <div class="cart-panel p-3">
        <h5>Panier</h5>
        <div id="cartContent"></div>
        <hr>
        <button class="btn btn-primary w-100 py-3 mt-auto">ENCAISSER</button>
    </div>
</div>
<script>
async function loadByCat(catId) {
    const r = await fetch(`../medicaments/api.php?action=by_cat&cat_id=${catId}`);
    const data = await r.json();
    let html = '<div class="row g-3">';
    data.forEach(m => {
        html += `<div class="col-md-4">
            <div class="card h-100 shadow-sm border-0" onclick="addToCart(${m.id}, '${m.denomination}', ${m.prix_vente})">
                <div class="card-body text-center">
                    <div class="fw-bold">${m.denomination}</div>
                    <div class="text-success fw-bold mt-2">${m.prix_vente} F</div>
                </div>
            </div>
        </div>`;
    });
    document.getElementById('productGrid').innerHTML = html + '</div>';
}
</script>
</body>
</html>
