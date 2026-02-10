<?php
// /var/www/piece_auto/modules/creation_vente.php

// Correction de l'inclusion : utilisez la méthode qui fonctionne le mieux pour vous.
// Si vous avez corrigé le Warning de 'auth_check.php', utilisez cette ligne, sinon utilisez le chemin simple '../includes/auth_check.php'
include_once '../config/Database.php';
include_once dirname(__DIR__) . '/includes/auth_check.php'; 

// Inclusion du header standard
include '../includes/header.php';
$page_title = "Créer une Nouvelle Commande de Vente";

$database = new Database();
$db = $database->getConnection();
$message_status = "";

// Inclusion de la fonction de log de mouvement (définie dans l'étape précédente)
function log_mouvement($db, $id_piece, $quantite, $type, $ref_externe = null) {
    $query = "INSERT INTO MOUVEMENTS_STOCK (id_piece, date_mouvement, quantite_change, type_mouvement, reference_externe) 
              VALUES (:id_p, NOW(), :qte, :type, :ref_ext)";
    $stmt = $db->prepare($query);
    return $stmt->execute([
        ':id_p' => $id_piece, 
        ':qte' => $quantite, 
        ':type' => $type, 
        ':ref_ext' => $ref_externe
    ]);
}

// --- 0. RÉCUPÉRATION DES DONNÉES POUR LES LISTES DÉROULANTES ---
// Clients
$clients = $db->query("SELECT id_client, nom, prenom FROM CLIENTS ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Pièces (avec leur stock actuel)
$query_pieces = "
    SELECT 
        P.id_piece, P.reference_sku, P.nom_piece, P.prix_vente, S.quantite_dispo  /* <-- CORRECTION SQL */
    FROM PIECES P
    LEFT JOIN STOCK S ON P.id_piece = S.id_piece
    ORDER BY P.nom_piece";
// La Fatal Error se produisait ici (Ligne 44 dans votre code initial). C'est corrigé.
$pieces = $db->query($query_pieces)->fetchAll(PDO::FETCH_ASSOC);


// --- 1. TRAITEMENT DU FORMULAIRE DE CRÉATION DE VENTE ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'create_sale') {
    $id_client = empty($_POST['id_client']) ? NULL : (int)$_POST['id_client'];
    $date_vente = date('Y-m-d H:i:s');
    $lignes_vente = $_POST['lignes_vente'] ?? [];

    if (empty($lignes_vente)) {
        $message_status = "<div class='alert alert-danger'>Veuillez ajouter au moins une pièce à la vente.</div>";
    } else {
        $db->beginTransaction();
        $total_ht = 0;
        
        try {
            // A. Insertion de l'en-tête de VENTE
            $query_vente = "INSERT INTO VENTES (id_client, date_vente, total_ht, total_ttc, statut) 
                            VALUES (:id_client, :date_v, 0, 0, 'Payée')";
            $stmt_vente = $db->prepare($query_vente);
            $stmt_vente->execute([':id_client' => $id_client, ':date_v' => $date_vente]);
            $id_vente = $db->lastInsertId();

            // B. Insertion des LIGNES_VENTE et Mise à jour du Stock
            $tva_rate = 0.20; // Taux de TVA standard (20%)

            foreach ($lignes_vente as $ligne) {
                $id_piece = (int)$ligne['id_piece'];
                $quantite = (int)$ligne['quantite'];
                $prix_unitaire_vendu = (float)$ligne['prix_vente'];

                if ($quantite > 0 && $id_piece > 0) {
                    // Calcul
                    $sous_total_ligne = $quantite * $prix_unitaire_vendu;
                    $total_ht += $sous_total_ligne;
                    
                    // 1. Insertion de la ligne
                    $query_ligne_insert = "INSERT INTO LIGNES_VENTE (id_vente, id_piece, quantite, prix_unitaire_vendu) 
                                           VALUES (:id_v, :id_p, :qte, :prix)";
                    $stmt_ligne_insert = $db->prepare($query_ligne_insert);
                    $stmt_ligne_insert->execute([
                        ':id_v' => $id_vente, 
                        ':id_p' => $id_piece, 
                        ':qte' => $quantite, 
                        ':prix' => $prix_unitaire_vendu
                    ]);

                    // 2. Décrémenter le STOCK
                    $query_stock_update = "UPDATE STOCK SET quantite_dispo = quantite_dispo - :qte WHERE id_piece = :id_p"; /* <-- CORRECTION SQL */
                    $stmt_stock_update = $db->prepare($query_stock_update);
                    $stmt_stock_update->execute([':qte' => $quantite, ':id_p' => $id_piece]);

                    // 3. Enregistrer le Mouvement de Stock (STRATÉGIQUE)
                    log_mouvement($db, $id_piece, -$quantite, 'Vente', (string)$id_vente);
                }
            }

            // C. Mise à jour des totaux dans l'en-tête VENTES
            $total_ttc = $total_ht * (1 + $tva_rate);
            $query_update_totals = "UPDATE VENTES SET total_ht = :total_ht, total_ttc = :total_ttc WHERE id_vente = :id_v";
            $stmt_update_totals = $db->prepare($query_update_totals);
            $stmt_update_totals->execute([':total_ht' => $total_ht, ':total_ttc' => $total_ttc, ':id_v' => $id_vente]);

            $db->commit();
            $message_status = "<div class='alert alert-success'>Commande de Vente **#$id_vente** créée avec succès ! Total HT: " . number_format($total_ht, 2) . " €</div>";
            
        } catch (PDOException $e) {
            $db->rollBack();
            $message_status = "<div class='alert alert-danger'>Erreur lors de la création de la vente : " . $e->getMessage() . "</div>";
        }
    }
}
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-cash-register"></i> <?= $page_title ?></h2>
        
        <?= $message_status ?>

        <div class="card p-4 shadow">
            <h4 class="card-title mb-4">Détails de la Vente</h4>
            <form id="saleForm" method="POST" action="creation_vente.php">
                <input type="hidden" name="action" value="create_sale">
                
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="id_client" class="form-label">Client (Optionnel)</label>
                        <select name="id_client" id="id_client" class="form-select">
                            <option value="">-- Client non spécifié (Vente au comptoir) --</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?= $client['id_client'] ?>">
                                    <?= htmlspecialchars($client['nom'] . ' ' . $client['prenom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="date_vente" class="form-label">Date de la Vente</label>
                        <input type="text" id="date_vente" class="form-control" value="<?= date('Y-m-d H:i') ?>" disabled>
                    </div>
                </div>

                <h5 class="mt-4 mb-3">Articles Vendus</h5>
                <table class="table table-bordered align-middle" id="lignesTable">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Pièce & Stock Disponible</th>
                            <th style="width: 20%;">Quantité</th>
                            <th style="width: 20%;">Prix Unitaire (€)</th>
                            <th style="width: 20%;">Sous-Total (€)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="sale-row">
                            <td>
                                <select name="lignes_vente[0][id_piece]" class="form-select piece-select" required>
                                    <option value="">Sélectionner une pièce</option>
                                    <?php foreach ($pieces as $piece): ?>
                                        <option 
                                            value="<?= $piece['id_piece'] ?>" 
                                            data-prix="<?= $piece['prix_vente'] ?>" 
                                            data-stock="<?= $piece['quantite_dispo'] ?? 0 ?>"> <?= htmlspecialchars($piece['nom_piece'] . ' (' . $piece['reference_sku'] . ') - Stock: ' . ($piece['quantite_dispo'] ?? 0)) ?> </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="lignes_vente[0][quantite]" class="form-control qte-input" min="1" value="1" required>
                            </td>
                            <td>
                                <input type="number" name="lignes_vente[0][prix_vente]" class="form-control prix-input" step="0.01" readonly required>
                            </td>
                            <td>
                                <span class="subtotal-display">0.00</span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total HT:</td>
                            <td colspan="2" class="fw-bold fs-5 total-ht-display">0.00 €</td>
                        </tr>
                    </tfoot>
                </table>

                <button type="button" class="btn btn-secondary mb-4" id="addRowButton"><i class="fas fa-plus"></i> Ajouter une ligne</button>
                
                <hr>
                <button type="submit" class="btn btn-primary btn-lg mt-3"><i class="fas fa-check-circle"></i> Enregistrer la Vente</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lignesTableBody = document.querySelector('#lignesTable tbody');
    const totalHTDisplay = document.querySelector('.total-ht-display');
    const addRowButton = document.getElementById('addRowButton');
    let rowIndex = 1;
    
    // Fonction pour mettre à jour le calcul de la ligne et du total
    function updateCalculations() {
        let globalTotalHT = 0;
        
        document.querySelectorAll('.sale-row').forEach(row => {
            const select = row.querySelector('.piece-select');
            const qteInput = row.querySelector('.qte-input');
            const prixInput = row.querySelector('.prix-input');
            const subtotalDisplay = row.querySelector('.subtotal-display');
            
            const qte = parseFloat(qteInput.value) || 0;
            const prix = parseFloat(prixInput.value) || 0;
            
            const subtotal = qte * prix;
            globalTotalHT += subtotal;
            
            subtotalDisplay.textContent = subtotal.toFixed(2);

            // Vérification de la disponibilité du stock
            const selectedOption = select.options[select.selectedIndex];
            // Utilisation du data-stock pour la vérification JavaScript
            const stock = parseInt(selectedOption.getAttribute('data-stock')) || 0; 
            
            if (qte > stock) {
                qteInput.classList.add('is-invalid');
                qteInput.setCustomValidity('Quantité insuffisante en stock (' + stock + ' dispo).');
            } else {
                qteInput.classList.remove('is-invalid');
                qteInput.setCustomValidity('');
            }
        });
        
        totalHTDisplay.textContent = globalTotalHT.toFixed(2) + ' €';
    }

    // Gère le changement de pièce (met à jour le prix)
    function handlePieceChange(event) {
        const row = event.target.closest('.sale-row');
        const selectedOption = event.target.options[event.target.selectedIndex];
        const prixVente = selectedOption.getAttribute('data-prix') || 0;
        
        const prixInput = row.querySelector('.prix-input');
        
        prixInput.value = parseFloat(prixVente).toFixed(2);
        updateCalculations();
    }

    // Gère la suppression de ligne
    function handleRemoveRow(event) {
        if (document.querySelectorAll('.sale-row').length > 1) {
            event.target.closest('.sale-row').remove();
            updateCalculations();
        } else {
            alert("La vente doit contenir au moins une ligne.");
        }
    }

    // Ajoute une nouvelle ligne
    function addNewRow() {
        const newRow = lignesTableBody.querySelector('.sale-row').cloneNode(true);
        
        newRow.querySelectorAll('input, select').forEach(input => {
            // Incrémente l'index pour que PHP puisse recevoir les données
            input.name = input.name.replace(/\[\d+\]/g, '[' + rowIndex + ']');
            
            if (input.classList.contains('qte-input')) {
                input.value = 1;
                input.classList.remove('is-invalid');
                input.setCustomValidity('');
            }
            if (input.classList.contains('prix-input')) {
                input.value = ''; // Remet le prix à zéro
            }
            if (input.classList.contains('piece-select')) {
                input.selectedIndex = 0; // Remet la sélection à la première option
            }
        });

        // Met à jour l'affichage du sous-total
        newRow.querySelector('.subtotal-display').textContent = '0.00';

        // Attache les écouteurs d'événements à la nouvelle ligne
        attachListeners(newRow);

        lignesTableBody.appendChild(newRow);
        rowIndex++;
        updateCalculations();
    }

    // Attache tous les écouteurs d'événements à une ligne donnée
    function attachListeners(row) {
        row.querySelector('.piece-select').addEventListener('change', handlePieceChange);
        row.querySelector('.qte-input').addEventListener('input', updateCalculations);
        row.querySelector('.remove-row').addEventListener('click', handleRemoveRow);
        // Initialiser le prix de vente pour la première ligne (si elle n'est pas vide)
        if (row.querySelector('.piece-select').value) {
            const selectedOption = row.querySelector('.piece-select').options[row.querySelector('.piece-select').selectedIndex];
            const prixVente = selectedOption.getAttribute('data-prix') || 0;
            row.querySelector('.prix-input').value = parseFloat(prixVente).toFixed(2);
        }
    }

    // Initialisation des écouteurs pour les lignes existantes au chargement
    document.querySelectorAll('.sale-row').forEach(attachListeners);

    // Initialisation du bouton Ajouter
    addRowButton.addEventListener('click', addNewRow);
    
    // Premier calcul au chargement
    updateCalculations();
});
</script>

<?php 
include '../includes/footer.php'; 
?>
