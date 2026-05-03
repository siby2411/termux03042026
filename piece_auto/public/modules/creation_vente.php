<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

$database = new Database();
$db = $database->getConnection();

$clients = $db->query("SELECT id_client, nom, prenom FROM CLIENTS ORDER BY nom ASC")->fetchAll(PDO::FETCH_ASSOC);
$pieces = $db->query("SELECT id_piece, reference, nom_piece, prix_vente, stock_actuel, IFNULL(categorie, 'Divers') as categorie FROM PIECES WHERE stock_actuel > 0")->fetchAll(PDO::FETCH_ASSOC);
$categories = array_unique(array_column($pieces, 'categorie'));
sort($categories);

$page_title = "Vente par Catégorie";
include '../../includes/header.php';
?>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3">
                <input type="text" id="liveSearch" class="form-control mb-2" placeholder="🔍 Rechercher une pièce...">
                <div class="d-flex flex-wrap gap-1" id="catBar">
                    <button class="btn btn-xs btn-primary active" onclick="filterCat('Tous', this)">Tous</button>
                    <?php foreach($categories as $c): ?>
                        <button class="btn btn-xs btn-outline-secondary" onclick="filterCat('<?= addslashes($c) ?>', this)"><?= $c ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="list-group list-group-flush overflow-auto" id="itemList" style="max-height: 65vh;">
                </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form action="traitement_vente.php" method="POST">
                    <select name="id_client" class="form-select mb-3 fw-bold border-primary" required>
                        <option value="">👤 SÉLECTIONNER LE CLIENT</option>
                        <?php foreach($clients as $cl): ?>
                            <option value="<?= $cl['id_client'] ?>"><?= strtoupper($cl['nom'])." ".$cl['prenom'] ?></option>
                        <?php endforeach; ?>
                    </select>

                    <table class="table align-middle">
                        <thead class="table-light small">
                            <tr><th>Article</th><th width="80">Qté</th><th class="text-end">Total</th><th></th></tr>
                        </thead>
                        <tbody id="cartBody"></tbody>
                    </table>

                    <div class="p-3 bg-light rounded">
                        <div class="d-flex justify-content-between h3">
                            <span>TOTAL :</span>
                            <span class="text-primary fw-bold"><span id="gTotal">0</span> F</span>
                        </div>
                        <input type="hidden" name="total_final" id="hTotal" value="0">
                        <button type="submit" class="btn btn-success btn-lg w-100 mt-2">VALIDER LA VENTE</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const items = <?= json_encode($pieces) ?>;
let selCat = 'Tous';

function render(search = '', cat = 'Tous') {
    const list = document.getElementById('itemList');
    list.innerHTML = '';
    items.filter(p => (cat === 'Tous' || p.categorie === cat) && 
        (p.nom_piece.toLowerCase().includes(search.toLowerCase()) || p.reference.toLowerCase().includes(search.toLowerCase())))
    .forEach(p => {
        const btn = document.createElement('button');
        btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3';
        btn.innerHTML = `<div><div class="fw-bold">${p.nom_piece}</div><small class="text-muted">${p.categorie} | Réf: ${p.reference}</small></div>
                         <div class="text-end"><div class="fw-bold">${parseFloat(p.prix_vente).toLocaleString()} F</div>
                         <span class="badge bg-info p-1" style="font-size:0.6rem">Stock: ${p.stock_actuel}</span></div>`;
        btn.onclick = () => {
            if(document.getElementById('r-'+p.id_piece)) return;
            const tr = document.createElement('tr');
            tr.id = 'r-'+p.id_piece;
            tr.innerHTML = `<td><small class="fw-bold">${p.nom_piece}</small><input type="hidden" name="id_piece[]" value="${p.id_piece}"><input type="hidden" name="prix[]" value="${p.prix_vente}"></td>
                            <td><input type="number" name="qte[]" class="form-control form-control-sm" value="1" min="1" max="${p.stock_actuel}" onchange="upd()"></td>
                            <td class="text-end fw-bold"><span class="r-tot">${p.prix_vente}</span> F</td>
                            <td class="text-end"><button class="btn btn-sm text-danger" onclick="this.closest('tr').remove();upd();">×</button></td>`;
            document.getElementById('cartBody').appendChild(tr);
            upd();
        };
        list.appendChild(btn);
    });
}

function filterCat(c, b) {
    selCat = c;
    document.querySelectorAll('#catBar .btn').forEach(btn => btn.className = 'btn btn-xs btn-outline-secondary');
    b.className = 'btn btn-xs btn-primary active';
    render(document.getElementById('liveSearch').value, selCat);
}

function upd() {
    let t = 0;
    document.querySelectorAll('#cartBody tr').forEach(r => {
        const q = r.querySelector('input[name="qte[]"]').value;
        const p = r.querySelector('input[name="prix[]"]').value;
        r.querySelector('.r-tot').innerText = (q*p).toLocaleString();
        t += (q*p);
    });
    document.getElementById('gTotal').innerText = t.toLocaleString();
    document.getElementById('hTotal').value = t;
}

document.getElementById('liveSearch').addEventListener('input', e => render(e.target.value, selCat));
render();
</script>

<style>
.btn-xs { padding: 0.2rem 0.5rem; font-size: 0.7rem; border-radius: 15px; }
.list-group-item:hover { background-color: #e9ecef; }
</style>

<?php include '../../includes/header.php'; ?>
