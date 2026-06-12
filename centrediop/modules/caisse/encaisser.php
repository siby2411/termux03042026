<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
$pdo = getPDO();

$consultation_id = $_GET['id'] ?? null;
$error = '';

// Récupération des infos de la consultation
if ($consultation_id) {
    $stmt = $pdo->prepare("SELECT c.*, p.nom, p.prenom FROM consultations c 
                           JOIN patients p ON c.patient_id = p.id WHERE c.id = ?");
    $stmt->execute([$consultation_id]);
    $consultation = $stmt->fetch();
}

// Traitement du paiement
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // 1. Enregistrer le paiement
        $stmt = $pdo->prepare("INSERT INTO paiements (consultation_id, caissier_id, montant, date_paiement) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$consultation_id, $_SESSION['user_id'], $_POST['montant']]);

        // 2. Mettre à jour le statut de la consultation
        $stmt = $pdo->prepare("UPDATE consultations SET statut = 'payee' WHERE id = ?");
        $stmt->execute([$consultation_id]);

        $pdo->commit();
        header('Location: index.php?success=1');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Erreur lors de l'encaissement : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0"><?php require_once '../../includes/sidebar.php'; ?></div>
        <div class="col-md-10 p-4">
            <h2 class="mb-4">Validation d'encaissement</h2>
            <?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
            
            <div class="card shadow-sm border-0 p-4 col-md-6">
                <h5>Patient : <?= htmlspecialchars($consultation['nom'].' '.$consultation['prenom']) ?></h5>
                <p>Montant à payer : <strong><?= number_format($consultation['prix'], 0, ',', ' ') ?> FCFA</strong></p>
                
                <form method="POST">
                    <input type="hidden" name="montant" value="<?= $consultation['prix'] ?>">
                    <div class="mb-3">
                        <label class="form-label">Mode de paiement</label>
                        <select class="form-select" name="mode">
                            <option value="especes">Espèces</option>
                            <option value="mobile">Mobile Money</option>
                            <option value="carte">Carte Bancaire</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Valider l'encaissement</button>
                    <a href="index.php" class="btn btn-secondary w-100 mt-2">Annuler</a>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
