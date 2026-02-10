<?php
$page_title = "Enregistrer une Nouvelle Commande";
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php';

$database = new Database();
$db = $database->getConnection();

// 1. Récupérer les clients pour le sélecteur
$stmt_clients = $db->query("SELECT ClientID, Nom FROM Clients ORDER BY Nom ASC");
$clients = $stmt_clients->fetchAll(PDO::FETCH_ASSOC);

// 2. Récupérer les produits pour le sélecteur de détail
$stmt_produits = $db->query("SELECT ProduitID, Nom, PrixVente, CUMP, StockActuel FROM Produits ORDER BY Nom ASC");
$produits = $stmt_produits->fetchAll(PDO::FETCH_ASSOC);
?>

<h1 class="mt-4"><i class="fas fa-plus-circle me-2"></i> Nouvelle Commande</h1>
<p class="text-muted">Enregistrement d'une vente et gestion de l'impact sur le stock/CUMP.</p>
<hr>

<?php if (isset($_GET['action']) && $_GET['action'] == 'success'): ?>
    <div class="alert alert-success">Commande enregistrée avec succès ! (ID: <?= htmlspecialchars($_GET['id']) ?>)</div>
<?php endif; ?>

<div class="card shadow-lg p-4">
    <form id="commandeForm" action="traitement_commande.php" method="POST">
        
        <h5 class="mb-3">Informations de Base</h5>
        <div class="row mb-4">
            <div class="col-md-4">
                <label for="client_id" class="form-label">Client</label>
                <select name="client_id" id="client_id" class="form-select" required>
                    <option value="">Sélectionner un client</option>
                    <?php foreach ($clients as $client): ?>
                        <option value="<?= htmlspecialchars($client['ClientID']) ?>"><?= htmlspecialchars($client['Nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="date_commande" class="form-label">Date de la Commande</label>
                <input type="date" class="form-control" name="date_commande" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-4">
                <label for="ref_facture" class="form-label">Référence Facture / Bon de Commande</label>
                <input type="text" class="form-control" name="ref_facture" placeholder="Réf. (ex: FCT-2025-001)">
            </div>
        </div>

        <h5 class="mb-3">Détails des Produits</h5>
        <table class="table table-bordered table-custom" id="detailsTable">
            <thead>
                <tr>
                    <th style="width: 35%;">Produit</th>
                    <th style="width: 15%;">Stock Actuel</th>
                    <th style="width: 15%;">Prix Vente Unitaire</th>
                    <th style="width: 15%;">Quantité</th>
                    <th style="width: 15%;">Total Ligne</th>
                    <th style="width: 5%;"></th>
                </tr>
            </thead>
            <tbody>
                </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-end fw-bold">Montant Total HT :</td>
                    <td><input type="text" class="form-control fw-bold text-end" id="grandTotal" name="montant_total" value="0.00" readonly></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <button type="button" class="btn btn-sm btn-info text-white mb-4" onclick="addRow()"><i class="fas fa-plus"></i> Ajouter une Ligne Produit</button>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i> Enregistrer la Commande</button>
        </div>
    </form>
</div>

<script>
    // Convertir les données PHP des produits en objet JS
    const produitsData = <?= json_encode($produits) ?>;
    const produitsMap = {};
    produitsData.forEach(p => {
        produitsMap[p.ProduitID] = {
            nom: p.Nom,
            prixVente: parseFloat(p.PrixVente),
            cump: parseFloat(p.CUMP),
            stock: parseInt(p.StockActuel)
        };
    });

    let rowCounter = 0;

    // Fonction pour ajouter une ligne au tableau
    function addRow() {
        rowCounter++;
        const tableBody = document.getElementById('detailsTable').getElementsByTagName('tbody')[0];
        const newRow = tableBody.insertRow();
        newRow.id = `row-${rowCounter}`;

        // Cellule 1: Sélecteur de produit
        const cell1 = newRow.insertCell(0);
        const selectHtml = `
            <select name="details[${rowCounter}][produit_id]" class="form-select product-select" onchange="updateRow(${rowCounter})" required>
                <option value="">Sélectionner...</option>
                <?php foreach ($produits as $p): ?>
                    <option value="<?= $p['ProduitID'] ?>" 
                            data-prix="<?= $p['PrixVente'] ?>" 
                            data-stock="<?= $p['StockActuel'] ?>"
                            data-cump="<?= $p['CUMP'] ?>">
                        <?= htmlspecialchars($p['Nom']) ?> (Stock: <?= $p['StockActuel'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        `;
        cell1.innerHTML = selectHtml;

        // Cellule 2: Stock Actuel (Affichage seulement)
        const cell2 = newRow.insertCell(1);
        cell2.innerHTML = `<span id="stock-${rowCounter}" class="badge bg-secondary">--</span>`;
        cell2.className = 'text-center';

        // Cellule 3: Prix Vente Unitaire (Input masqué pour le traitement)
        const cell3 = newRow.insertCell(2);
        cell3.innerHTML = `
            <input type="text" class="form-control text-end prix-vente" id="prixVente-${rowCounter}" 
                   name="details[${rowCounter}][prix_vente]" value="0.00" oninput="updateRow(${rowCounter})" required>
            <input type="hidden" name="details[${rowCounter}][cump]" id="cump-${rowCounter}" value="0.00">
        `;

        // Cellule 4: Quantité
        const cell4 = newRow.insertCell(3);
        cell4.innerHTML = `<input type="number" step="1" min="1" class="form-control text-end quantity-input" 
                           name="details[${rowCounter}][quantite]" value="1" oninput="updateRow(${rowCounter})" required>`;

        // Cellule 5: Total Ligne
        const cell5 = newRow.insertCell(4);
        cell5.innerHTML = `<input type="text" class="form-control text-end line-total" id="total-${rowCounter}" value="0.00" readonly>`;

        // Cellule 6: Bouton Supprimer
        const cell6 = newRow.insertCell(5);
        cell6.innerHTML = `<button type="button" class="btn btn-danger btn-sm" onclick="removeRow(${rowCounter})"><i class="fas fa-trash"></i></button>`;

        updateGrandTotal();
    }

    // Fonction pour mettre à jour une ligne et le total général
    function updateRow(rowId) {
        const select = document.querySelector(`#row-${rowId} select`);
        const prixInput = document.getElementById(`prixVente-${rowId}`);
        const quantityInput = document.querySelector(`#row-${rowId} input[type="number"]`);
        const totalInput = document.getElementById(`total-${rowId}`);
        const stockSpan = document.getElementById(`stock-${rowId}`);
        const cumpInput = document.getElementById(`cump-${rowId}`);
        
        const selectedOption = select.options[select.selectedIndex];
        
        // Mise à jour si le produit change
        if (selectedOption.value) {
            const data = produitsMap[selectedOption.value];
            
            // Si l'utilisateur n'a pas encore modifié le prix, utiliser le prix de vente par défaut
            if (prixInput.value === '0.00') {
                 prixInput.value = data.prixVente.toFixed(2);
            }
            
            // Mettre à jour le CUMP (caché) pour l'enregistrement
            cumpInput.value = data.cump.toFixed(2); 

            // Mise à jour du stock affiché
            stockSpan.textContent = data.stock;
            stockSpan.className = data.stock < quantityInput.value ? 'badge bg-danger' : 'badge bg-success';
        } else {
            stockSpan.textContent = '--';
            stockSpan.className = 'badge bg-secondary';
        }

        const prix = parseFloat(prixInput.value) || 0;
        const quantite = parseInt(quantityInput.value) || 0;
        
        const total = prix * quantite;
        totalInput.value = total.toFixed(2);
        
        updateGrandTotal();
    }

    // Fonction pour supprimer une ligne
    function removeRow(rowId) {
        document.getElementById(`row-${rowId}`).remove();
        updateGrandTotal();
    }

    // Fonction pour calculer le total général
    function updateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.line-total').forEach(input => {
            grandTotal += parseFloat(input.value) || 0;
        });
        document.getElementById('grandTotal').value = grandTotal.toFixed(2);
    }

    // Initialisation
    window.onload = function() {
        addRow(); // Ajouter une ligne par défaut au chargement
        // Optionnel: Écouteur pour la soumission du formulaire pour prévenir la double soumission
        document.getElementById('commandeForm').addEventListener('submit', function(e) {
            // Désactiver le bouton de soumission après le premier clic
            const submitButton = this.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Enregistrement...';
        });
    }
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
