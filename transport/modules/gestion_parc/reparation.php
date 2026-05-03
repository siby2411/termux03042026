<?php
session_start();
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

$mecaniciens = $db->query("SELECT * FROM fournisseurs WHERE type_fournisseur = 'mecanicien'")->fetchAll();
$bus = $db->query("SELECT id_bus, immatriculation FROM bus")->fetchAll();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        $query = "INSERT INTO charges (id_categorie, id_fournisseur, id_bus, description, montant_ttc, date_charge, mode_paiement, statut_paiement, id_secretaire) 
                  VALUES (3, ?, ?, ?, ?, ?, ?, 'paye', 1)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_POST['id_mecanicien'],
            $_POST['id_bus'],
            $_POST['description'],
            $_POST['montant'],
            $_POST['date_reparation'],
            $_POST['mode_paiement']
        ]);
        $id_charge = $db->lastInsertId();
        
        $stmt2 = $db->prepare("INSERT INTO reparations (id_charge, id_bus, id_mecanicien, date_debut, date_fin, type_reparation, kilometrage, garantie_mois) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt2->execute([
            $id_charge, $_POST['id_bus'], $_POST['id_mecanicien'],
            $_POST['date_debut'], $_POST['date_fin'], $_POST['type_reparation'],
            $_POST['kilometrage'], $_POST['garantie_mois']
        ]);
        
        $db->commit();
        $message = '<div class="alert alert-success">Réparation enregistrée avec succès !</div>';
    } catch(Exception $e) {
        $db->rollBack();
        $message = '<div class="alert alert-danger">Erreur: ' . $e->getMessage() . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réparations - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include_once '../../includes/header.php'; ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h4><i class="fas fa-wrench"></i> Réparation / Maintenance</h4>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <label>Bus *</label>
                        <select name="id_bus" class="form-control" required>
                            <option value="">Sélectionner</option>
                            <?php foreach($bus as $b): ?>
                            <option value="<?php echo $b['id_bus']; ?>"><?php echo $b['immatriculation']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Mécanicien *</label>
                        <select name="id_mecanicien" class="form-control" required>
                            <option value="">Sélectionner</option>
                            <?php foreach($mecaniciens as $m): ?>
                            <option value="<?php echo $m['id_fournisseur']; ?>"><?php echo htmlspecialchars($m['nom_fournisseur']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label>Type de réparation *</label>
                        <select name="type_reparation" class="form-control" required>
                            <option value="Vidange">Vidange</option>
                            <option value="Freinage">Freinage</option>
                            <option value="Moteur">Moteur</option>
                            <option value="Boîte de vitesse">Boîte de vitesse</option>
                            <option value="Électricité">Électricité</option>
                            <option value="Pneumatiques">Pneumatiques</option>
                            <option value="Carrosserie">Carrosserie</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Kilométrage actuel</label>
                        <input type="number" name="kilometrage" class="form-control">
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label>Date début *</label>
                        <input type="date" name="date_debut" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Date fin *</label>
                        <input type="date" name="date_fin" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Garantie (mois)</label>
                        <input type="number" name="garantie_mois" class="form-control" value="3">
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label>Description des travaux *</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label>Montant total (FCFA) *</label>
                        <input type="number" name="montant" class="form-control" step="1000" required>
                    </div>
                    <div class="col-md-4">
                        <label>Date de la charge *</label>
                        <input type="date" name="date_reparation" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label>Mode de paiement *</label>
                        <select name="mode_paiement" class="form-control" required>
                            <option value="especes">Espèces</option>
                            <option value="virement">Virement bancaire</option>
                            <option value="cheque">Chèque</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-warning btn-lg">Enregistrer la réparation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
