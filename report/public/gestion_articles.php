<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Gestion des Articles";
$page_icon = "box";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';

// Ajout article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'add_article') {
    $stmt = $pdo->prepare("INSERT INTO ARTICLES_STOCK (code_article, libelle, compte_stock, compte_charge, unite, prix_unitaire, stock_initial) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_POST['code'], $_POST['libelle'], $_POST['compte_stock'], $_POST['compte_charge'], $_POST['unite'], $_POST['prix'], $_POST['stock_initial']]);
    $message = "✅ Article ajouté";
}

$articles = $pdo->query("SELECT * FROM ARTICLES_STOCK ORDER BY code_article")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-box"></i> Gestion des articles</h5>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-5">
                        <div class="card bg-light">
                            <div class="card-header">➕ Nouvel article</div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="add_article">
                                    <div class="mb-2"><label>Code article</label><input type="text" name="code" class="form-control" placeholder="REF001" required></div>
                                    <div class="mb-2"><label>Libellé</label><input type="text" name="libelle" class="form-control" required></div>
                                    <div class="row"><div class="col-md-6"><label>Prix unitaire (F)</label><input type="number" name="prix" class="form-control" step="100" required></div>
                                    <div class="col-md-6"><label>Unité</label><select name="unite" class="form-select"><option>Pièce</option><option>Kg</option><option>Mètre</option><option>Lot</option></select></div></div>
                                    <div class="row mt-2"><div class="col-md-6"><label>Compte stock</label><select name="compte_stock" class="form-select"><option value="35">35 - Stock marchandises</option><option value="31">31 - Matières premières</option></select></div>
                                    <div class="col-md-6"><label>Stock initial</label><input type="number" name="stock_initial" class="form-control" value="0"></div></div>
                                    <button type="submit" class="btn-omega w-100 mt-2">Ajouter</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark"><tr><th>Code</th><th>Libellé</th><th>Prix unitaire</th><th>Unité</th><th>Stock</th></tr></thead>
                                <tbody>
                                    <?php foreach($articles as $a): ?>
                                    <tr onclick="selectArticle('<?= $a['code_article'] ?>', '<?= $a['libelle'] ?>', <?= $a['prix_unitaire'] ?>)" style="cursor:pointer">
                                        <td><?= $a['code_article'] ?></td><td><?= $a['libelle'] ?></td><td class="text-end"><?= number_format($a['prix_unitaire'], 0, ',', ' ') ?> F</td>
                                        <td class="text-center"><?= $a['unite'] ?></td><td class="text-center"><?= $a['stock_initial'] ?></td>
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

<script>
function selectArticle(code, libelle, prix) {
    // Remplir le formulaire de facturation
    let container = document.querySelector('.ligne-article');
    if(container) {
        let index = document.querySelectorAll('.ligne-article').length;
        container.innerHTML += `
            <div class="row mb-2 ligne-article">
                <div class="col-md-4"><input type="text" class="form-control" value="${code} - ${libelle}" readonly></div>
                <div class="col-md-2"><input type="number" name="quantite[]" class="form-control" value="1" onchange="calculerLigne(this)"></div>
                <div class="col-md-2"><input type="number" name="prix[]" class="form-control" value="${prix}" readonly></div>
                <div class="col-md-2"><input type="number" name="remise[]" class="form-control" value="0" onchange="calculerLigne(this)"></div>
                <div class="col-md-2 montant-ligne text-end">${prix} F</div>
            </div>`;
    }
}
</script>

<?php include 'inc_footer.php'; ?>
