<?php
require_once '../../includes/config.php';

if (!isLoggedIn()) {
    header('Location: ../../login.php');
    exit;
}

$page_title = 'Ajouter un client';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $adresse = $_POST['adresse'];
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO clients (nom, prenom, email, telephone, adresse) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nom, $prenom, $email, $telephone, $adresse]);
        
        $success = "Client ajouté avec succès !";
        logActivity('ajout_client', "Ajout du client: $prenom $nom");
        
    } catch (Exception $e) {
        $error = "Erreur: " . $e->getMessage();
    }
}

include '../../includes/header.php';
?>

<div class="row">
    <div class="col-md-6 mx-auto">
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-user-plus"></i> Nouveau client</h4>
            </div>
            <div class="card-body">
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nom *</label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Prénom *</label>
                            <input type="text" name="prenom" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Email *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Téléphone</label>
                        <input type="tel" name="telephone" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label>Adresse</label>
                        <textarea name="adresse" class="form-control" rows="3"></textarea>
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
