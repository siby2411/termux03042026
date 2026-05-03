<?php
// COMPTABILITE_INDICATORS.PHP - Affiche les cartes d'indicateurs RLI et NPL

// Le calcul des couleurs et des conseils est nécessaire ici ou doit être fait en amont
$rli_color = ($rli >= 1.20) ? 'success' : (($rli >= 1.00) ? 'warning' : 'danger');
$rli_conseil = ($rli >= 1.20) ? "Excellent." : (($rli >= 1.00) ? "Marge de sécurité faible." : "Risque de Liquidité.");

$npl_color = ($npl <= 3.00) ? 'success' : (($npl <= 5.00) ? 'warning' : 'danger');
$npl_conseil = ($npl <= 3.00) ? "Très bonne qualité du portefeuille." : (($npl <= 5.00) ? "Niveau acceptable." : "Risque de Crédit Élevé.");

// Récupérer l'actif liquide total (Trésorerie) si la variable n'existe pas déjà
if (!isset($liquidite_totale)) {
    // Ceci est une requête de secours, idéalement le montant devrait être passé.
    $treso_result = $conn->query("SELECT Montant FROM TRESORERIE WHERE TresorerieID = 1");
    $liquidite_totale = ($treso_result && $treso_result->num_rows > 0) ? $treso_result->fetch_assoc()['Montant'] : 0.00;
}
?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Actifs Liquides Disponibles</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= number_format($liquidite_totale, 2, ',', ' ') ?> €
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card shadow border-left-<?= $rli_color ?> h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-<?= $rli_color ?> text-uppercase mb-1">
                    Ratio de Liquidité Immédiate (RLI)
                </div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= number_format($rli, 2) ?>
                </div>
                <small class="text-<?= $rli_color ?> mt-2 d-block"><?= $rli_conseil ?></small>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow border-left-<?= $npl_color ?> h-100 py-2">
            <div class="card-body">
                <div class="text-xs font-weight-bold text-<?= $npl_color ?> text-uppercase mb-1">
                    Taux de Défaillance Brut (NPL)
                </div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                    <?= number_format($npl, 2) ?> %
                </div>
                <small class="text-<?= $npl_color ?> mt-2 d-block"><?= $npl_conseil ?></small>
            </div>
        </div>
    </div>
</div>
