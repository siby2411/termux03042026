<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Recherche de comptes";
include 'inc_navbar.php';
require_once dirname(__DIR__) . '/config/config.php';

$resultats = [];
$mot_cle = '';
if (isset($_POST['rechercher'])) {
    $mot_cle = trim($_POST['mot_cle']);
    $mode = $_POST['mode'] ?? 'libelle';
    if ($mode == 'libelle') {
        $sql = "SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA WHERE intitule_compte LIKE :q ORDER BY compte_id LIMIT 50";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['q' => "%$mot_cle%"]);
    } else {
        $sql = "SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA WHERE compte_id LIKE :q ORDER BY compte_id LIMIT 50";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['q' => "$mot_cle%"]);
    }
    $resultats = $stmt->fetchAll();
}
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3><i class="bi bi-search"></i> Recherche de comptes (Plan comptable UEMOA)</h3>
                </div>
                <div class="card-body">
                    <form method="post" class="row g-3">
                        <div class="col-md-6">
                            <input type="text" name="mot_cle" class="form-control" placeholder="Saisissez un libellé ou un numéro..." value="<?= htmlspecialchars($mot_cle) ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="mode" class="form-select">
                                <option value="libelle">Recherche par libellé</option>
                                <option value="numero">Recherche par numéro</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" name="rechercher" class="btn btn-primary w-100">Rechercher</button>
                        </div>
                    </form>
                    <?php if ($resultats): ?>
                    <div class="table-responsive mt-4">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>N° compte</th><th>Intitulé</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resultats as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['compte_id']) ?></td>
                                    <td><?= htmlspecialchars($c['intitule_compte']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
