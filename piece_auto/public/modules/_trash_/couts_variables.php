<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Méthode des coûts variables - Direct Costing";
$page_icon = "graph-down";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

$produits = $pdo->query("SELECT * FROM PRODUITS_CAE ORDER BY code")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach($produits as $p) {
        $stmt = $pdo->prepare("SELECT * FROM COUTS_VARIABLES WHERE produit_id = ?");
        $stmt->execute([$p['id']]);
        $cv = $stmt->fetch();
        
        $cout_variable_unitaire = ($cv['matieres_premieres'] + $cv['main_oeuvre_directe'] + $cv['energie'] + $cv['autres_charges_variables']) / $p['quantite_produite'];
        $marge_sur_cout_variable = $p['prix_vente'] - $cout_variable_unitaire;
        $taux_marge_variable = ($marge_sur_cout_variable / $p['prix_vente']) * 100;
        
        $resultats[$p['code']] = [
            'libelle' => $p['libelle'],
            'cout_variable' => $cout_variable_unitaire,
            'marge_scv' => $marge_sur_cout_variable,
            'taux_marge' => $taux_marge_variable
        ];
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-down"></i> Méthode des coûts variables - Direct Costing</h5>
                <small>Seules les charges variables sont imputées aux produits</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Définition :</strong> Le direct costing consiste à ne prendre en compte que les charges variables pour le calcul du coût de revient.<br>
                    <strong>📌 Formule :</strong> Marge sur coût variable = Chiffre d'affaires - Coûts variables
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📦 Produits et coûts variables unitaires</div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr><th>Produit</th><th>Prix vente</th><th>Matières</th><th>MOD</th><th>Énergie</th><th>Autres</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($produits as $p): 
                                            $stmt = $pdo->prepare("SELECT * FROM COUTS_VARIABLES WHERE produit_id = ?");
                                            $stmt->execute([$p['id']]);
                                            $cv = $stmt->fetch();
                                        ?>
                                        <tr>
                                            <td class="fw-bold"><?= $p['code'] ?> - <?= $p['libelle'] ?> </td>
                                            <td class="text-end"><?= number_format($p['prix_vente'], 0, ',', ' ') ?> F</td>
                                            <td class="text-end"><?= number_format($cv['matieres_premieres'] / $p['quantite_produite'], 0, ',', ' ') ?> F</td>
                                            <td class="text-end"><?= number_format($cv['main_oeuvre_directe'] / $p['quantite_produite'], 0, ',', ' ') ?> F</td>
                                            <td class="text-end"><?= number_format($cv['energie'] / $p['quantite_produite'], 0, ',', ' ') ?> F</td>
                                            <td class="text-end"><?= number_format($cv['autres_charges_variables'] / $p['quantite_produite'], 0, ',', ' ') ?> F</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">⚙️ Charges fixes totales</div>
                            <div class="card-body">
                                <?php
                                $total_fixes = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM CHARGES_INDIRECTES WHERE centre_id IS NOT NULL")->fetchColumn();
                                ?>
                                <div class="alert alert-warning text-center">
                                    <h4><?= number_format($total_fixes, 0, ',', ' ') ?> F</h4>
                                    <small>Charges fixes de la période</small>
                                </div>
                                <div class="text-center mt-3">
                                    <form method="POST">
                                        <button type="submit" class="btn-omega">Calculer les marges sur coût variable</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(!empty($resultats)): ?>
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">📊 Résultats - Direct Costing</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Produit</th>
                                        <th>Prix vente (F)</th>
                                        <th>Coût variable (F)</th>
                                        <th>Marge/SCV (F)</th>
                                        <th>Taux marge (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $ca_total = 0;
                                    $scv_total = 0;
                                    foreach($resultats as $code => $r):
                                        $produit = $produits[array_search($code, array_column($produits, 'code'))];
                                        $ca_produit = $produit['prix_vente'] * $produit['quantite_produite'];
                                        $scv_produit = $r['marge_scv'] * $produit['quantite_produite'];
                                        $ca_total += $ca_produit;
                                        $scv_total += $scv_produit;
                                    ?>
                                    <tr>
                                        <td class="fw-bold"><?= $code ?> - <?= $r['libelle'] ?> </td>
                                        <td class="text-end"><?= number_format($produit['prix_vente'], 0, ',', ' ') ?> F</td>
                                        <td class="text-end"><?= number_format($r['cout_variable'], 0, ',', ' ') ?> F</td>
                                        <td class="text-end fw-bold text-success"><?= number_format($r['marge_scv'], 0, ',', ' ') ?> F</td>
                                        <td class="text-end"><?= number_format($r['taux_marge'], 2) ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <tr class="table-primary fw-bold">
                                        <td class="text-end" colspan="2">TOTAUX :</td>
                                        <td class="text-end"><?= number_format($ca_total - $scv_total, 0, ',', ' ') ?> F</td>
                                        <td class="text-end text-success"><?= number_format($scv_total, 0, ',', ' ') ?> F</td>
                                        <td class="text-end"><?= number_format($scv_total / $ca_total * 100, 2) ?>%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <strong>📊 Résultat net = Marge SCV totale - Charges fixes</strong><br>
                            <?= number_format($scv_total, 0, ',', ' ') ?> F - <?= number_format($total_fixes, 0, ',', ' ') ?> F = 
                            <strong class="<?= ($scv_total - $total_fixes) >= 0 ? 'text-success' : 'text-danger' ?>">
                                <?= number_format($scv_total - $total_fixes, 0, ',', ' ') ?> F
                            </strong>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
