<?php require_once '../../includes/header.php'; ?>

<div class="container-fluid">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 text-warning">
                <i class="fas fa-cart-plus me-2"></i>VENTE MULTI-PIÈCES & FACTURATION
            </h5>
            <div class="d-flex align-items-center">
                <span id="status-api" class="badge bg-success me-3">Système Connecté</span>
                <a href="../../index.php" class="btn btn-outline-light btn-sm">Quitter</a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-5 border-end">
                    <label class="fw-bold mb-2 text-primary">Rechercher dans le stock (Nom de la pièce)</label>
                    <div class="input-group mb-1">
                        <span class="input-group-text bg-white border-primary"><i class="fas fa-search text-primary"></i></span>
                        <input type="text" id="search-input" class="form-control form-control-lg border-primary" placeholder="Tapez 'Fil' pour Filtres..." autocomplete="off">
                    </div>
                    <div id="search-results" class="list-group shadow-sm mt-1" style="max-height: 300px; overflow-y: auto; position: absolute; z-index: 1050; width: 90%;"></div>
                    
                    <div class="alert alert-info mt-5">
                        <small>
                            <i class="fas fa-info-circle me-1"></i> 
                            <strong>Conseil :</strong> Sélectionnez une pièce dans la liste pour l'ajouter à la facture. Vous pouvez modifier la quantité directement dans le tableau.
                        </small>
                    </div>
                </div>

                <div class="col-md-7">
                    <form action="save_vente.php" method="POST">
                        <div class="table-responsive" style="min-height: 200px;">
                            <table class="table table-hover align-middle" id="cart-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Désignation</th>
                                        <th width="120">Prix (F)</th>
                                        <th width="100">Qté</th>
                                        <th width="120" class="text-end">Sous-Total</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody id="cart-body">
                                    </tbody>
                            </table>
                        </div>

                        <div class="card bg-light border-0 mt-3">
                            <div class="card-body d-flex justify-content-between align-items-center py-4">
                                <h3 class="mb-0 fw-bold text-secondary">TOTAL À PAYER :</h3>
                                <h1 class="text-success fw-bold mb-0"><span id="grand-total">0</span> <small style="font-size: 1.5rem">F CFA</small></h1>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <button type="reset" class="btn btn-outline-secondary btn-lg w-100" onclick="window.location.reload();">
                                    <i class="fas fa-sync me-2"></i>Vider
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-warning btn-lg w-100 fw-bold shadow">
                                    <i class="fas fa-print me-2"></i>VALIDER & FACTURER
                                </button>
                            </div>
                        </div>
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

// 1. RECHERCHE DYNAMIQUE (Vôtre version finale)
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
            
            if (data.length > 0) {
                data.forEach(piece => {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center bg-light border-start border-primary border-4 mb-1 shadow-sm';
                    item.innerHTML = `
                        <div>
                            <i class="fas fa-cog me-2 text-secondary"></i>
                            <strong>${piece.libelle}</strong>
                        </div>
                        <span class="badge bg-primary rounded-pill">${piece.prix} F</span>
                    `;
                    item.onclick = (e) => {
                        e.preventDefault();
                        addToCart(piece);
                        searchResults.innerHTML = '';
                        searchInput.value = '';
                    };
                    searchResults.appendChild(item);
                });
            } else {
                searchResults.innerHTML = '<div class="list-group-item text-danger small">Aucune pièce trouvée pour "' + q + '"</div>';
            }
        })
        .catch(err => {
            console.error('Erreur API:', err);
            document.getElementById('status-api').className = 'badge bg-danger';
            document.getElementById('status-api').innerText = 'Erreur Connexion';
        });
});

// 2. AJOUTER AU PANIER
function addToCart(piece) {
    // Vérifier si la pièce est déjà dans le panier
    const existingRow = document.querySelector(`input[value="${piece.id}"]`);
    if (existingRow) {
        const qtyInput = existingRow.closest('tr').querySelector('.qty');
        qtyInput.value = parseInt(qtyInput.value) + 1;
        updateTotalRow(qtyInput);
        return;
    }

    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <input type="hidden" name="pieces[]" value="${piece.id}">
            <span class="fw-bold">${piece.libelle}</span>
        </td>
        <td>
            <input type="number" class="form-control price" value="${piece.prix}" readonly>
        </td>
        <td>
            <input type="number" name="qtes[]" class="form-control qty border-primary" value="1" min="1" oninput="updateTotalRow(this)">
        </td>
        <td class="row-total fw-bold text-end">${piece.prix}</td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm border-0" onclick="this.closest('tr').remove(); calculateGrandTotal();">
                <i class="fas fa-times-circle fa-lg"></i>
            </button>
        </td>
    `;
    cartBody.appendChild(row);
    calculateGrandTotal();
}

// 3. CALCULS DYNAMIQUES
function updateTotalRow(input) {
    const row = input.closest('tr');
    const price = parseFloat(row.querySelector('.price').value);
    const qty = parseInt(input.value) || 0;
    const total = price * qty;
    row.querySelector('.row-total').innerText = total;
    calculateGrandTotal();
}

function calculateGrandTotal() {
    let grandTotal = 0;
    document.querySelectorAll('.row-total').forEach(cell => {
        grandTotal += parseFloat(cell.innerText);
    });
    // Formatage avec séparateur de milliers
    grandTotalDisplay.innerText = grandTotal.toLocaleString('fr-FR');
}
</script>

<?php require_once '../../includes/footer.php'; ?>
