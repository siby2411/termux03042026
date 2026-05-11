<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Modèles de saisie";
$page_icon = "speedometer2";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'creer_modele') {
        $stmt = $pdo->prepare("INSERT INTO MODELES_SAISIE (code, libelle, description, compte_debit, compte_credit, montant, type_mouvement) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['code'], $_POST['libelle'], $_POST['description'], $_POST['compte_debit'], $_POST['compte_credit'], $_POST['montant'] ?: null, $_POST['type_mouvement']]);
        $message = "✅ Modèle créé";
    }
    if ($_POST['action'] === 'utiliser_modele') {
        $modele = $pdo->prepare("SELECT * FROM MODELES_SAISIE WHERE id = ?");
        $modele->execute([$_POST['modele_id']]);
        $m = $modele->fetch();
        
        $stmt = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, journal_id, modele_id, type_ecriture) VALUES (CURDATE(), ?, ?, ?, ?, ?, ?, 'AUTOMATIQUE')");
        $stmt->execute([$m['libelle'], $m['compte_debit'], $m['compte_credit'], $_POST['montant'] ?: $m['montant'], 1, $m['id']]);
        $message = "✅ Écriture générée à partir du modèle";
    }
}

$modeles = $pdo->query("SELECT m.*, d.intitule_compte as lib_debit, c.intitule_compte as lib_credit 
                        FROM MODELES_SAISIE m 
                        JOIN PLAN_COMPTABLE_UEMOA d ON m.compte_debit = d.compte_id 
                        JOIN PLAN_COMPTABLE_UEMOA c ON m.compte_credit = c.compte_id 
                        ORDER BY m.type_mouvement, m.code")->fetchAll();
$comptes = $pdo->query("SELECT compte_id, intitule_compte FROM PLAN_COMPTABLE_UEMOA ORDER BY compte_id")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-speedometer2"></i> Modèles de saisie</h5>
                <small>Automatisation des écritures récurrentes</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>
                
                <ul class="nav nav-tabs" id="modeleTab" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#liste">📋 Liste des modèles</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#nouveau">➕ Nouveau modèle</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#utiliser">⚡ Utiliser un modèle</button></li>
                </ul>

                <div class="tab-content mt-3">
                    <div class="tab-pane fade show active" id="liste">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-dark">
                                    <tr><th>Code</th><th>Libellé</th><th>Compte débit</th><th>Compte crédit</th><th>Montant par défaut</th><th>Type</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach($modeles as $m): ?>
                                    <tr>
                                        <td class="text-center fw-bold"><?= $m['code'] ?> </td>
                                        <td><?= htmlspecialchars($m['libelle']) ?> </td>
                                        <td class="text-center"><?= $m['compte_debit'] ?> - <?= $m['lib_debit'] ?> </td>
                                        <td class="text-center"><?= $m['compte_credit'] ?> - <?= $m['lib_credit'] ?> </td>
                                        <td class="text-end"><?= $m['montant'] ? number_format($m['montant'], 0, ',', ' ') . ' F' : 'À saisir' ?> </td>
                                        <td class="text-center"><?= $m['type_mouvement'] ?> </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="nouveau">
                        <div class="card bg-light">
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="creer_modele">
                                    <div class="col-md-3"><label>Code</label><input type="text" name="code" class="form-control" required></div>
                                    <div class="col-md-5"><label>Libellé</label><input type="text" name="libelle" class="form-control" required></div>
                                    <div class="col-md-4"><label>Description</label><input type="text" name="description" class="form-control"></div>
                                    <div class="col-md-4"><label>Compte débit</label><select name="compte_debit" class="form-select"><?php foreach($comptes as $c): ?><option value="<?= $c['compte_id'] ?>"><?= $c['compte_id'] ?> - <?= $c['intitule_compte'] ?></option><?php endforeach; ?></select></div>
                                    <div class="col-md-4"><label>Compte crédit</label><select name="compte_credit" class="form-select"><?php foreach($comptes as $c): ?><option value="<?= $c['compte_id'] ?>"><?= $c['compte_id'] ?> - <?= $c['intitule_compte'] ?></option><?php endforeach; ?></select></div>
                                    <div class="col-md-2"><label>Montant défaut</label><input type="number" name="montant" class="form-control" step="1000"></div>
                                    <div class="col-md-2"><label>Type</label><select name="type_mouvement" class="form-select"><option value="ACHAT">Achat</option><option value="VENTE">Vente</option><option value="CAISSE">Caisse</option><option value="BANQUE">Banque</option><option value="OD">OD</option></select></div>
                                    <div class="col-12 text-center"><button type="submit" class="btn-omega">Créer modèle</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="utiliser">
                        <div class="card bg-light">
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <input type="hidden" name="action" value="utiliser_modele">
                                    <div class="col-md-6"><label>Modèle</label>
                                        <select name="modele_id" class="form-select" required>
                                            <?php foreach($modeles as $m): ?>
                                                <option value="<?= $m['id'] ?>"><?= $m['code'] ?> - <?= $m['libelle'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4"><label>Montant (F)</label><input type="number" name="montant" class="form-control" step="1000" required></div>
                                    <div class="col-md-2"><button type="submit" class="btn-omega mt-4">Générer</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>
