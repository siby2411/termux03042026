<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Indicateurs d'investissement (VAN, TRI, IP, délai, rentabilité comptable, etc.)";
include 'inc_navbar.php';

$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invest = floatval($_POST['invest']);
    $taux = floatval($_POST['taux']);
    $flux_str = trim($_POST['flux']);
    $flux = array_map('floatval', explode(',', str_replace(' ', '', $flux_str)));
    
    if (count($flux) == 0) {
        $error = "Veuillez saisir au moins un flux.";
    } else {
        // 1. VAN
        $van = -$invest;
        for ($i = 0; $i < count($flux); $i++) {
            $van += $flux[$i] / pow(1 + $taux/100, $i+1);
        }
        
        // 2. IP (indice de profitabilité)
        $ip = ($van / $invest) + 1;
        
        // 3. Délai de récupération simple (pay-back)
        $cumul = 0;
        $delai = null;
        for ($i = 0; $i < count($flux); $i++) {
            $cumul += $flux[$i];
            if ($cumul >= $invest) {
                $reste = $invest - ($cumul - $flux[$i]);
                $delai = ($i + 1) - 1 + ($reste / $flux[$i]);
                break;
            }
        }
        if ($delai === null) $delai = 'Non récupéré';
        
        // 4. Délai de récupération actualisé
        $cumul_act = 0;
        $delai_act = null;
        for ($i = 0; $i < count($flux); $i++) {
            $cumul_act += $flux[$i] / pow(1 + $taux/100, $i+1);
            if ($cumul_act >= $invest) {
                $reste_act = $invest - ($cumul_act - ($flux[$i] / pow(1 + $taux/100, $i+1)));
                $delai_act = ($i + 1) - 1 + ($reste_act / ($flux[$i] / pow(1 + $taux/100, $i+1)));
                break;
            }
        }
        if ($delai_act === null) $delai_act = 'Non récupéré';
        
        // 5. TRI (approximation)
        $tri = null;
        if ($van > 0) {
            $guess = $taux / 100;
            $step = 0.01;
            $iter = 0;
            $van_tri = 1;
            while (abs($van_tri) > 0.01 && $iter < 100) {
                $van_tri = -$invest;
                for ($i = 0; $i < count($flux); $i++) {
                    $van_tri += $flux[$i] / pow(1 + $guess, $i+1);
                }
                if ($van_tri > 0) $guess += $step;
                else $guess -= $step;
                $step /= 2;
                $iter++;
            }
            $tri = $guess * 100;
        } else {
            $tri = "Négatif (projet non rentable)";
        }
        
        // 6. Taux de rentabilité comptable moyen (TRCM)
        $benefice_moyen = array_sum($flux) / count($flux);
        $trcm = ($benefice_moyen / $invest) * 100;
        
        $result = [
            'van' => $van,
            'ip' => $ip,
            'delai' => $delai,
            'delai_actualise' => $delai_act,
            'tri' => $tri,
            'trcm' => $trcm
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .kpi-card { border-radius: 12px; padding: 15px; margin-bottom: 20px; transition: 0.3s; }
        .kpi-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
        .badge-custom { font-size: 0.9rem; padding: 5px 10px; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-calculator-fill"></i> Indicateurs d'investissement complets</h2>
                    <p>VAN, TRI, IP, délai de récupération (simple & actualisé), taux de rentabilité comptable</p>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>📘 Guide des indicateurs</strong><br>
                        - <strong>VAN</strong> : >0 → acceptable.<br>
                        - <strong>TRI</strong> : à comparer au coût du capital.<br>
                        - <strong>IP</strong> : >1 → acceptable.<br>
                        - <strong>Délai récupération</strong> : plus court, moins risqué (actualisé est plus précis).<br>
                        - <strong>TRCM</strong> : taux de rentabilité comptable moyen.
                    </div>

                    <form method="post" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Investissement initial (k€)</label>
                            <input type="number" step="10" name="invest" class="form-control" value="1000" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Taux d'actualisation (%)</label>
                            <input type="number" step="0.5" name="taux" class="form-control" value="8" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Flux annuels (k€) séparés par des virgules</label>
                            <input type="text" name="flux" class="form-control" value="400,450,500" placeholder="ex: 400,450,500" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-lg"><i class="bi bi-bar-chart-steps"></i> Calculer tous les indicateurs</button>
                        </div>
                    </form>

                    <?php if ($result): ?>
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="kpi-card bg-info text-white">
                                    <h4><i class="bi bi-cash-stack"></i> VAN</h4>
                                    <h2><?= number_format($result['van'], 2) ?> k€</h2>
                                    <p class="mb-0"><?= $result['van'] > 0 ? '✅ Projet acceptable' : '❌ Projet non rentable' ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="kpi-card bg-success text-white">
                                    <h4><i class="bi bi-percent"></i> TRI</h4>
                                    <h2><?= is_numeric($result['tri']) ? number_format($result['tri'], 2) . ' %' : $result['tri'] ?></h2>
                                    <p class="mb-0">Comparer au coût du capital</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="kpi-card bg-warning text-dark">
                                    <h4><i class="bi bi-graph-up"></i> Indice de profitabilité (IP)</h4>
                                    <h2><?= number_format($result['ip'], 2) ?></h2>
                                    <p class="mb-0"><?= $result['ip'] > 1 ? '✅ > 1, acceptable' : '❌ < 1, rejet' ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="kpi-card bg-secondary text-white">
                                    <h4><i class="bi bi-clock-history"></i> Délai récupération simple</h4>
                                    <h2><?= is_numeric($result['delai']) ? number_format($result['delai'], 2) . ' ans' : $result['delai'] ?></h2>
                                    <p class="mb-0">Temps pour récupérer l'investissement (sans actualisation)</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="kpi-card bg-dark text-white">
                                    <h4><i class="bi bi-clock-fill"></i> Délai récupération actualisé</h4>
                                    <h2><?= is_numeric($result['delai_actualise']) ? number_format($result['delai_actualise'], 2) . ' ans' : $result['delai_actualise'] ?></h2>
                                    <p class="mb-0">Temps pour récupérer l'investissement (actualisé)</p>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="kpi-card bg-light text-dark">
                                    <h4><i class="bi bi-bar-chart"></i> Taux de rentabilité comptable moyen (TRCM)</h4>
                                    <h2><?= number_format($result['trcm'], 2) ?> %</h2>
                                    <p class="mb-0">Bénéfice annuel moyen / Investissement</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-success mt-3">
                            <strong>💡 Synthèse :</strong>
                            <?php if ($result['van'] > 0 && $result['ip'] > 1): ?>
                                ✔️ Le projet crée de la valeur et est financièrement acceptable.
                            <?php else: ?>
                                ⚠️ Le projet détruit de la valeur. Envisagez de réviser les flux ou l'investissement.
                            <?php endif; ?>
                        </div>
                    <?php elseif ($error): ?>
                        <div class="alert alert-danger mt-3"><?= $error ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
