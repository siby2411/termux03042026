<?php
// Fichier : facturation.php - Formulaire principal de création de documents
session_start();
// Assurez-vous que ce fichier 'db_connect.php' existe et contient la fonction db_connect()
include_once 'db_connect.php';

// Protection de session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$conn = db_connect();

// Récupérer les clients pour la liste déroulante
$clients = [];
$result_clients = $conn->query("SELECT id_client, nom, adresse FROM clients ORDER BY nom ASC");
if ($result_clients) {
    while ($row = $result_clients->fetch_assoc()) {
        $clients[] = $row;
    }
}

// Récupérer les produits
$produits = [];
$result_produits = $conn->query("SELECT id_produit, code_produit, designation, prix_unitaire FROM produits ORDER BY code_produit ASC");
if ($result_produits) {
    while ($row = $result_produits->fetch_assoc()) {
        // Stocker le prix unitaire pour le calcul JavaScript
        $produits[$row['id_produit']] = [
            'code' => $row['code_produit'], 
            // Assurer que le prix est une chaîne avec un point pour le JS
            'prix' => number_format((float)$row['prix_unitaire'], 2, '.', ''), 
            'designation' => $row['designation']
        ];
    }
}
$conn->close();

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Préparer la liste des options HTML/JS pour les produits
$product_options = '<option value="" data-prix="0">-- Choisir un produit --</option>';
foreach ($produits as $id => $p) {
    // Le prix est stocké dans l'attribut data-prix pour être récupéré en JS
    $product_options .= "<option value=\"$id\" data-prix=\"{$p['prix']}\" data-code=\"{$p['code']}\">{$p['code']} - {$p['designation']} (Prix: {$p['prix']})</option>";
}

