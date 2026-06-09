<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$db = (new Database())->getConnection();

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ref = trim($_POST['ref']);
    $nom = trim($_POST['nom']);
    $prix = (float)$_POST['prix'];
    $stock = (int)$_POST['stock'];
    
    // 1. Vérification de l'existence de la référence
    $check = $db->prepare("SELECT id_piece FROM PIECES WHERE reference = ?");
    $check->execute([$ref]);
    
    if ($check->rowCount() > 0) {
        $message = '<div class="alert alert-danger mb-3"><i class="fas fa-exclamation-triangle me-2"></i> Erreur : La référence <strong>' . htmlspecialchars($ref) . '</strong> existe déjà !</div>';
    } else {
        // 2. Insertion avec le nom de colonne correct : stock_actuel
        $stmt = $db->prepare("INSERT INTO PIECES (nom_piece, reference, prix_vente, stock_actuel) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nom, $ref, $prix, $stock])) {
            header("Location: gestion_stock.php?success=1");
            exit();
        } else {
            $message = '<div class="alert alert-danger mb-3">Erreur lors de l\'enregistrement en base de données.</div>';
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="card shadow-sm border-0 col-md-6 mx-auto">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i> Ajouter une nouvelle pièce</h5>
        </div>
        <div class="card-body">
            <?= $message ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nom de la pièce</label>
                    <input type="text" name="nom" class="form-control" placeholder="Ex: Plaquette de frein" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Référence (Unique)</label>
                    <input type="text" name="ref" class="form-control" placeholder="Ex: REF-12345" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Prix de Vente (F)</label>
                    <input type="number" name="prix" class="form-control" step="0.01" placeholder="0.00" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Stock Initial (Stock actuel)</label>
                    <input type="number" name="stock" class="form-control" value="0" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-2">
                    <i class="fas fa-save me-2"></i> Enregistrer la pièce
                </button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
