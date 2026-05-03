<?php
require_once '../../includes/config.php';

if (!isAdmin()) {
    header('Location: ../../login.php');
    exit;
}

$page_title = 'Ajouter un livre';
$error = '';
$success = '';

// Récupérer les catégories
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = $_POST['titre'];
    $auteur = $_POST['auteur'];
    $isbn = $_POST['isbn'];
    $editeur = $_POST['editeur'];
    $annee = $_POST['annee_publication'];
    $categorie_id = $_POST['categorie_id'] ?: null;
    $prix_achat = $_POST['prix_achat'];
    $prix_vente = $_POST['prix_vente'];
    $quantite = $_POST['quantite'];
    $quantite_min = $_POST['quantite_min'];
    $emplacement = $_POST['emplacement'];
    $description = $_POST['description'];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO livres (titre, auteur, isbn, editeur, annee_publication, categorie_id, 
                              prix_achat, prix_vente, quantite_stock, quantite_min, emplacement, description)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$titre, $auteur, $isbn, $editeur, $annee, $categorie_id, 
                       $prix_achat, $prix_vente, $quantite, $quantite_min, $emplacement, $description]);
        
        $success = "Livre ajouté avec succès !";
        
        // Log l'activité
        logActivity('ajout_livre', "Ajout du livre: $titre");
        
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-plus"></i> Ajouter un nouveau livre</h4>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Titre *</label>
                            <input type="text" name="titre" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Auteur *</label>
                            <input type="text" name="auteur" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>ISBN</label>
                            <input type="text" name="isbn" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Éditeur</label>
                            <input type="text" name="editeur" class="form-control">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Année publication</label>
                            <input type="number" name="annee_publication" class="form-control">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Catégorie</label>
                            <select name="categorie_id" class="form-control">
                                <option value="">Sélectionner une catégorie</option>
                                <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo $cat['nom']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Emplacement</label>
                            <input type="text" name="emplacement" class="form-control" placeholder="Rayon, étagère...">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label>Prix d'achat *</label>
                            <input type="number" name="prix_achat" class="form-control" step="100" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Prix de vente *</label>
                            <input type="number" name="prix_vente" class="form-control" step="100" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Quantité initiale *</label>
                            <input type="number" name="quantite" class="form-control" value="0" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Quantité minimum</label>
                            <input type="number" name="quantite_min" class="form-control" value="5">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Enregistrer
                    </button>
                    <a href="liste.php" class="btn btn-secondary">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
