<?php
session_start();
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

// Récupérer les pièces et fournisseurs
$pieces = $db->query("SELECT * FROM pieces_detachees ORDER BY nom_piece")->fetchAll();
$fournisseurs = $db->query("SELECT * FROM fournisseurs WHERE type_fournisseur = 'pieces'")->fetchAll();
$bus = $db->query("SELECT id_bus, immatriculation FROM bus WHERE statut_bus = 'operationnel'")->fetchAll();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        // Insertion de la charge
        $query = "INSERT INTO charges (id_categorie, id_fournisseur, id_bus, description, montant_ht, montant_ttc, date_charge, mode_paiement, statut_paiement, id_secretaire) 
                  VALUES (4, ?, ?, ?, ?, ?, ?, ?, 'paye', 1)";
        $stmt = $db->prepare($query);
        $stmt->execute([
            $_POST['id_fournisseur'],
            $_POST['id_bus'] ?: null,
            $_POST['description'],
            $_POST['montant_ht'],
            $_POST['montant_ttc'],
            $_POST['date_charge'],
            $_POST['mode_paiement']
        ]);
        $id_charge = $db->lastInsertId();
        
        // Insertion des pièces achetées
        for($i = 0; $i < count($_POST['id_piece']); $i++) {
            if(!empty($_POST['id_piece'][$i]) && !empty($_POST['quantite'][$i])) {
                $stmt2 = $db->prepare("INSERT INTO achats_pieces (id_charge, id_piece, quantite, prix_unitaire_achat) VALUES (?, ?, ?, ?)");
                $stmt2->execute([$id_charge, $_POST['id_piece'][$i], $_POST['quantite'][$i], $_POST['prix_unitaire'][$i]]);
                
                // Mettre à jour le stock
                $stmt3 = $db->prepare("UPDATE pieces_detachees SET quantite_stock = quantite_stock + ? WHERE id_piece = ?");
                $stmt3->execute([$_POST['quantite'][$i], $_POST['id_piece'][$i]]);
            }
        }
        
        $db->commit();
        $message = '<div class="alert alert-success">Achat enregistré. Réf: CHG' . date('Ymd') . '...</div>';
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
    <title>Achat pièces détachées - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<?php include_once '../../includes/header.php'; ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4><i class="fas fa-tools"></i> Achat de pièces détachées</h4>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <label>Fournisseur *</label>
                        <select name="id_fournisseur" class="form-control" required>
                            <option value="">Sélectionner</option>
                            <?php foreach($fournisseurs as $f): ?>
                            <option value="<?php echo $f['id_fournisseur']; ?>"><?php echo htmlspecialchars($f['nom_fournisseur']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label>Bus concerné (optionnel)</label>
                        <select name="id_bus" class="form-control">
                            <option value="">Non spécifié</option>
                            <?php foreach($bus as $b): ?>
                            <option value="<?php echo $b['id_bus']; ?>"><?php echo $b['immatriculation']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <label>Description de l'achat *</label>
                        <textarea name="description" class="form-control" rows="2" required></textarea>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-4">
                        <label>Date *</label>
                        <input type="date" name="date_charge" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label>Montant HT (FCFA)</label>
                        <input type="number" name="montant_ht" class="form-control" step="100" id="montant_ht" onchange="calculTTC()">
                    </div>
                    <div class="col-md-4">
                        <label>Montant TTC (FCFA) *</label>
                        <input type="number" name="montant_ttc" class="form-control" step="100" required id="montant_ttc">
                    </div>
                </div>
                
                <div class="row mt-3">
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
                
                <hr class="mt-4">
                <h5><i class="fas fa-boxes"></i> Détail des pièces achetées</h5>
                
                <div id="pieces-container">
                    <div class="row piece-row mt-2">
                        <div class="col-md-5">
                            <select name="id_piece[]" class="form-control" required>
                                <option value="">Sélectionner une pièce</option>
                                <?php foreach($pieces as $p): ?>
                                <option value="<?php echo $p['id_piece']; ?>"><?php echo htmlspecialchars($p['nom_piece']); ?> (Stock: <?php echo $p['quantite_stock']; ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="quantite[]" class="form-control" placeholder="Quantité" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="prix_unitaire[]" class="form-control" placeholder="Prix unitaire (FCFA)" step="100">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger remove-piece">✖</button>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-secondary mt-2" id="add-piece">
                    <i class="fas fa-plus"></i> Ajouter une pièce
                </button>
                
                <div class="mt-4">
                    <button type="submit" class="btn btn-success btn-lg">Enregistrer l'achat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function calculTTC() {
    let ht = parseFloat($('#montant_ht').val()) || 0;
    let ttc = ht * 1.18;
    $('#montant_ttc').val(Math.round(ttc));
}

$('#add-piece').click(function() {
    let newRow = $('.piece-row:first').clone();
    newRow.find('input').val('');
    newRow.find('select').val('');
    $('#pieces-container').append(newRow);
});

$(document).on('click', '.remove-piece', function() {
    if($('.piece-row').length > 1) {
        $(this).closest('.piece-row').remove();
    }
});
</script>

<?php include_once '../../includes/footer.php'; ?>
</body>
</html>
