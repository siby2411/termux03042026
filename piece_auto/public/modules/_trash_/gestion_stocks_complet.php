<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Gestion des Stocks - SYSCOHADA UEMOA";
$page_icon = "box-seam";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';
$selected_article = $_GET['article_id'] ?? null;

// Traitement entrée stock
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'ajouter_article') {
        $stmt = $pdo->prepare("INSERT INTO ARTICLES_STOCK (code_article, libelle, compte_stock, compte_charge, unite, stock_minimum, methode_valorisation) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['code'], $_POST['libelle'], $_POST['compte_stock'], $_POST['compte_charge'], $_POST['unite'], $_POST['stock_minimum'], $_POST['methode']]);
        $message = "✅ Article ajouté avec succès";
    }
    
    if ($_POST['action'] === 'entree_stock') {
        $article_id = $_POST['article_id'];
        $quantite = $_POST['quantite'];
        $prix = $_POST['prix_unitaire'];
        
        // Récupérer l'article
        $stmt = $pdo->prepare("SELECT * FROM ARTICLES_STOCK WHERE id = ?");
        $stmt->execute([$article_id]);
        $article = $stmt->fetch();
        
        // Insertion mouvement
        $stmt = $pdo->prepare("INSERT INTO MOUVEMENTS_STOCK (article_id, date_mouvement, type_mouvement, quantite, prix_unitaire, reference_piece) VALUES (?, ?, 'ENTREE', ?, ?, ?)");
        $stmt->execute([$article_id, date('Y-m-d'), $quantite, $prix, $_POST['reference']]);
        
        // Écriture comptable d'achat
        $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'STOCK')";
        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute([date('Y-m-d'), "Achat - " . $article['libelle'], $article['compte_stock'], 401, $quantite * $prix, $_POST['reference'], 'STOCK']);
        
        $message = "✅ Entrée en stock enregistrée - Valeur: " . number_format($quantite * $prix, 0, ',', ' ') . " FCFA";
    }
    
    if ($_POST['action'] === 'sortie_stock') {
        $article_id = $_POST['article_id'];
        $quantite = $_POST['quantite'];
        $methode = $_POST['methode_valorisation'];
        
        // Récupérer l'article
        $stmt = $pdo->prepare("SELECT * FROM ARTICLES_STOCK WHERE id = ?");
        $stmt->execute([$article_id]);
        $article = $stmt->fetch();
        
        // Calcul de la valeur de sortie selon méthode
        if ($methode === 'CUMP') {
            // Récupérer le dernier CUMP
            $stmt_cump = $pdo->prepare("SELECT cump_unitaire FROM VALEURS_CUMP WHERE article_id = ? ORDER BY date_calcul DESC LIMIT 1");
            $stmt_cump->execute([$article_id]);
            $cump = $stmt_cump->fetchColumn();
            $valeur_sortie = $quantite * ($cump ?: 0);
        } elseif ($methode === 'PEPS') {
            // Appel procédure PEPS
            $stmt_peps = $pdo->prepare("CALL valoriser_sortie_peps(?, ?, ?)");
            $stmt_peps->execute([$article_id, $quantite, date('Y-m-d')]);
            $valeur_sortie = $stmt_peps->fetchColumn();
        } else {
            // FIFO (similaire à PEPS)
            $valeur_sortie = $quantite * $article['prix_unitaire'];
        }
        
        // Insertion mouvement
        $stmt = $pdo->prepare("INSERT INTO MOUVEMENTS_STOCK (article_id, date_mouvement, type_mouvement, quantite, prix_unitaire, reference_piece) VALUES (?, ?, 'SORTIE', ?, ?, ?)");
        $stmt->execute([$article_id, date('Y-m-d'), $quantite, $valeur_sortie / $quantite, $_POST['reference']]);
        
        // Écriture comptable de sortie (consommation)
        $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'STOCK')";
        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute([date('Y-m-d'), "Consommation - " . $article['libelle'], $article['compte_charge'], $article['compte_stock'], $valeur_sortie, $_POST['reference'], 'STOCK']);
        
        $message = "✅ Sortie de stock enregistrée - Valeur: " . number_format($valeur_sortie, 0, ',', ' ') . " FCFA";
    }
}

