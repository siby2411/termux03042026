<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Amortissements SYSCOHADA";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$error = '';

// Calcul automatique des amortissements pour nouvel investissement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $compte_immo = (int)$_POST['compte_immo'];
        $libelle = trim($_POST['libelle']);
        $date_acq = $_POST['date_acquisition'];
        $valeur = (float)$_POST['valeur'];
        $duree = (int)$_POST['duree'];
        $type_amort = $_POST['type_amort'] ?? 'LINEAIRE';
        $taux = 100 / $duree;
        $compte_amort = $compte_immo + 100;
        
        if ($valeur > 0 && $duree > 0) {
            try {
                $stmt = $pdo->prepare("INSERT INTO AMORTISSEMENTS (compte_immobilisation, compte_amortissement, libelle, date_acquisition, valeur_originale, duree_ans, type_amort, taux, exercice_en_cours) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$compte_immo, $compte_amort, $libelle, $date_acq, $valeur, $duree, $type_amort, $taux, date('Y')]);
                $message = "✅ Immobilisation enregistrée.";
            } catch (Exception $e) { $error = "Erreur : " . $e->getMessage(); }
        }
    }
    
    if ($_POST['action'] === 'calculer_dotation') {
        $immobilisation_id = (int)$_POST['immobilisation_id'];
        $exercice = (int)$_POST['exercice'];
        $type_calcul = $_POST['type_calcul'];
        $stmt = $pdo->prepare("SELECT * FROM AMORTISSEMENTS WHERE id = ?");
        $stmt->execute([$immobilisation_id]);
        $immo = $stmt->fetch();
        if ($immo) {
            $valeur_originale = $immo['valeur_originale'];
            $taux = $immo['taux'];
            $amortissement_cumule = $immo['amortissement_cumule'];
            $duree_ans = $immo['duree_ans'];
            $date_acquisition = new DateTime($immo['date_acquisition']);
            $annee_acquisition = $date_acquisition->format('Y');
            
            if ($type_calcul === 'LINEAIRE') {
                $annuite = ($valeur_originale * $taux) / 100;
            } elseif ($type_calcul === 'DEGRESSIF') {
                $coefficient = ($duree_ans <= 4) ? 1.5 : (($duree_ans <= 6) ? 2 : 2.5);
                $taux_degressif = $taux * $coefficient;
                $annuite = ($valeur_originale - $amortissement_cumule) * ($taux_degressif / 100);
                if ($duree_ans <= 2) $annuite = ($valeur_originale - $amortissement_cumule) / $duree_ans;
            } else {
                $annuite = ($valeur_originale - $amortissement_cumule) / ($duree_ans - ($exercice - $annee_acquisition));
            }
            
            // Insertion dans DOTATIONS_AMORTISSEMENTS
            $stmt = $pdo->prepare("INSERT INTO DOTATIONS_AMORTISSEMENTS (immobilisation_id, date_dotation, exercice, montant_dotation, amortissement_cumule, valeur_nette_comptable, type_calcul) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$immobilisation_id, date('Y-m-d'), $exercice, $annuite, $amortissement_cumule + $annuite, $valeur_originale - ($amortissement_cumule + $annuite), $type_calcul]);
            
            // Mise à jour de la table AMORTISSEMENTS
            $update = $pdo->prepare("UPDATE AMORTISSEMENTS SET amortissement_cumule = amortissement_cumule + ?, exercice_en_cours = ? WHERE id = ?");
            $update->execute([$annuite, $exercice, $immobilisation_id]);
            
            // ========== ÉCRITURE COMPTABLE ==========
            $compte_amort = $immo['compte_amortissement'];
            $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, 681, ?, ?, ?, 'AMORTISSEMENT')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([date('Y-m-d'), "Dotation amortissement " . $immo['libelle'], $compte_amort, $annuite, "DOT-" . date('Ymd') . "-" . $immobilisation_id]);
            
            $message = "✅ Dotation aux amortissements de " . number_format($annuite, 0, ',', ' ') . " FCFA enregistrée (écriture comptable générée).";
        }
    }
}

$immobilisations = $pdo->query("SELECT * FROM AMORTISSEMENTS WHERE statut = 'ACTIF'")->fetchAll();
$dotations = $pdo->query("SELECT d.*, a.libelle FROM DOTATIONS_AMORTISSEMENTS d JOIN AMORTISSEMENTS a ON d.immobilisation_id = a.id ORDER BY d.exercice DESC")->fetchAll();
?>
<!-- HTML du formulaire (identique à avant) – ne change pas -->
<div class="row"><div class="col-md-12">
<div class="card"><div class="card-header bg-primary text-white"><h5>Amortissements</h5></div>
<div class="card-body">
<?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<form method="POST"><input type="hidden" name="action" value="calculer_dotation">
<div class="row"><div class="col-md-5"><label>Immobilisation</label><select name="immobilisation_id" class="form-select"><?php foreach($immobilisations as $i): ?><option value="<?= $i['id'] ?>"><?= $i['libelle'] ?> (<?= number_format($i['valeur_originale'],0,',',' ') ?> F)</option><?php endforeach; ?></select></div>
<div class="col-md-3"><label>Exercice</label><select name="exercice" class="form-select"><option value="<?= date('Y') ?>"><?= date('Y') ?></option><option value="<?= date('Y')+1 ?>"><?= date('Y')+1 ?></option></select></div>
<div class="col-md-4"><label>Méthode</label><select name="type_calcul" class="form-select"><option value="LINEAIRE">Linéaire</option><option value="DEGRESSIF">Dégressif</option></select></div>
<div class="col-12 text-center mt-3"><button type="submit" class="btn-omega">Calculer et enregistrer la dotation</button></div></div></form>
<hr>
<h6>Historique des dotations</h6>
<div class="table-responsive"><table class="table table-bordered"><thead class="table-dark"><tr><th>Date</th><th>Immobilisation</th><th>Exercice</th><th>Montant</th><th>Amort. cumulé</th><th>VNC</th></tr></thead>
<tbody><?php foreach($dotations as $d): ?><tr><td><?= $d['date_dotation'] ?></td><td><?= $d['libelle'] ?></td><td><?= $d['exercice'] ?></td><td class="text-end"><?= number_format($d['montant_dotation'],0,',',' ') ?> F</td><td class="text-end"><?= number_format($d['amortissement_cumule'],0,',',' ') ?> F</td><td class="text-end"><?= number_format($d['valeur_nette_comptable'],0,',',' ') ?> F</td></tr><?php endforeach; ?></tbody></table></div>
</div></div></div></div>
<?php include 'inc_footer.php'; ?>
