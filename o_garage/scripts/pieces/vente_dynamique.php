<?php require_once '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <h5 class="mb-0"><i class="fas fa-cart-plus me-2"></i>Vente Multi-Pièces & Facturation Rapide</h5>
            <span id="status-api" class="badge bg-light text-dark small">API Prête</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-5 border-end">
                    <label class="fw-bold mb-2">Rechercher une pièce (Réf ou Libellé)</label>
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-primary"></i></span>
                        <input type="text" id="search-input" class="form-control form-control-lg" placeholder="Ex: FIL, HUI, PLA..." autocomplete="off">
                    </div>
                    <div id="search-results" class="list-group shadow-sm" style="position: absolute; z-index: 1000; width: 350px;"></div>
                    
                    <div class="alert alert-info mt-4">
                        <small><i class="fas fa-info-circle me-1"></i> Tapez au moins 2 caractères pour déclencher la recherche dynamique dans la base <strong>o_garage</strong>.</small>
                    </div>
                </div>

                <div class="col-md-7">
                    <form action="save_vente.php" method="POST">
                        <table class="table table-hover align-middle" id="cart-table">
                            <thead class="table-dark">
                                <tr>
                                    <th>Pièce</th>
                                    <th width="120">Prix Unitaire</th>
                                    <th width="100">Qté</th>
                                    <th width="120">Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="cart-body">
                                </tbody>
                        </table>
                        
                        <div class="d-flex justify-content-between align-items-center mt-4 p-3 bg-light rounded">
                            <h4 class="mb-0">TOTAL À PAYER :</h4>
                            <h2 class="text-success fw-bold mb-0"><span id="grand-total">0</span> F</h2>
                        </div>
                        
                        <button type="submit" class="btn btn-success btn-lg w-100 mt-3 shadow">
                            <i class="fas fa-print me-2"></i>Valider & Imprimer Facture
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const searchInput = document.getElementById('search-input');
const searchResults = document.getElementById('search-results');
const cartBody = document.getElementById('cart-body');
const grandTotalDisplay = document.getElementById('grand-total');

// 1. Recherche Dynamique via AJAX
searchInput.addEventListener('input', function() {
    const q = this.value;
    if (q.length < 2) {
        searchResults.innerHTML = '';
        return;
    }

    fetch('../api/get_piece.php?q=' + q)
        .then(response => response.json())
        .then(data => {
            searchResults.innerHTML = '';
            if (data && !data.error) {
                const item = document.createElement('a');
                item.href = '#';
                item.className = 'list-group-item list-group-item-action animate__animated animate__fadeIn';
                item.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <strong>${data.libelle}</strong>
                        <span class="badge bg-primary">${data.prix} F</span>
                    </div>
                `;
                item.onclick = (e) => {
                    e.preventDefault();
                    addToCart(data);
                    searchResults.innerHTML = '';
                    searchInput.value = '';
                };
                searchResults.appendChild(item);
            } else if (q.length > 2) {
                searchResults.innerHTML = '<div class="list-group-item text-muted">Aucune pièce trouvée</div>';
            }
        })
        .catch(err => {
            console.error('Erreur API:', err);
            document.getElementById('status-api').className = 'badge bg-danger';
            document.getElementById('status-api').innerText = 'Erreur API';
        });
});

// 2. Ajouter au panier
function addToCart(piece) {
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <input type="hidden" name="pieces[]" value="${piece.id}">
            <strong>${piece.libelle}</strong>
        </td>
        <td><input type="number" class="form-control price" value="${piece.prix}" readonly></td>
        <td><input type="number" name="qtes[]" class="form-control qty" value="1" min="1" oninput="updateTotalRow(this)"></td>
        <td class="row-total fw-bold text-end">${piece.prix}</td>
        <td><button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('tr').remove(); calculateGrandTotal();">×</button></td>
    `;
    cartBody.appendChild(row);
    calculateGrandTotal();
}

// 3. Calculs
function updateTotalRow(input) {
    const row = input.closest('tr');
    const price = row.querySelector('.price').value;
    const qty = input.value;
    row.querySelector('.row-total').innerText = (price * qty);
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let total = 0;
    document.querySelectorAll('.row-total').forEach(cell => {
        total += parseFloat(cell.innerText);
    });
    grandTotalDisplay.innerText = total.toLocaleString();
}
</script>

<?php require_once '../../includes/footer.php'; ?>
