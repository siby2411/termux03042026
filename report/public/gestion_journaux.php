<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Gestion des Journaux";
$page_icon = "journal";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';

// Création d'un nouveau journal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'creer_journal') {
    $code = strtoupper(trim($_POST['code']));
    $libelle = trim($_POST['libelle']);
    $type = $_POST['type_journal'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO JOURNAUX (code, libelle, type_journal) VALUES (?, ?, ?)");
        $stmt->execute([$code, $libelle, $type]);
        $message = "✅ Journal $code créé avec succès";
    } catch(Exception $e) {
        $message = "❌ Erreur : " . $e->getMessage();
    }
}

$journaux = $pdo->query("SELECT * FROM JOURNAUX ORDER BY type_journal, code")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-journal"></i> Gestion des Journaux</h5>
                <small>Multi-journaux : Achats, Ventes, Caisse, Banque, Opérations Diverses</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-5">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">➕ Nouveau journal</div>
                            <div class="card-body">
                                <form method="POST">
                                    <input type="hidden" name="action" value="creer_journal">
                                    <div class="mb-2"><label>Code (2-5 caractères)</label><input type="text" name="code" class="form-control" placeholder="AC, VE, BK, OD" required></div>
                                    <div class="mb-2"><label>Libellé</label><input type="text" name="libelle" class="form-control" placeholder="Achats, Ventes, Banque..." required></div>
                                    <div class="mb-2"><label>Type de journal</label>
                                        <select name="type_journal" class="form-select">
                                            <option value="ACHATS">Achats</option><option value="VENTES">Ventes</option>
                                            <option value="CAISSE">Caisse</option><option value="BANQUE">Banque</option>
                                            <option value="OD">Opérations Diverses</option>
                                            <option value="SITUATION">Situation</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn-omega w-100">Créer le journal</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <h6>Journaux existants</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Code</th><th>Libellé</th><th>Type</th><th>Dernier numéro</th><th>Statut</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($journaux as $j): ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?= $j['code'] ?> </td>
                                        <td><?= htmlspecialchars($j['libelle']) ?> <table>
                                        <td class="text-center"><?= $j['type_journal'] ?> </td>
                                        <td class="text-center"><?= $j['dernier_numero'] ?> </td>
                                        <td class="text-center"><?= $j['actif'] ? '<span class="badge bg-success">Actif</span>' : '<span class="badge bg-danger">Inactif</span>' ?> </td>
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
