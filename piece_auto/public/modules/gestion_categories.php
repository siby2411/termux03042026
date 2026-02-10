<?php
// /var/www/piece_auto/public/modules/gestion_categories.php
include_once '../../config/Database.php'; 
include '../../includes/header.php'; 

$page_title = "Gestion des Catégories de Pièces";
$message = "";
$categories = []; 

$db = new Database();
$pdo = $db->getConnection();

// --- TRAITEMENT GÉNÉRAL DES ACTIONS CRUD ---

// 1. Suppression (Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_categorie') {
    $id_categorie = $_POST['id_categorie'];

    try {
        $query = "DELETE FROM CATEGORIES WHERE id_categorie = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id_categorie, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $message = "Succès : La catégorie ID " . htmlspecialchars($id_categorie) . " a été supprimée.";
        } else {
            $message = "Erreur: Échec de la suppression de la catégorie.";
        }
    } catch (PDOException $e) {
        $message = "Erreur de base de données : Cette catégorie est peut-être liée à des pièces existantes. Veuillez d'abord les modifier. " . $e->getMessage();
    }
}

// 2. Modification (Update)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_categorie') {
    $id_categorie = $_POST['id_categorie'];
    $nom = $_POST['nom_categorie'];
    $description = $_POST['description'];

    if (empty($nom)) {
        $message = "Erreur: Le nom de la catégorie est obligatoire pour la modification.";
    } else {
        try {
            $query = "UPDATE CATEGORIES SET nom_categorie = :nom, description = :desc WHERE id_categorie = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':desc', $description);
            $stmt->bindParam(':id', $id_categorie, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $message = "Succès : La catégorie ID " . htmlspecialchars($id_categorie) . " a été mise à jour.";
            } else {
                $message = "Erreur: Échec de la mise à jour de la catégorie.";
            }
        } catch (PDOException $e) {
            $message = "Erreur de base de données : " . $e->getMessage();
        }
    }
}

// 3. Ajout (Create) - Reprise du code précédent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_categorie') {
    $nom = $_POST['nom_categorie'];
    $description = $_POST['description'];

    if (empty($nom)) {
        $message = "Erreur: Le nom de la catégorie est obligatoire.";
    } else {
        try {
            $query = "INSERT INTO CATEGORIES (nom_categorie, description) VALUES (:nom, :desc)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':desc', $description);
            
            if ($stmt->execute()) {
                $message = "Succès : La catégorie " . htmlspecialchars($nom) . " a été ajoutée.";
            } else {
                $message = "Erreur: Échec de l'ajout de la catégorie.";
            }
        } catch (PDOException $e) {
            $message = "Erreur de base de données : " . $e->getMessage();
        }
    }
}


// --- AFFICHAGE DE LA LISTE DES CATÉGORIES (Read) ---
try {
    $query_select = "SELECT id_categorie, nom_categorie, description FROM CATEGORIES ORDER BY nom_categorie";
    $stmt_select = $pdo->query($query_select);
    $categories = $stmt_select->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Cette erreur est la seule qui devrait persister si la colonne description n'est pas trouvée
    $message = (empty($message) ? "Erreur de base de données : " : $message . " - ") . $e->getMessage();
}

?>
<div class="container-fluid">
    <h1><i class="fas fa-boxes"></i> <?= $page_title ?></h1>
    <p class="lead">Gestion complète (CRUD) des catégories de pièces automobiles.</p>
    
    <?php if ($message): ?>
        <div class="alert alert-<?= (strpos($message, 'Succès') !== false) ? 'success' : 'danger' ?>"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <button class="btn btn-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAddCategorie" aria-expanded="false" aria-controls="collapseAddCategorie">
        Ajouter une nouvelle catégorie
    </button>
    
    <div class="collapse mb-4" id="collapseAddCategorie">
        <div class="card card-body">
            <h5 class="card-title">Nouvelle Catégorie</h5>
            <form method="POST" action="gestion_categories.php">
                <input type="hidden" name="action" value="add_categorie">
                <div class="mb-3">
                    <label for="nom_categorie_add" class="form-label">Nom de la Catégorie *</label>
                    <input type="text" class="form-control" id="nom_categorie_add" name="nom_categorie" required>
                </div>
                <div class="mb-3">
                    <label for="description_add" class="form-label">Description</label>
                    <textarea class="form-control" id="description_add" name="description" rows="2"></textarea>
                </div>
                <button type="submit" class="btn btn-success"><i class="fas fa-plus-circle"></i> Ajouter la catégorie</button>
            </form>
        </div>
    </div>

    <h3 class="mt-4">Liste des Catégories</h3>
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom de la Catégorie</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="4" class="text-center">Aucune catégorie trouvée.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($categories as $categorie): ?>
                <tr>
                    <td><?= htmlspecialchars($categorie['id_categorie']) ?></td>
                    <td><?= htmlspecialchars($categorie['nom_categorie']) ?></td>
                    <td><?= htmlspecialchars($categorie['description']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-info me-1 edit-btn" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editCategorieModal" 
                                data-id="<?= $categorie['id_categorie'] ?>" 
                                data-nom="<?= htmlspecialchars($categorie['nom_categorie']) ?>"
                                data-desc="<?= htmlspecialchars($categorie['description']) ?>"
                                title="Modifier">
                            <i class="fas fa-edit"></i>
                        </button>
                        <form method="POST" action="gestion_categories.php" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie? CELA PEUT CAUSER DES ERREURS SI DES PIÈCES Y SONT ASSOCIÉES.');">
                            <input type="hidden" name="action" value="delete_categorie">
                            <input type="hidden" name="id_categorie" value="<?= $categorie['id_categorie'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" title="Supprimer"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="editCategorieModal" tabindex="-1" aria-labelledby="editCategorieModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategorieModalLabel">Modifier Catégorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="gestion_categories.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_categorie">
                    <input type="hidden" name="id_categorie" id="edit_id_categorie">
                    
                    <div class="mb-3">
                        <label for="edit_nom_categorie" class="form-label">Nom de la Catégorie *</label>
                        <input type="text" class="form-control" id="edit_nom_categorie" name="nom_categorie" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var editModal = document.getElementById('editCategorieModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            
            // Extraction des données
            var id = button.getAttribute('data-id');
            var nom = button.getAttribute('data-nom');
            var desc = button.getAttribute('data-desc');
            
            // Mise à jour du contenu du modal
            editModal.querySelector('.modal-title').textContent = 'Modifier Catégorie ID: ' + id;
            editModal.querySelector('#edit_id_categorie').value = id;
            editModal.querySelector('#edit_nom_categorie').value = nom;
            editModal.querySelector('#edit_description').value = desc;
        });
    });
</script>

<?php include '../../includes/footer.php'; ?>
