<?php require_once '../../includes/header.php'; ?>
<div class="card shadow border-0">
    <div class="card-header bg-dark text-white d-flex justify-content-between">
        <h5 class="mb-0">Facturation Professionnelle Dynamique</h5>
        <button type="button" class="btn btn-sm btn-warning" onclick="ajouterLigne()">+ Ajouter une pièce</button>
    </div>
    <div class="card-body">
        <form id="factureForm" method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Client / Véhicule</label>
                    <select name="id_vehicule" class="form-select" required>
                        </select>
                </div>
            </div>
            
            <table class="table" id="tablePieces">
                <thead>
                    <tr>
                        <th>Pièce (Code/Nom)</th>
                        <th width="150">Prix Unitaire</th>
                        <th width="100">Quantité</th>
                        <th width="150">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="lignesFacture">
                    </tbody>
            </table>
            
            <div class="row justify-content-end">
                <div class="col-md-4">
                    <div class="bg-light p-3 rounded">
                        <h4 class="d-flex justify-content-between">Total HT: <span id="totalGlobal">0</span> F</h4>
                        <button class="btn btn-success w-100 mt-2">Valider et Éditer la Plaquette</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function ajouterLigne() {
    const tbody = document.getElementById('lignesFacture');
    const index = tbody.children.length;
    const row = `
    <tr>
        <td>
            <input type="text" class="form-control" placeholder="Chercher pièce..." onkeyup="recherchePiece(this, ${index})">
            <input type="hidden" name="items[${index}][id_piece]" id="id_piece_${index}">
        </td>
        <td><input type="number" name="items[${index}][prix]" id="prix_${index}" class="form-control" readonly></td>
        <td><input type="number" name="items[${index}][qty]" id="qty_${index}" class="form-control" value="1" onchange="calculerLigne(${index})"></td>
        <td><input type="text" id="total_${index}" class="form-control" readonly value="0"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.closest('tr').remove(); calculerTotal();">x</button></td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', row);
}

function calculerLigne(i) {
    const p = document.getElementById('prix_' + i).value || 0;
    const q = document.getElementById('qty_' + i).value || 0;
    document.getElementById('total_' + i).value = p * q;
    calculerTotal();
}

function calculerTotal() {
    let total = 0;
    document.querySelectorAll('[id^="total_"]').forEach(el => total += parseFloat(el.value || 0));
    document.getElementById('totalGlobal').innerText = total.toLocaleString('fr-FR');
}
</script>
<?php require_once '../../includes/footer.php'; ?>
