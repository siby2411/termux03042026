<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Étude de projets d'investissement";
$page_icon = "briefcase";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$van_result = null;
$tri_result = null;
$ip_result = null;

// Création d'un projet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'creer_projet') {
        $code = trim($_POST['code']);
        $libelle = trim($_POST['libelle']);
        $montant = (float)$_POST['montant'];
        $duree = (int)$_POST['duree'];
        
        $stmt = $pdo->prepare("INSERT INTO PROJETS_INVESTISSEMENT (code, libelle, montant_total, duree_vie) VALUES (?, ?, ?, ?)");
        $stmt->execute([$code, $libelle, $montant, $duree]);
        $message = "✅ Projet créé avec succès";
    }
    
    if ($_POST['action'] === 'calculer_van') {
        $projet_id = (int)$_POST['projet_id'];
        $taux = (float)$_POST['taux_actualisation'];
        
        $stmt = $pdo->prepare("SELECT * FROM PROJETS_INVESTISSEMENT WHERE id = ?");
        $stmt->execute([$projet_id]);
        $projet = $stmt->fetch();
        
        $flux = [];
        for($i = 1; $i <= 10; $i++) {
            $flux[$i] = (float)($_POST["flux_$i"] ?? 0);
        }
        
        // Calcul VAN
        $van = -$projet['montant_total'];
        for($i = 1; $i <= $projet['duree_vie']; $i++) {
            if($flux[$i] > 0) {
                $van += $flux[$i] / pow(1 + ($taux/100), $i);
            }
        }
        
        // Calcul TRI (méthode par itération)
        $tri = 0;
        for($t = 1; $t <= 50; $t++) {
            $tri_test = $t;
            $van_test = -$projet['montant_total'];
            for($i = 1; $i <= $projet['duree_vie']; $i++) {
                if($flux[$i] > 0) {
                    $van_test += $flux[$i] / pow(1 + ($tri_test/100), $i);
                }
            }
            if($van_test <= 0 && $tri == 0) {
                $tri = $tri_test - 1;
                break;
            }
        }
        
        // Indice de rentabilité
        $total_flux = array_sum($flux);
        $ip = ($total_flux / $projet['montant_total']) * 100;
        
        // Sauvegarde
        $stmt = $pdo->prepare("INSERT INTO CRITERES_INVESTISSEMENT (projet_id, van, tri, ip, decision) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE van = ?, tri = ?, ip = ?");
        $decision = ($van > 0 && $tri > $taux) ? 'ACCEPTE' : (($van < 0) ? 'REJETE' : 'A_ETUDIER');
        $stmt->execute([$projet_id, $van, $tri, $ip, $decision, $van, $tri, $ip]);
        
        $van_result = $van;
        $tri_result = $tri;
        $ip_result = $ip;
        
        $message = "✅ Calcul effectué - VAN: " . number_format($van, 0, ',', ' ') . " FCFA";
    }
}

