<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Module de retraitement comptable – Guide pédagogique";
include 'inc_navbar.php';
require_once dirname(__DIR__) . '/config/config.php';

// Fonction de calcul du tableau d'amortissement financier
function genererTableauAmortissement($capital, $taux, $duree) {
    $tableau = [];
    $annuite = ($capital * $taux) / (1 - pow(1 + $taux, -$duree));
    $capitalRestant = $capital;
    for ($annee = 1; $annee <= $duree; $annee++) {
        $interets = $capitalRestant * $taux;
        $amortissement = $annuite - $interets;
        $capitalRestant -= $amortissement;
        $tableau[] = [
            'annee' => $annee,
            'interets' => round($interets, 2),
            'amortissement' => round($amortissement, 2),
            'capital_restant' => round(max(0, $capitalRestant), 2),
            'annuite' => round($annuite, 2)
        ];
    }
    return $tableau;
}

// Traitement des formulaires
$message = '';
$contrats = [];
$resultats = [];

// Insertion d'un contrat de leasing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_leasing'])) {
    $nom = trim($_POST['nom_immobilisation']);
    $valeur = (float)$_POST['valeur_origine'];
    $duree = (int)$_POST['duree_ans'];
    $loyer = (float)$_POST['loyer_annuel'];
    $taux = (float)$_POST['taux_interet'] / 100;
    $code_compte = trim($_POST['code_compte_loyer']);
    
    $stmt = $pdo->prepare("INSERT INTO contrats_leasing (nom_immobilisation, valeur_origine, duree_ans, loyer_annuel, taux_interet_annuel, code_compte_loyer) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nom, $valeur, $duree, $loyer, $taux, $code_compte]);
    
    $contrat_id = $pdo->lastInsertId();
    $tableau = genererTableauAmortissement($valeur, $taux, $duree);
    
    $stmt2 = $pdo->prepare("INSERT INTO amortissements_previsionnels (contrat_id, annee, interets, amortissement, capital_restant) VALUES (?, ?, ?, ?, ?)");
    foreach ($tableau as $ligne) {
        $stmt2->execute([$contrat_id, $ligne['annee'], $ligne['interets'], $ligne['amortissement'], $ligne['capital_restant']]);
    }
    $message = "✅ Contrat de leasing ajouté avec succès.";
}

// Simulation de retraitement
$simulation = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simuler_retraitement'])) {
    $contrat_id = (int)$_POST['contrat_id'];
    $annee = (int)$_POST['annee'];
    $stmt = $pdo->prepare("SELECT c.*, a.amortissement, a.interets, a.annuite, a.capital_restant 
                           FROM contrats_leasing c 
                           JOIN amortissements_previsionnels a ON c.id = a.contrat_id 
                           WHERE c.id = ? AND a.annee = ?");
    $stmt->execute([$contrat_id, $annee]);
    $simulation = $stmt->fetch();
}

