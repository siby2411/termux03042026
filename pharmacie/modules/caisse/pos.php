<?php
require_once '../../core/Auth.php';
require_once '../../core/Database.php';
Auth::check();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Caisse — Omega Pharma</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .search-results { position: absolute; width: 100%; z-index: 1050; max-height: 300px; overflow-y: auto; box-shadow: 0 8px 16px rgba(0,0,0,0.2); }
        .cart-container { min-height: 500px; background: white; border-radius: 12px; }
        .bg-omega { background: #00713e; color: white; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid py-3">
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <div class="position-relative">
                        <input type="text" id="searchInput" class="form-control form-control-lg" 
                               placeholder="Rechercher un médicament (ex: PARA...)" autocomplete="off">
                        <div id="searchResults" class="list-group search-results d-none"></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm cart-container p-3">
                <h5 class="fw-bold mb-3"><i class="bi bi-cart3"></i> Panier Actuel</h5>
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr><th>Désignation</th><th>Prix (F)</th><th width="100">Qté</th><th>Total</th><th></th></tr>
                    </thead>
                    <tbody id="cartBody"></tbody>
                </table>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-dark text-white p-4">
                <p class="small text-uppercase opacity-50 mb-1">Net à payer</p>
                <h1 class="fw-bold mb-4" id="grandTotal">0 FCFA</h1>
                
                <div class="mb-4">
                    <label class="form-label small">Mode de règlement</label>
                    <select id="payMethod" class="form-select form-select-lg bg-dark text-white border-secondary">
                        <option value="espèces">Espèces (Cash)</option>
                        <option value="wave">Wave</option>
                        <option value="orange_money">Orange Money</option>
                    </select>
                </div>

                <button onclick="validerLaVente()" class="btn btn-success btn-lg w-100 py-3 fw-bold shadow">
                    VALIDER LA VENTE (F10)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];

document.getElementById('searchInput').addEventListener('input', function(e) {
    let q = e.target.value;
    if (q.length < 2) {
        document.getElementById('searchResults').classList.add('d-none');
        return;
    }

    fetch(`../medicaments/api.php?q=${encodeURIComponent(q)}`)
        .then(res => res.json())
        .then(data => displayResults(data))
        .catch(err => console.error("Erreur API:", err));
});

function displayResults(data) {
    let html = '';
    let resDiv = document.getElementById('searchResults');
    if (data.length === 0) {
        html = '<div class="list-group-item text-muted">Aucun médicament trouvé...</div>';
    } else {
        data.forEach(med => {
            html += `
                <div class="list-group-item list-group-item-action" 
                     onclick="addToCart(${med.id}, '${med.denomination.replace(/'/g, "\\'")}', ${med.prix_vente})">
                    <div class="d-flex justify-content-between">
                        <strong>${med.denomination}</strong>
                        <span class="badge bg-success">${parseInt(med.prix_vente)} F</span>
                    </div>
                    <small class="text-muted">En stock : ${med.stock_actuel}</small>
                </div>`;
        });
    }
    resDiv.innerHTML = html;
    resDiv.classList.remove('d-none');
}

function addToCart(id, name, price) {
    let item = cart.find(i => i.id === id);
    if (item) {
        item.qte++;
    } else {
        cart.push({ id, name, price, qte: 1 });
    }
    updateCartUI();
    document.getElementById('searchResults').classList.add('d-none');
    document.getElementById('searchInput').value = '';
}

function updateCartUI() {
    let body = document.getElementById('cartBody');
    let total = 0;
    body.innerHTML = '';
    cart.forEach((item, index) => {
        let subtotal = item.price * item.qte;
        total += subtotal;
        body.innerHTML += `
            <tr>
                <td>${item.name}</td>
                <td>${item.price}</td>
                <td><input type="number" class="form-control" value="${item.qte}" onchange="updateQte(${index}, this.value)"></td>
                <td class="fw-bold">${subtotal} F</td>
                <td><button onclick="remove(${index})" class="btn btn-sm btn-outline-danger">&times;</button></td>
            </tr>`;
    });
    document.getElementById('grandTotal').innerText = total.toLocaleString() + ' FCFA';
}

function updateQte(idx, val) { cart[idx].qte = val; updateCartUI(); }
function remove(idx) { cart.splice(idx, 1); updateCartUI(); }

function validerLaVente() {
    if (cart.length === 0) return alert("Panier vide !");
    
    let data = {
        items: cart,
        total: cart.reduce((acc, i) => acc + (i.price * i.qte), 0),
        paiement: document.getElementById('payMethod').value
    };

    fetch('ventes_api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        if(result.success) {
            alert("Vente enregistrée !");
            window.open('ticket.php?id=' + result.vente_id, '_blank', 'width=350,height=600');
            location.reload();
        } else {
            alert("Erreur: " + result.message);
        }
    });
}
</script>
</body>
</html>
