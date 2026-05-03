<?php
include '../../includes/header.php';
include '../../config/database.php';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "INSERT INTO patients (code_patient, civilite, nom, prenom, date_naissance, genre, telephone, is_active, date_creation) 
              VALUES (:code, :civ, :nom, :prenom, :dob, :genre, :tel, 1, NOW())";
    
    $stmt = $db->prepare($query);
    $code = "PAT-" . date('YmdHis'); // Génération automatique du code
    
    $stmt->execute([
        ':code' => $code,
        ':civ' => $_POST['civilite'],
        ':nom' => $_POST['nom'],
        ':prenom' => $_POST['prenom'],
        ':dob' => $_POST['date_naissance'],
        ':genre' => $_POST['genre'],
        ':tel' => $_POST['telephone']
    ]);
    
    echo "<script>window.location.href='list.php';</script>";
}
?>

<div class="container mt-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="bi bi-person-plus me-2"></i>Nouveau Patient
        </div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Civilité</label>
                    <select name="civilite" class="form-select"><option>M</option><option>Mme</option></select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="form-control" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date Naissance</label>
                    <input type="date" name="date_naissance" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Genre</label>
                    <select name="genre" class="form-select"><option value="M">Masculin</option><option value="F">Féminin</option></select>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-success px-4">Enregistrer le Patient</button>
                    <a href="list.php" class="btn btn-light ms-2">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>
