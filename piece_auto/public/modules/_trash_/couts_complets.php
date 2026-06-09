<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Méthode des coûts complets - Centres d'analyse";
$page_icon = "building";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

// Récupération des données
$centres = $pdo->query("SELECT * FROM CENTRES_ANALYSE ORDER BY type_centre, code")->fetchAll();
$produits = $pdo->query("SELECT * FROM PRODUITS_CAE ORDER BY code")->fetchAll();
$cles_repartition = $pdo->query("SELECT * FROM CLES_REPARTITION")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des charges indirectes par centre
    $charges_par_centre = [];
    foreach($centres as $c) {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM CHARGES_INDIRECTES WHERE centre_id = ?");
        $stmt->execute([$c['id']]);
        $charges_par_centre[$c['id']] = $stmt->fetchColumn();
    }
    
    // Répartition des centres auxiliaires
    $total_auxiliaire = $charges_par_centre[4] ?? 0;
    foreach($cles_repartition as $cr) {
        $centre_dest = $pdo->prepare("SELECT id FROM CENTRES_ANALYSE WHERE code = ?");
        $centre_dest->execute([$cr['centre_destination']]);
        $dest_id = $centre_dest->fetchColumn();
        if($dest_id) {
            $charges_par_centre[$dest_id] += $total_auxiliaire * $cr['pourcentage'] / 100;
        }
    }
    
    // Calcul des coûts d'unité d'œuvre
    $cout_uo = [];
    foreach($centres as $c) {
        if($c['type_centre'] == 'PRINCIPAL') {
            $total_uo = $pdo->prepare("SELECT COALESCE(SUM(quantite_unite_oeuvre), 0) FROM CONSOMMATIONS_CENTRES WHERE centre_id = ?");
            $total_uo->execute([$c['id']]);
            $total_uo = $total_uo->fetchColumn();
            $cout_uo[$c['id']] = $total_uo > 0 ? $charges_par_centre[$c['id']] / $total_uo : 0;
        }
    }
    
    // Calcul des coûts par produit
    foreach($produits as $p) {
        $cout_total = 0;
        $cout_par_produit = [];
        foreach($centres as $c) {
            if($c['type_centre'] == 'PRINCIPAL') {
                $stmt = $pdo->prepare("SELECT quantite_unite_oeuvre FROM CONSOMMATIONS_CENTRES WHERE produit_id = ? AND centre_id = ?");
                $stmt->execute([$p['id'], $c['id']]);
                $qte = $stmt->fetchColumn();
                $cout_centre = $qte * ($cout_uo[$c['id']] ?? 0);
                $cout_total += $cout_centre;
                $cout_par_produit[$c['code']] = $cout_centre;
            }
        }
        
        $resultats[$p['code']] = [
            'libelle' => $p['libelle'],
            'quantite' => $p['quantite_produite'],
            'cout_total' => $cout_total,
            'cout_unitaire' => $cout_total / $p['quantite_produite'],
            'details' => $cout_par_produit
        ];
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-building"></i> Méthode des coûts complets - Centres d'analyse</h5>
                <small>Répartition des charges indirectes par unité d'œuvre</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Définition :</strong> La méthode des coûts complets consiste à imputer l'ensemble des charges (directes et indirectes) aux produits.<br>
                    <strong>📌 Principe :</strong> Charges indirectes → Centres d'analyse → Unités d'œuvre → Produits
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Centres d'analyse</div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr><th>Code</th><th>Libellé</th><th>Type</th><th>Unité d'œuvre</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($centres as $c): ?>
                                        <tr>
                                            <td class="fw-bold"><?= $c['code'] ?> </td>
                                            <td><?= $c['libelle'] ?> </td>
                                            <td class="text-center"><?= $c['type_centre'] ?> </td>
                                            <td><?= $c['unite_oeuvre'] ?> </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📦 Produits</div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr><th>Code</th><th>Libellé</th><th>Quantité</th><th>Prix vente</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($produits as $p): ?>
                                        <tr>
                                            <td class="fw-bold"><?= $p['code'] ?> </td>
                                            <td><?= $p['libelle'] ?> </td>
                                            <td class="text-end"><?= number_format($p['quantite_produite'], 0, ',', ' ') ?> </td>
                                            <td class="text-end"><?= number_format($p['prix_vente'], 0, ',', ' ') ?> F</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <form method="POST">
                        <button type="submit" class="btn-omega">Calculer les coûts complets</button>
                    </form>
                </div>

                <?php if(!empty($resultats)): ?>
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">📊 Résultats - Coûts complets par produit</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Produit</th>
                                        <th>Quantité</th>
                                        <th>Coût total (F)</th>
                                        <th>Coût unitaire (F)</th>
                                        <th>Prix vente (F)</th>
                                        <th>Résultat unitaire (F)</th>
                                        <th>Marge (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($resultats as $code => $r): ?>
                                    <tr>
                                        <td class="fw-bold"><?= $code ?> - <?= $r['libelle'] ?> </td>
                                        <td class="text-end"><?= number_format($r['quantite'], 0, ',', ' ') ?> </td>
                                        <td class="text-end"><?= number_format($r['cout_total'], 0, ',', ' ') ?> F</td>
                                        <td class="text-end fw-bold"><?= number_format($r['cout_unitaire'], 0, ',', ' ') ?> F</td>
                                        <td class="text-end"><?= number_format($produits[array_search($code, array_column($produits, 'code'))]['prix_vente'], 0, ',', ' ') ?> F</td>
                                        <td class="text-end <?= ($produits[array_search($code, array_column($produits, 'code'))]['prix_vente'] - $r['cout_unitaire']) >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format($produits[array_search($code, array_column($produits, 'code'))]['prix_vente'] - $r['cout_unitaire'], 0, ',', ' ') ?> F
                                         </td>
                                        <td class="text-end <?= $r['cout_unitaire'] > 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format(($produits[array_search($code, array_column($produits, 'code'))]['prix_vente'] - $r['cout_unitaire']) / $produits[array_search($code, array_column($produits, 'code'))]['prix_vente'] * 100, 2) ?>%
                                         </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-secondary mt-3">
                            <strong>📖 Détail des coûts par centre :</strong><br>
                            <?php foreach($resultats as $code => $r): ?>
                                <strong><?= $code ?> :</strong>
                                <?php foreach($r['details'] as $centre => $cout): ?>
                                    <?= $centre ?> = <?= number_format($cout, 0, ',', ' ') ?> F |
                                <?php endforeach; ?>
                                <br>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
