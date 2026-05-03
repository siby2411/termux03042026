<?php
require 'config/db.php';
$page_title = 'Compagnies Aériennes';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = $_POST;
    if (empty($f['code_iata']) || empty($f['nom'])) { $error = 'Code IATA et nom obligatoires.'; }
    else {
        try {
            $pdo->prepare("INSERT INTO compagnies_aeriennes (code_iata,nom,pays,hub) VALUES(:c,:n,:p,:h)")->execute([
                ':c'=>strtoupper($f['code_iata']),':n'=>$f['nom'],':p'=>$f['pays']??null,':h'=>$f['hub']??null,
            ]);
            $success = 'Compagnie '.htmlspecialchars($f['nom']).' ajoutée.';
        } catch(PDOException $e){ $error='Erreur: '.$e->getMessage(); }
    }
}

$compagnies = $pdo->query("SELECT c.*, COUNT(v.id) as nb_vols
    FROM compagnies_aeriennes c LEFT JOIN vols v ON c.id=v.compagnie_id
    WHERE c.actif=1 GROUP BY c.id ORDER BY c.nom")->fetchAll();
require 'includes/header.php';
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Partenaires</div>
      <h1>Compagnies Aériennes</h1>
      <p><?= count($compagnies) ?> compagnie(s) partenaire(s)</p>
    </div>
  </div>

  <?php if($success) echo "<div class='alert alert-success'>✓ $success</div>"; ?>
  <?php if($error) echo "<div class='alert alert-error'>⚠ ".htmlspecialchars($error)."</div>"; ?>

  <div class="grid-2" style="gap:24px;align-items:start">
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>Code</th><th>Compagnie</th><th>Pays</th><th>Hub</th><th>Vols</th></tr></thead>
          <tbody>
          <?php foreach($compagnies as $c): ?>
          <tr>
            <td>
              <span style="font-family:'Bebas Neue',sans-serif;font-size:1.1rem;letter-spacing:0.06em;
                background:rgba(255,255,255,0.06);padding:3px 9px;border-radius:6px;color:var(--gold)">
                <?= htmlspecialchars($c['code_iata']) ?>
              </span>
            </td>
            <td style="font-weight:600;font-size:0.88rem"><?= htmlspecialchars($c['nom']) ?></td>
            <td style="font-size:0.8rem;color:var(--muted)"><?= htmlspecialchars($c['pays']??'—') ?></td>
            <td style="font-size:0.78rem;color:var(--muted)"><?= htmlspecialchars($c['hub']??'—') ?></td>
            <td><span style="font-family:'Bebas Neue',sans-serif;font-size:1.1rem;color:var(--cyan)"><?= $c['nb_vols'] ?></span></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <div class="card-header"><span class="card-title">+ Nouvelle Compagnie</span></div>
      <div class="card-body">
        <form method="POST">
          <div class="form-grid" style="gap:14px">
            <div class="form-grid form-grid-2">
              <div class="form-group">
                <label class="form-label">Code IATA <span class="req">*</span></label>
                <input type="text" name="code_iata" class="form-control" maxlength="3" placeholder="AF" style="text-transform:uppercase">
              </div>
              <div class="form-group">
                <label class="form-label">Nom <span class="req">*</span></label>
                <input type="text" name="nom" class="form-control" placeholder="Air France">
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Pays</label>
              <input type="text" name="pays" class="form-control" placeholder="France">
            </div>
            <div class="form-group">
              <label class="form-label">Hub principal</label>
              <input type="text" name="hub" class="form-control" placeholder="Paris CDG">
            </div>
            <button type="submit" class="btn btn-gold" style="width:100%">🏢 Ajouter la Compagnie</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
