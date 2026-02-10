




<?php
session_start();
require __DIR__ . '/../config/config.php';
require_once 'layout.php';

$message = '';
// liste amortissements
$amorts = $pdo->query("SELECT a.*, i.designation FROM AMORTISSEMENTS a LEFT JOIN IMMOBILISATIONS i ON a.immo_id = i.immo_id ORDER BY a.exercice DESC")->fetchAll(PDO::FETCH_ASSOC);
$immobilisations = $pdo->query("SELECT immo_id, designation, valeur_origine, duree_utilite, valeur_residuelle FROM IMMOBILISATIONS ORDER BY immo_id")->fetchAll(PDO::FETCH_ASSOC);

// générer dotation pour un exercice (ex: année)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'gen_amort') {
    $immo_id = intval($_POST['immo_id']);
    $exercice = intval($_POST['exercice']);
    // trouver immo
    $immo = null;
    foreach($immobilisations as $i) if($i['immo_id'] == $immo_id) $immo = $i;
    if (!$immo) { $message = "Immo introuvable"; }
    else {
        // durée en années = duree_utilite / 12 (duree_utilite en mois si tu as mis ainsi). On prend durée en années approximée:
        $duree_annees = floatval($immo['duree_utilite']) / 12.0;
        if ($duree_annees <= 0) $duree_annees = 1;
        $base = floatval($immo['valeur_origine']) - floatval($immo['valeur_residuelle']);
        $dotation = $base / $duree_annees;
        // calcul cumul simple : somme existante + dotation
        $stmtC = $pdo->prepare("SELECT SUM(dotation_exercice) AS s FROM AMORTISSEMENTS WHERE immo_id = ?");
        $stmtC->execute([$immo_id]); $row = $stmtC->fetch(); $cumul = floatval($row['s']);
        $cumul_new = $cumul + $dotation;
        $vnc = floatval($immo['valeur_origine']) - $cumul_new;
        try {
            $ins = $pdo->prepare("INSERT INTO AMORTISSEMENTS (immo_id, exercice, dotation_exercice, cumul_amortissements, vnc_fin_exercice) VALUES (?, ?, ?, ?, ?)");
            $ins->execute([$immo_id, $exercice, $dotation, $cumul_new, $vnc]);
            $message = "✅ Amortissement généré pour l'exercice $exercice (dotation: ".number_format($dotation,2).")";
            // rafraîchir liste
            $amorts = $pdo->query("SELECT a.*, i.designation FROM AMORTISSEMENTS a LEFT JOIN IMMOBILISATIONS i ON a.immo_id = i.immo_id ORDER BY a.exercice DESC")->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $message = "Erreur insertion amortissement : ".$e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Amortissements</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body style="background:#f6f7fb;padding:20px">
<div class="container">
    <h3>Amortissements</h3>
    <?php if($message): ?><div class="alert alert-info"><?=htmlspecialchars($message)?></div><?php endif; ?>

    <div class="card p-3 mb-3">
        <h5>Générer dotation (mode linéaire par défaut)</h5>
        <form method="post" class="row g-2">
            <input type="hidden" name="action" value="gen_amort">
            <div class="col-md-6">
                <label>Immobilisation</label>
                <select name="immo_id" class="form-select" required>
                    <option value="">-- choisir --</option>
                    <?php foreach($immobilisations as $im): ?>
                        <option value="<?= $im['immo_id'] ?>"><?= htmlspecialchars($im['immo_id'].' - '.$im['designation']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Exercice (année)</label>
                <input type="number" name="exercice" class="form-control" value="<?= date('Y') ?>" required>
            </div>
            <div class="col-md-3 align-self-end">
                <button class="btn btn-primary w-100">Générer</button>
            </div>
        </form>
    </div>

    <div class="card p-3">
        <h5>Liste des amortissements</h5>
        <?php if(empty($amorts)): ?>
            <div class="alert alert-warning">Aucun amortissement enregistré.</div>
        <?php else: ?>
            <table class="table table-striped">
                <thead><tr><th>ID</th><th>Immo</th><th>Exercice</th><th>Dotation</th><th>Cumul</th><th>VNC fin</th></tr></thead>
                <tbody>
                <?php foreach($amorts as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['amort_id']) ?></td>
                        <td><?= htmlspecialchars($a['designation'] ?? $a['immo_id']) ?></td>
                        <td><?= htmlspecialchars($a['exercice']) ?></td>
                        <td><?= number_format($a['dotation_exercice'],2) ?></td>
                        <td><?= number_format($a['cumul_amortissements'],2) ?></td>
                        <td><?= number_format($a['vnc_fin_exercice'],2) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
</body>
</html>