$projets = $pdo->query("SELECT * FROM PROJETS_INVESTISSEMENT ORDER BY code")->fetchAll();
$criteres = $pdo->query("SELECT c.*, p.code, p.libelle FROM CRITERES_INVESTISSEMENT c JOIN PROJETS_INVESTISSEMENT p ON c.projet_id = p.id")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-briefcase"></i> Étude de projets d'investissement</h5>
                <small>VAN - TRI - IP - Délai de récupération</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <ul class="nav nav-tabs" id="projetTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nouveau">➕ Nouveau projet</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#analyse">📊 Analyse VAN/TRI</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#resultats">📈 Résultats des projets</button></li>
                </ul>

                <div class="tab-content mt-3">
                    <!-- Onglet Nouveau projet -->
                    <div class="tab-pane fade show active" id="nouveau">
                        <div class="card bg-light">
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="creer_projet">
                                    <div class="col-md-3"><label>Code projet</label><input type="text" name="code" class="form-control" placeholder="PROJ001" required></div>
                                    <div class="col-md-5"><label>Libellé</label><input type="text" name="libelle" class="form-control" required></div>
                                    <div class="col-md-2"><label>Investissement (F)</label><input type="number" name="montant" class="form-control" step="100000" required></div>
                                    <div class="col-md-2"><label>Durée (ans)</label><input type="number" name="duree" class="form-control" required></div>
                                    <div class="col-12 text-center"><button type="submit" class="btn-omega">Créer le projet</button></div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Analyse VAN/TRI -->
                    <div class="tab-pane fade" id="analyse">
                        <div class="card bg-light">
                            <div class="card-body">
                                <form method="POST" class="row g-3" id="vanForm">
                                    <input type="hidden" name="action" value="calculer_van">
                                    <div class="col-md-4">
                                        <label>Projet</label>
                                        <select name="projet_id" class="form-select" required onchange="chargerFlux(this.value)">
                                            <option value="">-- Sélectionner --</option>
                                            <?php foreach($projets as $p): ?>
                                                <option value="<?= $p['id'] ?>"><?= $p['code'] ?> - <?= $p['libelle'] ?> (<?= number_format($p['montant_total'], 0, ',', ' ') ?> F)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Taux actualisation (%)</label>
                                        <input type="number" name="taux_actualisation" class="form-control" value="12" step="0.5" required>
                                    </div>
                                    <div class="col-md-12">
                                        <h6>Flux de trésorerie annuels (FCFA)</h6>
                                    </div>
                                    <div id="flux_container">
                                        <?php for($i=1; $i<=10; $i++): ?>
                                        <div class="col-md-2 flux-annee" style="display:inline-block; margin-bottom:10px">
                                            <label>Année <?= $i ?></label>
                                            <input type="number" name="flux_<?= $i ?>" class="form-control" step="100000" value="0">
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="col-12 text-center mt-3">
                                        <button type="submit" class="btn-omega">Calculer VAN / TRI</button>
                                    </div>
                                </form>

                                <?php if($van_result !== null): ?>
                                <div class="alert alert-success mt-4">
                                    <h6>📊 Résultats du calcul</h6>
                                    <table class="table table-bordered">
                                        <tr><th>VAN (Valeur Actuelle Nette)</th><td class="text-end fw-bold"><?= number_format($van_result, 0, ',', ' ') ?> F</td><td><?= $van_result > 0 ? '✅ Projet rentable' : '❌ Projet non rentable' ?></td></tr>
                                        <tr><th>TRI (Taux Rentabilité Interne)</th><td class="text-end fw-bold"><?= number_format($tri_result, 2) ?>%</td><td><?= $tri_result > 12 ? '✅ Supérieur au coût du capital' : '⚠️ Inférieur au coût du capital' ?></td></tr>
                                        <tr><th>IP (Indice de Rentabilité)</th><td class="text-end fw-bold"><?= number_format($ip_result, 2) ?>%</td><td><?= $ip_result > 100 ? '✅ Rentable' : '❌ Non rentable' ?></td></tr>
                                    </table>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Résultats des projets -->
                    <div class="tab-pane fade" id="resultats">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Code</th><th>Projet</th><th>Investissement</th><th class="text-end">VAN</th><th>TRI</th><th>IP</th><th>Décision</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($criteres as $c): ?>
                                    <tr>
                                        <td><?= $c['code'] ?> </td>
                                        <td><?= htmlspecialchars($c['libelle']) ?> </td>
                                        <td class="text-end"><?= number_format($c['montant_total'], 0, ',', ' ') ?> F</td>
                                        <td class="text-end <?= $c['van'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($c['van'], 0, ',', ' ') ?> F</td>
                                        <td class="text-center"><?= number_format($c['tri'], 2) ?>%</td>
                                        <td class="text-center"><?= number_format($c['ip'], 2) ?>%</td>
                                        <td class="text-center"><span class="badge <?= $c['decision'] == 'ACCEPTE' ? 'bg-success' : ($c['decision'] == 'REJETE' ? 'bg-danger' : 'bg-warning') ?>"><?= $c['decision'] ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Critères de choix d'investissement -->
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">📋 Critères de choix d'investissement</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card text-center bg-info text-white">
                                    <div class="card-body">
                                        <h5>VAN > 0</h5>
                                        <small>Projet rentable</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center bg-success text-white">
                                    <div class="card-body">
                                        <h5>TRI > WACC</h5>
                                        <small>Rentabilité supérieure au coût du capital</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center bg-warning text-dark">
                                    <div class="card-body">
                                        <h5>IP > 100%</h5>
                                        <small>Valeur créée</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center bg-danger text-white">
                                    <div class="card-body">
                                        <h5>DR < Durée</h5>
                                        <small>Récupération rapide</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
