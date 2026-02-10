<?php
// /var/www/piece_auto/modules/gestion_pieces.php
include_once '../config/Database.php';

// Optionnel: Seuls les Stockeurs et Admins peuvent gérer les pièces
// check_role(['Stockeur', 'Admin'], 'Gestion des Pièces'); 

include '../includes/header.php';
$page_title = "Gestion du Catalogue Pièces";

$database = new Database();
$db = $database->getConnection();
$message_status = "";
$image_uploaded_path = NULL;

// --- 0. RÉCUPÉRATION DES DONNÉES DE BASE (Catégories et Marques) ---
$categories = $db->query("SELECT id_categorie, nom_categorie FROM CATEGORIES ORDER BY nom_categorie")->fetchAll(PDO::FETCH_ASSOC);
$marques = $db->query("SELECT id_marque, nom_marque, logo_url FROM MARQUES_AUTO ORDER BY nom_marque")->fetchAll(PDO::FETCH_ASSOC);


// --- 1. LOGIQUE : GESTION DE L'AJOUT DE PIÈCE (AVEC UPLOAD) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_piece') {
    // 1. GESTION DE L'UPLOAD D'IMAGE
    if (isset($_FILES['piece_image']) && $_FILES['piece_image']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['piece_image']['tmp_name'];
        $file_name = $_FILES['piece_image']['name'];
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = uniqid('piece_') . '.' . $file_extension;
        
        $upload_dir = __DIR__ . '/../public/assets/img/pieces/';
        $destination = $upload_dir . $new_file_name;
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array(mime_content_type($file_tmp), $allowed_types)) {
            if (move_uploaded_file($file_tmp, $destination)) {
                $image_uploaded_path = '/piece_auto/public/assets/img/pieces/' . $new_file_name;
            } else {
                $message_status = "<div class='alert alert-warning'>Erreur lors du déplacement du fichier. Vérifiez les permissions (chown www-data).</div>";
            }
        } else {
            $message_status = "<div class='alert alert-warning'>Type de fichier non autorisé. Seules JPG, PNG, GIF sont acceptées.</div>";
        }
    }

    // 2. ENREGISTREMENT EN BASE DE DONNÉES
    if (!isset($message_status) || strpos($message_status, 'success') === false) {
        
        $reference_sku = htmlspecialchars($_POST['reference_sku'] ?? '');
        $nom_piece = htmlspecialchars($_POST['nom_piece']);
        $prix_achat = (float)($_POST['prix_achat'] ?? 0);
        $prix_vente = (float)($_POST['prix_vente'] ?? 0);
        $id_marque = (int)($_POST['id_marque'] ?? 0);
        $modele_voiture = htmlspecialchars($_POST['modele_voiture']);
        $id_categorie = (int)($_POST['id_categorie'] ?? 0);
        
        $query = "INSERT INTO PIECES (reference_sku, nom_piece, prix_achat, prix_vente, id_marque, modele_voiture, id_categorie, image_url) 
                  VALUES (:sku, :nom, :pa, :pv, :idm, :modv, :cat_id, :img)";
        
        $stmt = $db->prepare($query);

        try {
            $stmt->execute([
                ':sku' => $reference_sku ?: NULL, 
                ':nom' => $nom_piece,
                ':pa' => $prix_achat,
                ':pv' => $prix_vente,
                ':idm' => $id_marque,
                ':modv' => $modele_voiture,
                ':cat_id' => $id_categorie,
                ':img' => $image_uploaded_path 
            ]);
            $message_status = "<div class='alert alert-success'>Pièce **" . $nom_piece . "** ajoutée avec succès. Image enregistrée: " . basename($image_uploaded_path ?: 'Aucune') . "</div>";
        } catch (PDOException $e) {
            $message_status = "<div class='alert alert-danger'>Erreur lors de l'ajout DB : " . $e->getMessage() . "</div>";
        }
    }
}

// --- 2. LOGIQUE : RÉCUPÉRATION DE LA LISTE DES PIÈCES POUR L'AFFICHAGE (avec stock) ---
$pieces = [];
$filter_marque_id = filter_input(INPUT_GET, 'id_marque', FILTER_VALIDATE_INT);
$where_clause = $filter_marque_id ? "WHERE P.id_marque = :id_marque" : "";

$query_pieces = "SELECT 
                    P.*, 
                    C.nom_categorie, 
                    MA.nom_marque, 
                    MA.logo_url,
                    -- NOUVEAU: Récupération des données de stock
                    COALESCE(S.quantite_dispo, 0) AS quantite_dispo,
                    S.emplacement
                 FROM PIECES P
                 JOIN CATEGORIES C ON P.id_categorie = C.id_categorie
                 JOIN MARQUES_AUTO MA ON P.id_marque = MA.id_marque
                 -- NOUVEAU: Utilisation de LEFT JOIN pour inclure les pièces sans stock encore enregistré
                 LEFT JOIN STOCK S ON P.id_piece = S.id_piece 
                 $where_clause
                 ORDER BY P.nom_piece LIMIT 50";

$stmt_pieces = $db->prepare($query_pieces);

if ($filter_marque_id) {
    $stmt_pieces->bindParam(':id_marque', $filter_marque_id, PDO::PARAM_INT);
}

if ($stmt_pieces->execute()) {
    $pieces = $stmt_pieces->fetchAll(PDO::FETCH_ASSOC);
}

