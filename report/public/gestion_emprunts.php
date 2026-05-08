<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Gestion des Emprunts et Prêts";
$page_icon = "bank";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';

// Création emprunt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'creer_emprunt') {
    $montant = $_POST['montant'];
    $taux = $_POST['taux'];
    $duree = $_POST['duree'];
    $type = $_POST['type_remboursement'];
    
    // Calcul annuité constante
    $annuite = 0;
    if($type == 'CONSTANTE') {
        $taux_mensuel = $taux / 100 / 12;
        $annuite = $montant * ($taux_mensuel * pow(1+$taux_mensuel, $duree*12)) / (pow(1+$taux_mensuel, $duree*12) - 1);
    } else {
        $annuite = $montant / $duree;
    }
    
    $stmt = $pdo->prepare("INSERT INTO EMPRUNTS (montant, taux, duree, type_remboursement, annuite, statut) VALUES (?, ?, ?, ?, ?, 'ACTIF')");
    $stmt->execute([$montant, $taux, $duree, $type, $annuite]);
    
    // Écriture comptable décaissement
    $ecriture = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, type_ecriture) VALUES (CURDATE(), 'Décaissement emprunt', 521, 164, ?, 'EMPRUNT')");
    $ecriture->execute([$montant]);
    
    $message = "✅ Emprunt créé - Annuité: " . number_format($annuite, 0, ',', ' ') . " F/mois";
}

$emprunts = $pdo->query("SELECT * FROM EMPRUNTS ORDER BY id DESC")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-bank"></i> Emprunts et Prêts - SYSCOHADA</h5>
            </div>
            <div class="card-body">
                
                <!-- Formulaire -->
                <div class="card bg-light mb-4">
                    <div class="card-header">➕ Nouvel emprunt</div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="action" value="creer_emprunt">
                            <div class="col-md-3"><label>Montant</label><input type="number" name="montant" class="form-control" required></div>
                            <div class="col-md-2"><label>Taux (%)</label><input type="number" name="taux" class="form-control" step="0.1" required></div>
                            <div class="col-md-2"><label>Durée (ans)</label><input type="number" name="duree" class="form-control" required></div>
                            <div class="col-md-3"><label>Type remboursement</label><select name="type_remboursement" class="form-select"><option value="CONSTANTE">Annuités constantes</option><option value="IN_FINE">In fine</option></select></div>
                            <div class="col-md-2"><button type="submit" class="btn-omega">Créer</button></div>
                        </form>
                    </div>
                </div>
                
                <!-- Tableau des emprunts -->
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark"><tr><th>Montant</th><th>Taux</th><th>Durée</th><th>Type</th><th>Annuité</th><th>Statut</th></tr></thead>
                        <tbody>
                            <?php foreach($emprunts as $e): ?>
                            <tr>
                                <td class="text-end"><?= number_format($e['montant'], 0, ',', ' ') ?> F</td>
                                <td class="text-center"><?= $e['taux'] ?>%</td>
                                <td class="text-center"><?= $e['duree'] ?> ans</td>
                                <td><?= $e['type_remboursement'] ?></td>
                                <td class="text-end"><?= number_format($e['annuite'], 0, ',', ' ') ?> F</td>
                                <td><span class="badge bg-success"><?= $e['statut'] ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
