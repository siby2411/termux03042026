<?php
$page_title = "Modifier la Commande";
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

$id = $_GET['id'] ?? die("<div class='alert alert-danger'>ID de commande manquant.</div>");

// Récupération des listes déroulantes
$clients = $db->query("SELECT ClientID, Nom FROM Clients ORDER BY Nom")->fetchAll(PDO::FETCH_ASSOC);
$produits = $db->query("SELECT ProduitID, Nom, PrixVente, StockActuel FROM Produits ORDER BY Nom")->fetchAll(PDO::FETCH_ASSOC);

// --- 1. Logique de MISE À JOUR (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client_id = $_POST['client_id'] ?? null;
    $date_commande = $_POST['date_commande'] ?? date("Y-m-d");
    $statut = $_POST['statut'] ?? 'EN_COURS';
    $produits_ids = $_POST['produit_id'] ?? [];
    $quantites = $_POST['quantite'] ?? [];
    $prix_ventes = $_POST['prix_vente'] ?? [];
    $detail_ids = $_POST['detail_id'] ?? []; // IDs existants pour mise à jour/suppression

    try {
        $db->beginTransaction();
        $montant_total = 0;

        // A. Mise à jour de la Commande Master
        $query_master = "UPDATE Commandes SET ClientID = :client_id, DateCommande = :date_commande, Statut = :statut WHERE CommandeID = :id";
        $stmt_master = $db->prepare($query_master);
        $stmt_master->bindParam(':client_id', $client_id);
        $stmt_master->bindParam(':date_commande', $date_commande);
        $stmt_master->bindParam(':statut', $statut);
        $stmt_master->bindParam(':id', $id);
        $stmt_master->execute();

        // B. Récupération des IDs de détails EXISTANTS pour gérer les suppressions
        $existing_details = $db->query("SELECT DetailID FROM DetailsCommande WHERE CommandeID = {$id}")->fetchAll(PDO::FETCHCOLUMN);
        $keep_details_ids = [];

        // C. Traitement des lignes de Détail (Mise à jour ou Création)
        $query_update_detail = "UPDATE DetailsCommande SET ProduitID = :produit_id, Quantite = :quantite, PrixUnitaireVente = :prix_vente WHERE DetailID = :detail_id";
        $query_insert_detail = "INSERT INTO DetailsCommande (CommandeID, ProduitID, Quantite, PrixUnitaireVente) VALUES (:commande_id, :produit_id, :quantite, :prix_vente)";

        for ($i = 0; $i < count($produits_ids); $i++) {
            $produit_id = $produits_ids[$i];
            $quantite = $quantites[$i];
            $prix_vente = $prix_ventes[$i];
            $detail_id = $detail_ids[$i] ?? null;

            if ($produit_id && $quantite > 0 && $prix_vente > 0) {
                $montant_ligne = $quantite * $prix_vente;
                $montant_total += $montant_ligne;

                if ($detail_id && in_array($detail_id, $existing_details)) {
                    // Mise à jour de ligne existante
                    $stmt = $db->prepare($query_update_detail);
                    $stmt->bindParam(':detail_id', $detail_id);
                    $keep_details_ids[] = $detail_id;
                } else {
                    // Création de nouvelle ligne
                    $stmt = $db->prepare($query_insert_detail);
                    $stmt->bindParam(':commande_id', $id);
                }
                
                $stmt->bindParam(':produit_id', $produit_id);
                $stmt->bindParam(':quantite', $quantite);
                $stmt->bindParam(':prix_vente', $prix_vente);
                $stmt->execute();
            }
        }

        // D. Suppression des lignes retirées du formulaire
        $delete_ids = array_diff($existing_details, $keep_details_ids);
        if (!empty($delete_ids)) {
            $placeholders = implode(',', array_fill(0, count($delete_ids), '?'));
            $query_delete = "DELETE FROM DetailsCommande WHERE DetailID IN ({$placeholders})";
            $stmt_delete = $db->prepare($query_delete);
            // Liaison des paramètres
            foreach ($delete_ids as $k => $id_to_delete) {
                $stmt_delete->bindParam(($k + 1), $id_to_delete, PDO::PARAM_INT);
            }
            $stmt_delete->execute();
        }

        // E. Mise à jour finale du Montant Total de la Commande Master
        $query_update_total = "UPDATE Commandes SET MontantTotal = :montant WHERE CommandeID = :id";
        $stmt_update_total = $db->prepare($query_update_total);
        $stmt_update_total->bindParam(':montant', $montant_total);
        $stmt_update_total->bindParam(':id', $id);
        $stmt_update_total->execute();
        
        // F. (CRITIQUE) Déclenchement de la logique Stock/Comptabilité si le statut est "LIVREE"

        $db->commit();
        $message = "<div class='alert alert-success'>Commande #{$id} mise à jour avec succès.</div>";
        header("Refresh: 2; URL=details.php?id={$id}");

    } catch(PDOException $e) {
        $db->rollBack();
        $message = "<div class='alert alert-danger'>Erreur de transaction : " . $e->getMessage() . "</div>";
    }
}

