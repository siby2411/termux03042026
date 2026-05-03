<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$database = new Database();
$db = $database->getConnection();

$fournisseurs = $db->query("SELECT * FROM FOURNISSEURS")->fetchAll(PDO::FETCH_ASSOC);
$pieces = $db->query("SELECT id_piece, reference, nom_piece, stock_actuel, cump FROM PIECES")->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Nouvel Achat (Réappro)";
include '../../includes/header.php';
?>

<div class="row">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-primary text-white">Sélection Articles</div>
            <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                <input type="text" id="searchP" class="form-control border-0 p-3" placeholder="Filtrer les pièces...">
                <div class="list-group list-group-flush" id="pList">
                    <?php foreach($pieces as $p): ?>
                    <button class="list-group-item list-group-item-action" onclick='addItem(<?= json_encode($p) ?>)'>
                        <div class="fw-bold"><?= $p['nom_piece'] ?></div>
                        <small class="text-muted">Réf: <?= $p['reference'] ?> | Stock: <?= $p['stock_actuel'] ?></small>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <form action="traitement_achat.php" method="POST" class="card shadow-sm border-0">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Fournisseur</label>
                    <select name="id_fournisseur" class="form-select" required>
                        <?php foreach($fournisseurs as $f): ?>
                            <option value="<?= $f['id_fournisseur'] ?>"><?= $f['nom'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Article</th>
                            <th width="120">Quantité</th>
                            <th width="150">P.Achat Unit (F)</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody id="achatBody"></tbody>
                </table>
                
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-success btn-lg">Valider l'entrée en stock</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function addItem(p) {
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><b>${p.nom_piece}</b><input type="hidden" name="id_piece[]" value="${p.id_piece}"></td>
        <td><input type="number" name="qte[]" class="form-control" value="1" min="1"></td>
        <td><input type="number" name="prix_achat[]" class="form-control" value="${p.cump}"></td>
        <td class="text-end fw-bold">---</td>
    `;
    document.getElementById('achatBody').appendChild(row);
}
</script>
<?php include '../../includes/footer.php'; ?>
