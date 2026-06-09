<?php
session_start();
require __DIR__ . '/../includes/db.php'; // $pdo

$message = '';

// Récupérer les sociétés et comptes pour les selects
$societes = $pdo->query("SELECT societe_id, nom_societe FROM SOCIETES")->fetchAll(PDO::FETCH_ASSOC);
$comptes = $pdo->query("SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $societe_id = $_POST['societe_id'] ?? '';
    $date_operation = $_POST['date_operation'] ?? '';
    $libelle_operation = $_POST['libelle_operation'] ?? '';
    $compte_debite_id = $_POST['compte_debite_id'] ?? '';
    $compte_credite_id = $_POST['compte_credite_id'] ?? '';
    $montant = $_POST['montant'] ?? '';

    // Vérification simple
    if ($societe_id && $date_operation && $libelle_operation && $compte_debite_id && $compte_credite_id && $montant) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO `ECRITURES_COMPTABLES` 
                (societe_id, date_operation, libelle_operation, compte_debite_id, compte_credite_id, montant)
                VALUES (:societe_id, :date_operation, :libelle_operation, :compte_debite_id, :compte_credite_id, :montant)
            ");
            $stmt->execute([
                ':societe_id' => $societe_id,
                ':date_operation' => $date_operation,
                ':libelle_operation' => $libelle_operation,
                ':compte_debite_id' => $compte_debite_id,
                ':compte_credite_id' => $compte_credite_id,
                ':montant' => $montant
            ]);
            $message = "✅ Écriture ajoutée avec succès !";
        } catch (PDOException $e) {
            $message = "❌ Erreur lors de l'ajout : " . $e->getMessage();
        }
    } else {
        $message = "⚠️ Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ajouter une écriture comptable</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f0f2f5; padding: 50px 0; font-family: 'Segoe UI', sans-serif; }
.card { max-width: 720px; margin: auto; padding: 2rem; border-radius: 1rem; box-shadow: 0 0 20px rgba(0,0,0,0.1); background: #fff; }
.btn-primary { background: linear-gradient(to right, #6a11cb, #2575fc); border: none; }
.btn-primary:hover { background: linear-gradient(to right, #2575fc, #6a11cb); }
.alert { font-weight: bold; }
</style>
</head>
<body>

<div class="card">
    <h3 class="text-center mb-4">Ajouter une écriture comptable</h3>

    <?php if($message): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label for="societe_id" class="form-label">Société :</label>
            <select id="societe_id" name="societe_id" class="form-select" required>
                <option value="">-- Sélectionner une société --</option>
                <?php foreach($societes as $societe): ?>
                    <option value="<?= $societe['societe_id'] ?>"><?= htmlspecialchars($societe['nom_societe']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="date_operation" class="form-label">Date de l'opération :</label>
            <input type="date" id="date_operation" name="date_operation" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="libelle_operation" class="form-label">Libellé de l'opération :</label>
            <input type="text" id="libelle_operation" name="libelle_operation" class="form-control" maxlength="255" required>
        </div>

        <div class="mb-3">
            <label for="compte_debite_id" class="form-label">Compte débité :</label>
            <select id="compte_debite_id" name="compte_debite_id" class="form-select" required>
                <option value="">-- Sélectionner un compte --</option>
                <?php foreach($comptes as $compte): ?>
                    <option value="<?= $compte['compte_id'] ?>"><?= htmlspecialchars($compte['compte_id'] . ' - ' . $compte['intitule_compte']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="compte_credite_id" class="form-label">Compte crédité :</label>
            <select id="compte_credite_id" name="compte_credite_id" class="form-select" required>
                <option value="">-- Sélectionner un compte --</option>
                <?php foreach($comptes as $compte): ?>
                    <option value="<?= $compte['compte_id'] ?>"><?= htmlspecialchars($compte['compte_id'] . ' - ' . $compte['intitule_compte']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="montant" class="form-label">Montant :</label>
            <input type="number" step="0.01" id="montant" name="montant" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Ajouter l'écriture</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


