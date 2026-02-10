<?php
// /compte_de_resultat_formel.php
$page_title = "Compte de Résultat Annuel Complet";
// IMPORTANT: Inclure ici le bloc de calcul complet du tableau_de_bord_complet.php
include_once 'tableau_de_bord_calcul.php'; // On suppose que le bloc de calcul est dans un fichier séparé
include_once 'includes/header.php'; 
?>

<div class="container my-5">
    <h1 class="mb-4 text-dark text-center"><i class="fas fa-hand-holding-usd me-2"></i> Compte de Résultat Annuel (Méthode soustractive)</h1>
    <p class="text-muted text-center">Mesure de la performance et de la rentabilité de l'exercice.</p>
    <hr>
    
    <div class="card shadow-lg border-primary border-3">
        <div class="card-header bg-primary text-white fw-bold">Détail des Produits et des Charges</div>
        <div class="card-body">
            <table class="table table-striped table-hover align-middle">
                <tbody>
                    <tr class="table-info">
                        <th>PRODUITS D'EXPLOITATION</th>
                        <td class="text-end fw-bold"><?= number_format($ca, 2, ',', ' ') ?> €</td>
                    </tr>
                    <tr>
                        <td>Chiffre d'Affaires (Ventes)</td>
                        <td class="text-end"><?= number_format($ca, 2, ',', ' ') ?> €</td>
                    </tr>
                    
                    <tr class="table-danger">
                        <th>CHARGES D'EXPLOITATION</th>
                        <td class="text-end fw-bold">(<?= number_format($cdv + $charges_fixes, 2, ',', ' ') ?>) €</td>
                    </tr>
                    <tr>
                        <td>Coût des Ventes (Achats de Marchandises)</td>
                        <td class="text-end text-danger">(<?= number_format($cdv, 2, ',', ' ') ?>) €</td>
                    </tr>
                    <tr>
                        <td>Charges de Personnel et Sociales</td>
                        <td class="text-end text-danger">(<?= number_format(45000.00, 2, ',', ' ') ?>) €</td>
                    </tr>
                    <tr>
                        <td>Autres Charges Externes (Loyer, Services...)</td>
                        <td class="text-end text-danger">(<?= number_format(15000.00, 2, ',', ' ') ?>) €</td>
                    </tr>
                    <tr>
                        <td>Dotations aux Amortissements</td>
                        <td class="text-end text-danger">(<?= number_format($amortissements_reels, 2, ',', ' ') ?>) €</td>
                    </tr>
                    
                    <tr class="table-success">
                        <th>RÉSULTAT NET DE L'EXERCICE</th>
                        <td class="text-end fw-bold"><?= number_format($resultat_net, 2, ',', ' ') ?> €</td>
                    </tr>
                </tbody>
            </table>
            <p class="mt-4 text-center small text-muted">Marge Brute : <?= number_format($marge_brute, 2, ',', ' ') ?> € | Seuil de Rentabilité : <?= number_format($sr, 2, ',', ' ') ?> €</p>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
