<?php
require 'config/db.php';
$page_title = 'Nouvelle Réservation';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = $_POST;
    if (empty($f['vol_id']) || empty($f['client_id'])) {
        $error = 'Vol et client obligatoires.';
    } else {
        $ref = 'OMG-' . date('Y') . '-' . str_pad($pdo->query("SELECT COUNT(*)+1 FROM reservations")->fetchColumn(), 4, '0', STR_PAD_LEFT);
        $prixMap = ['ECONOMIQUE' => 'prix_eco', 'BUSINESS' => 'prix_business', 'PREMIERE' => 'prix_first'];
        $col = $prixMap[$f['classe'] ?? 'ECONOMIQUE'];
        $prix_unit = (float)$pdo->query("SELECT $col FROM vols WHERE id=" . (int)$f['vol_id'])->fetchColumn();
        $total = $prix_unit * (int)($f['nb_passagers'] ?? 1);
        try {
            $pdo->prepare("INSERT INTO reservations (reference,vol_id,client_id,classe,nb_passagers,prix_total,statut,notes)
            VALUES(:ref,:v,:c,:cl,:nb,:tot,:s,:n)")->execute([
                ':ref'=>$ref,':v'=>$f['vol_id'],':c'=>$f['client_id'],
                ':cl'=>$f['classe']??'ECONOMIQUE',':nb'=>(int)($f['nb_passagers']??1),
                ':tot'=>$total,':s'=>$f['statut']??'EN_ATTENTE',':n'=>$f['notes']??null,
            ]);
            $success = "Réservation <strong>$ref</strong> créée — Total : ".money($total);
        } catch(PDOException $e){ $error='Erreur: '.$e->getMessage(); }
    }
}

$vols = $pdo->query("SELECT v.*, o.code_iata as oc, d.code_iata as dc, o.ville as ov, d.ville as dv
    FROM vols v LEFT JOIN destinations o ON v.origine_id=o.id LEFT JOIN destinations d ON v.destination_id=d.id
    WHERE v.statut IN('PROGRAMME','EN_COURS') ORDER BY v.date_depart ASC")->fetchAll();
$clients = $pdo->query("SELECT * FROM clients ORDER BY nom,prenom")->fetchAll();

require 'includes/header.php';
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Réservations</div>
      <h1>Nouvelle Réservation</h1>
    </div>
    <a href="reservations.php" class="btn btn-ghost">← Retour</a>
  </div>
  <?php if($success) echo "<div class='alert alert-success'>✓ $success</div>"; ?>
  <?php if($error) echo "<div class='alert alert-error'>⚠ ".htmlspecialchars($error)."</div>"; ?>

  <form method="POST" style="max-width:720px">
    <div class="form-section">
      <div class="form-section-title">✈ Vol & Client</div>
      <div class="form-section-body">
        <div class="form-group">
          <label class="form-label">Vol <span class="req">*</span></label>
          <select name="vol_id" class="form-control" required>
            <option value="">— Sélectionner un vol —</option>
            <?php foreach($vols as $v):
              $presel = (($_POST['vol_id']??$_GET['vol_id']??'')==$v['id'])?'selected':''; ?>
            <option value="<?= $v['id'] ?>" <?= $presel ?>>
              <?= htmlspecialchars($v['numero_vol']) ?> — <?= $v['oc'] ?> → <?= $v['dc'] ?>
              (<?= htmlspecialchars($v['ov']) ?> → <?= htmlspecialchars($v['dv']) ?>)
              · <?= date('d/m/Y H:i', strtotime($v['date_depart'])) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Client <span class="req">*</span></label>
          <select name="client_id" class="form-control" required>
            <option value="">— Sélectionner un client —</option>
            <?php foreach($clients as $c):
              $presel = (($_POST['client_id']??$_GET['client_id']??'')==$c['id'])?'selected':''; ?>
            <option value="<?= $c['id'] ?>" <?= $presel ?>>
              <?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?> · <?= htmlspecialchars($c['telephone']??'') ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">💺 Détails du Voyage</div>
      <div class="form-section-body">
        <div class="form-grid form-grid-2">
          <div class="form-group">
            <label class="form-label">Classe</label>
            <select name="classe" class="form-control">
              <?php foreach(['ECONOMIQUE'=>'Économique','BUSINESS'=>'Business','PREMIERE'=>'Première'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= (($_POST['classe']??'ECONOMIQUE')===$v)?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Nombre de passagers</label>
            <input type="number" name="nb_passagers" class="form-control" value="<?= htmlspecialchars($_POST['nb_passagers']??1) ?>" min="1" max="9">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Statut</label>
          <select name="statut" class="form-control">
            <?php foreach(['EN_ATTENTE'=>'En attente','CONFIRMEE'=>'Confirmée','PAYEE'=>'Payée'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= (($_POST['statut']??'EN_ATTENTE')===$v)?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Notes internes</label>
          <textarea name="notes" class="form-control" placeholder="Instructions spéciales, demandes particulières…"><?= htmlspecialchars($_POST['notes']??'') ?></textarea>
        </div>
      </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end">
      <a href="reservations.php" class="btn btn-ghost">Annuler</a>
      <button type="submit" class="btn btn-gold">📋 Créer la Réservation</button>
    </div>
  </form>
</div>
<?php require 'includes/footer.php'; ?>
