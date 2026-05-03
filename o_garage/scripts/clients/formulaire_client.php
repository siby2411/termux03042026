<?php include '../../includes/header.php'; ?>
<div class="card shadow border-0 mx-auto" style="max-width: 700px;">
    <div class="card-header bg-dark text-white text-center py-3">
        <h4 class="mb-0">NOUVEAU CLIENT OMEGA</h4>
    </div>
    <div class="card-body p-4">
        <form action="traitement_client.php" method="POST">
            <div class="row g-3">
                <div class="col-md-6"><input type="text" name="prenom" class="form-control" placeholder="Prénom" required></div>
                <div class="col-md-6"><input type="text" name="nom" class="form-control" placeholder="Nom" required></div>
                <div class="col-12"><input type="tel" name="telephone" class="form-control" placeholder="Téléphone (ex: 77...)" required></div>
                <div class="col-12"><input type="email" name="email" class="form-control" placeholder="Email"></div>
                <div class="col-12"><textarea name="adresse" class="form-control" placeholder="Adresse complète"></textarea></div>
                <div class="col-12"><button type="submit" class="btn btn-primary w-100 shadow">CRÉER LE DOSSIER CLIENT</button></div>
            </div>
        </form>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
