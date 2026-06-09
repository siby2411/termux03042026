<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Gestion avancée des stocks - CUMP / FIFO / LIFO";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

// Récupération des articles
$articles = $pdo->query("SELECT * FROM ARTICLES_STOCK ORDER BY code_article")->fetchAll();

// Récupération des mouvements
$mouvements = $pdo->query("
    SELECT m.*, a.libelle, a.code_article 
    FROM MOUVEMENTS_STOCK m
    JOIN ARTICLES_STOCK a ON m.article_id = a.id
    ORDER BY m.date_mouvement ASC
")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $article_id = (int)$_POST['article_id'];
    $methode = $_POST['methode'];
    
    // Récupérer les mouvements de l'article
    $stmt = $pdo->prepare("
        SELECT * FROM MOUVEMENTS_STOCK 
        WHERE article_id = ? 
        ORDER BY date_mouvement ASC
    ");
    $stmt->execute([$article_id]);
    $mvmts = $stmt->fetchAll();
    
    // Récupérer l'article
    $stmt = $pdo->prepare("SELECT * FROM ARTICLES_STOCK WHERE id = ?");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();
    
    $entrees = [];
    $sorties = [];
    $stock = [];
    $valeur_stock = 0;
    
    if ($methode == 'CUMP') {
        // Méthode CUMP (Coût Unitaire Moyen Pondéré)
        $quantite_totale = 0;
        $valeur_totale = 0;
        $cump_actuel = 0;
        
        foreach ($mvmts as $m) {
            if ($m['type_mouvement'] == 'ENTREE') {
                $quantite_totale += $m['quantite'];
                $valeur_totale += $m['montant_total'];
                $cump_actuel = $quantite_totale > 0 ? $valeur_totale / $quantite_totale : 0;
                $stock[] = ['date' => $m['date_mouvement'], 'type' => 'ENTREE', 
                           'qte' => $m['quantite'], 'cump' => $cump_actuel, 
                           'valeur' => $m['montant_total']];
            } else {
                $valeur_sortie = $m['quantite'] * $cump_actuel;
                $stock[] = ['date' => $m['date_mouvement'], 'type' => 'SORTIE', 
                           'qte' => $m['quantite'], 'cump' => $cump_actuel, 
                           'valeur' => $valeur_sortie];
                $quantite_totale -= $m['quantite'];
                $valeur_totale -= $valeur_sortie;
            }
        }
        
        $resultats = [
            'methode' => 'CUMP',
            'stock_final_qte' => $quantite_totale,
            'stock_final_valeur' => $valeur_totale,
            'cump_final' => $cump_actuel,
            'detail' => $stock
        ];
        
    } elseif ($methode == 'FIFO') {
        // Méthode FIFO (First In, First Out)
        $file_entrees = [];
        $quantite_stock = 0;
        $valeur_stock = 0;
        
        foreach ($mvmts as $m) {
            if ($m['type_mouvement'] == 'ENTREE') {
                array_push($file_entrees, [
                    'qte' => $m['quantite'],
                    'prix' => $m['prix_unitaire'],
                    'valeur' => $m['montant_total']
                ]);
                $quantite_stock += $m['quantite'];
                $valeur_stock += $m['montant_total'];
            } else {
                $qte_sortie = $m['quantite'];
                $valeur_sortie = 0;
                while ($qte_sortie > 0 && count($file_entrees) > 0) {
                    $lot = &$file_entrees[0];
                    $prise = min($lot['qte'], $qte_sortie);
                    $valeur_sortie += $prise * $lot['prix'];
                    $lot['qte'] -= $prise;
                    $qte_sortie -= $prise;
                    $quantite_stock -= $prise;
                    $valeur_stock -= $prise * $lot['prix'];
                    if ($lot['qte'] == 0) array_shift($file_entrees);
                }
            }
        }
        
        $resultats = [
            'methode' => 'FIFO',
            'stock_final_qte' => $quantite_stock,
            'stock_final_valeur' => $valeur_stock,
            'detail' => $file_entrees
        ];
        
    } elseif ($methode == 'LIFO') {
        // Méthode LIFO (Last In, First Out)
        $pile_entrees = [];
        $quantite_stock = 0;
        $valeur_stock = 0;
        
        foreach ($mvmts as $m) {
            if ($m['type_mouvement'] == 'ENTREE') {
                array_push($pile_entrees, [
                    'qte' => $m['quantite'],
                    'prix' => $m['prix_unitaire'],
                    'valeur' => $m['montant_total']
                ]);
                $quantite_stock += $m['quantite'];
                $valeur_stock += $m['montant_total'];
            } else {
                $qte_sortie = $m['quantite'];
                $valeur_sortie = 0;
                while ($qte_sortie > 0 && count($pile_entrees) > 0) {
                    $lot = &$pile_entrees[count($pile_entrees)-1];
                    $prise = min($lot['qte'], $qte_sortie);
                    $valeur_sortie += $prise * $lot['prix'];
                    $lot['qte'] -= $prise;
                    $qte_sortie -= $prise;
                    $quantite_stock -= $prise;
                    $valeur_stock -= $prise * $lot['prix'];
                    if ($lot['qte'] == 0) array_pop($pile_entrees);
                }
            }
        }
        
        $resultats = [
            'methode' => 'LIFO',
            'stock_final_qte' => $quantite_stock,
            'stock_final_valeur' => $valeur_stock,
            'detail' => $pile_entrees
        ];
    }
    
    // Sauvegarde de l'évaluation
    $stmt = $pdo->prepare("INSERT INTO EVALUATIONS_STOCK (article_id, date_evaluation, methode, quantite, valeur_totale, valeur_unitaire) VALUES (?, CURDATE(), ?, ?, ?, ?)");
    $stmt->execute([$article_id, $methode, $resultats['stock_final_qte'], $resultats['stock_final_valeur'], 
                    $resultats['stock_final_qte'] > 0 ? $resultats['stock_final_valeur'] / $resultats['stock_final_qte'] : 0]);
    
    $message = "✅ Évaluation par méthode $methode terminée";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calculator"></i> Valorisation des stocks - CUMP / FIFO / LIFO</h5>
                <small>Méthodes recommandées par SYSCOHADA</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <!-- Formulaire de sélection -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Sélectionner un article</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-12">
                                        <label>Article</label>
                                        <select name="article_id" class="form-select" required>
                                            <option value="">-- Sélectionner --</option>
                                            <?php foreach($articles as $a): ?>
                                                <option value="<?= $a['id'] ?>"><?= $a['code_article'] ?> - <?= $a['libelle'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label>Méthode de valorisation</label>
                                        <select name="methode" class="form-select" required>
                                            <option value="CUMP">CUMP (Coût Unitaire Moyen Pondéré)</option>
                                            <option value="FIFO">FIFO (Premier Entré, Premier Sorti)</option>
                                            <option value="LIFO">LIFO (Dernier Entré, Premier Sorti)</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-omega w-100">Calculer la valorisation</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <?php if(!empty($resultats)): ?>
                        <div class="card">
                            <div class="card-header bg-success text-white">📊 Résultat - Méthode <?= $resultats['methode'] ?></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card text-center bg-info text-white">
                                            <div class="card-body">
                                                <h5>Stock final</h5>
                                                <h3><?= $resultats['stock_final_qte'] ?> unités</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-center bg-warning text-dark">
                                            <div class="card-body">
                                                <h5>Valeur totale</h5>
                                                <h3><?= number_format($resultats['stock_final_valeur'], 0, ',', ' ') ?> F</h3>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-center bg-success text-white">
                                            <div class="card-body">
                                                <h5>Valeur unitaire</h5>
                                                <h3><?= number_format($resultats['stock_final_qte'] > 0 ? $resultats['stock_final_valeur'] / $resultats['stock_final_qte'] : 0, 0, ',', ' ') ?> F</h3>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if($resultats['methode'] == 'CUMP' && isset($resultats['cump_final'])): ?>
                                <div class="alert alert-secondary mt-2">
                                    <strong>CUMP final :</strong> <?= number_format($resultats['cump_final'], 0, ',', ' ') ?> F/unité
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mouvements des stocks -->
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">📋 Historique des mouvements</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0">
                                <thead class="table-dark">
                                    <tr><th>Date</th><th>Article</th><th>Type</th><th>Quantité</th><th>Prix unitaire</th><th>Montant total</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($mouvements as $m): ?>
                                    <tr>
                                        <td class="text-center"><?= date('d/m/Y', strtotime($m['date_mouvement'])) ?> </td>
                                        <td><?= $m['code_article'] ?> - <?= $m['libelle'] ?> </td>
                                        <td class="text-center"><span class="badge <?= $m['type_mouvement'] == 'ENTREE' ? 'bg-success' : 'bg-warning' ?>"><?= $m['type_mouvement'] ?></span></td>
                                        <td class="text-end"><?= $m['quantite'] ?> <?= $m['unite'] ?> </td>
                                        <td class="text-end"><?= number_format($m['prix_unitaire'], 0, ',', ' ') ?> F</td>
                                        <td class="text-end"><?= number_format($m['montant_total'], 0, ',', ' ') ?> F</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
