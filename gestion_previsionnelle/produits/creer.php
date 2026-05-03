<?php
// /produits/creer.php
$page_title = "Ajouter un Nouveau Produit";
include_once __DIR__ . '/../config/db.php';
include_once __DIR__ . '/../includes/header.php'; 

$database = new Database();
$db = $database->getConnection();
$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Récupération des données
    $nom = $_POST['nom'] ?? '';
    $reference = $_POST['reference'] ?? '';
    $stock_actuel = intval($_POST['stock_actuel'] ?? 0);
    $cump = floatval(str_replace(',', '.', $_POST['cump'] ?? 0)); // Gère le format décimal , ou .
    $prix_vente = floatval(str_replace(',', '.', $_POST['prix_vente'] ?? 0));
    $fournisseur_id = $_POST['fournisseur_id'] ?? null;
    
    // 2. Validation
    if (empty($nom) || empty($reference) || $stock_actuel < 0 || $cump <= 0 || $prix_vente <= 0 || empty($fournisseur_id)) {
        $message = "<div class='alert alert-danger'>Veuillez remplir tous les champs obligatoires (Nom, Référence, Stock, CUMP, Prix Vente, Fournisseur) correctement.</div>";
    } else {
        try {
            // 3. Préparation de la requête
            $query = "INSERT INTO Produits (Nom, Reference, StockActuel, CUMP, PrixVente, FournisseurID) 
                      VALUES (:nom, :reference, :stock_actuel, :cump, :prix_vente, :fournisseur_id)";
            $stmt = $db->prepare($query);

            // 4. Bind et exécution
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':reference', $reference);
            $stmt->bindParam(':stock_actuel', $stock_actuel);
            $stmt->bindParam(':cump', $cump);
            $stmt->bindParam(':prix_vente', $prix_vente);
            $stmt->bindParam(':fournisseur_id', $fournisseur_id);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Produit **" . htmlspecialchars($nom) . "** créé avec succès !</div>";
                // Réinitialiser les variables après succès
                $nom = $reference = '';
                $stock_actuel = $cump = $prix_vente = 0;
            } else {
                $message = "<div class='alert alert-danger'>Échec de la création du produit.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='alert alert-danger'>Erreur SQL: " . $e->getMessage() . "</div>";
        }
    }
}

// Récupérer la liste des fournisseurs pour le select
$fournisseurs = [];
try {
    $stmt_fourn = $db->query("SELECT FournisseurID, Nom FROM Fournisseurs ORDER BY Nom ASC");
    $fournisseurs = $stmt_fourn->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message .= "<div class='alert alert-warning'>Erreur lors du chargement des fournisseurs: " . $e->getMessage() . "</div>";
}
?>

<h1 class="mt-4 text-center"><i class="fas fa-plus-circle me-2"></i> Ajouter un Produit</h1>
<p class="text-muted text-center">Enregistrez les informations de base, le stock initial et le coût unitaire de référence.</p>
<hr>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <?= $message ?>
        
        <div class="card shadow-lg p-4">
            <form action="creer.php" method="POST">
                
                <h5 class="mb-3 text-primary"><i class="fas fa-info-circle me-1"></i> Informations Générales</h5>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="nom" class="form-label">Nom du Produit <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($nom ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="reference" class="form-label">Référence Produit <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="reference" name="reference" value="<?= htmlspecialchars($reference ?? '') ?>" required>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-12">
                        <label for="fournisseur_id" class="form-label">Fournisseur Principal <span class="text-danger">*</span></label>
                        <select class="form-select" id="fournisseur_id" name="fournisseur_id" required>
                            <option value="">Sélectionnez un fournisseur</option>
                            <?php foreach ($fournisseurs as $fourn): ?>
                                <option value="<?= $fourn['FournisseurID'] ?>"><?= htmlspecialchars($fourn['Nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($fournisseurs)): ?>
                            <small class="text-warning">Veuillez <a href="/gestion_previsionnelle/fournisseurs/creer.php">créer un fournisseur</a> d'abord.</small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <h5 class="mb-3 text-success"><i class="fas fa-calculator me-1"></i> Données Financières & Stock</h5>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="stock_actuel" class="form-label">Stock Initial <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="stock_actuel" name="stock_actuel" value="<?= $stock_actuel ?? 0 ?>" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label for="cump" class="form-label">CUMP Initial (€) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="cump" name="cump" value="<?= $cump ?? 0.00 ?>" step="0.01" pattern="[0-9]+([,\.][0-9]+)?" title="Utiliser un point ou une virgule pour les décimales" required>
                        <small class="form-text text-muted">Coût Unitaire Moyen Pondéré.</small>
                    </div>
                    <div class="col-md-4">
                        <label for="prix_vente" class="form-label">Prix de Vente HT (€) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="prix_vente" name="prix_vente" value="<?= $prix_vente ?? 0.00 ?>" step="0.01" pattern="[0-9]+([,\.][0-9]+)?" title="Utiliser un point ou une virgule pour les décimales" required>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i> Enregistrer le Produit</button>
                    <a href="index.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
