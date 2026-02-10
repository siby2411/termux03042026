<?php
// /var/www/piece_auto/public/modules/creation_vente.php - (CORRECTION DE L'INSERTION id_client)

$page_title = "Enregistrement d'une Nouvelle Vente (Facture)";
include '../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$message = '';

// --- FONCTIONS UTILITAIRES ---
function get_clients($db) {
    // Utilisation des colonnes 'nom' et 'prenom' comme décrit dans votre DESCRIBE CLIENTS
    $query = "SELECT id_client, CONCAT(nom, ' ', prenom) AS nom_complet FROM CLIENTS ORDER BY nom";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_pieces_info($db) {
    // Récupère les infos essentielles, y compris le CUMP actuel et le prix de vente
    $query = "SELECT id_piece, nom_piece, reference, quantite_stock, cump_actuel, prix_vente 
              FROM PIECES 
              WHERE quantite_stock > 0
              ORDER BY nom_piece";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Initialisation des listes
$clients = get_clients($db);
$pieces_info = get_pieces_info($db);

// Convertir les informations des pièces en JSON pour utilisation dans le JavaScript (pour le calcul)
$pieces_json = json_encode($pieces_info);

// --- 1. GESTION DU POST (CRÉATION DE LA VENTE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['client_id']) && isset($_POST['lignes_vente'])) {
    
    $client_id = filter_input(INPUT_POST, 'client_id', FILTER_VALIDATE_INT);
    $lignes_vente = json_decode($_POST['lignes_vente'], true);
    
    if (!$client_id || empty($lignes_vente)) {
        $message = '<div class="alert alert-danger">Veuillez sélectionner un client et ajouter au moins une pièce.</div>';
    } else {
        try {
            $db->beginTransaction();
            $montant_total = 0;
            $cout_total_cump = 0;

            // --- A. Création de la Commande Vente (En-tête) ---
            // CORRECTION: Liste explicite des colonnes pour éviter les erreurs de "Field doesn't have a default value"
            // Nous insérons l'id_client, la date (par défaut) et les totaux à zéro, puis nous mettons à jour les totaux
            // Assurez-vous que les colonnes 'montant_total', 'cout_total_cump', 'marge_brute' existent bien avec une valeur par défaut de 0.00
            $query_commande = "INSERT INTO COMMANDE_VENTE (id_client, montant_total, cout_total_cump, marge_brute) 
                               VALUES (:client_id, :montant_total, :cout_total_cump, :marge_brute)";
            $stmt_commande = $db->prepare($query_commande);
            $stmt_commande->execute([
                ':client_id' => $client_id, // L'ID client est désormais passé explicitement ici
                ':montant_total' => 0.00, 
                ':cout_total_cump' => 0.00, 
                ':marge_brute' => 0.00
            ]);
            $commande_vente_id = $db->lastInsertId();

            // --- B. Traitement des Lignes de Vente ---
            foreach ($lignes_vente as $ligne) {
                $piece_id = filter_var($ligne['piece_id'], FILTER_VALIDATE_INT);
                $quantite = filter_var($ligne['quantite'], FILTER_VALIDATE_INT);
                $prix_vente_unitaire = filter_var($ligne['prix_vente_unitaire'], FILTER_VALIDATE_FLOAT);
                $cump_unitaire = filter_var($ligne['cump_unitaire'], FILTER_VALIDATE_FLOAT);
                
                if ($piece_id && $quantite > 0 && $prix_vente_unitaire !== false && $cump_unitaire !== false) {
                    
                    $total_ligne = $quantite * $prix_vente_unitaire;
                    $cout_ligne = $quantite * $cump_unitaire;
                    
                    $montant_total += $total_ligne;
                    $cout_total_cump += $cout_ligne;

                    // 1. Insertion de la Ligne de Vente (avec CUMP)
                    // Nous utilisons id_commande_vente pour faire le lien avec l'en-tête
                    $query_ligne = "INSERT INTO LIGNE_VENTE (id_commande_vente, id_piece, quantite, prix_vente_unitaire, cout_unitaire_cump, cout_total_ligne) 
                                    VALUES (:commande_id, :piece_id, :quantite, :prix_unitaire, :cump_unitaire, :cout_total)";
                    $stmt_ligne = $db->prepare($query_ligne);
                    $stmt_ligne->execute([
                        ':commande_id' => $commande_vente_id,
                        ':piece_id' => $piece_id,
                        ':quantite' => $quantite,
                        ':prix_unitaire' => $prix_vente_unitaire,
                        ':cump_unitaire' => $cump_unitaire,
                        ':cout_total' => $cout_ligne
                    ]);

                    // 2. Mise à jour du Stock (Décrémentation)
                    $query_stock = "UPDATE PIECES SET quantite_stock = quantite_stock - :quantite WHERE id_piece = :piece_id AND quantite_stock >= :quantite";
                    $stmt_stock = $db->prepare($query_stock);
                    $stmt_stock->execute([':quantite' => $quantite, ':piece_id' => $piece_id]);

                    if ($stmt_stock->rowCount() == 0) {
                        throw new Exception("Stock insuffisant pour la pièce ID: " . $piece_id);
                    }
                    
                    // 3. Mise à jour de la Valeur Totale du Stock (quantite_stock et valeur_stock_total sont liées par le CUMP qui ne change pas à la sortie)
                    // Il faut mettre à jour la valeur totale après la décrémentation de stock
                    $query_valeur_stock = "UPDATE PIECES 
                                           SET valeur_stock_total = quantite_stock * cump_actuel 
                                           WHERE id_piece = :piece_id";
                    $stmt_valeur_stock = $db->prepare($query_valeur_stock);
                    $stmt_valeur_stock->execute([':piece_id' => $piece_id]);
                }
            }
            
            // --- C. Mise à jour de l'En-tête de Commande Vente (Totaux) ---
            $marge_brute = $montant_total - $cout_total_cump;
            $query_update = "UPDATE COMMANDE_VENTE 
                             SET montant_total = :montant_total, 
                                 cout_total_cump = :cout_total_cump, 
                                 marge_brute = :marge_brute
                             WHERE id_commande_vente = :id";
            $stmt_update = $db->prepare($query_update);
            $stmt_update->execute([
                ':montant_total' => $montant_total,
                ':cout_total_cump' => $cout_total_cump,
                ':marge_brute' => $marge_brute,
                ':id' => $commande_vente_id
            ]);

            $db->commit();
            $message = '<div class="alert alert-success">Vente N° ' . $commande_vente_id . ' enregistrée avec succès. Marge Brute calculée : ' . number_format($marge_brute, 2) . ' €.</div>';

        } catch (Exception $e) {
            $db->rollBack();
            $message = '<div class="alert alert-danger">Erreur lors de l\'enregistrement de la vente : ' . $e->getMessage() . '</div>';
        }
    }
}

// ... Reste du code HTML et JavaScript inchangé ...
?>

<h1><i class="fas fa-cash-register"></i> <?= $page_title ?></h1>
<p class="lead">Enregistrez une vente pour décrémenter le stock et calculer la Marge Brute (Prix Vente - CUMP).</p>
<hr>

<?= $message ?>

<form method="POST" id="venteForm">
    <input type="hidden" name="lignes_vente" id="lignesVenteInput">

    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">Sélection du Client *</div>
        <div class="card-body">
            <label for="client_id" class="form-label">Client</label>
            <select class="form-select" id="client_id" name="client_id" required>
                <option value="" disabled selected>Sélectionnez un client</option>
                <?php foreach ($clients as $c): ?>
                    <option value="<?= $c['id_client'] ?>"><?= htmlspecialchars($c['nom_complet']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-info text-white">Détails de la Vente (Pièces)</div>
        <div class="card-body">
            <table class="table table-sm table-striped" id="lignesTable">
                <thead>
                    <tr>
                        <th>Pièce *</th>
                        <th>Stock Disp.</th>
                        <th>Qté Vendue *</th>
                        <th>Prix Unitaire (€)</th>
                        <th>CUMP (Coût) (€)</th>
                        <th>Total Ligne (€)</th>
                        <th>Marge Ligne (€)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">TOTAL VENTE :</td>
                        <td class="text-end fw-bold" id="grandTotal">0.00</td>
                        <td class="text-end fw-bold text-success" id="margeTotale">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
            <button type="button" class="btn btn-outline-success btn-sm" onclick="ajouterLigneVente()">
                <i class="fas fa-plus"></i> Ajouter une ligne
            </button>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary btn-lg" id="submitVente">
            <i class="fas fa-save"></i> Enregistrer la Vente
        </button>
    </div>
</form>

<script>
    // Variables PHP vers JS
    const piecesInfo = <?= $pieces_json ?>;
    let ligneIndex = 0;
    let lignesVenteData = [];

    function ajouterLigneVente() {
        const tableBody = document.querySelector('#lignesTable tbody');
        const newRow = tableBody.insertRow();
        newRow.id = `ligne-${ligneIndex}`;
        newRow.innerHTML = `
            <td>
                <select class="form-select form-select-sm piece-select" data-index="${ligneIndex}" required>
                    <option value="" data-stock="0" data-cump="0" data-prix="0" disabled selected>Sélectionnez une pièce</option>
                    ${piecesInfo.map(p => 
                        `<option value="${p.id_piece}" data-stock="${p.quantite_stock}" data-cump="${p.cump_actuel}" data-prix="${p.prix_vente}">
                            ${p.reference} - ${p.nom_piece} (Stock: ${p.quantite_stock})
                        </option>`
                    ).join('')}
                </select>
            </td>
            <td class="stock-dispo" data-index="${ligneIndex}">0</td>
            <td><input type="number" class="form-control form-control-sm quantite-input" data-index="${ligneIndex}" min="1" value="1" required></td>
            <td class="prix-unitaire" data-index="${ligneIndex}" data-prix="0">0.00</td>
            <td class="cump-unitaire fw-bold text-info" data-index="${ligneIndex}" data-cump="0">0.00</td>
            <td class="total-ligne" data-index="${ligneIndex}">0.00</td>
            <td class="marge-ligne text-success" data-index="${ligneIndex}">0.00</td>
            <td><button type="button" class="btn btn-sm btn-danger" onclick="supprimerLigne(${ligneIndex})"><i class="fas fa-trash"></i></button></td>
        `;
        
        // Attacher les écouteurs d'événements
        document.querySelector(`#ligne-${ligneIndex} .piece-select`).addEventListener('change', updateLigne);
        document.querySelector(`#ligne-${ligneIndex} .quantite-input`).addEventListener('input', updateLigne);
        
        ligneIndex++;
    }

    function updateLigne(event) {
        const index = event.target.getAttribute('data-index');
        const row = document.getElementById(`ligne-${index}`);
        const select = row.querySelector('.piece-select');
        const option = select.options[select.selectedIndex];
        const quantiteInput = row.querySelector('.quantite-input');
        
        const pieceId = parseInt(option.value);
        const stockDispo = parseFloat(option.getAttribute('data-stock'));
        const prixUnitaire = parseFloat(option.getAttribute('data-prix'));
        const cumpUnitaire = parseFloat(option.getAttribute('data-cump'));
        const quantite = parseInt(quantiteInput.value) || 0;

        // Validation de la quantité et mise à jour des éléments visuels
        row.querySelector('.stock-dispo').textContent = stockDispo;
        row.querySelector('.prix-unitaire').textContent = prixUnitaire.toFixed(2);
        row.querySelector('.cump-unitaire').textContent = cumpUnitaire.toFixed(2);
        
        if (quantite > stockDispo) {
            alert(`Stock insuffisant. Maximum disponible : ${stockDispo}`);
            quantiteInput.value = stockDispo > 0 ? stockDispo : 0;
            // On continue avec la quantité ajustée pour mettre à jour la ligne
            quantite = parseInt(quantiteInput.value) || 0; 
        }

        const totalLigne = quantite * prixUnitaire;
        const coutTotalLigne = quantite * cumpUnitaire;
        const margeLigne = totalLigne - coutTotalLigne;

        row.querySelector('.total-ligne').textContent = totalLigne.toFixed(2);
        row.querySelector('.marge-ligne').textContent = margeLigne.toFixed(2);

        // Mettre à jour l'objet de données pour la soumission
        const ligneData = {
            piece_id: pieceId,
            quantite: quantite,
            prix_vente_unitaire: prixUnitaire,
            cump_unitaire: cumpUnitaire 
        };
        // Trouver et remplacer la ligne dans le tableau global
        const existingIndex = lignesVenteData.findIndex(item => item.index === index);
        if (existingIndex > -1) {
             lignesVenteData[existingIndex] = {...ligneData, index: index};
        } else {
             lignesVenteData.push({...ligneData, index: index});
        }
        
        recalculerTotaux();
    }
    
    function supprimerLigne(index) {
        const row = document.getElementById(`ligne-${index}`);
        if (row) {
            row.remove();
            lignesVenteData = lignesVenteData.filter(item => item.index !== String(index));
            recalculerTotaux();
        }
    }

    function recalculerTotaux() {
        let grandTotal = 0;
        let coutTotal = 0;

        lignesVenteData.forEach(ligne => {
            if (ligne.quantite > 0) {
                grandTotal += ligne.quantite * ligne.prix_vente_unitaire;
                coutTotal += ligne.quantite * ligne.cump_unitaire;
            }
        });

        const margeTotale = grandTotal - coutTotal;

        document.getElementById('grandTotal').textContent = grandTotal.toFixed(2);
        document.getElementById('margeTotale').textContent = margeTotale.toFixed(2);
    }
    
    // Soumission du formulaire
    document.getElementById('venteForm').addEventListener('submit', function(e) {
        // Enlever les propriétés d'index temporaires pour la soumission
        const finalData = lignesVenteData.map(({ index, ...rest }) => rest).filter(ligne => ligne.piece_id);

        if (finalData.length === 0) {
            alert("Veuillez ajouter au moins une pièce valide à la vente.");
            e.preventDefault();
            return;
        }

        document.getElementById('lignesVenteInput').value = JSON.stringify(finalData);
        // Laisser le formulaire soumettre normalement
    });

    // Initialiser avec une ligne vide au chargement
    document.addEventListener('DOMContentLoaded', () => {
        ajouterLigneVente();
    });

</script>

<?php include '../../includes/footer.php'; ?>
