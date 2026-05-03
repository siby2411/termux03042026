<?php
require_once __DIR__ . '/../includes/db.php';
// On remplace l'include problématique par layout.php ou on vérifie l'existence de header.php
if (file_exists("layout.php")) {
    require_once "layout.php";
} elseif (file_exists("header.php")) {
    require_once "header.php";
}

// 1. On s'assure que la table existe
$pdo->exec("CREATE TABLE IF NOT EXISTS FLUX_TRESORERIE (
    id INT AUTO_INCREMENT PRIMARY KEY,
    periode VARCHAR(20) NOT NULL,
    flux_activite_exploit DECIMAL(15,2) DEFAULT 0,
    flux_activite_invest DECIMAL(15,2) DEFAULT 0,
    flux_activite_finance DECIMAL(15,2) DEFAULT 0,
    variation_tresorerie DECIMAL(15,2) DEFAULT 0,
    UNIQUE(periode)
) ENGINE=InnoDB;");

$flux = $pdo->query("SELECT * FROM FLUX_TRESORERIE ORDER BY periode DESC")->fetchAll(PDO::FETCH_ASSOC);

// 2. Calcul dynamique sur la Classe 5
$sql = "
SELECT DATE_FORMAT(date_ecriture,'%Y-%m') AS mois,
  SUM(CASE WHEN compte_debite_id LIKE '5%' THEN montant ELSE 0 END) AS bank_debit,
  SUM(CASE WHEN compte_credite_id LIKE '5%' THEN montant ELSE 0 END) AS bank_credit
FROM ECRITURES_COMPTABLES
GROUP BY mois
ORDER BY mois DESC;
";
$bank_mov = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class='bx bx-water'></i> Flux de Trésorerie OMEGA</h3>
        <a href="generer_flux.php" class="btn btn-outline-primary btn-sm">Actualiser les calculs</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">Tableau de Synthèse (TFT)</div>
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr><th>Période</th><th>Exploitation</th><th>Investissement</th><th>Financement</th><th>Variation</th></tr>
                </thead>
                <tbody>
                <?php foreach($flux as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f['periode']) ?></td>
                    <td class="text-end"><?= number_format($f['flux_activite_exploit'], 0, ',', ' ') ?> F</td>
                    <td class="text-end"><?= number_format($f['flux_activite_invest'], 0, ',', ' ') ?> F</td>
                    <td class="text-end"><?= number_format($f['flux_activite_finance'], 0, ',', ' ') ?> F</td>
                    <td class="text-end fw-bold"><?= number_format($f['variation_tresorerie'], 0, ',', ' ') ?> F</td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php if(file_exists("footer.php")) include "footer.php"; ?>
