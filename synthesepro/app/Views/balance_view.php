<div class="container">
    <h2>Balance des Comptes (Exercice <?= $exercice ?>)</h2>
    <p>Générée à partir de <?= count($balance) ?> comptes actifs.</p>

    <table>
        <thead>
            <tr>
                <th>Compte</th>
                <th>Intitulé</th>
                <th colspan="2" class="center">Mouvements (Grand Livre)</th>
                <th colspan="2" class="center">Soldes (Balance)</th>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th>Débit</th>
                <th>Crédit</th>
                <th>Débiteur</th>
                <th>Créditeur</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $TotalMvtsD = $TotalMvtsC = $TotalSoldesD = $TotalSoldesC = 0;
            foreach ($balance as $row): 
                $TotalMvtsD += $row['mouvement_debit'];
                $TotalMvtsC += $row['mouvement_credit'];
                $TotalSoldesD += $row['solde_debiteur'];
                $TotalSoldesC += $row['solde_crediteur'];
            ?>
                <tr>
                    <td><?= $row['compte_id'] ?></td>
                    <td><?= $row['intitule_compte'] ?></td>
                    <td class="num"><?= number_format($row['mouvement_debit'], 2, ',', ' ') ?></td>
                    <td class="num"><?= number_format($row['mouvement_credit'], 2, ',', ' ') ?></td>
                    <td class="num"><?= number_format($row['solde_debiteur'], 2, ',', ' ') ?></td>
                    <td class="num"><?= number_format($row['solde_crediteur'], 2, ',', ' ') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="strong right">TOTAL (Doit être Équilibré)</td>
                <td class="num strong <?= ($TotalMvtsD != $TotalMvtsC) ? 'error' : '' ?>"><?= number_format($TotalMvtsD, 2, ',', ' ') ?></td>
                <td class="num strong <?= ($TotalMvtsD != $TotalMvtsC) ? 'error' : '' ?>"><?= number_format($TotalMvtsC, 2, ',', ' ') ?></td>
                <td class="num strong"><?= number_format($TotalSoldesD, 2, ',', ' ') ?></td>
                <td class="num strong"><?= number_format($TotalSoldesC, 2, ',', ' ') ?></td>
            </tr>
        </tfoot>
    </table>

    <hr>

    <h3>Synthèse du Résultat Net</h3>
    <table class="summary">
        <tr>
            <th>Résultat d'Exploitation (Produits - Charges)</th>
            <td class="num"><?= number_format($synthese_finale['resultat_exploitation']['produits'] - $synthese_finale['resultat_exploitation']['charges'], 2, ',', ' ') ?></td>
        </tr>
        <tr>
            <th>Résultat Financier (Produits - Charges)</th>
            <td class="num"><?= number_format($synthese_finale['resultat_financier']['produits'] - $synthese_finale['resultat_financier']['charges'], 2, ',', ' ') ?></td>
        </tr>
        <tr>
            <th>Résultat Hors AO (Produits - Charges)</th>
            <td class="num"><?= number_format($synthese_finale['resultat_hao']['produits'] - $synthese_finale['resultat_hao']['charges'], 2, ',', ' ') ?></td>
        </tr>
        <tr class="<?= ($synthese_finale['resultat_net_final'] < 0) ? 'loss' : 'profit' ?>">
            <th class="strong">RÉSULTAT NET AVANT IMPÔT</th>
            <td class="num strong"><?= number_format($synthese_finale['resultat_net_final'], 2, ',', ' ') ?></td>
        </tr>
    </table>
