<?php 
require_once '../../includes/header.php'; 
require_once '../../includes/classes/Database.php';

$dbObj = new Database();
$pdo = $dbObj->getConnection();

// Fonction de génération automatique de référence
function genererReferenceOmega($pdo) {
    $annee = date('Y');
    $stmt = $pdo->query("SELECT MAX(id_piece) as dernier FROM pieces_detachees");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $prochainID = ($row['dernier'] ?? 0) + 1;
    
    // Format : OMG-2026-001
    return "OMG-" . $annee . "-" . str_pad($prochainID, 3, '0', STR_PAD_LEFT);
}

// Traitement du formulaire
$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enregistrer'])) {
    try {
        $ref = genererReferenceOmega($pdo);
        $libelle = trim($_POST['libelle']);
        $prix_achat = floatval($_POST['prix_achat']);
        $prix_vente = floatval($_POST['prix_vente']);
        $stock = intval($_POST['stock']);
        $seuil = intval($_POST['seuil']);
        $num_serie = trim($_POST['num_serie']);

        $sql = "INSERT INTO pieces_detachees (reference, num_serie, libelle, prix_achat, prix_vente, stock_actuel, seuil_alerte) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$ref, $num_serie, $libelle, $prix_achat, $prix_vente, $stock, $seuil]);

        $message = "<div class='alert alert-success shadow-sm'><i class='fas fa-check-circle'></i> Pièce enregistrée avec succès ! Référence : <strong>$ref</strong></div>";
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger'><i class='fas fa-exclamation-triangle'></i> Erreur : " . $e->getMessage() . "</div>";
    }
}

// Génération de la référence prévisionnelle pour l'affichage
$nextRef = genererReferenceOmega($pdo);
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-plus-circle text-warning me-2"></i> Ajouter une nouvelle pièce au stock</h5>
                <span class="badge bg-secondary">Prochaine Réf : <?= $nextRef ?></span>
            </div>
            <div class="card-body p-4">
                <?= $message ?>
                
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Désignation de la pièce</label>
                            <input type="text" name="libelle" class="form-control form-control-lg" placeholder="ex: Filtre à huile Toyota" required autofocus>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Numéro de série / Code constructeur</label>
                            <input type="text" name="num_serie" class="form-control" placeholder="Optionnel">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-muted">Référence Système (Auto)</label>
                            <input type="text" class="form-control bg-light" value="<?= $nextRef ?>" readonly disabled>
                        </div>

                        <hr class="my-4">

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary">Prix d'Achat (FCFA)</label>
                            <input type="number" name="prix_achat" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-success">Prix de Vente (FCFA)</label>
                            <input type="number" name="prix_vente" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Quantité Initiale</label>
                            <input type="number" name="stock" class="form-control" value="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Seuil d'Alerte</label>
                            <input type="number" name="seuil" class="form-control" value="5" required>
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" name="enregistrer" class="btn btn-primary btn-lg w-100 shadow">
                                <i class="fas fa-save me-2"></i> Enregistrer dans le Magasin
                            </button>
                            <a href="liste_pieces.php" class="btn btn-link w-100 mt-2 text-muted text-decoration-none">Retour à la liste des pièces</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
