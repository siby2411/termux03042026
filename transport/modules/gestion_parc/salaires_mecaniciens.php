<?php
session_start();
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

$mecaniciens = $db->query("SELECT * FROM fournisseurs WHERE type_fournisseur = 'mecanicien'")->fetchAll();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        $query = "INSERT INTO charges (id_categorie, id_fournisseur, description, montant_ttc, date_charge, mode_paiement, statut_paiement, id_secretaire) 
                  VALUES (8, ?, ?, ?, ?, ?, 'paye', 1)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_POST['id_mecanicien'],
            $_POST['description'],
            $_POST['montant'],
            $_POST['date_paiement'],
            $_POST['mode_paiement']
        ]);
        
        $db->commit();
        $message = '<div class="alert alert-success">Salaire enregistré avec succès !</div>';
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
    <title>Salaires mécaniciens - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include_once '../../includes/header.php'; ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-info text-white">
            <h4><i class="fas fa-users"></i> Paiement salaires mécaniciens</h4>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <label>Mécanicien *</label>
                        <select name="id_mecanicien" class="form-control" required>
                            <option value="">Sélectionner</option>
                            <?php foreach($mecaniciens as $m): ?>
                            <option value="<?php echo $m['id_fournisseur']; ?>"><?php echo htmlspecialchars($m['nom_fournisseur']); ?> - <?php echo $m['telephone']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Période / Mois *</label>
                        <input type="month" name="date_paiement" class="form-control" value="<?php echo date('Y-m'); ?>" required>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label>Montant (FCFA) *</label>
                        <input type="number" name="montant" class="form-control" step="5000" required>
                    </div>
                    <div class="col-md-6">
                        <label>Mode de paiement *</label>
                        <select name="mode_paiement" class="form-control" required>
                            <option value="especes">Espèces</option>
                            <option value="wave">Wave</option>
                            <option value="orange_money">Orange Money</option>
                            <option value="virement">Virement bancaire</option>
                        </select>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label>Description / Observations</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Salaire mensuel, prime, heures supplémentaires..."></textarea>
                    </div>
                </div>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-info btn-lg">Enregistrer le paiement</button>
                </div>
            </form>
            
            <hr class="mt-4">
            <h5>Historique des salaires</h5>
            <?php
            $historique = $db->query("
                SELECT c.*, f.nom_fournisseur 
                FROM charges c
                JOIN fournisseurs f ON c.id_fournisseur = f.id_fournisseur
                WHERE c.id_categorie = 8
                ORDER BY c.date_charge DESC
                LIMIT 10
            ");
            ?>
            <table class="table table-sm">
                <thead>
                    <tr><th>Date</th><th>Mécanicien</th><th>Montant</th><th>Référence</th></tr>
                </thead>
                <tbody>
                    <?php while($row = $historique->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo $row['date_charge']; ?></td>
                        <td><?php echo htmlspecialchars($row['nom_fournisseur']); ?></td>
                        <td><?php echo number_format($row['montant_ttc'], 0, ',', ' '); ?> FCFA</td>
                        <td><code><?php echo $row['reference_charge']; ?></code></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
