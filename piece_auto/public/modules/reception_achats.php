<?php
// /var/www/piece_auto/public/modules/reception_achats.php
// Enregistrement de la réception de pièces d'un fournisseur (augmentation du stock et calcul du CUMP).

$page_title = "Enregistrement de Réception d'Achat";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

// --- FONCTIONS DE LECTURE ---

// Récupérer la liste des fournisseurs
function get_fournisseurs($db) {
    $query = "SELECT id_fournisseur, nom_fournisseur FROM FOURNISSEURS ORDER BY nom_fournisseur ASC";
    $stmt = $db->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Récupérer la liste des pièces
function get_pieces($db) {
    $query = "SELECT id_piece, reference, nom_piece, stock_actuel, cout_unitaire_moyen_pondere FROM PIECES ORDER BY reference ASC";
    $stmt = $db->query($query);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// --- LOGIQUE DE TRAITEMENT (MISE À JOUR DU STOCK ET DU CUMP) ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enregistrer_reception'])) {
    
    $id_fournisseur = (int)$_POST['id_fournisseur'];
    $id_piece = (int)$_POST['id_piece'];
    $quantite_recue = (int)$_POST['quantite_recue'];
    $prix_achat_unitaire = (float)$_POST['prix_achat_unitaire'];
    $date_reception = date('Y-m-d H:i:s'); // Date/heure actuelle
    $utilisateur_id = $_SESSION['user_id'] ?? null; // Récupération de l'utilisateur connecté

    if ($id_fournisseur <= 0 || $id_piece <= 0 || $quantite_recue <= 0 || $prix_achat_unitaire < 0) {
        $message = '<div class="alert alert-danger">Veuillez remplir tous les champs obligatoires (Fournisseur, Pièce, Quantité > 0, Prix Achat).</div>';
    } else {
        try {
            $db->beginTransaction();

            // 1. Récupérer les données de stock et CUMP actuels
            $query_current = "SELECT stock_actuel, cout_unitaire_moyen_pondere FROM PIECES WHERE id_piece = :id_piece FOR UPDATE";
            $stmt_current = $db->prepare($query_current);
            $stmt_current->execute([':id_piece' => $id_piece]);
            $current_data = $stmt_current->fetch(PDO::FETCH_ASSOC);

            if (!$current_data) {
                throw new Exception("Pièce introuvable.");
            }

            $ancien_stock = $current_data['stock_actuel'];
            $ancien_cump = $current_data['cout_unitaire_moyen_pondere'];
            
            // --- Calcul du CUMP (Coût Unitaire Moyen Pondéré) ---
            $stock_total_ancien = $ancien_stock * $ancien_cump;
            $cout_total_nouvel_achat = $quantite_recue * $prix_achat_unitaire;
            $nouveau_stock = $ancien_stock + $quantite_recue;
            
            if ($nouveau_stock > 0) {
                $nouveau_cump = ($stock_total_ancien + $cout_total_nouvel_achat) / $nouveau_stock;
            } else {
                $nouveau_cump = $ancien_cump;
            }

            // 2. Mise à jour du stock et du CUMP dans la table PIECES
            $query_update = "UPDATE PIECES SET 
                             stock_actuel = :nouveau_stock, 
                             cout_unitaire_moyen_pondere = :nouveau_cump 
                             WHERE id_piece = :id_piece";
            $stmt_update = $db->prepare($query_update);
            $stmt_update->execute([
                ':nouveau_stock' => $nouveau_stock,
                ':nouveau_cump' => $nouveau_cump,
                ':id_piece' => $id_piece
            ]);

            // 3. ENREGISTREMENT DU MOUVEMENT DANS LA NOUVELLE TABLE
            $query_mvt = "
                INSERT INTO MOUVEMENTS_STOCK 
                (id_piece, type_mouvement, quantite_impact, stock_avant_mouvement, stock_apres_mouvement, prix_unitaire, utilisateur_id)
                VALUES 
                (:id_piece, 'Entrée Achat', :quantite_impact, :stock_avant, :stock_apres, :prix_unitaire, :utilisateur_id)
            ";
            $stmt_mvt = $db->prepare($query_mvt);
            $stmt_mvt->execute([
                ':id_piece' => $id_piece,
                ':quantite_impact' => $quantite_recue,
                ':stock_avant' => $ancien_stock,
                ':stock_apres' => $nouveau_stock,
                ':prix_unitaire' => $prix_achat_unitaire,
                ':utilisateur_id' => $utilisateur_id
            ]);


            $db->commit();
            $message = '<div class="alert alert-success">Réception enregistrée avec succès. Le stock est passé de ' . $ancien_stock . ' à ' . $nouveau_stock . '. Nouveau CUMP: ' . number_format($nouveau_cump, 2) . ' €. (Mouvement de stock tracé)</div>';

        } catch (Exception $e) {
            $db->rollBack();
            $message = '<div class="alert alert-danger">Erreur critique lors de l\'enregistrement : ' . $e->getMessage() . '</div>';
        }
    }
}

$fournisseurs = get_fournisseurs($db);
$pieces = get_pieces($db);

?>

<h1><i class="fas fa-dolly"></i> <?= $page_title ?></h1>
<p class="lead">Enregistrez la réception de nouvelles pièces pour mettre à jour le niveau de stock et le Coût Unitaire Moyen Pondéré (CUMP).</p>
<hr>

<?= $message ?>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-plus-circle"></i> Enregistrer une nouvelle réception
    </div>
    <div class="card-body">
        <form method="POST">
            
            <div class="mb-3">
                <label for="id_fournisseur" class="form-label">Fournisseur <span class="text-danger">*</span></label>
                <select class="form-select" id="id_fournisseur" name="id_fournisseur" required>
                    <option value="">Sélectionnez un fournisseur</option>
                    <?php foreach ($fournisseurs as $f): ?>
                        <option value="<?= $f['id_fournisseur'] ?>">
                            <?= htmlspecialchars($f['nom_fournisseur']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="id_piece" class="form-label">Pièce Reçue <span class="text-danger">*</span></label>
                <select class="form-select" id="id_piece" name="id_piece" required>
                    <option value="">Sélectionnez la pièce</option>
                    <?php foreach ($pieces as $p): ?>
                        <option value="<?= $p['id_piece'] ?>">
                            <?= htmlspecialchars($p['reference']) ?> - <?= htmlspecialchars($p['nom_piece']) ?> (Stock actuel: <?= $p['stock_actuel'] ?>, CUMP: <?= number_format($p['cout_unitaire_moyen_pondere'], 2) ?> €)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="quantite_recue" class="form-label">Quantité Reçue <span class="text-danger">*</span></label>
                    <input type="number" step="1" min="1" class="form-control" id="quantite_recue" name="quantite_recue" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="prix_achat_unitaire" class="form-label">Prix Achat Unitaire HT (€) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" class="form-control" id="prix_achat_unitaire" name="prix_achat_unitaire" required>
                </div>
            </div>

            <button type="submit" name="enregistrer_reception" class="btn btn-success">
                <i class="fas fa-save"></i> Enregistrer la Réception et Mettre à Jour le Stock
            </button>
        </form>
    </div>
</div>

<h3>Rappel : Formule du CUMP</h3>
$$ \text{Nouveau CUMP} = \frac{(\text{Stock Ancien} \times \text{CUMP Ancien}) + (\text{Qté Achetée} \times \text{Prix Achat})}{\text{Stock Ancien} + \text{Qté Achetée}} $$


<?php include '../../includes/footer.php'; ?>
