<?php
session_start();
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

$categories = $db->query("SELECT * FROM categories_depenses ORDER BY nom_categorie")->fetchAll();
$fournisseurs = $db->query("SELECT * FROM fournisseurs ORDER BY nom_fournisseur")->fetchAll();
$bus = $db->query("SELECT id_bus, immatriculation FROM bus WHERE statut_bus = 'operationnel'")->fetchAll();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $query = "INSERT INTO charges (id_categorie, id_fournisseur, id_bus, description, montant_ttc, date_charge, mode_paiement, statut_paiement, id_secretaire) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'paye', 1)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_POST['id_categorie'],
            $_POST['id_fournisseur'] ?: null,
            $_POST['id_bus'] ?: null,
            $_POST['description'],
            $_POST['montant'],
            $_POST['date_charge'],
            $_POST['mode_paiement']
        ]);
        $message = '<div class="alert alert-success">Charge enregistrée avec succès !</div>';
    } catch(Exception $e) {
        $message = '<div class="alert alert-danger">Erreur: ' . $e->getMessage() . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une charge - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include_once '../../includes/header.php'; ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4><i class="fas fa-plus-circle"></i> Ajouter une charge</h4>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <label>Catégorie *</label>
                        <select name="id_categorie" class="form-control" required>
                            <option value="">Sélectionner</option>
                            <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id_categorie']; ?>"><?php echo htmlspecialchars($cat['nom_categorie']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Fournisseur</label>
                        <select name="id_fournisseur" class="form-control">
                            <option value="">Non spécifié</option>
                            <?php foreach($fournisseurs as $f): ?>
                            <option value="<?php echo $f['id_fournisseur']; ?>"><?php echo htmlspecialchars($f['nom_fournisseur']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label>Bus concerné</label>
                        <select name="id_bus" class="form-control">
                            <option value="">Non spécifié</option>
                            <?php foreach($bus as $b): ?>
                            <option value="<?php echo $b['id_bus']; ?>"><?php echo $b['immatriculation']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Date *</label>
                        <input type="date" name="date_charge" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label>Montant (FCFA) *</label>
                        <input type="number" name="montant" class="form-control" step="100" required>
                    </div>
                    <div class="col-md-6">
                        <label>Mode de paiement *</label>
                        <select name="mode_paiement" class="form-control" required>
                            <option value="especes">Espèces</option>
                            <option value="wave">Wave</option>
                            <option value="orange_money">Orange Money</option>
                            <option value="virement">Virement bancaire</option>
                            <option value="cheque">Chèque</option>
                        </select>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label>Description *</label>
                        <textarea name="description" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-success btn-lg">Enregistrer la charge</button>
                    <a href="etats_financiers_complets.php" class="btn btn-info btn-lg">Voir les états financiers</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
