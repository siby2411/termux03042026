<?php
session_start();
if(!isset($_SESSION['user_id'])){ header("Location: index.php"); exit(); }
require_once __DIR__ . '/../includes/db.php';

$societe_id = 1;
$exercice = date('Y');

// 1) Calcul mouvements par compte (débit/crédit)
$sql_mov = "
SELECT pc.compte_id, pc.intitule_compte, pc.classe, pc.solde_normal,
 COALESCE(SUM(CASE WHEN ec.compte_debite_id = pc.compte_id THEN ec.montant ELSE 0 END),0) AS total_debit,
 COALESCE(SUM(CASE WHEN ec.compte_credite_id = pc.compte_id THEN ec.montant ELSE 0 END),0) AS total_credit
FROM PLAN_COMPTABLE_UEMOA pc
LEFT JOIN ECRITURES_COMPTABLES ec
  ON (ec.compte_debite_id = pc.compte_id OR ec.compte_credite_id = pc.compte_id)
  AND ec.societe_id = :societe_id
  AND YEAR(ec.date_operation) = :exercice
GROUP BY pc.compte_id, pc.intitule_compte, pc.classe, pc.solde_normal
";
$stmt = $pdo->prepare($sql_mov);
$stmt->execute([':societe_id'=>$societe_id, ':exercice'=>$exercice]);
$movs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2) Calcul des soldes par compte (sens normal)
$balances = [];
foreach($movs as $m){
    $td = (float)$m['total_debit']; $tc = (float)$m['total_credit'];
    // solde selon solde_normal
    if($m['solde_normal']==='D'){
        $solde = $td - $tc;
    } else {
        $solde = $tc - $td;
    }
    $balances[] = [
        'compte' => $m['compte_id'],
        'intitule' => $m['intitule_compte'],
        'classe' => (int)$m['classe'],
        'solde' => $solde,
        'type' => ($m['classe'] >=6 ? 'RESULTAT' : 'BILAN')
    ];
}

// 3) Agrégation: Compte de Résultat (classes 6 & 7)
$total_charges = $total_produits = 0.0;
foreach($balances as $b){
    if($b['classe']>=6 && $b['classe']<7){ // classe 6 = charges
        $total_charges += $b['solde'];
    } elseif($b['classe']>=7 && $b['classe']<8){ // classe 7 = produits
        $total_produits += $b['solde'];
    } elseif($b['classe']==7){ // defensive in case classes as ints
        $total_produits += $b['solde'];
    }
}
$résultat = $total_produits - $total_charges;

// 4) Bilan : Actif (classes 1-5) / Passif (classes 1-5 but opposite sign)
$actif = $passif = 0.0;
foreach($balances as $b){
    if($b['classe'] >=1 && $b['classe'] <=5){
        if($b['solde'] >= 0) $actif += $b['solde']; else $passif += abs($b['solde']);
    }
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>États financiers - Reporting</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1>États financiers — Exercice <?=htmlspecialchars($exercice)?></h1>
    <div><a class="btn btn-outline-secondary" href="dashboard.php">Retour</a></div>
  </div>

  <div class="row">
    <div class="col-md-6">
      <div class="card mb-3 p-3">
        <h4>Compte de résultat (simplifié)</h4>
        <table class="table table-sm">
          <tr><th>Produits</th><td class="text-end"><?=number_format($total_produits,2,","," ")?></td></tr>
          <tr><th>Charges</th><td class="text-end"><?=number_format($total_charges,2,","," ")?></td></tr>
          <tr class="table-secondary"><th>Résultat net</th><td class="text-end"><?=number_format($résultat,2,","," ")?></td></tr>
        </table>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card mb-3 p-3">
        <h4>Bilan (simplifié)</h4>
        <table class="table table-sm">
          <tr><th>Actif</th><td class="text-end"><?=number_format($actif,2,","," ")?></td></tr>
          <tr><th>Passif</th><td class="text-end"><?=number_format($passif + ($résultat<0?abs($résultat):0),2,","," ")?></td></tr>
          <tr class="table-secondary"><th>Total</th><td class="text-end"><?=number_format(max($actif, $passif + max(0,-$résultat)),2,","," ")?></td></tr>
        </table>
      </div>
    </div>
  </div>

  <h5>Détail des comptes (extraits)</h5>
  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead><tr><th>Compte</th><th>Intitulé</th><th>Classe</th><th class="text-end">Solde</th></tr></thead>
      <tbody>
<?php
foreach($balances as $b){
    echo "<tr><td>{$b['compte']}</td><td>".htmlspecialchars($b['intitule'])."</td><td>{$b['classe']}</td><td class='text-end'>".number_format($b['solde'],2,","," ")."</td></tr>";
}
?>
      </tbody>
    </table>
  </div>

</div>
</body>
</html>