// --- 2. Logique de LECTURE (GET) ---
// Récupérer la Commande Master et ses Détails pour pré-remplir le formulaire
try {
    $query_master = "SELECT CommandeID, ClientID, DateCommande, MontantTotal, Statut FROM Commandes WHERE CommandeID = ?";
    $stmt_master = $db->prepare($query_master);
    $stmt_master->bindParam(1, $id);
    $stmt_master->execute();
    $commande = $stmt_master->fetch(PDO::FETCH_ASSOC);

    if (!$commande) {
        die("<div class='alert alert-danger'>Commande non trouvée.</div>");
    }
    
    $query_details = "SELECT DetailID, ProduitID, Quantite, PrixUnitaireVente FROM DetailsCommande WHERE CommandeID = ?";
    $stmt_details = $db->prepare($query_details);
    $stmt_details->bindParam(1, $id);
    $stmt_details->execute();
    $details = $stmt_details->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("<div class='alert alert-danger'>Erreur de lecture: " . $e->getMessage() . "</div>");
}
?>

<h1 class="mt-4"><i class="fas fa-edit me-2"></i> Modifier la Commande #<?= htmlspecialchars($commande['CommandeID']) ?></h1>
<p class="text-muted">Mise à jour des informations et des lignes de commande.</p>
<hr>
<?= $message ?>

