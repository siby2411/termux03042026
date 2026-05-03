<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

$page_title = "Ajouter une Pièce";
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = "";

// Récupération des marques et fournisseurs pour les listes déroulantes
$marques = $db->query("SELECT id_marque, nom_marque FROM MARQUES ORDER BY nom_marque")->fetchAll(PDO::FETCH_ASSOC);
$fournisseurs = $db->query("SELECT id_fournisseur, nom_fournisseur FROM FOURNISSEURS ORDER BY nom_fournisseur")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $sql = "INSERT INTO PIECES (reference, nom_piece, id_marque, prix_achat, prix_vente, stock_actuel, type_motorisation, id_fournisseur) 
                VALUES (:ref, :nom, :marque, :pa, :pv, :stock, :moteur, :fourn)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':ref'    => $_POST['reference'],
            ':nom'    => $_POST['nom_piece'],
            ':marque' => $_POST['id_marque'],
            ':pa'     => $_POST['prix_achat'],
            ':pv'     => $_POST['prix_vente'],
            ':stock'  => $_POST['stock_actuel'],
            ':moteur' => $_POST['type_motorisation'],
            ':fourn'  => $_POST['id_fournisseur']
        ]);
        
        $message = "<div class='alert alert-success'>✅ Pièce ajoutée avec succès au catalogue !</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>❌ Erreur : " . $e->getMessage() . "</div>";
    }
}
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-plus-circle"></i> Ajouter une nouvelle pièce</h4>
                </div>
                <div class="card-body">
                    <?= $message ?>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Référence (SKU)</label>
                                <input type="text" name="reference" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Désignation / Nom</label>
                                <input type="text" name="nom_piece" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Marque</label>
                                <select name="id_marque" class="form-select">
                                    <?php foreach ($marques as $m): ?>
                                        <option value="<?= $m['id_marque'] ?>"><?= $m['nom_marque'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Motorisation</label>
                                <select name="type_motorisation" class="form-select">
                                    <option value="Thermique">Thermique</option>
                                    <option value="Electrique">Electrique</option>
                                    <option value="Hybride">Hybride</option>
                                    <option value="Non Classé">Non Classé</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prix Achat (€)</label>
                                <input type="number" step="0.01" name="prix_achat" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Prix Vente (€)</label>
                                <input type="number" step="0.01" name="prix_vente" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stock Initial</label>
                                <input type="number" name="stock_actuel" class="form-control" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fournisseur</label>
                                <select name="id_fournisseur" class="form-select">
                                    <?php foreach ($fournisseurs as $f): ?>
                                        <option value="<?= $f['id_fournisseur'] ?>"><?= $f['nom_fournisseur'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-success w-100">Enregistrer la pièce</button>
                            <a href="gestion_pieces.php" class="btn btn-link w-100 text-muted mt-2">Retour au catalogue</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
