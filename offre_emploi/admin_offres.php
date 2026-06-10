<?php
require_once 'includes/db.php';
include 'includes/header.php';

// Traitement de l'envoi du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stmt = $pdo->prepare("INSERT INTO offres (titre, secteur, competences_cles, is_featured, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['titre'], 
        $_POST['secteur'], 
        $_POST['competences_cles'], 
        isset($_POST['is_featured']) ? 1 : 0,
        $_POST['description']
    ]);
    $message = "Offre publiée avec succès !";
}
?>

<div class="container my-5 fade-in">
    <h2>Espace Publication d'Offres</h2>
    <?php if (isset($message)) echo "<div class='alert alert-success'>$message</div>"; ?>
    
    <form method="POST" class="bg-light p-4 mb-5 shadow-sm">
        <div class="row">
            <div class="col-md-6 mb-3">
                <input type="text" name="titre" class="form-control" placeholder="Titre du poste" required>
            </div>
            <div class="col-md-6 mb-3">
                <input type="text" name="secteur" class="form-control" placeholder="Secteur" required>
            </div>
            <div class="col-md-12 mb-3">
                <input type="text" name="competences_cles" class="form-control" placeholder="Compétences clés (ex: Python, Django, SQL)">
            </div>
            <div class="col-md-12 mb-3">
                <textarea name="description" class="form-control" placeholder="Description de l'offre"></textarea>
            </div>
            <div class="col-md-12 mb-3">
                <input type="checkbox" name="is_featured" value="1"> Marquer comme Sponsorisé (Gold)
            </div>
            <button type="submit" class="btn btn-primary">Publier l'Offre</button>
        </div>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
