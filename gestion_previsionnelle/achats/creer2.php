<?php
// /achats/creer.php
$page_title = "Enregistrer un Nouvel Achat";
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php'; 

$database = new Database();
$db = $database->getConnection();
$message = '';
$fournisseurs = [];
$produits = [];

// 1. Charger les listes déroulantes
try {
    $stmt_fourn = $db->query("SELECT FournisseurID, Nom FROM Fournisseurs ORDER BY Nom ASC");
    $fournisseurs = $stmt_fourn->fetchAll(PDO::FETCH_ASSOC);

    $stmt_prod = $db->query("SELECT ProduitID, Nom, Reference, CUMP FROM Produits ORDER BY Nom ASC");
    $produits = $stmt_prod->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message .= "<div class='alert alert-danger'>Erreur de chargement des listes: " . $e->getMessage() . "</div>";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 2. Récupération et conversion des données
    $produit_id = $_POST['produit_id'] ?? null;
    $fournisseur_id = $_POST['fournisseur_id'] ?? null;
    $date_achat = $_POST['date_achat'] ?? date('Y-m-d');
    $reference_facture = $_POST['reference_facture'] ?? '';
    $quantite = intval($_POST['quantite'] ?? 0);
    $prix_unitaire_achat = floatval(str_replace(',', '.', $_POST['prix_unitaire_achat'] ?? 0));
    $montant_total = $quantite * $prix_unitaire_achat;

    // 3. Validation
    if (!$produit_id || !$fournisseur_id || $quantite <= 0 || $prix_unitaire_achat <= 0) {
        $message = "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (Produit, Fournisseur, Quantité, Prix Unitaire).</div>";
    } else {
        $db->beginTransaction();
        try {
            // A. Insérer l'achat
            $query_achat = "INSERT INTO Achats (ProduitID, FournisseurID, DateAchat, ReferenceFacture, Quantite, PrixUnitaireAchat, MontantTotal) 
                            VALUES (:pid, :fid, :date, :ref, :qty, :prix, :total)";
            $stmt_achat = $db->prepare($query_achat);
            $stmt_achat->bindParam(':pid', $produit_id);
            $stmt_achat->bindParam(':fid', $fournisseur_id);
            $stmt_achat->bindParam(':date', $date_achat);
            $stmt_achat->bindParam(':ref', $reference_facture);
            $stmt_achat->bindParam(':qty', $quantite);
            $stmt_achat->bindParam(':prix', $prix_unitaire_achat);
            $stmt_achat->bindParam(':total', $montant_total);
            $stmt_achat->execute();
            
            // B. Mise à jour du Produit (Stock et CUMP)
            $query_produit = "SELECT StockActuel, CUMP FROM Produits WHERE ProduitID = :pid FOR UPDATE";
            $stmt_produit = $db->prepare($query_produit);
            $stmt_produit->bindParam(':pid', $produit_id);
            $stmt_produit->execute();
            $data_produit = $stmt_produit->fetch(PDO::FETCH_ASSOC);

            $stock_ancien = $data_produit['StockActuel'];
            $cump_ancien = $data_produit['CUMP'];

            // Calcul du Nouveau CUMP
            $stock_nouveau = $stock_ancien + $quantite;
            $cout_total_ancien = $stock_ancien * $cump_ancien;
            $cout_total_nouvel_achat = $quantite * $prix_unitaire_achat;
            
            if ($stock_nouveau > 0) {
                $cump_nouveau = ($cout_total_ancien + $cout_total_nouvel_achat) / $stock_nouveau;
            } else {
                $cump_nouveau = $prix_unitaire_achat; // Ne devrait pas arriver, mais sécurité
            }
            
            $query_update = "UPDATE Produits SET StockActuel = :s_nouveau, CUMP = :c_nouveau WHERE ProduitID = :pid";
            $stmt_update = $db->prepare($query_update);
            $stmt_update->bindParam(':s_nouveau', $stock_nouveau);
            $stmt_update->bindParam(':c_nouveau', $cump_nouveau);
            $stmt_update->bindParam(':pid', $produit_id);
            $stmt_update->execute();

            $db->commit();
            $message = "<div class='alert alert-success'>Achat enregistré. Stock mis à jour de **$stock_ancien** à **$stock_nouveau** et CUMP mis à jour à **" . number_format($cump_nouveau, 2, ',', ' ') . " €** !</div>";
        } catch (PDOException $e) {
            $db->rollBack();
            $message = "<div class='alert alert-danger'>Erreur SQL lors de l'achat et mise à jour du stock: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<h1 class="mt-4 text-center"><i class="fas fa-truck me-2"></i> Enregistrer un Achat (Entrée de Stock)</h1>
<p class="text-muted text-center">Formulaire d'enregistrement des factures d'achat et de mise à jour automatique du stock et du CUMP.</p>
<hr>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <?= $message ?>
        
        <div class="card shadow-lg p-4">
            <form action="creer.php" method="POST">
                
                <h5 class="mb-3 text-primary"><i class="fas fa-file-invoice me-1"></i> Détails de l'Achat</h5>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="date_achat" class="form-label">Date de l'Achat <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date_achat" name="date_achat" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="reference_facture" class="form-label">Référence Facture Fournisseur</label>
                        <input type="text" class="form-control" id="reference_facture" name="reference_facture" placeholder="FAC-F001">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="fournisseur_id" class="form-label">Fournisseur <span class="text-danger">*</span></label>
                        <select class="form-select" id="fournisseur_id" name="fournisseur_id" required>
                            <option value="">Sélectionnez un fournisseur</option>
                            <?php foreach ($fournisseurs as $fourn): ?>
                                <option value="<?= $fourn['FournisseurID'] ?>"><?= htmlspecialchars($fourn['Nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <h5 class="mb-3 text-success"><i class="fas fa-boxes me-1"></i> Produit et Quantités</h5>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="produit_id" class="form-label">Produit Acheté <span class="text-danger">*</span></label>
                        <select class="form-select" id="produit_id" name="produit_id" required>
                            <option value="">Sélectionnez un produit</option>
                            <?php foreach ($produits as $prod): ?>
                                <option value="<?= $prod['ProduitID'] ?>">
                                    <?= htmlspecialchars($prod['Nom']) ?> (Réf: <?= htmlspecialchars($prod['Reference']) ?> | CUMP Actuel: <?= number_format($prod['CUMP'], 2, ',', ' ') ?> €)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="quantite" class="form-label">Quantité Achetée <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="quantite" name="quantite" min="1" required>
                    </div>
                    <div class="col-md-6">
                        <label for="prix_unitaire_achat" class="form-label">Prix Achat Unitaire HT (€) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="prix_unitaire_achat" name="prix_unitaire_achat" step="0.01" pattern="[0-9]+([,\.][0-9]+)?" title="Utiliser un point ou une virgule pour les décimales" required>
                        <small class="form-text text-muted">Ce prix servira au calcul du nouveau CUMP.</small>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-check-circle me-2"></i> Valider l'Achat & Mettre à Jour le Stock</button>
                    <a href="index.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
