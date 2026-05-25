<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Méthode ABC - Activity-Based Costing";
$page_icon = "grid";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

$activites = $pdo->query("SELECT * FROM ACTIVITES_ABC ORDER BY code")->fetchAll();
$produits = $pdo->query("SELECT * FROM PRODUITS_CAE ORDER BY code")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des charges indirectes par activité
    $charges_par_activite = [];
    foreach($activites as $a) {
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM CHARGES_INDIRECTES WHERE activite_id = ?");
        $stmt->execute([$a['id']]);
        $charges_par_activite[$a['id']] = $stmt->fetchColumn();
    }
    
    // Calcul des coûts unitaires des inducteurs
    $cout_inducteur = [];
    foreach($activites as $a) {
        $total_inducteurs = $pdo->prepare("SELECT COALESCE(SUM(nombre_inducteurs), 0) FROM CONSOMMATIONS_ACTIVITES WHERE activite_id = ?");
        $total_inducteurs->execute([$a['id']]);
        $total_inducteurs = $total_inducteurs->fetchColumn();
        $cout_inducteur[$a['id']] = $total_inducteurs > 0 ? $charges_par_activite[$a['id']] / $total_inducteurs : 0;
    }
    
    // Calcul des coûts par produit
    foreach($produits as $p) {
        $cout_total = 0;
        $details = [];
        foreach($activites as $a) {
            $stmt = $pdo->prepare("SELECT nombre_inducteurs FROM CONSOMMATIONS_ACTIVITES WHERE produit_id = ? AND activite_id = ?");
            $stmt->execute([$p['id'], $a['id']]);
            $nb_inducteurs = $stmt->fetchColumn();
            $cout_activite = $nb_inducteurs * ($cout_inducteur[$a['id']] ?? 0);
            $cout_total += $cout_activite;
            $details[$a['code']] = $cout_activite;
        }
        
        $resultats[$p['code']] = [
            'libelle' => $p['libelle'],
            'quantite' => $p['quantite_produite'],
            'cout_total' => $cout_total,
            'cout_unitaire' => $cout_total / $p['quantite_produite'],
            'details' => $details
        ];
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-grid"></i> Méthode ABC - Activity-Based Costing</h5>
                <small>Coûts par activités - La méthode la plus précise</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Définition :</strong> L'ABC consiste à identifier les activités qui consomment des ressources puis à imputer leurs coûts aux produits en fonction de leur consommation réelle.<br>
                    <strong>📌 Principe :</strong> Ressources → Activités → Inducteurs → Produits
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Activités et inducteurs</div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0">
                                    <thead class="table-light">
                                        <tr><th>Code</th><th>Libellé</th><th>Inducteur</th><th>Charges</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($activites as $a): 
                                            $stmt = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM CHARGES_INDIRECTES WHERE activite_id = ?");
                                            $stmt->execute([$a['id']]);
                                            $charges = $stmt->fetchColumn();
                                        ?>
                                        <tr>
                                            <td class="fw-bold"><?= $a['code'] ?> </td>
                                            <td><?= $a['libelle'] ?> </td>
                                            <td><?= $a['inducteur'] ?> </td>
                                            <td class="text-end"><?= number_format($charges, 0, ',', ' ') ?> F</td>
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
                        <button type="submit" class="btn-omega">Calculer les coûts ABC</button>
                    </form>
                </div>

                <?php if(!empty($resultats)): ?>
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">📊 Résultats - Méthode ABC</div>
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
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-secondary mt-3">
                            <strong>📖 Détail des coûts par activité :</strong><br>
                            <?php foreach($resultats as $code => $r): ?>
                                <strong><?= $code ?> :</strong>
                                <?php foreach($r['details'] as $activite => $cout): ?>
                                    <?= $activite ?> = <?= number_format($cout, 0, ',', ' ') ?> F |
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
