<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Optimisation BCG & Simplexe – Gestion prévisionnelle";
include 'inc_navbar.php';
require_once dirname(__DIR__) . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .section-title { background: #0d6efd; color: white; padding: 8px 15px; border-radius: 20px; display: inline-block; margin-bottom: 20px; }
        .bcg-card { transition: 0.2s; border-left: 5px solid; margin-bottom: 15px; }
        .vedette { border-left-color: #0d6efd; background-color: #e8f4fd; }
        .vache { border-left-color: #28a745; background-color: #e8f8f0; }
        .dilemme { border-left-color: #ffc107; background-color: #fffae6; }
        .poids-mort { border-left-color: #dc3545; background-color: #fef0f0; }
        .matrix-canvas { max-width: 500px; margin: 0 auto; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-pie-chart"></i> Optimisation BCG & Simplexe – Gestion prévisionnelle</h2>
                    <p>Matrice BCG, pondération stratégique, plan de production optimal (simplexe simplifié)</p>
                </div>
                <div class="card-body">

                    <!-- ==================== SECTION 1 : MATRICE BCG ==================== -->
                    <h4 class="section-title"><i class="bi bi-grid-3x3"></i> 1. Matrice BCG – Positionnement des produits</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="bcgMatrix" class="matrix-canvas"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <strong>📌 Légende :</strong><br>
                                <span class="badge bg-primary">🔵 Vedette</span> : Forte croissance / forte part de marché → Investir<br>
                                <span class="badge bg-success">🟢 Vache à lait</span> : Faible croissance / forte part → Génère du cash<br>
                                <span class="badge bg-warning">🟡 Dilemme</span> : Forte croissance / faible part → Décision stratégique<br>
                                <span class="badge bg-danger">🔴 Poids mort</span> : Faible croissance / faible part → Désinvestir
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 2 : PRODUITS ENREGISTRÉS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-box-seam"></i> 2. Produits et catégorisation BCG</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-dark">
                                <tr><th>Produit</th><th>CA (€)</th><th>Part marché (%)</th><th>Croissance (%)</th><th>Catégorie BCG</th><th>Poids stratégique</th><th>Marge (€/u)</th><th>Temps (h/u)</th></tr>
                            </thead>
                            <tbody>
                                <?php
                                // Insertion de données de démonstration si la table est vide
                                $check = $pdo->query("SELECT COUNT(*) FROM produits_bcg")->fetchColumn();
                                if ($check == 0) {
                                    $pdo->exec("INSERT INTO produits_bcg (code_produit, designation, ca_actuel, part_marche_entreprise, part_marche_concurrent, taux_croissance_marche, marge_unitaire, temps_production) VALUES
                                        ('P001', 'Tablette Pro', 500000, 25, 10, 15, 100, 2),
                                        ('P002', 'Smartphone X', 200000, 5, 20, 12, 120, 2),
                                        ('P003', 'Ordinateur S', 800000, 30, 30, 2, 80, 3),
                                        ('P004', 'Accessoire Eco', 50000, 2, 20, 20, 40, 1)");
                                }
                                
                                $produits = $pdo->query("SELECT * FROM produits_bcg")->fetchAll();
                                foreach ($produits as $p) {
                                    $categorie = $p['categorie_bcg'];
                                    $badge = $categorie == 'Vedette' ? 'primary' : ($categorie == 'VacheLait' ? 'success' : ($categorie == 'Dilemme' ? 'warning' : 'danger'));
                                    echo "<tr>";
                                    echo "<td>{$p['designation']} ({$p['code_produit']})</div></div></div></div></td>";
                                    echo "<td class='text-end'>" . number_format($p['ca_actuel'], 0, ',', ' ') . " €</div></div></div></div></td>";
                                    echo "<td class='text-end'>{$p['part_marche_entreprise']}%</div></div></div></div></td>";
                                    echo "<td class='text-end'>{$p['taux_croissance_marche']}%</div></div></div></div></td>";
                                    echo "<td><span class='badge bg-{$badge}'>{$categorie}</span></div></div></div></div></td>";
                                    echo "<td class='text-end'>{$p['poids_strategique']}</div></div></div></div></td>";
                                    echo "<td class='text-end'>" . number_format($p['marge_unitaire'], 0, ',', ' ') . " €</div></div></div></div></td>";
                                    echo "<td class='text-end'>{$p['temps_production']} h</div></div></div></div></td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== SECTION 3 : SIMPLEXE AVEC PONDÉRATION BCG ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-calculator"></i> 3. Optimisation Simplexe – Allocation des ressources</h4>
                    <div class="card bg-light p-3">
                        <form method="post">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Capacité machine disponible (heures)</label>
                                    <input type="number" name="capacite" class="form-control" value="100" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Demande minimale Vedette (unités)</label>
                                    <input type="number" name="demande_min_vedette" class="form-control" value="10">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Demande minimale Dilemme (unités)</label>
                                    <input type="number" name="demande_min_dilemme" class="form-control" value="5">
                                </div>
                                <div class="col-12">
                                    <button type="submit" name="optimiser" class="btn btn-primary">Lancer l'optimisation (Simplexe pondéré)</button>
                                </div>
                            </div>
                        </form>

                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['optimiser'])) {
                            $capacite = (float)$_POST['capacite'];
                            $demande_min_vedette = (int)$_POST['demande_min_vedette'];
                            $demande_min_dilemme = (int)$_POST['demande_min_dilemme'];
                            
                            // Récupération des produits avec leurs poids BCG
                            $produits = $pdo->query("SELECT code_produit, designation, marge_unitaire, poids_strategique, temps_production, categorie_bcg FROM produits_bcg")->fetchAll();
                            
                            // Calcul de la marge pondérée
                            foreach ($produits as &$p) {
                                $p['marge_ponderee'] = $p['marge_unitaire'] * $p['poids_strategique'];
                                $p['efficacite'] = $p['marge_ponderee'] / $p['temps_production'];
                            }
                            
                            // Tri par efficacité décroissante (simplexe glouton)
                            usort($produits, function($a, $b) { return $b['efficacite'] <=> $a['efficacite']; });
                            
                            $resultats = [];
                            $reste = $capacite;
                            $total_marge = 0;
                            
                            foreach ($produits as $p) {
                                // Déterminer la quantité maximale possible
                                $max_possible = floor($reste / $p['temps_production']);
                                
                                // Appliquer les demandes minimales stratégiques
                                if ($p['categorie_bcg'] == 'Vedette') {
                                    $quantite = max($demande_min_vedette, min($max_possible, 50));
                                } elseif ($p['categorie_bcg'] == 'Dilemme') {
                                    $quantite = max($demande_min_dilemme, min($max_possible, 30));
                                } else {
                                    $quantite = $max_possible;
                                }
                                
                                $quantite = min($quantite, $max_possible);
                                $resultats[] = [
                                    'produit' => $p['designation'],
                                    'categorie' => $p['categorie_bcg'],
                                    'quantite' => $quantite,
                                    'temps_utilise' => $quantite * $p['temps_production'],
                                    'marge_totale' => $quantite * $p['marge_ponderee']
                                ];
                                $reste -= $quantite * $p['temps_production'];
                                $total_marge += $quantite * $p['marge_ponderee'];
                            }
                            
                            echo '<div class="alert alert-success mt-4">';
                            echo '<strong>📊 Plan de production optimal (pondéré par BCG) – Capacité : ' . $capacite . ' h</strong><br>';
                            echo '<div class="table-responsive mt-2"><table class="table table-sm table-bordered">';
                            echo '<thead class="table-dark"><tr><th>Produit</th><th>Catégorie BCG</th><th>Quantité à produire</th><th>Temps utilisé (h)</th><th>Marge pondérée totale (€)</th></tr></thead><tbody>';
                            foreach ($resultats as $r) {
                                $badge = $r['categorie'] == 'Vedette' ? 'primary' : ($r['categorie'] == 'VacheLait' ? 'success' : ($r['categorie'] == 'Dilemme' ? 'warning' : 'danger'));
                                echo "<tr>";
                                echo "<td>{$r['produit']}</div></div></div></div></td>";
                                echo "<td><span class='badge bg-{$badge}'>{$r['categorie']}</span></div></div></div></div></td>";
                                echo "<td class='text-end'>{$r['quantite']}</div></div></div></div></td>";
                                echo "<td class='text-end'>" . round($r['temps_utilise'], 1) . " h</div></div></div></div></td>";
                                echo "<td class='text-end'>" . number_format($r['marge_totale'], 0, ',', ' ') . " €</div></div></div></div></td>";
                                echo "</tr>";
                            }
                            echo "<tr><td colspan='4' class='text-end fw-bold'>Capacité résiduelle : " . round($reste, 1) . " h</div></div></div></div><td class='text-end fw-bold'>" . number_format($total_marge, 0, ',', ' ') . " €</div></div></div></div></tr>";
                            echo '</tbody></table></div>';
                            echo '<p class="mt-2"><strong>🎯 Conclusion :</strong> Le Simplexe pondéré a privilégié les produits "Vedette" et "Dilemme" conformément à la stratégie BCG, même si leur marge brute est parfois inférieure à celle des "Vaches à lait".</p>';
                            echo '</div>';
                            
                            // Sauvegarde en base
                            $cycle_id = time();
                            $stmt = $pdo->prepare("INSERT INTO plan_production_optimise (cycle_id, produit_id, quantite_prevue, marge_ponderee, temps_utilise) VALUES (?, ?, ?, ?, ?)");
                            foreach ($resultats as $r) {
                                $prod = $pdo->query("SELECT id FROM produits_bcg WHERE designation = '{$r['produit']}'")->fetch();
                                if ($prod) {
                                    $stmt->execute([$cycle_id, $prod['id'], $r['quantite'], $r['marge_totale'], $r['temps_utilise']]);
                                }
                            }
                        }
                        ?>
                    </div>

                    <!-- ==================== SECTION 4 : LIEN STRATÉGIE → OPÉRATIONNEL ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-diagram-3"></i> 4. Chaîne de décision : Stratégie (BCG) → Opération (Simplexe)</h4>
                    <div class="row text-center">
                        <div class="col-md-3"><div class="card bg-light"><div class="card-body"><i class="bi bi-bar-chart fs-1"></i><br><strong>1. Matrice BCG</strong><br>Catégorise les produits</div></div></div>
                        <div class="col-md-1 align-self-center"><i class="bi bi-arrow-right fs-2"></i></div>
                        <div class="col-md-3"><div class="card bg-light"><div class="card-body"><i class="bi bi-calculator fs-1"></i><br><strong>2. Pondération stratégique</strong><br>w = 1.5 / 1.2 / 1.0 / 0.8</div></div></div>
                        <div class="col-md-1 align-self-center"><i class="bi bi-arrow-right fs-2"></i></div>
                        <div class="col-md-4"><div class="card bg-light"><div class="card-body"><i class="bi bi-gear fs-1"></i><br><strong>3. Simplexe pondéré</strong><br>Max Z = Σ(w × m × x) sous contraintes</div></div></div>
                    </div>

                    <div class="alert alert-warning mt-4">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Rappel :</strong> Le Simplexe classique maximise la marge brute immédiate. Avec la pondération BCG, il maximise la marge <strong>stratégique</strong> en privilégiant les "Vedettes" (coefficient 1.5) pour soutenir la croissance future de l'entreprise.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Matrice BCG (graphique)
const ctx = document.getElementById('bcgMatrix').getContext('2d');
const produits = <?php
    $prods = $pdo->query("SELECT designation, part_marche_entreprise, part_marche_concurrent, taux_croissance_marche FROM produits_bcg")->fetchAll();
    $data = [];
    foreach ($prods as $p) {
        $part_relative = $p['part_marche_entreprise'] / $p['part_marche_concurrent'];
        $data[] = ['x' => $part_relative, 'y' => $p['taux_croissance_marche'], 'nom' => $p['designation']];
    }
    echo json_encode($data);
?>;

new Chart(ctx, {
    type: 'scatter',
    data: {
        datasets: [{
            label: 'Produits',
            data: produits.map(p => ({x: p.x, y: p.y})),
            backgroundColor: produits.map(p => {
                if (p.x > 1 && p.y > 10) return '#0d6efd';
                if (p.x > 1 && p.y < 10) return '#28a745';
                if (p.x < 1 && p.y > 10) return '#ffc107';
                return '#dc3545';
            }),
            pointRadius: 10,
            pointHoverRadius: 15
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: (ctx) => {
                        const produit = produits[ctx.dataIndex];
                        return `${produit.nom} | Part relative: ${produit.x.toFixed(2)} | Croissance: ${produit.y}%`;
                    }
                }
            }
        },
        scales: {
            x: { title: { display: true, text: 'Part de marché relative' }, min: 0, max: 3, stepSize: 0.5 },
            y: { title: { display: true, text: 'Taux de croissance du marché (%)' }, min: 0, max: 25, stepSize: 5 }
        }
    }
});
</script>
<?php include 'inc_footer.php'; ?>
