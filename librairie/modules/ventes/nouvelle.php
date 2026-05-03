<?php
require_once '../../includes/config.php';

if (!isCaissier()) {
    header('Location: ../../login.php');
    exit;
}

$page_title = 'Nouvelle vente';

// Récupérer les livres pour la recherche
$livres = $pdo->query("SELECT id, titre, auteur, prix_vente, quantite_stock FROM livres WHERE quantite_stock > 0 ORDER BY titre")->fetchAll();

// Récupérer les clients
$clients = $pdo->query("SELECT id, nom, prenom, points_fidelite FROM clients WHERE statut = 'actif' ORDER BY nom")->fetchAll();

include '../../includes/header.php';
?>

<style>
    .search-results {
        position: absolute;
        z-index: 1000;
        background: white;
        border: 1px solid #ddd;
        border-radius: 5px;
        max-height: 300px;
        overflow-y: auto;
        width: 100%;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .search-result-item {
        padding: 10px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    .search-result-item:hover {
        background-color: #f5f5f5;
    }
    .panier-item {
        background-color: #f9f9f9;
        margin-bottom: 10px;
        padding: 10px;
        border-radius: 5px;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            <strong>Nouvelle vente</strong> - Recherchez un livre par titre, auteur ou ISBN
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="fas fa-search"></i> Rechercher un livre</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label>Sélectionner un client</label>
                    <select id="client_id" class="form-control">
                        <option value="">Client anonyme</option>
                        <?php foreach($clients as $client): ?>
                        <option value="<?php echo $client['id']; ?>">
                            <?php echo $client['prenom'] . ' ' . $client['nom']; ?> 
                            (Points: <?php echo $client['points_fidelite']; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label>Rechercher un livre</label>
                    <input type="text" id="search_livre" class="form-control" 
                           placeholder="Tapez le titre, auteur ou ISBN..." autocomplete="off">
                    <div id="resultats_recherche" class="search-results" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5><i class="fas fa-shopping-cart"></i> Panier d'achat</h5>
            </div>
            <div class="card-body">
                <div id="panier_contenu">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle"></i> Aucun article dans le panier
                    </div>
                </div>
                
                <div id="total_panier" class="alert alert-success text-right" style="display: none;">
                    <strong>Total: <span id="total_montant">0</span> FCFA</strong>
                </div>
                
                <div class="row mt-3" id="paiement_section" style="display: none;">
                    <div class="col-md-6">
                        <label>Mode de paiement</label>
                        <select id="mode_paiement" class="form-control">
                            <option value="especes">Espèces</option>
                            <option value="carte">Carte bancaire</option>
                            <option value="cheque">Chèque</option>
                            <option value="virement">Virement</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Montant payé</label>
                        <input type="number" id="montant_paye" class="form-control" step="100" value="0">
                        <small class="text-muted" id="monnaie_msg"></small>
                    </div>
                </div>
                
                <div class="mt-3 text-right" id="actions_section" style="display: none;">
                    <button class="btn btn-danger" onclick="viderPanier()">
                        <i class="fas fa-trash"></i> Vider le panier
                    </button>
                    <button class="btn btn-success" onclick="validerVente()">
                        <i class="fas fa-check-circle"></i> Valider la vente
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let panier = [];

// Recherche en temps réel
let searchTimeout;
document.getElementById('search_livre').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    let search = this.value;
    
    if(search.length < 2) {
        document.getElementById('resultats_recherche').style.display = 'none';
        return;
    }
    
    searchTimeout = setTimeout(() => {
        fetch('ajax_recherche_livre.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'search=' + encodeURIComponent(search)
        })
        .then(response => response.text())
        .then(html => {
            const resultsDiv = document.getElementById('resultats_recherche');
            resultsDiv.innerHTML = html;
            resultsDiv.style.display = 'block';
        });
    }, 300);
});

// Fermer les résultats quand on clique ailleurs
document.addEventListener('click', function(e) {
    if(!e.target.closest('#search_livre') && !e.target.closest('#resultats_recherche')) {
        document.getElementById('resultats_recherche').style.display = 'none';
    }
});

// Ajouter au panier
function ajouterAuPanier(id, titre, prix, stock) {
    let existing = panier.find(item => item.id === id);
    
    if(existing) {
        if(existing.quantite >= stock) {
            alert('Stock insuffisant ! Stock disponible: ' + stock);
            return;
        }
        existing.quantite++;
        existing.sous_total = existing.quantite * existing.prix;
    } else {
        if(stock <= 0) {
            alert('Ce livre n\'est plus en stock');
            return;
        }
        panier.push({
            id: id,
            titre: titre,
            prix: prix,
            quantite: 1,
            stock_max: stock,
            sous_total: prix
        });
    }
    
    actualiserPanier();
    document.getElementById('resultats_recherche').style.display = 'none';
    document.getElementById('search_livre').value = '';
}

// Modifier la quantité
function modifierQuantite(index, quantite) {
    quantite = parseInt(quantite);
    let item = panier[index];
    
    if(quantite < 1) {
        quantite = 1;
    }
    if(quantite > item.stock_max) {
        alert('Stock maximum disponible: ' + item.stock_max);
        quantite = item.stock_max;
    }
    
    panier[index].quantite = quantite;
    panier[index].sous_total = panier[index].quantite * panier[index].prix;
    actualiserPanier();
}

// Supprimer du panier
function supprimerDuPanier(index) {
    if(confirm('Retirer ce livre du panier ?')) {
        panier.splice(index, 1);
        actualiserPanier();
    }
}

// Vider le panier
function viderPanier() {
    if(panier.length > 0 && confirm('Vider complètement le panier ?')) {
        panier = [];
        actualiserPanier();
    }
}

// Calculer le total
function calculerTotal() {
    return panier.reduce((total, item) => total + item.sous_total, 0);
}

// Actualiser l'affichage du panier
function actualiserPanier() {
    const panierContenu = document.getElementById('panier_contenu');
    const totalSection = document.getElementById('total_panier');
    const paiementSection = document.getElementById('paiement_section');
    const actionsSection = document.getElementById('actions_section');
    const totalMontant = document.getElementById('total_montant');
    
    if(panier.length === 0) {
        panierContenu.innerHTML = '<div class="alert alert-info text-center"><i class="fas fa-info-circle"></i> Aucun article dans le panier</div>';
        totalSection.style.display = 'none';
        paiementSection.style.display = 'none';
        actionsSection.style.display = 'none';
        return;
    }
    
    let html = '<div class="table-responsive"><table class="table table-bordered"><thead><tr>';
    html += '<th>Livre</th><th>Auteur</th><th>Prix unitaire</th><th>Quantité</th><th>Sous-total</th><th>Action</th>';
    html += '</tr></thead><tbody>';
    
    panier.forEach((item, index) => {
        html += '<tr>';
        html += '<td><strong>' + item.titre + '</strong></td>';
        html += '<td>' + item.titre.split(' ')[0] + '</td>';
        html += '<td>' + item.prix.toLocaleString() + ' FCFA</td>';
        html += '<td><input type="number" value="' + item.quantite + '" min="1" max="' + item.stock_max + '" ';
        html += 'onchange="modifierQuantite(' + index + ', this.value)" class="form-control" style="width: 80px;"></td>';
        html += '<td>' + item.sous_total.toLocaleString() + ' FCFA</td>';
        html += '<td><button class="btn btn-danger btn-sm" onclick="supprimerDuPanier(' + index + ')"><i class="fas fa-trash"></i></button></td>';
        html += '</tr>';
    });
    
    html += '</tbody></table></div>';
    panierContenu.innerHTML = html;
    
    let total = calculerTotal();
    totalMontant.innerHTML = total.toLocaleString();
    totalSection.style.display = 'block';
    paiementSection.style.display = 'flex';
    actionsSection.style.display = 'block';
    
    // Vérifier le montant payé
    verifierMontantPaye();
}

// Vérifier le montant payé
function verifierMontantPaye() {
    let total = calculerTotal();
    let paye = parseFloat(document.getElementById('montant_paye').value) || 0;
    let monnaie = paye - total;
    let msg = document.getElementById('monnaie_msg');
    
    if(paye < total) {
        msg.innerHTML = '<span class="text-danger">Manque: ' + (total - paye).toLocaleString() + ' FCFA</span>';
        return false;
    } else if(paye >= total) {
        msg.innerHTML = '<span class="text-success">Monnaie à rendre: ' + monnaie.toLocaleString() + ' FCFA</span>';
        return true;
    }
    return false;
}

// Écouter les changements de montant payé
document.getElementById('montant_paye').addEventListener('input', verifierMontantPaye);

// Valider la vente
function validerVente() {
    if(panier.length === 0) {
        alert('Le panier est vide');
        return;
    }
    
    let total = calculerTotal();
    let montant_paye = parseFloat(document.getElementById('montant_paye').value);
    
    if(montant_paye < total) {
        alert('Le montant payé est insuffisant. Manque: ' + (total - montant_paye).toLocaleString() + ' FCFA');
        return;
    }
    
    if(!confirm('Confirmer la vente de ' + total.toLocaleString() + ' FCFA ?')) {
        return;
    }
    
    let vente = {
        client_id: document.getElementById('client_id').value,
        mode_paiement: document.getElementById('mode_paiement').value,
        montant_paye: montant_paye,
        articles: panier
    };
    
    fetch('ajax_valider_vente.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(vente)
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert('Vente validée avec succès !');
            window.location.href = 'facture.php?id=' + data.facture_id;
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Erreur lors de la validation de la vente');
    });
}
</script>

<?php include '../../includes/footer.php'; ?>
