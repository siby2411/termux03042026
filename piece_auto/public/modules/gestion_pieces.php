<?php
// /var/www/piece_auto/public/modules/gestion_pieces.php - (CORRECTION FINALE : AJOUT id_marque)

$page_title = "Gestion des Pièces (Produits)";
include '../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$message = '';

// --- FONCTIONS DE RÉCUPÉRATION (Avec ajout de get_marques) ---
function get_fournisseurs($db) {
    // Colonne nom_fournisseur utilisée
    $query = "SELECT id_fournisseur, nom_fournisseur AS nom FROM FOURNISSEURS ORDER BY nom_fournisseur";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_categories($db) {
    // Colonne nom_categorie utilisée
    $query = "SELECT id_categorie, nom_categorie AS nom FROM CATEGORIES ORDER BY nom_categorie";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_marques($db) {
    // NOUVEAU : Récupération des marques
    $query = "SELECT id_marque, nom_marque AS nom FROM MARQUES ORDER BY nom_marque";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$fournisseurs = get_fournisseurs($db);
$categories = get_categories($db);
$marques = get_marques($db); // NOUVEAU

// --- 2. GESTION DU POST (CRÉATION DE NOUVELLE PIÈCE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_piece') {
    
    // Nettoyage et validation des entrées
    $nom_piece = filter_input(INPUT_POST, 'nom_piece', FILTER_SANITIZE_STRING);
    $reference = filter_input(INPUT_POST, 'reference', FILTER_SANITIZE_STRING);
    $stock_initial = filter_input(INPUT_POST, 'stock_initial', FILTER_VALIDATE_INT) ?? 0;
    $id_fournisseur = filter_input(INPUT_POST, 'id_fournisseur', FILTER_VALIDATE_INT);
    $id_categorie = filter_input(INPUT_POST, 'id_categorie', FILTER_VALIDATE_INT);
    $id_marque = filter_input(INPUT_POST, 'id_marque', FILTER_VALIDATE_INT); // NOUVEAU
    $prix_vente = filter_input(INPUT_POST, 'prix_vente', FILTER_VALIDATE_FLOAT);
    $prix_achat = filter_input(INPUT_POST, 'prix_achat', FILTER_VALIDATE_FLOAT) ?? 0.00;
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    // Vérification des champs obligatoires (id_marque inclus)
    if (!$nom_piece || !$reference || !$id_fournisseur || !$id_categorie || !$id_marque || $prix_vente === false || $prix_vente < 0) {
        $message = '<div class="alert alert-danger">Veuillez remplir tous les champs obligatoires (marqués d\'un *).</div>';
    } else {
        try {
            $db->beginTransaction();

            // --- CALCUL CUMP ET VALEUR INITIALE ---
            $cump_initial = round($prix_achat, 2);
            $valeur_stock_total_initiale = round($stock_initial * $cump_initial, 2);

            $query = "INSERT INTO PIECES (
                nom_piece, reference, quantite_stock, id_fournisseur, id_categorie, id_marque,
                prix_vente, prix_achat, description, 
                cump_actuel, valeur_stock_total, stock_minimum, stock_securite
            ) VALUES (
                :nom_piece, :reference, :quantite_stock, :id_fournisseur, :id_categorie, :id_marque,
                :prix_vente, :prix_achat, :description, 
                :cump_actuel, :valeur_stock_total, :stock_min, :stock_sec
            )";

            $stmt = $db->prepare($query);
            $stmt->execute([
                ':nom_piece' => $nom_piece,
                ':reference' => $reference,
                ':quantite_stock' => $stock_initial,
                ':id_fournisseur' => $id_fournisseur,
                ':id_categorie' => $id_categorie,
                ':id_marque' => $id_marque, // NOUVEAU
                ':prix_vente' => $prix_vente,
                ':prix_achat' => $prix_achat,
                ':description' => $description,
                
                ':cump_actuel' => $cump_initial,
                ':valeur_stock_total' => $valeur_stock_total_initiale,
                ':stock_min' => 5, 
                ':stock_sec' => 10
            ]);

            $db->commit();
            $message = '<div class="alert alert-success">Pièce **' . htmlspecialchars($nom_piece) . '** ajoutée avec succès. CUMP initial : ' . number_format($cump_initial, 2) . ' €.</div>';

        } catch (PDOException $e) {
            $db->rollBack();
            if ($e->getCode() == 23000) {
                $message = '<div class="alert alert-danger">Erreur : La référence Fabricant **' . htmlspecialchars($reference) . '** est déjà utilisée.</div>';
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de l\'ajout de la pièce : ' . $e->getMessage() . '</div>';
            }
        }
    }
}

// --- 3. RÉCUPÉRATION DE LA LISTE DES PIÈCES ---
$query_pieces = "SELECT p.id_piece, p.nom_piece, p.reference, p.quantite_stock, p.prix_vente, p.prix_achat, 
                        p.cump_actuel, f.nom_fournisseur, c.nom_categorie, m.nom_marque
                 FROM PIECES p
                 JOIN FOURNISSEURS f ON p.id_fournisseur = f.id_fournisseur
                 JOIN CATEGORIES c ON p.id_categorie = c.id_categorie
                 JOIN MARQUES m ON p.id_marque = m.id_marque -- NOUVELLE JOINTURE
                 ORDER BY p.nom_piece";
$stmt_pieces = $db->prepare($query_pieces);
$stmt_pieces->execute();
$pieces = $stmt_pieces->fetchAll(PDO::FETCH_ASSOC);

?>

<h1><i class="fas fa-box"></i> <?= $page_title ?></h1>
<p class="lead">Gérez le référentiel des pièces détachées et initiez leur valorisation.</p>
<hr>

<div class="d-flex justify-content-between mb-3">
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPieceModal">
        <i class="fas fa-plus"></i> Nouvelle Pièce
    </button>
</div>

<?= $message ?>

<?php if (count($pieces) > 0): ?>
    <table class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>Référence</th>
                <th>Nom Pièce</th>
                <th>Marque</th>
                <th>Catégorie</th>
                <th>Fournisseur Principal</th>
                <th class="text-center">Stock</th>
                <th class="text-end">CUMP Actuel (€)</th>
                <th class="text-end">Prix Vente (€)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pieces as $p): 
                $marge_pct = ($p['prix_vente'] > 0 && $p['cump_actuel'] > 0) 
                             ? round((($p['prix_vente'] - $p['cump_actuel']) / $p['prix_vente']) * 100, 1) 
                             : 0;
            ?>
                <tr>
                    <td><?= htmlspecialchars($p['reference']) ?></td>
                    <td><?= htmlspecialchars($p['nom_piece']) ?></td>
                    <td><?= htmlspecialchars($p['nom_marque']) ?></td> <td><?= htmlspecialchars($p['nom_categorie']) ?></td>
                    <td><?= htmlspecialchars($p['nom_fournisseur']) ?></td>
                    <td class="text-center fw-bold"><?= htmlspecialchars($p['quantite_stock']) ?></td>
                    <td class="text-end fw-bold text-info"><?= number_format($p['cump_actuel'], 2) ?></td>
                    <td class="text-end"><?= number_format($p['prix_vente'], 2) ?></td>
                    <td class="text-center">
                        <span class="badge bg-<?= $marge_pct > 20 ? 'success' : ($marge_pct > 0 ? 'warning' : 'danger') ?>">
                            <?= $marge_pct ?>%
                        </span>
                    </td>
                    <td>
                        <a href="edition_piece.php?id=<?= $p['id_piece'] ?>" class="btn btn-sm btn-info disabled" title="Éditer">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="alert alert-info">Aucune pièce n'est enregistrée dans le référentiel.</div>
