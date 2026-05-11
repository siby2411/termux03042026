<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Inventaire physique des stocks";
$page_icon = "clipboard-data";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
$message = '';

$articles = $pdo->query("SELECT * FROM ARTICLES_STOCK ORDER BY code_article")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'valider_inventaire') {
    $article_id = (int)$_POST['article_id'];
    $quantite_reelle = (int)$_POST['quantite_reelle'];
    $date_inventaire = $_POST['date_inventaire'];
    $stmt = $pdo->prepare("SELECT * FROM ARTICLES_STOCK WHERE id = ?");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();
    if ($article) {
        $stock_theorique = $article['stock_actuel'];
        $ecart = $quantite_reelle - $stock_theorique;
        $prix = $article['prix_unitaire'];
        $valeur_ecart = $ecart * $prix;
        
        // Mise à jour du stock
        $update = $pdo->prepare("UPDATE ARTICLES_STOCK SET stock_actuel = ? WHERE id = ?");
        $update->execute([$quantite_reelle, $article_id]);
        
        // Enregistrement inventaire
        $sql = "INSERT INTO INVENTAIRE_PHYSIQUE (article_id, date_inventaire, quantite_theorique, quantite_reelle, valeur_ecart, statut) VALUES (?, ?, ?, ?, ?, 'VALIDE')";
        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute([$article_id, $date_inventaire, $stock_theorique, $quantite_reelle, abs($valeur_ecart)]);
        
        // Écriture comptable (603)
        if ($ecart != 0) {
            $compte_stock = $article['compte_stock'];
            if ($ecart < 0) {
                $sql_ecr = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 603, ?, ?, ?, 'INVENTAIRE')";
                $stmt3 = $pdo->prepare($sql_ecr);
                $stmt3->execute([$date_inventaire, "Régularisation stock " . $article['libelle'], $compte_stock, abs($valeur_ecart), "INV-" . $article['code_article']]);
            } else {
                $sql_ecr = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, 603, ?, ?, 'INVENTAIRE')";
                $stmt3 = $pdo->prepare($sql_ecr);
                $stmt3->execute([$date_inventaire, "Régularisation stock " . $article['libelle'], $compte_stock, $valeur_ecart, "INV-" . $article['code_article']]);
            }
            $message = "✅ Inventaire validé - Écart : " . $ecart . " unités (" . number_format(abs($valeur_ecart),0,',',' ') . " F). Écriture générée.";
        } else {
            $message = "✅ Inventaire validé - Aucun écart.";
        }
    }
}
?>
<div class="row"><div class="col-md-12"><div class="card"><div class="card-header bg-primary text-white">Inventaire physique</div>
<div class="card-body">
<?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
<form method="POST" class="row g-3"><input type="hidden" name="action" value="valider_inventaire">
<div class="col-md-4"><label>Article</label><select name="article_id" class="form-select"><?php foreach($articles as $a): ?><option value="<?= $a['id'] ?>"><?= $a['code_article'] ?> - <?= $a['libelle'] ?> (stock: <?= $a['stock_actuel'] ?>)</option><?php endforeach; ?></select></div>
<div class="col-md-3"><label>Date</label><input type="date" name="date_inventaire" class="form-control" value="<?= date('Y-m-d') ?>"></div>
<div class="col-md-3"><label>Quantité réelle</label><input type="number" name="quantite_reelle" class="form-control" required></div>
<div class="col-md-2"><button type="submit" class="btn-omega mt-4">Valider</button></div></form>
</div></div></div></div>
<?php include 'inc_footer.php'; ?>
