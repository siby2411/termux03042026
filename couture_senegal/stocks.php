<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['designation'])) {
    $stmt = $pdo->prepare("INSERT INTO stocks (designation, type_tissu, couleur, quantite_initiale, quantite_restante, unite, prix_achat_unitaire, date_achat) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([$_POST['designation'], $_POST['type_tissu'], $_POST['couleur'], $_POST['quantite'], $_POST['quantite'], $_POST['unite'], $_POST['prix'], $_POST['date_achat']]);
    header("Location: stocks.php"); exit;
}

$tissus = $pdo->query("SELECT * FROM stocks ORDER BY quantite_restante ASC")->fetchAll();
require_once __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold">Stocks Tissus</h4>
    <button class="btn btn-dark border-gold" data-bs-toggle="modal" data-bs-target="#modalStock"><i class="bi bi-plus-lg text-gold"></i> Nouveau Tissu</button>
</div>

<div class="row g-3">
    <?php foreach($tissus as $t): ?>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3">
            <h6 class="fw-bold"><?= $t['designation'] ?> (<?= $t['couleur'] ?>)</h6>
            <h3 class="text-gold"><?= number_format($t['quantite_restante'], 1) ?> <small class="fs-6 text-muted">m</small></h3>
            <div class="input-group input-group-sm mt-3">
                <input type="number" id="out_<?= $t['id'] ?>" step="0.5" class="form-control" placeholder="Sortie (m)">
                <button onclick="retirer(<?= $t['id'] ?>)" class="btn btn-dark">Retirer</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
function retirer(id) {
    const qty = document.getElementById('out_' + id).value;
    if(!qty) return;
    fetch('ajax_stock.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `stock_id=${id}&quantite_sortie=${qty}`
    }).then(() => location.reload());
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
