<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Gestion des approvisionnements - Méthodes de calcul";
$page_icon = "truck";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';

// Données pour les calculs
$demande_moyenne = 500; // unités par mois
$cout_lancement = 50000; // FCFA par commande
$cout_stockage = 10; // FCFA par unité par an
$delai_approvisionnement = 15; // jours
$stock_minimum = 100; // unités
$stock_securite = 50; // unités

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $demande_moyenne = (float)$_POST['demande_moyenne'];
    $cout_lancement = (float)$_POST['cout_lancement'];
    $cout_stockage = (float)$_POST['cout_stockage'];
    $delai_approvisionnement = (int)$_POST['delai_approvisionnement'];
    
    // Calcul de la quantité économique de commande (Wilson)
    $qec = sqrt((2 * $demande_moyenne * 12 * $cout_lancement) / $cout_stockage);
    
    // Stock d'alerte
    $stock_alerte = ($demande_moyenne / 30) * $delai_approvisionnement + $stock_securite;
    
    // Nombre de commandes optimales
    $nb_commandes = ($demande_moyenne * 12) / $qec;
    
    // Coût total de gestion des stocks
    $cout_total = ($demande_moyenne * 12 / $qec) * $cout_lancement + ($qec / 2) * $cout_stockage;
    
    $resultats = [
        'qec' => $qec,
        'stock_alerte' => $stock_alerte,
        'nb_commandes' => $nb_commandes,
        'cout_total' => $cout_total
    ];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-truck"></i> Gestion des approvisionnements</h5>
                <small>Méthodes de calcul : Wilson, stock d'alerte, point de commande</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Méthodes de calcul disponibles :</strong><br>
                    • <strong>Modèle de Wilson</strong> : Quantité économique de commande (QEC)<br>
                    • <strong>Stock d'alerte</strong> : Niveau déclenchant une nouvelle commande<br>
                    • <strong>Point de commande</strong> = Stock d'alerte + Stock de sécurité
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">⚙️ Paramètres de calcul</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-6">
                                        <label>Demande moyenne mensuelle (unités)</label>
                                        <input type="number" name="demande_moyenne" class="form-control" value="<?= $demande_moyenne ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Coût de lancement d'une commande (F)</label>
                                        <input type="number" name="cout_lancement" class="form-control" value="<?= $cout_lancement ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Coût de stockage unitaire (F/an)</label>
                                        <input type="number" name="cout_stockage" class="form-control" step="1" value="<?= $cout_stockage ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Délai d'approvisionnement (jours)</label>
                                        <input type="number" name="delai_approvisionnement" class="form-control" value="<?= $delai_approvisionnement ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-omega w-100">Calculer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <?php if(isset($resultats)): ?>
                        <div class="card">
                            <div class="card-header bg-success text-white">📊 Résultats des calculs</div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr><th>Indicateur</th><th>Valeur</th><th>Formule</th></tr>
                                    <tr><td class="fw-bold">Quantité économique de commande (QEC)</td>
                                        <td class="text-end fw-bold"><?= round($resultats['qec']) ?> unités</td>
                                        <td><small>√(2 × D × Cl / Cs)</small></td>
                                    </tr>
                                    <tr><td class="fw-bold">Stock d'alerte</td>
                                        <td class="text-end fw-bold"><?= round($resultats['stock_alerte']) ?> unités</td>
                                        <td><small>(Demande journalière × Délai) + Stock sécurité</small></td>
                                    </tr>
                                    <tr><td class="fw-bold">Nombre optimal de commandes</td>
                                        <td class="text-end fw-bold"><?= round($resultats['nb_commandes'], 1) ?> / an</td>
                                        <td><small>Demande annuelle / QEC</small></td>
                                    </tr>
                                    <tr><td class="fw-bold">Coût total de gestion</td>
                                        <td class="text-end fw-bold"><?= number_format($resultats['cout_total'], 0, ',', ' ') ?> F</td>
                                        <td><small>(N × Cl) + (QEC/2 × Cs)</small></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Guide des méthodes -->
                <div class="card mt-4">
                    <div class="card-header bg-warning text-dark">📘 Guide des méthodes de réapprovisionnement</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <i class="bi bi-calculator fs-1 text-primary"></i>
                                        <h6>Méthode de Wilson</h6>
                                        <p class="small">Détermine la quantité économique à commander pour minimiser les coûts.</p>
                                        <hr>
                                        <code>QEC = √(2 × D × Cl / Cs)</code>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <i class="bi bi-exclamation-triangle fs-1 text-warning"></i>
                                        <h6>Stock d'alerte</h6>
                                        <p class="small">Niveau de stock déclenchant une nouvelle commande.</p>
                                        <hr>
                                        <code>Salerte = (Dm × Délai) + Ss</code>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <i class="bi bi-shield-check fs-1 text-success"></i>
                                        <h6>Stock de sécurité</h6>
                                        <p class="small">Protège contre les aléas (retards, pics de demande).</p>
                                        <hr>
                                        <code>Ss = k × σ × √(Délai)</code>
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