// 1. INCLUSION DU HEADER (doit contenir <DOCTYPE>, <html>, <head>, <body> et la balise de début du conteneur)
include 'header.php';
?>

    <h1>Créer une Facture / BL / BC</h1>
    <p><a href="dashboard.php">Retour au Tableau de Bord</a></p>

    <?php if ($message): ?>
        <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form action="traitement_facture.php" method="post" id="factureForm">
        <input type="hidden" name="id_vendeur" value="<?php echo $_SESSION['id_vendeur']; ?>">
        
        <fieldset>
            <legend>Informations Générales</legend>
            <label for="id_client">Client:</label>
            <select name="id_client" required>
                <option value="">-- Sélectionner un client --</option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?php echo $c['id_client']; ?>"><?php echo htmlspecialchars($c['nom']) . " - " . htmlspecialchars($c['adresse']); ?></option>
                <?php endforeach; ?>
            </select><br><br>
            
            <label for="type_document">Type de Document:</label>
            <select name="type_document" required>
                <option value="FACTURE">FACTURE</option>
                <option value="BL">BON DE LIVRAISON (BL)</option>
                <option value="BC">BON DE COMMANDE (BC)</option>
            </select><br><br>
        </fieldset>
        
        <fieldset>
            <legend>Détails des Produits (Max 10)</legend>
            
            <table border="1" id="productTable">
                <thead>
                    <tr>
                        <th>Code Produit / Désignation</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                        <th>Sous-Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" align="right"><strong>TOTAL GLOBAL:</strong></td>
                        <td><strong id="total_global">0.00</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <br>
            <button type="button" onclick="ajouterLigne()" id="addBtn">Ajouter Ligne Produit</button>
            <input type="hidden" name="total_facture_final" id="total_facture_final">
        </fieldset>
        
        <br>
        <button type="submit" name="action" value="creer_document">Créer le Document et Enregistrer</button>
    </form>

    <script>
        // Objet des produits pour calculs rapides (le prix sera dans l'attribut data-prix)
        const PRODUCT_OPTIONS = '<?php echo $product_options; ?>';
        let rowCount = 0;
        const MAX_ROWS = 10;

        /** Ajoute une ligne de produit au tableau */
        function ajouterLigne() {
            if (rowCount >= MAX_ROWS) {
                alert("Maximum de 10 produits atteint.");
                return;
            }

            rowCount++;
            const tableBody = document.querySelector('#productTable tbody');
            const newRow = tableBody.insertRow();
            newRow.id = `row_${rowCount}`;

            // Cellule 1: Sélection du Produit (id_produit)
            const cell1 = newRow.insertCell();
            cell1.innerHTML = `<select name="produits[${rowCount}][id_produit]" onchange="updateRow(${rowCount})" required>${PRODUCT_OPTIONS}</select>`;

            // Cellule 2: Quantité
            const cell2 = newRow.insertCell();
            cell2.innerHTML = `<input type="number" name="produits[${rowCount}][quantite]" min="1" value="1" oninput="updateRow(${rowCount})" style="width: 70px;">`;
            
            // Cellule 3: Prix Unitaire (Lecture seule) - type="text" pour éviter les problèmes de formatage
            const cell3 = newRow.insertCell();
            cell3.innerHTML = `<input type="text" name="produits[${rowCount}][prix_unitaire]" value="0.00" readonly style="width: 80px;">`;
            
            // Cellule 4: Sous-Total (Lecture seule)
            const cell4 = newRow.insertCell();
            cell4.innerHTML = `<span id="subtotal_${rowCount}">0.00</span>`;
            
            // Cellule 5: Action (Supprimer)
            const cell5 = newRow.insertCell();
            cell5.innerHTML = `<button type="button" onclick="supprimerLigne(${rowCount})">X</button>`;

            // Initialiser les valeurs de la nouvelle ligne
            updateRow(rowCount);
            
            // Mettre à jour l'état du bouton
            if (rowCount >= MAX_ROWS) {
                document.getElementById('addBtn').disabled = true;
            }
        }

        /** Met à jour le sous-total d'une ligne et le total global */
        function updateRow(rowId) {
            const row = document.getElementById(`row_${rowId}`);
            if (!row) return;

            const select = row.querySelector(`select[name="produits[${rowId}][id_produit]"]`);
            const quantiteInput = row.querySelector(`input[name="produits[${rowId}][quantite]"]`);
            const prixInput = row.querySelector(`input[name="produits[${rowId}][prix_unitaire]"]`);
            const subtotalSpan = row.querySelector(`#subtotal_${rowId}`);

            const selectedOption = select.options[select.selectedIndex];
            const prixUnitaire = parseFloat(selectedOption.getAttribute('data-prix')) || 0;
            const quantite = parseInt(quantiteInput.value) || 0;

            // Mettre à jour le champ Prix Unitaire avec un point décimal
            prixInput.value = prixUnitaire.toFixed(2);

            // Calculer le sous-total
            const subtotal = prixUnitaire * quantite;
            subtotalSpan.textContent = subtotal.toFixed(2);

            updateTotalGlobal();
        }

        /** Supprime une ligne et recalcule le total */
        function supprimerLigne(rowId) {
            document.getElementById(`row_${rowId}`).remove();
            updateTotalGlobal();
            document.getElementById('addBtn').disabled = false; // Réactiver si le max n'est plus atteint
        }

        /** Recalcule le total de toutes les lignes */
        function updateTotalGlobal() {
            let total = 0;
            const subtotalSpans = document.querySelectorAll('[id^="subtotal_"]');
            
            subtotalSpans.forEach(span => {
                total += parseFloat(span.textContent) || 0;
            });

            document.getElementById('total_global').textContent = total.toFixed(2);
            document.getElementById('total_facture_final').value = total.toFixed(2);
        }

        // Ajouter une ligne par défaut au chargement
        window.onload = function() {
            ajouterLigne();
        };

    </script>
    
<?php
// 3. INCLUSION DU FOOTER
// Ceci doit fermer le conteneur du contenu, <footer>, </body>, et </html>
include 'footer.php';
?>
