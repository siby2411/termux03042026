<?php
/**
 * OMEGA AUTO - VENTE_ENREGISTRER.PHP
 * Enregistrement d'une transaction et mise à jour du statut
 */
require_once 'config.php';
$db = Database::getInstance();
$error = null;
$success = null;

$vehicule_id = isset($_GET['vehicule_id']) ? (int)$_GET['vehicule_id'] : 0;

try {
    // 1. Récupération des infos du véhicule pour confirmation
    $stmt = $db->getConnection()->prepare("
        SELECT v.*, m.nom as modele_nom, mk.nom as marque_nom 
        FROM vehicules v
        JOIN modeles m ON v.modele_id = m.id
        JOIN marques mk ON m.marque_id = mk.id
        WHERE v.id = :id
    ");
    $stmt->execute(['id' => $vehicule_id]);
    $v = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$v) die("Véhicule introuvable.");

    // 2. Traitement de la vente
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $client_nom = trim($_POST['client_nom'] ?? '');
        $prix_final = (float)($_POST['prix_final'] ?? 0);
        $mode_paiement = $_POST['mode_paiement'] ?? 'especes';

        if (empty($client_nom) || $prix_final <= 0) {
            throw new Exception("Le nom du client et le prix final sont obligatoires.");
        }

        // DEBUT TRANSACTION SQL
        $pdo = $db->getConnection();
        $pdo->beginTransaction();

        // A. Insertion dans la table ventes
        $ins = $pdo->prepare("INSERT INTO ventes (vehicule_id, client_nom, client_telephone, prix_final, mode_paiement) VALUES (?, ?, ?, ?, ?)");
        $ins->execute([$vehicule_id, $client_nom, $_POST['client_tel'], $prix_final, $mode_paiement]);

        // B. Mise à jour du statut du véhicule
        $upd = $pdo->prepare("UPDATE vehicules SET statut = 'vendu' WHERE id = ?");
        $upd->execute([$vehicule_id]);

        $pdo->commit();
        $success = "Vente enregistrée avec succès ! Redirection...";
        header("Refresh:2; url=ventes.php");
    }

} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Enregistrer une vente - OMEGA AUTO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 p-4">
                    <h3 class="mb-4 text-primary"><i class="bi bi-cart-check me-2"></i>Finaliser la vente</h3>
                    
                    <?php if ($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>
                    <?php if ($success): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>

                    <div class="alert alert-info py-2">
                        <strong>Véhicule :</strong> <?= htmlspecialchars($v['marque_nom'] . ' ' . $v['modele_nom']) ?><br>
                        <strong>Immatriculation :</strong> <?= $v['immatriculation'] ?>
                    </div>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nom complet du client *</label>
                            <input type="text" name="client_nom" class="form-control" placeholder="ex: Mamadou Diop" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Téléphone client</label>
                            <input type="text" name="client_tel" class="form-control" placeholder="77 XXX XX XX">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Prix de vente final (FCFA) *</label>
                            <input type="number" name="prix_final" class="form-control" value="<?= (float)$v['prix_vente'] ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Mode de règlement</label>
                            <select name="mode_paiement" class="form-select">
                                <option value="especes">Espèces</option>
                                <option value="wave">Wave</option>
                                <option value="orange_money">Orange Money</option>
                                <option value="virement">Virement Bancaire</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">Valider la transaction</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
