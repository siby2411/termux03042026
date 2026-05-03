<?php
require 'config/db.php';
$page_title = 'Ajouter un Vol';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = $_POST;
    if (empty($f['numero_vol']) || empty($f['date_depart']) || empty($f['date_arrivee'])) {
        $error = 'Les champs obligatoires sont incomplets.';
    } else {
        try {
            $pdo->prepare("INSERT INTO vols (numero_vol,compagnie_id,origine_id,destination_id,date_depart,date_arrivee,type_appareil,places_eco,places_business,places_first,prix_eco,prix_business,prix_first,statut)
            VALUES(:n,:c,:o,:d,:dep,:arr,:ap,:pe,:pb,:pf,:xe,:xb,:xf,:s)")->execute([
                ':n'=>$f['numero_vol'],':c'=>$f['compagnie_id']??null,
                ':o'=>$f['origine_id'],':d'=>$f['destination_id'],
                ':dep'=>$f['date_depart'],':arr'=>$f['date_arrivee'],
                ':ap'=>$f['type_appareil'],
                ':pe'=>(int)($f['places_eco']??0),':pb'=>(int)($f['places_business']??0),':pf'=>(int)($f['places_first']??0),
                ':xe'=>(float)($f['prix_eco']??0),':xb'=>(float)($f['prix_business']??0),':xf'=>(float)($f['prix_first']??0),
                ':s'=>$f['statut']??'PROGRAMME',
            ]);
            $success = 'Vol <strong>'.htmlspecialchars($f['numero_vol']).'</strong> créé avec succès.';
        } catch(PDOException $e){ $error = 'Erreur: '.$e->getMessage(); }
    }
}

$compagnies = $pdo->query("SELECT * FROM compagnies_aeriennes WHERE actif=1 ORDER BY nom")->fetchAll();
$destinations = $pdo->query("SELECT * FROM destinations ORDER BY ville")->fetchAll();

require 'includes/header.php';
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Gestion des Vols</div>
      <h1>Nouveau Vol</h1>
    </div>
    <a href="vols.php" class="btn btn-ghost">← Retour</a>
  </div>

  <?php if($success) echo "<div class='alert alert-success'>✓ $success</div>"; ?>
  <?php if($error) echo "<div class='alert alert-error'>⚠ ".htmlspecialchars($error)."</div>"; ?>

  <form method="POST">
    <div class="form-section">
      <div class="form-section-title">✈ Identification du Vol</div>
      <div class="form-section-body">
        <div class="form-grid form-grid-2">
          <div class="form-group">
            <label class="form-label">Numéro de vol <span class="req">*</span></label>
            <input type="text" name="numero_vol" class="form-control" value="<?= htmlspecialchars($_POST['numero_vol']??'') ?>" placeholder="Ex: AF718" required>
          </div>
          <div class="form-group">
            <label class="form-label">Compagnie aérienne</label>
            <select name="compagnie_id" class="form-control">
              <option value="">— Sélectionner —</option>
              <?php foreach($compagnies as $c): ?>
              <option value="<?= $c['id'] ?>" <?= (($_POST['compagnie_id']??'')==$c['id'])?'selected':'' ?>>
                [<?= $c['code_iata'] ?>] <?= htmlspecialchars($c['nom']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Type d'appareil</label>
          <input type="text" name="type_appareil" class="form-control" value="<?= htmlspecialchars($_POST['type_appareil']??'') ?>" placeholder="Ex: Airbus A330-300, Boeing 777">
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">🗺 Trajet</div>
      <div class="form-section-body">
        <div class="form-grid form-grid-2">
          <div class="form-group">
            <label class="form-label">Origine <span class="req">*</span></label>
            <select name="origine_id" class="form-control" required>
              <option value="">— Aéroport de départ —</option>
              <?php foreach($destinations as $d): ?>
              <option value="<?= $d['id'] ?>" <?= (($_POST['origine_id']??'')==$d['id'])?'selected':'' ?>>
                [<?= $d['code_iata'] ?>] <?= htmlspecialchars($d['ville']) ?>, <?= htmlspecialchars($d['pays']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Destination <span class="req">*</span></label>
            <select name="destination_id" class="form-control" required>
              <option value="">— Aéroport d'arrivée —</option>
              <?php foreach($destinations as $d): ?>
              <option value="<?= $d['id'] ?>" <?= (($_POST['destination_id']??'')==$d['id'])?'selected':'' ?>>
                [<?= $d['code_iata'] ?>] <?= htmlspecialchars($d['ville']) ?>, <?= htmlspecialchars($d['pays']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-grid form-grid-2">
          <div class="form-group">
            <label class="form-label">Date & Heure de départ <span class="req">*</span></label>
            <input type="datetime-local" name="date_depart" class="form-control" value="<?= htmlspecialchars($_POST['date_depart']??'') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Date & Heure d'arrivée <span class="req">*</span></label>
            <input type="datetime-local" name="date_arrivee" class="form-control" value="<?= htmlspecialchars($_POST['date_arrivee']??'') ?>" required>
          </div>
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">💺 Capacité & Tarifs</div>
      <div class="form-section-body">
        <div class="form-grid" style="grid-template-columns:repeat(3,1fr)">
          <div class="form-group">
            <label class="form-label">Places Économique</label>
            <input type="number" name="places_eco" class="form-control" value="<?= htmlspecialchars($_POST['places_eco']??150) ?>" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">Places Business</label>
            <input type="number" name="places_business" class="form-control" value="<?= htmlspecialchars($_POST['places_business']??20) ?>" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">Places Première</label>
            <input type="number" name="places_first" class="form-control" value="<?= htmlspecialchars($_POST['places_first']??0) ?>" min="0">
          </div>
        </div>
        <div class="form-grid" style="grid-template-columns:repeat(3,1fr)">
          <div class="form-group">
            <label class="form-label">Prix Éco (FCFA)</label>
            <input type="number" name="prix_eco" class="form-control" value="<?= htmlspecialchars($_POST['prix_eco']??0) ?>" min="0" step="1000">
          </div>
          <div class="form-group">
            <label class="form-label">Prix Business (FCFA)</label>
            <input type="number" name="prix_business" class="form-control" value="<?= htmlspecialchars($_POST['prix_business']??0) ?>" min="0" step="1000">
          </div>
          <div class="form-group">
            <label class="form-label">Prix Première (FCFA)</label>
            <input type="number" name="prix_first" class="form-control" value="<?= htmlspecialchars($_POST['prix_first']??0) ?>" min="0" step="1000">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Statut</label>
          <select name="statut" class="form-control">
            <?php foreach(['PROGRAMME'=>'▷ Programmé','EN_COURS'=>'▶ En vol','ARRIVE'=>'✓ Arrivé','RETARDE'=>'⚠ Retardé','ANNULE'=>'✕ Annulé'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= (($_POST['statut']??'PROGRAMME')===$v)?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:4px">
      <a href="vols.php" class="btn btn-ghost">Annuler</a>
      <button type="submit" class="btn btn-gold">✈ Enregistrer le Vol</button>
    </div>
  </form>
</div>
<?php require 'includes/footer.php'; ?>
