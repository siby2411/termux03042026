
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SynthesePro UEMOA - Import du Journal</title>
    <link rel="stylesheet" href="/css/style.css"> </head>
<body>
    <header>
        <h1>Importation du Journal Comptable (Phase A)</h1>
        <p>Veuillez télécharger votre fichier d'écritures pour la société sélectionnée.</p>
    </header>



<div class="container">
    <h2>États Financiers Officiels OHADA - Exercice <?= $exercice ?></h2>

    <hr>
    
    <h3>📊 Compte de Résultat (Synthèse de Flux)</h3>
    <table class="cr-table">
        <thead>
            <tr>
                <th>Rubrique</th>
                <th class="num">Produits</th>
                <th class="num">Charges</th>
                <th class="num">Résultat (P - C)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="strong">Résultat d'Exploitation (EXP)</td>
                <td class="num"><?= number_format($cr_data['Exploitation']['Produits'] - 0, 0, ',', ' ') ?></td>
                <td class="num"> -<?= number_format($cr_data['Exploitation']['Charges'] - 0, 0, ',', ' ') ?></td>
                <td class="num strong"><?= number_format($cr_data['Exploitation']['Produits'] - $cr_data['Exploitation']['Charges'], 0, ',', ' ') ?></td>
            </tr>
            <tr>
                <td class="strong">Résultat Financier (FIN)</td>
                <td class="num"><?= number_format($cr_data['Financier']['Produits'] - 0, 0, ',', ' ') ?></td>
                <td class="num"> -<?= number_format($cr_data['Financier']['Charges'] - 0, 0, ',', ' ') ?></td>
                <td class="num strong"><?= number_format($cr_data['Financier']['Produits'] - $cr_data['Financier']['Charges'], 0, ',', ' ') ?></td>
            </tr>
            <tr>
                <td class="strong">Résultat Hors Activités Ordinaires (HAO)</td>
                <td class="num"><?= number_format($cr_data['Hors_AO']['Produits'] - 0, 0, ',', ' ') ?></td>
                <td class="num"> -<?= number_format($cr_data['Hors_AO']['Charges'] - 0, 0, ',', ' ') ?></td>
                <td class="num strong"><?= number_format($cr_data['Hors_AO']['Produits'] - $cr_data['Hors_AO']['Charges'], 0, ',', ' ') ?></td>
            </tr>
        </tbody>
        <tfoot>
             <tr class="<?= ($cr_data['Resultat_Net'] < 0) ? 'loss' : 'profit' ?>">
                <td class="strong">RÉSULTAT NET (Avant Impôt)</td>
                <td colspan="2"></td>
                <td class="num strong"><?= number_format($cr_data['Resultat_Net'], 0, ',', ' ') ?></td>
            </tr>
        </tfoot>
    </table>
    
    <hr>

    <h3>⚖️ Bilan (Synthèse du Patrimoine)</h3>
    <table class="bilan-table">
        <thead>
            <tr>
                <th>ACTIF (Emplois)</th>
                <th class="num">Montant (€)</th>
                <th>PASSIF (Ressources)</th>
                <th class="num">Montant (€)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="strong">I. ACTIF IMMOBILISÉ (Classe 2)</td>
                <td class="num strong"><?= number_format($bilan_data['Actif']['Immobilise'], 0, ',', ' ') ?></td>
                <td class="strong">I. CAPITAUX PROPRES (Classe 1)</td>
                <td class="num strong"><?= number_format($bilan_data['Passif']['Capitaux_Propres'], 0, ',', ' ') ?></td>
            </tr>
            <?php 
                $max_rows = max(count($bilan_data['Actif']['Details']), count($bilan_data['Passif']['Details']));
                for ($i = 0; $i < $max_rows; $i++):
            ?>
            <tr>
                <td><?= $bilan_data['Actif']['Details'][$i]['intitule'] ?? '' ?></td>
                <td class="num"><?= number_format($bilan_data['Actif']['Details'][$i]['montant'] ?? 0, 0, ',', ' ') ?></td>
                <td><?= $bilan_data['Passif']['Details'][$i]['intitule'] ?? '' ?></td>
                <td class="num"><?= number_format($bilan_data['Passif']['Details'][$i]['montant'] ?? 0, 0, ',', ' ') ?></td>
            </tr>
            <?php endfor; ?>
            
            <tr>
                <td class="strong">II. ACTIF CIRCULANT (Classes 3, 4 D)</td>
                <td class="num strong"><?= number_format($bilan_data['Actif']['Circulant'], 0, ',', ' ') ?></td>
                <td class="strong">Résultat Net (Transfert du CR)</td>
                <td class="num strong <?= ($bilan_data['Passif']['Resultat_Net'] < 0) ? 'loss' : 'profit' ?>"><?= number_format($bilan_data['Passif']['Resultat_Net'], 0, ',', ' ') ?></td>
            </tr>
            
            <tr>
                <td class="strong">III. TRÉSORERIE ACTIF (Classe 5 D)</td>
                <td class="num strong"><?= number_format($bilan_data['Actif']['Tresorerie'], 0, ',', ' ') ?></td>
                <td class="strong">II. DETTES (Classe 4 C, 1)</td>
                <td class="num strong"><?= number_format($bilan_data['Passif']['Dettes_CT'] + $bilan_data['Passif']['Dettes_LT'], 0, ',', ' ') ?></td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="final-total">
                <td class="strong">TOTAL GÉNÉRAL ACTIF</td>
                <td class="num strong"><?= number_format($bilan_data['Total_Actif'], 0, ',', ' ') ?></td>
                <td class="strong">TOTAL GÉNÉRAL PASSIF</td>
                <td class="num strong"><?= number_format($bilan_data['Total_Passif_Apres_Resultat'], 0, ',', ' ') ?></td>
            </tr>
            <tr class="<?= ($bilan_data['Total_Actif'] == $bilan_data['Total_Passif_Apres_Resultat']) ? 'success' : 'error' ?>">
                <td colspan="4" class="center strong">
                    ÉQUILIBRE : Actif = Passif : <?= ($bilan_data['Total_Actif'] == $bilan_data['Total_Passif_Apres_Resultat']) ? 'VÉRIFIÉ' : 'DÉSÉQUILIBRÉ (' . number_format($bilan_data['Total_Actif'] - $bilan_data['Total_Passif_Apres_Resultat'], 0, ',', ' ') . ')' ?>
                </td>
            </tr>
        </tfoot>
    </table>
</div>
  <footer>
        <p>&copy; SynthesePro UEMOA</p>
    </footer>
</body>
</html>

?>


