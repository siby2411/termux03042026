<?php
// /produits/modifier.php
$page_title = "Modifier un Produit";
include_once __DIR__ . '/../config/db.php';

$database = new Database();
$db = $database->getConnection();
$message = '';
$produit_id = isset($_GET['id']) ? $_GET['id'] : die('ERREUR: ID de produit non spécifié.');

// --- LOGIQUE DE MODIFICATION (POST) ---
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Récupération des données POST
    $produit_id = $_POST['produit_id'] ?? $produit_id;
    $nom = $_POST['nom'] ?? '';
    $reference = $_POST['reference'] ?? '';
    $stock_actuel = intval($_POST['stock_actuel'] ?? 0);
    $prix_vente = floatval(str_replace(',', '.', $_POST['prix_vente'] ?? 0));
    $fournisseur_id = $_POST['fournisseur_id'] ?? null;
    
    // 2. Validation
    if (empty($nom) || empty($reference) || $stock_actuel < 0 || $prix_vente <= 0 || empty($fournisseur_id)) {
        $message = "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (Nom, Référence, Prix Vente, Fournisseur).</div>";
    } else {
        try {
            // 3. Préparation de la requête de mise à jour
            // NOTE: Le CUMP n'est PAS modifiable manuellement; il est mis à jour par les achats.
            $query = "UPDATE Produits 
                      SET Nom = :nom, Reference = :reference, StockActuel = :stock_actuel, 
                          PrixVente = :prix_vente, FournisseurID = :fournisseur_id
                      WHERE ProduitID = :produit_id";
            $stmt = $db->prepare($query);

            // 4. Bind et exécution
            $stmt->bindParam(':produit_id', $produit_id);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':reference', $reference);
            $stmt->bindParam(':stock_actuel', $stock_actuel);
            $stmt->bindParam(':prix_vente', $prix_vente);
            $stmt->bindParam(':fournisseur_id', $fournisseur_id);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Produit **" . htmlspecialchars($nom) . "** mis à jour avec succès !</div>";
            } else {
                $message = "<div class='alert alert-danger'>Échec de la mise à jour du produit.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Erreur SQL: " . $e->getMessage() . "</div>";
        }
    }
}

// --- LOGIQUE DE LECTURE (GET ou après POST) ---
try {
    // 1. Lire les données du produit
    $query_read = "SELECT * FROM Produits WHERE ProduitID = :id LIMIT 0,1";
    $stmt_read = $db->prepare($query_read);
    $stmt_read->bindParam(':id', $produit_id);
    $stmt_read->execute();
    $produit = $stmt_read->fetch(PDO::FETCH_ASSOC);

    if (!$produit) {
        die("<div class='alert alert-danger mt-4'>Produit non trouvé.</div>");
    }
    
    // 2. Récupérer la liste des fournisseurs pour le select
    $fournisseurs = [];
    $stmt_fourn = $db->query("SELECT FournisseurID, Nom FROM Fournisseurs ORDER BY Nom ASC");
    $fournisseurs = $stmt_fourn->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("<div class='alert alert-danger mt-4'>Erreur de récupération: " . $e->getMessage() . "</div>");
}

// Assurez-vous d'inclure le header après avoir défini $page_title
include_once __DIR__ . '/../includes/header.php';
?>

<h1 class="mt-4 text-center"><i class="fas fa-edit me-2"></i> Modifier le Produit</h1>
<p class="text-muted text-center">Mettez à jour les informations du produit **<?= htmlspecialchars($produit['Nom']) ?>**.</p>
<hr>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <?= $message ?>
        
        <div class="card shadow-lg p-4">
            <form action="modifier.php?id=<?= $produit_id ?>" method="POST">
                <input type="hidden" name="produit_id" value="<?= $produit_id ?>">
                
                <h5 class="mb-3 text-primary"><i class="fas fa-info-circle me-1"></i> Informations Générales</h5>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nom" class="form-label">Nom du Produit <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($produit['Nom']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="reference" class="form-label">Référence Produit <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="reference" name="reference" value="<?= htmlspecialchars($produit['Reference']) ?>" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="fournisseur_id" class="form-label">Fournisseur Principal <span class="text-danger">*</span></label>
                        <select class="form-select" id="fournisseur_id" name="fournisseur_id" required>
                            <option value="">Sélectionnez un fournisseur</option>
                            <?php foreach ($fournisseurs as $fourn): ?>
                                <option value="<?= $fourn['FournisseurID'] ?>" <?= ($produit['FournisseurID'] == $fourn['FournisseurID']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($fourn['Nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <h5 class="mb-3 text-success"><i class="fas fa-calculator me-1"></i> Données Financières & Stock</h5>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="stock_actuel" class="form-label">Stock Actuel (Qté)</label>
                        <input type="number" class="form-control" id="stock_actuel" name="stock_actuel" value="<?= $produit['StockActuel'] ?>" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label for="cump" class="form-label">CUMP Actuel (€)</label>
                        <input type="text" class="form-control bg-light" value="<?= number_format($produit['CUMP'], 2, ',', ' ') ?>" readonly>
                        <small class="form-text text-danger fw-bold">NON MODIFIABLE. Mis à jour par les achats.</small>
                    </div>
                    <div class="col-md-4">
                        <label for="prix_vente" class="form-label">Prix de Vente HT (€) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="prix_vente" name="prix_vente" value="<?= number_format($produit['PrixVente'], 2, ',', '') ?>" step="0.01" pattern="[0-9]+([,\.][0-9]+)?" title="Utiliser un point ou une virgule pour les décimales" required>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-sync-alt me-2"></i> Mettre à Jour le Produit</button>
                    <a href="index.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