?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-cogs"></i> Gestion du Catalogue Pièces</h2>
        <a href="#formulaire_ajout" class="btn btn-auto mb-4" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="formulaire_ajout">
            <i class="fas fa-plus"></i> Ajouter une Nouvelle Pièce
        </a>
        
        <?= $message_status ?>

        <div class="collapse mb-5" id="formulaire_ajout">
            <div class="card p-4">
                <h4 class="card-title mb-4">Fiche Technique de la Pièce</h4>
                <form method="POST" action="gestion_pieces.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_piece">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                     <label for="nom_piece" class="form-label">Nom de la Pièce</label>
                            <input type="text" class="form-control" id="nom_piece" name="nom_piece" required>
                        </div>
                        <div class="col-md-6">
                            <label for="reference_sku" class="form-label">Référence SKU (Laissez vide pour auto-génération)</label>
                            <input type="text" class="form-control" id="reference_sku" name="reference_sku">
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3 text-secondary"><i class="fas fa-tags"></i> Tarification et Classification</h5>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="prix_achat" class="form-label">Prix d'Achat (€)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="prix_achat" name="prix_achat" required>
                        </div>
                        <div class="col-md-4">
                            <label for="prix_vente" class="form-label">Prix de Vente Conseillé (€)</label>
                            <input type="number" step="0.01" min="0" class="form-control" id="prix_vente" name="prix_vente" required>
                        </div>
                        <div class="col-md-4">
                            <label for="id_categorie" class="form-label">Catégorie</label>
                            <select id="id_categorie" name="id_categorie" class="form-select" required>
                                <option value="">Choisir...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id_categorie'] ?>"><?= $cat['nom_categorie'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <h5 class="mt-4 mb-3 text-secondary"><i class="fas fa-camera"></i> Image et Compatibilité</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="piece_image" class="form-label">Image de la Pièce</label>
                            <input class="form-control" type="file" id="piece_image" name="piece_image" accept="image/png, image/jpeg, image/gif">
                        </div>
                        <div class="col-md-6">
                            <label for="modele_voiture" class="form-label">Modèle(s) compatible(s)</label>
                            <input type="text" class="form-control" id="modele_voiture" name="modele_voiture" required>
                        </div>
                    </div>
                    <div class="row g-3 mt-3">
                        <div class="col-12">
                            <label for="id_marque" class="form-label">Marque de la Pièce (Fabricant)</label>
                            <select id="id_marque" name="id_marque" class="form-select" required>
                                <option value="">Choisir la Marque...</option>
                                <?php foreach ($marques as $marque): ?>
                                    <option value="<?= $marque['id_marque'] ?>"><?= $marque['nom_marque'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success mt-4"><i class="fas fa-save"></i> Enregistrer la Pièce</button>
                    <a href="#" class="btn btn-outline-secondary mt-4" data-bs-toggle="collapse" data-bs-target="#formulaire_ajout">Annuler</a>
                </form>
            </div>
        </div>

        <div class="card p-4">
            <h4 class="card-title mb-4">Catalogue (<?= count($pieces) ?> Pièces)</h4>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Image</th>
                            <th>Référence / SKU</th>
                            <th>Nom Pièce</th>
                            <th>Marque</th>
                            <th>Stock</th> <th>P. Vente (€)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pieces)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Aucune pièce enregistrée pour le moment.</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $seuil_alerte = 20; // Définir le seuil d'alerte bas
                            foreach ($pieces as $piece): 
                                $quantite = (int)$piece['quantite_dispo'];
                                
                                if ($quantite == 0) {
                                    $stock_class = 'text-danger fw-bold';
                                    $stock_icon = '<i class="fas fa-times-circle me-1"></i> Rupture';
                                } elseif ($quantite <= $seuil_alerte) {
                                    $stock_class = 'text-warning fw-bold';
                                    $stock_icon = '<i class="fas fa-exclamation-triangle me-1"></i> Alerte';
                                } else {
                                    $stock_class = 'text-success';
                                    $stock_icon = '';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($piece['image_url'])): ?>
                                            <img src="<?= htmlspecialchars($piece['image_url']) ?>" alt="<?= htmlspecialchars($piece['nom_piece']) ?>" style="height: 50px; width: auto; border: 1px solid #ddd;">
                                        <?php else: ?>
                                            <i class="fas fa-image text-muted" style="font-size: 24px;"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold"><?= htmlspecialchars($piece['reference_sku']) ?></td>
                                    <td><?= htmlspecialchars($piece['nom_piece']) ?></td>
                                    <td>
                                        <?php if (!empty($piece['logo_url'])): ?>
                                            <img src="<?= htmlspecialchars($piece['logo_url']) ?>" alt="<?= htmlspecialchars($piece['nom_marque']) ?>" style="height: 20px; margin-right: 5px;">
                                        <?php endif; ?>
                                        <?= htmlspecialchars($piece['nom_marque']) ?>
                                    </td>
                                    <td class="<?= $stock_class ?>">
                                        <?= $quantite ?>
                                        <span class="small"><?= $stock_icon ?></span>
                                        <?php if (!empty($piece['emplacement'])): ?>
                                            <small class="d-block text-muted" style="font-size: 0.75rem;">(Loc: <?= htmlspecialchars($piece['emplacement']) ?>)</small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-success fw-bold"><?= number_format($piece['prix_vente'], 2, ',', ' ') ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php 
include '../includes/footer.php'; 
?>