<?php endif; ?>

<div class="modal fade" id="addPieceModal" tabindex="-1" aria-labelledby="addPieceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_piece">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPieceModalLabel">Nouvelle Pièce</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nom_piece" class="form-label">Nom de la pièce *</label>
                            <input type="text" class="form-control" id="nom_piece" name="nom_piece" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="reference" class="form-label">Référence Fabricant *</label>
                            <input type="text" class="form-control" id="reference" name="reference" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="stock_initial" class="form-label">Stock Initial</label>
                            <input type="number" class="form-control" id="stock_initial" name="stock_initial" min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="prix_achat" class="form-label">Prix d'Achat (€)</label>
                            <input type="number" step="0.01" class="form-control" id="prix_achat" name="prix_achat" min="0" value="0.00">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="prix_vente" class="form-label">Prix de Vente (€) *</label>
                            <input type="number" step="0.01" class="form-control" id="prix_vente" name="prix_vente" min="0.01" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_fournisseur" class="form-label">Fournisseur *</label>
                            <select class="form-select" id="id_fournisseur" name="id_fournisseur" required>
                                <option value="" disabled selected>Sélectionnez un fournisseur</option>
                                <?php foreach ($fournisseurs as $f): ?>
                                    <option value="<?= $f['id_fournisseur'] ?>"><?= htmlspecialchars($f['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="id_categorie" class="form-label">Catégorie *</label>
                            <select class="form-select" id="id_categorie" name="id_categorie" required>
                                <option value="" disabled selected>Sélectionnez une catégorie</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id_categorie'] ?>"><?= htmlspecialchars($c['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div clmb-3">
                            <label for="id_marque" class="form-label">Marque du Véhicule *</label>
                            <select class="form-select" id="id_marque" name="id_marque" required>
                                <option value="" disabled selected>Sélectionnez une marque</option>
                                <?php foreach ($marques as $m): ?>
                                    <option value="<?= $m['id_marque'] ?>"><?= htmlspecialchars($m['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description (Optionnel)</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                         <i class="fas fa-plus"></i> Ajouter la Pièce
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