// Récupération des contrats
$contrats = $pdo->query("SELECT * FROM contrats_leasing ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .section-title { background: #0d6efd; color: white; padding: 8px 15px; border-radius: 20px; display: inline-block; margin-bottom: 20px; }
        .retraitement-card { transition: 0.2s; border-left: 5px solid #0d6efd; margin-bottom: 20px; }
        .retraitement-card:hover { transform: translateX(5px); background-color: #f8f9fa; }
        .compte-t { font-family: monospace; font-size: 0.9rem; }
        .debit { color: #dc3545; font-weight: bold; }
        .credit { color: #28a745; font-weight: bold; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-arrow-repeat"></i> Module de retraitement comptable – Guide pédagogique</h2>
                    <p>Crédit-bail (leasing), retraitements des comptes de résultat, SIG, logs d'audit</p>
                </div>
                <div class="card-body">

                    <!-- ==================== SECTION 1 : GUIDE THÉORIQUE ==================== -->
                    <h4 class="section-title"><i class="bi bi-book"></i> 1. Pourquoi retraiter les comptes ?</h4>
                    <div class="alert alert-info">
                        <strong>📌 Objectif :</strong> Transformer des données comptables (influencées par des règles fiscales ou juridiques) en <strong>données économiques</strong> permettant de comparer la performance réelle des entreprises.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card retraitement-card">
                                <div class="card-header bg-light fw-bold">📊 Crédit-bail (Leasing)</div>
                                <div class="card-body">
                                    <p>Le crédit-bail est une source de financement assimilable à un emprunt. On transforme le loyer (charge d'exploitation) en une <strong>dotation aux amortissements</strong> et des <strong>intérêts financiers</strong>.</p>
                                    <div class="alert alert-secondary">
                                        <strong>Action :</strong><br>
                                        - Augmenter les dotations aux amortissements<br>
                                        - Augmenter les charges financières<br>
                                        - Annuler la charge de loyer dans les charges externes
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card retraitement-card">
                                <div class="card-header bg-light fw-bold">👥 Personnel intérimaire</div>
                                <div class="card-body">
                                    <p>Le personnel intérimaire est une main-d'œuvre opérationnelle, bien que facturée en "Services extérieurs".</p>
                                    <div class="alert alert-secondary">
                                        <strong>Action :</strong><br>
                                        - Reclasser une partie des charges externes (factures d'intérim) en <strong>charges de personnel</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card retraitement-card">
                                <div class="card-header bg-light fw-bold">⚡ Éléments exceptionnels</div>
                                <div class="card-body">
                                    <p>Les charges et produits exceptionnels ne sont pas récurrents.</p>
                                    <div class="alert alert-secondary">
                                        <strong>Action :</strong><br>
                                        - Isoler ces éléments pour calculer un <strong>Résultat d'Exploitation Normatif</strong> (Recurring Operating Income)
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card retraitement-card">
                                <div class="card-header bg-light fw-bold">💰 Crédit Impôt Recherche (CIR)</div>
                                <div class="card-body">
                                    <p>Le CIR est une subvention déguisée en réduction d'impôt.</p>
                                    <div class="alert alert-secondary">
                                        <strong>Action :</strong><br>
                                        - Réintégrer le CIR dans le résultat d'exploitation (en diminution des frais de R&D)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 2 : AJOUTER UN CONTRAT DE LEASING ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-file-earmark-plus"></i> 2. Ajouter un contrat de crédit-bail (leasing)</h4>
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?= $message ?></div>
                    <?php endif; ?>
                    <form method="post" class="row g-3 p-3 bg-light rounded">
                        <div class="col-md-3">
                            <label class="form-label">Nom de l'immobilisation</label>
                            <input type="text" name="nom_immobilisation" class="form-control" placeholder="Ex: Machine industrielle" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Valeur d'origine (€)</label>
                            <input type="number" name="valeur_origine" class="form-control" placeholder="100000" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Durée (années)</label>
                            <input type="number" name="duree_ans" class="form-control" placeholder="5" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Loyer annuel (€)</label>
                            <input type="number" name="loyer_annuel" class="form-control" placeholder="22000" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Taux d'intérêt (%)</label>
                            <input type="number" step="0.1" name="taux_interet" class="form-control" placeholder="5" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Code compte</label>
                            <input type="text" name="code_compte_loyer" class="form-control" placeholder="612" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" name="ajouter_leasing" class="btn btn-primary"><i class="bi bi-save"></i> Ajouter le contrat</button>
                        </div>
                    </form>

                    <!-- ==================== SECTION 3 : SIMULATEUR DE RETRAITEMENT ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-calculator"></i> 3. Simulateur de retraitement – Crédit-bail</h4>
                    <div class="row">
                        <div class="col-md-5">
                            <form method="post" class="bg-light p-3 rounded">
                                <div class="mb-3">
                                    <label class="form-label">Sélectionner un contrat</label>
                                    <select name="contrat_id" class="form-select" required>
                                        <option value="">-- Choisir --</option>
                                        <?php foreach ($contrats as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom_immobilisation']) ?> (<?= number_format($c['valeur_origine'], 0, ',', ' ') ?> €)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Année de retraitement</label>
                                    <input type="number" name="annee" class="form-control" value="1" min="1" required>
                                </div>
                                <button type="submit" name="simuler_retraitement" class="btn btn-success w-100"><i class="bi bi-play"></i> Simuler le retraitement</button>
                            </form>
                        </div>
                        <div class="col-md-7">
                            <?php if ($simulation): ?>
                                <div class="card">
                                    <div class="card-header bg-secondary text-white">Résultat du retraitement – Année <?= $annee ?></div>
                                    <div class="card-body">
                                        <h6>📊 Données du contrat :</h6>
                                        <p>Immobilisation : <?= htmlspecialchars($simulation['nom_immobilisation']) ?><br>
                                        Valeur d'origine : <?= number_format($simulation['valeur_origine'], 0, ',', ' ') ?> €<br>
                                        Loyer annuel : <?= number_format($simulation['loyer_annuel'], 0, ',', ' ') ?> €</p>
                                        
                                        <h6>💰 Calcul du retraitement :</h6>
                                        <table class="table table-bordered table-sm">
                                            <thead class="table-light"><tr><th>Poste</th><th>Montant (€)</th></tr></thead>
                                            <tbody>
                                                <tr><td class="debit">Amortissement théorique (à doter)</td><td class="debit"><?= number_format($simulation['amortissement'], 0, ',', ' ') ?> €</td></tr>
                                                <tr><td class="debit">Intérêts théoriques (charges financières)</td><td class="debit"><?= number_format($simulation['interets'], 0, ',', ' ') ?> €</td></tr>
                                                <tr><td class="credit">Annulation du loyer (compte 612)</td><td class="credit"><?= number_format($simulation['loyer_annuel'], 0, ',', ' ') ?> €</td></tr>
                                            </tbody>
                                        </table>
                                        
                                        <h6>📝 Écriture de retraitement (comptes en T) :</h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered compte-t">
                                                <thead class="table-dark"><tr><th>Compte</th><th>Intitulé</th><th>Débit</th><th>Crédit</th></tr></thead>
                                                <tbody>
                                                    <tr><td class="debit">612</td><td>Loyers (annulation)</td><td class="debit">-</td><td class="credit"><?= number_format($simulation['loyer_annuel'], 0, ',', ' ') ?> €</td></tr>
                                                    <tr><td class="debit">681</td><td>Dotations aux amortissements</td><td class="debit"><?= number_format($simulation['amortissement'], 0, ',', ' ') ?> €</td><td class="credit">-</td></tr>
                                                    <tr><td class="debit">661</td><td>Intérêts (charges financières)</td><td class="debit"><?= number_format($simulation['interets'], 0, ',', ' ') ?> €</td><td class="credit">-</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <h6>📈 Impact sur les indicateurs financiers :</h6>
                                        <ul>
                                            <li><strong>EBE (Excédent Brut d'Exploitation)</strong> : augmente de <span class="text-success"><?= number_format($simulation['loyer_annuel'], 0, ',', ' ') ?> €</span> (car le loyer disparaît)</li>
                                            <li><strong>Résultat d'Exploitation</strong> : impact = +<?= number_format($simulation['loyer_annuel'] - $simulation['amortissement'], 0, ',', ' ') ?> €</li>
                                            <li><strong>Résultat Financier</strong> : diminue de <span class="text-danger"><?= number_format($simulation['interets'], 0, ',', ' ') ?> €</span> (intérêts théoriques)</li>
                                        </ul>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-secondary">Sélectionnez un contrat et une année pour visualiser le retraitement.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- ==================== SECTION 4 : TABLEAU DES RETRAITEMENTS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-table"></i> 4. Tableau des contrats de leasing</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>ID</th><th>Immobilisation</th><th>Valeur (€)</th><th>Durée</th><th>Loyer annuel</th><th>Taux</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contrats as $c): ?>
                                <tr>
                                    <td><?= $c['id'] ?></td>
                                    <td><?= htmlspecialchars($c['nom_immobilisation']) ?></td>
                                    <td class="text-end"><?= number_format($c['valeur_origine'], 0, ',', ' ') ?></td>
                                    <td><?= $c['duree_ans'] ?> ans</div></div></div></div></td>
                                    <td class="text-end"><?= number_format($c['loyer_annuel'], 0, ',', ' ') ?></td>
                                    <td><?= round($c['taux_interet_annuel'] * 100, 1) ?>%</div></div></div></div></td>
                                    <td><button class="btn btn-sm btn-outline-danger" onclick="alert('Fonction à implémenter')">Supprimer</button></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== SECTION 5 : LOGS ET AUDIT ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-journal"></i> 5. Logs de retraitement (audit)</h4>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> Chaque retraitement est enregistré dans la table <code>logs_retraitement</code> avec : exercice, type, compte, montant brut, montant retraité, redressement et justification.
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr><th>Date</th><th>Exercice</th><th>Type</th><th>Compte</th><th>Brut (€)</th><th>Retraité (€)</th><th>Redressement (€)</th><th>Justification</th></tr>
                            </thead>
                            <tbody>
                                <?php
                                $logs = $pdo->query("SELECT * FROM logs_retraitement ORDER BY date_traitement DESC LIMIT 10")->fetchAll();
                                if (empty($logs)) echo '<tr><td colspan="8" class="text-center">Aucun log pour le moment.</td></tr>';
                                foreach ($logs as $log): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($log['date_traitement'])) ?></td>
                                    <td><?= $log['exercice'] ?></td>
                                    <td><?= $log['type_retraitement'] ?></div></div></div></div></td>
                                    <td><?= $log['compte'] ?></div></div></div></div></td>
                                    <td class="text-end"><?= number_format($log['montant_brut'], 0, ',', ' ') ?></td>
                                    <td class="text-end"><?= number_format($log['montant_retraite'], 0, ',', ' ') ?></td>
                                    <td class="text-end <?= $log['redressement'] >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($log['redressement'], 0, ',', ' ') ?></td>
                                    <td><?= htmlspecialchars($log['justification']) ?></div></div></div></div></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Note d'audit :</strong> Les retraitements doivent être documentés et tracés pour garantir la reproductibilité de l'analyse financière.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
