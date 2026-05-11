<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Charges de personnel";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';
$message = '';

// Calcul automatique des charges à partir du brut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'calculer_salaires') {
    $brut = (float)$_POST['salaire_brut'];
    $taux_cnss = 4.5;    // %
    $taux_ipres = 8;     // %
    $taux_css = 7;       // %
    $taux_irpp = 10;     // % simplifié (à affiner)
    
    $cnss_emp = $brut * $taux_cnss / 100;
    $ipres_emp = $brut * $taux_ipres / 100;
    $css_emp = $brut * $taux_css / 100;
    $irpp = $brut * $taux_irpp / 100;
    $net_a_payer = $brut - $cnss_emp - $ipres_emp - $css_emp - $irpp;
    $charges_patronales = $cnss_emp + $ipres_emp + $css_emp;
    
    // Enregistrement en base (table BULLETINS_SALAIRE ou autre) – optionnel
    // Génération des écritures comptables
    $date = date('Y-m-d');
    $mois = date('m');
    $annee = date('Y');
    $ref = "PAIE-$annee-$mois";
    
    $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES 
            (?, 'Salaires bruts', 641, 421, ?, ?, 'PAIE'),
            (?, 'CNSS part employeur', 651, 431, ?, ?, 'PAIE'),
            (?, 'IPRES part employeur', 652, 432, ?, ?, 'PAIE'),
            (?, 'CSS part employeur', 653, 433, ?, ?, 'PAIE'),
            (?, 'IRPP dû', 421, 4442, ?, ?, 'PAIE')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $date, $brut, $ref,
        $date, $cnss_emp, $ref,
        $date, $ipres_emp, $ref,
        $date, $css_emp, $ref,
        $date, $irpp, $ref
    ]);
    $message = "✅ Paie comptabilisée - Brut : " . number_format($brut,0,',',' ') . " F | Net : " . number_format($net_a_payer,0,',',' ') . " F";
}
?>
<div class="row"><div class="col-md-12">
<div class="card"><div class="card-header bg-primary text-white">Comptabilisation des salaires (calcul automatique)</div>
<div class="card-body">
<?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
<form method="POST" class="row g-3">
<input type="hidden" name="action" value="calculer_salaires">
<div class="col-md-4"><label>Salaire brut mensuel (FCFA)</label><input type="number" name="salaire_brut" class="form-control" required></div>
<div class="col-md-4"><button type="submit" class="btn-omega">Comptabiliser</button></div>
</form>
<hr>
<div class="alert alert-info">
<strong>💡 Calculs appliqués automatiquement :</strong><br>
- CNSS employeur : 4,5% du brut<br>
- IPRES employeur : 8% du brut<br>
- CSS employeur : 7% du brut<br>
- IRPP (estimé) : 10% du brut (ajustez le taux si nécessaire)<br>
- Net à payer = Brut - (CNSS + IPRES + CSS + IRPP)
</div>
</div></div></div></div>
<?php include 'inc_footer.php'; ?>
