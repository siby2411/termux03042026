<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

$database = new Database();
$db = $database->getConnection();

// Récupération des clients et des pièces pour les listes déroulantes
$clients = $db->query("SELECT id_client, nom, prenom FROM CLIENTS ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$pieces = $db->query("SELECT id_piece, reference, nom_piece, stock_actuel, prix_vente FROM PIECES WHERE stock_actuel > 0 ORDER BY nom_piece ASC")->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Nouvelle Vente - OMEGA TECH";
include '../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white fw-bold">
                    <i class="fas fa-search me-2"></i> Catalogue Articles
                </div>
                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                    <div class="p-3 bg-light">
                        <input type="text" id="searchBar" class="form-control" placeholder="Rechercher une pièce...">
                    </div>
                    <div class="list-group list-group-flush" id="itemList">
                        <?php foreach($pieces as $p): ?>
                        <button type="button" class="list-group-item list-group-item-action item-row" 
                                onclick="addToCart(<?= htmlspecialchars(json_encode($p)) ?>)">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold"><?= $p['nom_piece'] ?></div>
                                    <small class="text-muted">Réf: <?= $p['reference'] ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success"><?= number_format($p['prix_vente'], 0, ',', ' ') ?> F</span><br>
                                    <small class="text-info">Stock: <?= $p['stock_actuel'] ?></small>
                                </div>
                            </div>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <form action="traitement_vente.php" method="POST" id="venteForm">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <span class="fw-bold"><i class="fas fa-shopping-cart me-2"></i> Panier de Vente</span>
                        <input type="text" name="vin_vehicule" class="form-control form-control-sm w-25" placeholder="VIN (Châssis)">
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Sélectionner le Client</label>
                            <select name="id_client" class="form-select form-select-lg" required>
                                <option value="">--- Choisir un client ---</option>
                                <?php foreach($clients as $c): ?>
                                    <option value="<?= $c['id_client'] ?>"><?= strtoupper($c['nom']) ?> <?= $c['prenom'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <table class="table align-middle" id="cartTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Désignation</th>
                                    <th width="120">Prix Unit.</th>
                                    <th width="100">Qté</th>
                                    <th width="120" class="text-end">Total</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody>
                                </tbody>
                            <tfoot class="table-group-divider">
                                <tr>
                                    <th colspan="3" class="text-end h4">NET À PAYER (FCFA) :</th>
                                    <th class="text-end h4 text-primary" id="grandTotal">0</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5 shadow">
                                <i class="fas fa-check-circle me-2"></i> VALIDER LA VENTE
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let cart = [];

function addToCart(piece) {
    const existing = cart.find(item => item.id_piece === piece.id_piece);
    if (existing) {
        if (existing.qty < piece.stock_actuel) existing.qty++;
    } else {
        cart.push({ ...piece, qty: 1 });
    }
    renderCart();
}

function updateQty(id, delta) {
    const item = cart.find(i => i.id_piece === id);
    if (item) {
        item.qty = Math.min(item.stock_actuel, Math.max(1, item.qty + delta));
    }
    renderCart();
}

function removeFromCart(id) {
    cart = cart.filter(i => i.id_piece !== id);
    renderCart();
}

function renderCart() {
    const tbody = document.querySelector('#cartTable tbody');
    tbody.innerHTML = '';
    let totalVente = 0;

    cart.forEach(item => {
        const totalLine = item.prix_vente * item.qty;
        totalVente += totalLine;
        tbody.innerHTML += `
            <tr>
                <td>
                    <div class="fw-bold">${item.nom_piece}</div>
                    <small class="text-muted">${item.reference}</small>
                    <input type="hidden" name="id_piece[]" value="${item.id_piece}">
                </td>
                <td>${item.prix_vente.toLocaleString()} F</td>
                <td>
                    <div class="input-group input-group-sm">
                        <button type="button" class="btn btn-outline-secondary" onclick="updateQty(${item.id_piece}, -1)">-</button>
                        <input type="text" name="quantite[]" class="form-control text-center" value="${item.qty}" readonly>
                        <button type="button" class="btn btn-outline-secondary" onclick="updateQty(${item.id_piece}, 1)">+</button>
                    </div>
                </td>
                <td class="text-end fw-bold">${totalLine.toLocaleString()} F</td>
                <td><button type="button" class="btn btn-link text-danger p-0" onclick="removeFromCart(${item.id_piece})"><i class="fas fa-times"></i></button></td>
            </tr>
        `;
    });

    document.getElementById('grandTotal').innerText = totalVente.toLocaleString();
}

// Filtre de recherche simple
document.getElementById('searchBar').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    document.querySelectorAll('.item-row').forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
});
</script>

<?php include '../../includes/footer.php'; ?>
