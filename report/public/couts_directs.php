<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Méthode des coûts directs - Direct Costing Évolué";
$page_icon = "arrow-right";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

$produits = $pdo->query("SELECT * FROM PRODUITS_CAE ORDER BY code")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produit_id = (int)$_POST['produit_id'];
    $cout_appro = (float)$_POST['cout_appro'];
    $cout_prod = (float)$_POST['cout_prod'];
    $cout_dist = (float)$_POST['cout_dist'];
    
    $produit = $pdo->prepare("SELECT * FROM PRODUITS_CAE WHERE id = ?");
    $produit->execute([$produit_id]);
    $p = $produit->fetch();
    
    // Récupération des coûts variables directs
    $stmt = $pdo->prepare("SELECT * FROM COUTS_VARIABLES WHERE produit_id = ?");
    $stmt->execute([$produit_id]);
    $cv = $stmt->fetch();
    
    $cout_variable_unitaire = ($cv['matieres_premieres'] + $cv['main_oeuvre_directe'] + $cv['energie'] + $cv['autres_charges_variables']) / $p['quantite_produite'];
    $marge_brute = $p['prix_vente'] - $cout_variable_unitaire;
    
    $resultats = [
        'code' => $p['code'],
        'libelle' => $p['libelle'],
        'cout_variable' => $cout_variable_unitaire,
        'cout_appro' => $cout_appro,
        'cout_prod' => $cout_prod,
        'cout_dist' => $cout_dist,
        'cout_total' => $cout_variable_unitaire + $cout_appro + $cout_prod + $cout_dist,
        'marge_brute' => $marge_brute,
        'resultat' => $p['prix_vente'] - ($cout_variable_unitaire + $cout_appro + $cout_prod + $cout_dist)
    ];
    
    $message = "✅ Calcul effectué pour le produit " . $p['code'];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-arrow-right"></i> Méthode des coûts directs - Direct Costing Évolué</h5>
                <small>Seuls les coûts directement attribuables sont pris en compte</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Définition :</strong> Le direct costing évolué distingue les charges variables des charges fixes directes.<br>
                    <strong>📌 Principe :</strong> Marge = Prix de vente - Coûts variables - Coûts fixes spécifiques
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📦 Sélection du produit</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-12">
                                        <label>Produit</label>
                                        <select name="produit_id" class="form-select" required>
                                            <option value="">-- Sélectionner --</option>
                                            <?php foreach($produits as $p): ?>
                                                <option value="<?= $p['id'] ?>"><?= $p['code'] ?> - <?= $p['libelle'] ?> (<?= number_format($p['prix_vente'], 0, ',', ' ') ?> F)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Coûts d'approvisionnement (F)</label>
                                        <input type="number" name="cout_appro" class="form-control" step="1000" value="50000">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Coûts de production (F)</label>
                                        <input type="number" name="cout_prod" class="form-control" step="1000" value="150000">
                                    </div>
                                    <div class="col-md-4">
                                        <label>Coûts de distribution (F)</label>
                                        <input type="number" name="cout_dist" class="form-control" step="1000" value="50000">
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn-omega">Calculer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if(!empty($resultats)): ?>
                        <div class="card">
                            <div class="card-header bg-success text-white">📊 Résultat - Produit <?= $resultats['code'] ?></div>
                            <div class="card-body">
                                <div class="alert alert-primary">
                                    <strong>📊 Coûts variables unitaires :</strong> <?= number_format($resultats['cout_variable'], 0, ',', ' ') ?> F
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="alert alert-secondary">
                                            <strong>💰 Prix de vente</strong><br>
                                            <?= number_format($resultats['cout_variable'] + $resultats['marge_brute'], 0, ',', ' ') ?> F
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-secondary">
                                            <strong>📉 Coûts directs fixes</strong><br>
                                            <?= number_format($resultats['cout_appro'] + $resultats['cout_prod'] + $resultats['cout_dist'], 0, ',', ' ') ?> F
                                        </div>
                                    </div>
                                </div>
                                <div class="alert <?= $resultats['resultat'] >= 0 ? 'alert-success' : 'alert-danger' ?> text-center">
                                    <strong>Résultat unitaire :</strong> <?= number_format($resultats['resultat'], 0, ',', ' ') ?> F
                                    <?= $resultats['resultat'] >= 0 ? '✅' : '❌' ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