<form method="POST">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">Informations Principales</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="client_id" class="form-label">Client</label>
                    <select class="form-select" name="client_id" required>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?= htmlspecialchars($client['ClientID']) ?>" <?= $commande['ClientID'] == $client['ClientID'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['Nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="date_commande" class="form-label">Date de la Commande</label>
                    <input type="date" class="form-control" name="date_commande" value="<?= htmlspecialchars($commande['DateCommande']) ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="statut" class="form-label">Statut</label>
                    <select class="form-select" name="statut">
                        <option value="EN_COURS" <?= $commande['Statut'] == 'EN_COURS' ? 'selected' : '' ?>>EN_COURS</option>
                        <option value="LIVREE" <?= $commande['Statut'] == 'LIVREE' ? 'selected' : '' ?>>LIVREE</option>
                        <option value="ANNULEE" <?= $commande['Statut'] == 'ANNULEE' ? 'selected' : '' ?>>ANNULEE</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-secondary text-white">Détails des Produits Commandés</div>
        <div class="card-body">
            <div id="ligne_produit_container">
                <?php $is_first = true; ?>
                <?php foreach ($details as $detail): ?>
                <div class="row ligne_produit mb-3 border-bottom pb-3">
                    <input type="hidden" name="detail_id[]" value="<?= htmlspecialchars($detail['DetailID']) ?>">
                    
                    <div class="col-md-5">
                        <label class="form-label">Produit</label>
                        <select class="form-select select-produit" name="produit_id[]" required>
                            <option value="">Sélectionner un produit...</option>
                            <?php foreach ($produits as $produit): ?>
                                <option 
                                    value="<?= htmlspecialchars($produit['ProduitID']) ?>" 
                                    data-prix="<?= htmlspecialchars($produit['PrixVente']) ?>"
                                    data-stock="<?= htmlspecialchars($produit['StockActuel']) ?>"
                                    <?= $detail['ProduitID'] == $produit['ProduitID'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($produit['Nom']) ?> (Stock: <?= $produit['StockActuel'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Quantité</label>
                        <input type="number" class="form-control input-quantite" name="quantite[]" value="<?= htmlspecialchars($detail['Quantite']) ?>" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Prix Unitaire Vente</label>
                        <input type="number" step="0.01" class="form-control input-prix" name="prix_vente[]" value="<?= htmlspecialchars($detail['PrixUnitaireVente']) ?>" required>
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm w-100 btn-remove-ligne" style="height: 38px;" <?= $is_first ? 'disabled' : '' ?>><i class="fas fa-times"></i></button>
                    </div>
                </div>
                <?php $is_first = false; ?>
                <?php endforeach; ?>
                
                <?php if (empty($details)): // Si aucune ligne n'existe, afficher une ligne vide pour l'ajout ?>
                    <div class="row ligne_produit mb-3 border-bottom pb-3">
                        <input type="hidden" name="detail_id[]" value="">
                        <div class="col-md-5">... (Structure similaire à ci-dessus) ...</div>
                        <div class="col-md-3">...</div>
                        <div class="col-md-3">...</div>
                        <div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-danger btn-sm w-100 btn-remove-ligne" style="height: 38px;" disabled><i class="fas fa-times"></i></button></div>
                    </div>
                <?php endif; ?>

            </div>
            
            <button type="button" id="btn_add_ligne" class="btn btn-sm btn-outline-primary"><i class="fas fa-plus me-2"></i> Ajouter une ligne produit</button>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <a href="details.php?id=<?= $id ?>" class="btn btn-secondary"><i class="fas fa-chevron-left me-2"></i> Annuler / Retour</a>
        <button type="submit" class="btn btn-info btn-lg"><i class="fas fa-sync me-2"></i> Sauvegarder les Modifications</button>
    </div>
</form>

<script>
// NOTE: Le code JavaScript pour ajouter/retirer des lignes et mettre à jour le prix doit être copié/réutilisé de creer.php ici.
document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('ligne_produit_container');
    const getProductOptions = () => {
        // Pour cloner, nous avons besoin d'une ligne modèle si nous n'avons pas d'éléments
        const selectHtml = `
            <select class="form-select select-produit" name="produit_id[]" required>
                <option value="">Sélectionner un produit...</option>
                <?php foreach ($produits as $produit): ?>
                    <option 
                        value="<?= htmlspecialchars($produit['ProduitID']) ?>" 
                        data-prix="<?= htmlspecialchars($produit['PrixVente']) ?>"
                        data-stock="<?= htmlspecialchars($produit['StockActuel']) ?>">
                        <?= htmlspecialchars($produit['Nom']) ?> (Stock: <?= $produit['StockActuel'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>`;
        return selectHtml;
    };
    
    function createNewLigne() {
        const newLine = document.createElement('div');
        newLine.className = 'row ligne_produit mb-3 border-bottom pb-3';
        newLine.innerHTML = `
            <input type="hidden" name="detail_id[]" value="">
            <div class="col-md-5">
                <label class="form-label">Produit</label>
                ${getProductOptions()}
            </div>
            <div class="col-md-3">
                <label class="form-label">Quantité</label>
                <input type="number" class="form-control input-quantite" name="quantite[]" value="1" min="1" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Prix Unitaire Vente</label>
                <input type="number" step="0.01" class="form-control input-prix" name="prix_vente[]" value="" required>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm w-100 btn-remove-ligne" style="height: 38px;"><i class="fas fa-times"></i></button>
            </div>
        `;
        addEventListenersToLigne(newLine);
        container.appendChild(newLine);
    }
    
    function updatePrice(ligne) {
        const select = ligne.querySelector('.select-produit');
        const prixInput = ligne.querySelector('.input-prix');
        
        const selectedOption = select.options[select.selectedIndex];
        const prixVente = selectedOption.dataset.prix;
        
        if (prixVente) {
            prixInput.value = prixVente;
        }
    }
    
    function addEventListenersToLigne(ligne) {
        ligne.querySelector('.select-produit').addEventListener('change', (e) => updatePrice(ligne));
        ligne.querySelector('.btn-remove-ligne').addEventListener('click', () => ligne.remove());
        updatePrice(ligne); // Initialisation
    }

    // Attacher les événements à toutes les lignes existantes au chargement
    container.querySelectorAll('.ligne_produit').forEach(addEventListenersToLigne);

    document.getElementById('btn_add_ligne').addEventListener('click', createNewLigne);
});
</script>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
