<?php 
require_once 'includes/db.php';
include 'includes/header.php'; 
?>
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4">Espace Candidat : Déposer votre demande</h2>
            <form action="upload.php" method="post" enctype="multipart/form-data" class="bg-light p-4 rounded shadow-sm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <input type="text" name="nom" class="form-control" placeholder="Nom" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <input type="text" name="prenom" class="form-control" placeholder="Prénom" required>
                    </div>
                </div>
                <input type="text" name="adresse" class="form-control mb-3" placeholder="Adresse complète" required>
                <input type="tel" name="telephone" class="form-control mb-3" placeholder="Téléphone" required>
                <input type="email" name="email" class="form-control mb-3" placeholder="Adresse Email" required>
                <select name="genre" class="form-select mb-3">
                    <option value="homme">Homme</option>
                    <option value="femme">Femme</option>
                </select>
                <textarea name="experience" class="form-control mb-3" placeholder="Votre expérience professionnelle" rows="4"></textarea>
                <label class="form-label">Télécharger votre CV (PDF uniquement) :</label>
                <input type="file" name="cv" class="form-control mb-4" accept=".pdf" required>
                <button type="submit" class="btn btn-primary w-100">Soumettre ma candidature</button>
            </form>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