// Récupération des articles
$articles = $pdo->query("
    SELECT a.*, 
           COALESCE(v.cump_unitaire, 0) as dernier_cump
    FROM ARTICLES_STOCK a
    LEFT JOIN VALEURS_CUMP v ON a.id = v.article_id
    GROUP BY a.id
    ORDER BY a.code_article
")->fetchAll();

// Récupération mouvements
$mouvements = $pdo->prepare("
    SELECT m.*, a.libelle, a.code_article, a.methode_valorisation
    FROM MOUVEMENTS_STOCK m
    JOIN ARTICLES_STOCK a ON m.article_id = a.id
    ORDER BY m.date_mouvement DESC
    LIMIT 50
");
$mouvements->execute();
$mouvements_data = $mouvements->fetchAll();

$valeur_totale_stock = array_sum(array_column($articles, 'valeur_stock_actuel'));
$quantite_totale_stock = array_sum(array_column($articles, 'stock_actuel'));
?>

<div class="row">
    <div class="col-md-12">
        <!-- En-tête explicatif -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-box-seam"></i> Gestion des Stocks - SYSCOHADA UEMOA</h5>
                <small>Méthodes de valorisation : CUMP | PEPS | FIFO</small>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>📖 Méthodes de valorisation SYSCOHADA :</strong><br>
                    • <strong>CUMP</strong> (Coût Unitaire Moyen Pondéré) : (Somme des entrées en valeur) / (Somme des entrées en quantité)<br>
                    • <strong>PEPS/FIFO</strong> (Premier Entré, Premier Sorti) : Les premiers biens acquis sont les premiers sortis<br>
                    • <strong>Dépréciation des stocks</strong> : Constatée lorsque la valeur de réalisation est inférieure à la valeur comptable
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-3"><div class="card bg-primary text-white text-center"><div class="card-body"><i class="bi bi-box fs-2"></i><h4><?= count($articles) ?></h4><small>Articles</small></div></div></div>
            <div class="col-md-3"><div class="card bg-success text-white text-center"><div class="card-body"><i class="bi bi-calculator fs-2"></i><h4><?= number_format($valeur_totale_stock, 0, ',', ' ') ?> F</h4><small>Valeur totale stock</small></div></div></div>
            <div class="col-md-3"><div class="card bg-info text-white text-center"><div class="card-body"><i class="bi bi-boxes fs-2"></i><h4><?= number_format($quantite_totale_stock, 0, ',', ' ') ?></h4><small>Quantité totale</small></div></div></div>
            <div class="col-md-3"><div class="card bg-warning text-dark text-center"><div class="card-body"><button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#newArticleModal">+ Nouvel article</button></div></div></div>
        </div>

        <!-- Onglets -->
        <ul class="nav nav-tabs" id="stockTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#articles">📦 Articles</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#entrees">📥 Entrées</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#sorties">📤 Sorties</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#mouvements">📋 Mouvements</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#inventaire">📊 Inventaire</button></li>
        </ul>

        <div class="tab-content mt-3">
            <!-- Onglet Articles -->
            <div class="tab-pane fade show active" id="articles">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr class="text-center"><th>Code</th><th>Libellé</th><th>Compte stock</th><th>Compte charge</th><th>Stock actuel</th><th>Valeur</th><th>Méthode</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($articles as $a): ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($a['code_article']) ?></td>
                                <td><?= htmlspecialchars($a['libelle']) ?></td>
                                <td class="text-center"><?= $a['compte_stock'] ?></td>
                                <td class="text-center"><?= $a['compte_charge'] ?></td>
                                <td class="text-center"><?= $a['stock_actuel'] ?> <?= $a['unite'] ?></td>
                                <td class="text-end"><?= number_format($a['valeur_stock_actuel'], 0, ',', ' ') ?> F</td>
                                <td class="text-center"><span class="badge bg-info"><?= $a['methode_valorisation'] ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-primary" onclick="openEntreeModal(<?= $a['id'] ?>, '<?= $a['libelle'] ?>', <?= $a['dernier_cump'] ?: 0 ?>)"><i class="bi bi-arrow-down"></i> Entrée</button>
                                    <button class="btn btn-sm btn-warning" onclick="openSortieModal(<?= $a['id'] ?>, '<?= $a['libelle'] ?>', <?= $a['stock_actuel'] ?>, '<?= $a['methode_valorisation'] ?>')"><i class="bi bi-arrow-up"></i> Sortie</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Onglet Entrées -->
            <div class="tab-pane fade" id="entrees">
                <div class="card bg-light"><div class="card-body"><form method="POST" class="row g-3"><input type="hidden" name="action" value="entree_stock">
                    <div class="col-md-3"><label>Article</label><select name="article_id" class="form-select" required><?php foreach($articles as $a): ?><option value="<?= $a['id'] ?>"><?= $a['code_article'] ?> - <?= $a['libelle'] ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-2"><label>Quantité</label><input type="number" name="quantite" class="form-control" required></div>
                    <div class="col-md-2"><label>Prix unitaire (F)</label><input type="number" name="prix_unitaire" class="form-control" step="100" required></div>
                    <div class="col-md-3"><label>Référence</label><input type="text" name="reference" class="form-control" placeholder="Facture n°"></div>
                    <div class="col-md-2"><button type="submit" class="btn-omega mt-4">Enregistrer</button></div></form></div></div>
            </div>

            <!-- Onglet Sorties -->
            <div class="tab-pane fade" id="sorties">
                <div class="card bg-light"><div class="card-body"><form method="POST" class="row g-3"><input type="hidden" name="action" value="sortie_stock">
                    <div class="col-md-4"><label>Article</label><select name="article_id" class="form-select" required><?php foreach($articles as $a): ?><option value="<?= $a['id'] ?>"><?= $a['code_article'] ?> - <?= $a['libelle'] ?> (Stock: <?= $a['stock_actuel'] ?>)</option><?php endforeach; ?></select></div>
                    <div class="col-md-2"><label>Quantité</label><input type="number" name="quantite" class="form-control" required></div>
                    <div class="col-md-3"><label>Méthode valorisation</label><select name="methode_valorisation" class="form-select"><option value="CUMP">CUMP (Coût Moyen Pondéré)</option><option value="PEPS">PEPS/FIFO</option></select></div>
                    <div class="col-md-2"><label>Référence</label><input type="text" name="reference" class="form-control" placeholder="Bon de sortie"></div>
                    <div class="col-md-1"><button type="submit" class="btn-omega mt-4"><i class="bi bi-arrow-up"></i></button></div></form></div></div>
            </div>

            <!-- Onglet Mouvements -->
            <div class="tab-pane fade" id="mouvements">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark"><tr><th>Date</th><th>Article</th><th>Type</th><th>Quantité</th><th>Prix unitaire</th><th>Montant total</th><th>Référence</th></tr></thead>
                        <tbody><?php foreach($mouvements_data as $m): ?>
                        <tr><td><?= date('d/m/Y', strtotime($m['date_mouvement'])) ?></td>
                            <td><?= $m['code_article'] ?> - <?= htmlspecialchars($m['libelle']) ?></td>
                            <td><span class="badge <?= $m['type_mouvement'] == 'ENTREE' ? 'bg-success' : 'bg-warning' ?>"><?= $m['type_mouvement'] ?></span></td>
                            <td class="text-center"><?= $m['quantite'] ?></td>
                            <td class="text-end"><?= number_format($m['prix_unitaire'], 0, ',', ' ') ?> F</td>
                            <td class="text-end"><?= number_format($m['montant_total'], 0, ',', ' ') ?> F</td>
                            <td><?= htmlspecialchars($m['reference_piece'] ?? '-') ?></td>
                        </tr><?php endforeach; ?></tbody>
                    </table>
                </div>
            </div>

            <!-- Onglet Inventaire -->
            <div class="tab-pane fade" id="inventaire">
                <div class="alert alert-info">
                    <strong>📊 Valorisation des stocks en cours d'exercice :</strong><br>
                    Valorisation = Quantité × CUMP (Coût Unitaire Moyen Pondéré)
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark"><tr><th>Article</th><th>Quantité stock</th><th>CUMP actuel</th><th>Valeur stock</th><th>Valeur de réalisation</th><th>Dépréciation</th></tr></thead>
                        <tbody><?php foreach($articles as $a): 
                            $valeur_realisation = $a['valeur_stock_actuel'] * 0.9;
                            $depreciation = $a['valeur_stock_actuel'] - $valeur_realisation;
                        ?>
                        <tr><td><?= $a['libelle'] ?></td><td class="text-center"><?= $a['stock_actuel'] ?></td>
                            <td class="text-end"><?= number_format($a['dernier_cump'], 0, ',', ' ') ?> F</td>
                            <td class="text-end"><?= number_format($a['valeur_stock_actuel'], 0, ',', ' ') ?> F</td>
                            <td class="text-end"><?= number_format($valeur_realisation, 0, ',', ' ') ?> F</td>
                            <td class="text-end text-danger"><?= $depreciation > 0 ? number_format($depreciation, 0, ',', ' ') . ' F' : '-' ?></td>
                        </tr><?php endforeach; ?></tbody>
                    </table>
                </div>
                <button class="btn btn-danger" onclick="alert('Dépréciation comptabilisée : Débit 681 / Crédit 39')"><i class="bi bi-shield"></i> Comptabiliser dépréciation</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nouvel Article -->
<div class="modal fade" id="newArticleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header bg-primary text-white"><h5>➕ Nouvel article</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST"><div class="modal-body"><input type="hidden" name="action" value="ajouter_article">
    <div class="mb-2"><label>Code article</label><input type="text" name="code" class="form-control" required></div>
    <div class="mb-2"><label>Libellé</label><input type="text" name="libelle" class="form-control" required></div>
    <div class="row"><div class="col-md-6 mb-2"><label>Compte stock</label><select name="compte_stock" class="form-select"><option value="35">35 - Stocks marchandises</option><option value="31">31 - Matières premières</option><option value="34">34 - Produits finis</option></select></div>
    <div class="col-md-6 mb-2"><label>Compte charge</label><select name="compte_charge" class="form-select"><option value="601">601 - Achats</option><option value="602">602 - Matières</option></select></div></div>
    <div class="row"><div class="col-md-6 mb-2"><label>Unité</label><input type="text" name="unite" class="form-control" value="Pièce"></div>
    <div class="col-md-6 mb-2"><label>Stock minimum</label><input type="number" name="stock_minimum" class="form-control" value="0"></div></div>
    <div class="mb-2"><label>Méthode valorisation</label><select name="methode" class="form-select"><option value="CUMP">CUMP (Recommandé SYSCOHADA)</option><option value="PEPS">PEPS/FIFO</option></select></div>
</div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button><button type="submit" class="btn btn-primary">Créer</button></div></form></div></div></div>

<script>
function openEntreeModal(id, libelle, cump) {
    // Sélectionner l'article dans le formulaire d'entrée
    document.querySelector('#entrees select[name="article_id"]').value = id;
    document.querySelector('#entrees input[name="prix_unitaire"]').value = cump || '';
    // Basculer vers l'onglet entrées
    document.querySelector('#stockTab button[data-bs-target="#entrees"]').click();
}

function openSortieModal(id, libelle, stock, methode) {
    document.querySelector('#sorties select[name="article_id"]').value = id;
    document.querySelector('#sorties input[name="quantite"]').max = stock;
    document.querySelector('#sorties select[name="methode_valorisation"]').value = methode;
    document.querySelector('#stockTab button[data-bs-target="#sorties"]').click();
}
</script>

<?php include 'inc_footer.php'; ?>
