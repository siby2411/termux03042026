<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Facturation';
$pdo = getPDO();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// ── STATUT UPDATE ──
if ($action === 'statut' && $id) {
    $s = $_GET['s'] ?? 'payee';
    $pdo->prepare("UPDATE factures SET statut=? WHERE id=?")->execute([$s, $id]);
    flash('Statut mis à jour.', 'success');
    secureRedirect('factures.php');
}

// ── DELETE ──
if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM factures WHERE id=?")->execute([$id]);
    flash('Facture supprimée.', 'success');
    secureRedirect('factures.php');
}

// ── SAVE FACTURE ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'save') {
    $clientId  = (int)$_POST['client_id'];
    $date      = $_POST['date_facture'];
    $tva       = (float)$_POST['tva'];
    $notes     = trim($_POST['notes'] ?? '');
    $prodIds   = $_POST['produit_id'] ?? [];
    $desigs    = $_POST['designation'] ?? [];
    $qtys      = $_POST['quantite'] ?? [];
    $unites    = $_POST['unite'] ?? [];
    $prix      = $_POST['prix_unitaire'] ?? [];

    // Filtrer lignes vides
    $lignes = [];
    foreach ($prodIds as $i => $pid) {
        $qty = (float)($qtys[$i] ?? 0);
        $pu  = (float)($prix[$i] ?? 0);
        if ($qty > 0 && $pu > 0) {
            $lignes[] = [
                'produit_id'   => (int)$pid,
                'designation'  => trim($desigs[$i] ?? ''),
                'quantite'     => $qty,
                'unite'        => $unites[$i] ?? 'kg',
                'prix_unitaire'=> $pu,
                'total_ligne'  => $qty * $pu,
            ];
        }
    }
    if (empty($lignes)) { flash('Au moins une ligne de produit requise.', 'error'); secureRedirect('factures.php?action=new'); }

    $totalHT  = array_sum(array_column($lignes, 'total_ligne'));
    $totalTTC = $totalHT * (1 + $tva / 100);
    $numero   = 'FAC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

    if ($id) {
        // UPDATE
        $pdo->prepare("UPDATE factures SET client_id=?,date_facture=?,total_ht=?,tva=?,total_ttc=?,notes=? WHERE id=?")
            ->execute([$clientId ?: null, $date, $totalHT, $tva, $totalTTC, $notes, $id]);
        $pdo->prepare("DELETE FROM facture_lignes WHERE facture_id=?")->execute([$id]);
    } else {
        $pdo->prepare("INSERT INTO factures (client_id,numero,date_facture,total_ht,tva,total_ttc,statut,notes)
            VALUES (?,?,?,?,?,?,'emise',?)")
            ->execute([$clientId ?: null, $numero, $date, $totalHT, $tva, $totalTTC, $notes]);
        $id = $pdo->lastInsertId();
    }

    $st = $pdo->prepare("INSERT INTO facture_lignes (facture_id,produit_id,designation,quantite,unite,prix_unitaire,total_ligne)
        VALUES (?,?,?,?,?,?,?)");
    foreach ($lignes as $l) {
        $st->execute([$id, $l['produit_id'] ?: null, $l['designation'], $l['quantite'], $l['unite'], $l['prix_unitaire'], $l['total_ligne']]);
        // décrémenter stock
        if ($l['produit_id']) {
            $pdo->prepare("UPDATE produits SET stock_actuel=GREATEST(0,stock_actuel-?) WHERE id=?")
                ->execute([$l['quantite'], $l['produit_id']]);
        }
    }
    // Insérer ventes
    $vSt = $pdo->prepare("INSERT INTO ventes (facture_id,produit_id,client_id,quantite,prix_unitaire,total,date_vente)
        VALUES (?,?,?,?,?,?,?)");
    foreach ($lignes as $l) {
        if ($l['produit_id']) {
            $vSt->execute([$id, $l['produit_id'], $clientId ?: null, $l['quantite'], $l['prix_unitaire'], $l['total_ligne'], $date]);
        }
    }
    flash('Facture enregistrée avec succès.', 'success');
    secureRedirect('factures.php?action=view&id='.$id);
}

// ── DATA ──
$clients  = $pdo->query("SELECT * FROM clients ORDER BY nom")->fetchAll();
$produits = $pdo->query("SELECT p.*,c.nom as cat_nom FROM produits p LEFT JOIN categories c ON p.categorie_id=c.id WHERE p.actif=1 ORDER BY p.nom")->fetchAll();

// ── VIEW SINGLE ──
$facture = null; $lignes = [];
if (in_array($action, ['view', 'edit']) && $id) {
    $s = $pdo->prepare("SELECT f.*,CONCAT(c.prenom,' ',c.nom) as client_nom,c.telephone,c.adresse,c.email
        FROM factures f LEFT JOIN clients c ON f.client_id=c.id WHERE f.id=?");
    $s->execute([$id]); $facture = $s->fetch();
    if (!$facture) { flash('Facture introuvable.', 'error'); secureRedirect('factures.php'); }
    $sl = $pdo->prepare("SELECT fl.*,p.nom as prod_nom FROM facture_lignes fl LEFT JOIN produits p ON fl.produit_id=p.id WHERE fl.facture_id=?");
    $sl->execute([$id]); $lignes = $sl->fetchAll();
}

// ── LIST ──
$factures = $pdo->query("SELECT f.*,CONCAT(c.prenom,' ',c.nom) as client_nom
    FROM factures f LEFT JOIN clients c ON f.client_id=c.id ORDER BY f.date_facture DESC, f.id DESC")->fetchAll();

require_once 'header.php';
?>

<div class="page-header">
  <h1><i class="fas fa-file-invoice" style="color:var(--or)"></i> <span>Facturation</span></h1>
  <p>Création et gestion des factures clients</p>
</div>

<?php if ($action === 'view' && $facture): ?>
<!-- ══ APERÇU FACTURE ══ -->
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap">
  <a href="factures.php" class="btn-omega btn-omega-outline">← Retour</a>
  <a href="factures.php?action=print&id=<?=$id?>" target="_blank" class="btn-omega btn-omega-gold"><i class="fas fa-print"></i> Imprimer</a>
  <?php if($facture['statut']==='emise'): ?>
    <a href="factures.php?action=statut&id=<?=$id?>&s=payee" class="btn-omega btn-omega-success"><i class="fas fa-check"></i> Marquer Payée</a>
  <?php endif; ?>
  <a href="factures.php?action=delete&id=<?=$id?>" class="btn-omega btn-omega-danger btn-delete"><i class="fas fa-trash"></i> Supprimer</a>
</div>

<div class="card-omega" id="factureDoc">
  <div class="card-body">
    <!-- EN-TÊTE -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:30px;flex-wrap:wrap;gap:20px">
      <div>
        <h2 style="font-family:'Playfair Display',serif;color:var(--or);margin:0">🥩 OMEGA CHARCUTERIE</h2>
        <p style="color:var(--muted);font-size:.85rem;margin:5px 0 0">OMEGA INFORMATIQUE CONSULTING<br>Zone Commerciale, Dakar, Sénégal<br>Tél: +221 33 XXX XX XX</p>
      </div>
      <div style="text-align:right">
        <h1 style="font-size:2rem;color:var(--rouge);font-family:'Playfair Display',serif;margin:0">FACTURE</h1>
        <p style="color:var(--text);margin:5px 0 0;font-weight:700"><?= htmlspecialchars($facture['numero']) ?></p>
        <p style="color:var(--muted);font-size:.85rem">Date : <?= date('d/m/Y', strtotime($facture['date_facture'])) ?></p>
        <?php
        $sCls = ['emise'=>'b-warning','payee'=>'b-success','annulee'=>'b-danger','brouillon'=>'b-muted'];
        $sLbl = ['emise'=>'📤 Émise','payee'=>'✅ Payée','annulee'=>'❌ Annulée','brouillon'=>'✏️ Brouillon'];
        ?>
        <span class="badge-stat <?= $sCls[$facture['statut']] ?>"><?= $sLbl[$facture['statut']] ?></span>
      </div>
    </div>
    <!-- CLIENT -->
    <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:15px 20px;margin-bottom:25px">
      <strong style="color:var(--or);font-size:.8rem;letter-spacing:1px">FACTURÉ À</strong>
      <p style="margin:8px 0 0;color:var(--text)">
        <strong><?= htmlspecialchars($facture['client_nom']??'Client comptoir') ?></strong><br>
        <?php if($facture['telephone']): ?><small><?= htmlspecialchars($facture['telephone']) ?></small><?php endif; ?>
        <?php if($facture['adresse']): ?><br><small style="color:var(--muted)"><?= htmlspecialchars($facture['adresse']) ?></small><?php endif; ?>
      </p>
    </div>
    <!-- LIGNES -->
    <table class="table-omega" style="margin-bottom:20px">
      <thead><tr><th>#</th><th>Désignation</th><th>Qté</th><th>Unité</th><th>P.U. (FCFA)</th><th>Total (FCFA)</th></tr></thead>
      <tbody>
        <?php foreach($lignes as $i=>$l): ?>
        <tr>
          <td><?=$i+1?></td>
          <td><strong><?= htmlspecialchars($l['designation']?:$l['prod_nom']??'—') ?></strong></td>
          <td><?= number_format($l['quantite'],3) ?></td>
          <td><?= htmlspecialchars($l['unite']) ?></td>
          <td><?= number_format($l['prix_unitaire'],0,',',' ') ?></td>
          <td><strong><?= number_format($l['total_ligne'],0,',',' ') ?></strong></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <!-- TOTAUX -->
    <div style="display:flex;justify-content:flex-end">
      <div style="min-width:280px">
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
          <span style="color:var(--muted)">Sous-total HT</span>
          <strong><?= number_format($facture['total_ht'],0,',',' ') ?> FCFA</strong>
        </div>
        <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--border)">
          <span style="color:var(--muted)">TVA (<?= $facture['tva'] ?>%)</span>
          <span><?= number_format($facture['total_ht']*($facture['tva']/100),0,',',' ') ?> FCFA</span>
        </div>
        <div style="display:flex;justify-content:space-between;padding:12px 0;border-top:2px solid var(--or);margin-top:5px">
          <strong style="color:var(--or);font-size:1.1rem">TOTAL TTC</strong>
          <strong style="color:var(--or);font-size:1.3rem"><?= number_format($facture['total_ttc'],0,',',' ') ?> FCFA</strong>
        </div>
      </div>
    </div>
    <?php if($facture['notes']): ?>
    <div style="margin-top:20px;padding:12px 15px;background:rgba(255,255,255,.03);border-radius:8px;font-size:.85rem;color:var(--muted)">
      <strong>Notes :</strong> <?= htmlspecialchars($facture['notes']) ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php elseif (in_array($action, ['new', 'edit'])): ?>
<!-- ══ FORMULAIRE FACTURE ══ -->
<div class="card-omega">
  <div class="card-head">
    <h4><i class="fas fa-file-alt"></i> <?= $id ? 'Modifier la Facture' : 'Nouvelle Facture' ?></h4>
    <a href="factures.php" class="btn-omega btn-omega-outline">← Retour</a>
  </div>
  <div class="card-body">
    <form method="POST" action="factures.php?action=save<?= $id?"&id=$id":'' ?>" class="form-omega">
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label">Client</label>
          <select name="client_id" class="form-select">
            <option value="">Client comptoir</option>
            <?php foreach($clients as $c): ?>
            <option value="<?=$c['id']?>" <?= ($facture['client_id']??0)==$c['id']?'selected':'' ?>>
              <?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Date</label>
          <input type="date" name="date_facture" class="form-control" required value="<?= $facture['date_facture']??date('Y-m-d') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">TVA (%)</label>
          <input type="number" name="tva" class="form-control" min="0" max="100" step="0.1" value="<?= $facture['tva']??18 ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Notes / Observations</label>
          <input type="text" name="notes" class="form-control" placeholder="Remarques..." value="<?= htmlspecialchars($facture['notes']??'') ?>">
        </div>
      </div>

      <!-- LIGNES PRODUITS -->
      <h5 style="color:var(--or);border-bottom:1px solid var(--border);padding-bottom:10px;margin-bottom:15px">
        <i class="fas fa-list"></i> Lignes de Facture
      </h5>
      <div id="lignesFact">
        <?php
        $existLines = $lignes;
        // Pad to 10 min
        while(count($existLines) < 10) $existLines[] = [];
        foreach($existLines as $i=>$l):
        ?>
        <div class="ligne-row" style="display:grid;grid-template-columns:2fr 3fr 1fr 1fr 1.5fr 1fr auto;gap:8px;align-items:end;margin-bottom:10px;padding:12px;background:rgba(255,255,255,.02);border-radius:10px;border:1px solid var(--border)">
          <div>
            <label class="form-label" style="font-size:.72rem">Produit</label>
            <select name="produit_id[]" class="form-select prod-select" onchange="fillLine(this,<?=$i?>)">
              <option value="">-- Produit --</option>
              <?php foreach($produits as $p): ?>
              <option value="<?=$p['id']?>"
                data-prix="<?=$p['prix_vente']?>"
                data-unite="<?=$p['unite']?>"
                data-nom="<?= htmlspecialchars($p['nom']) ?>"
                <?= ($l['produit_id']??0)==$p['id']?'selected':'' ?>>
                <?= htmlspecialchars($p['nom']) ?> – <?= number_format($p['prix_vente'],0,',',' ') ?> FCFA/<?=$p['unite']?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="form-label" style="font-size:.72rem">Désignation</label>
            <input type="text" name="designation[]" class="form-control" placeholder="Description ligne..."
              value="<?= htmlspecialchars($l['designation']??'') ?>">
          </div>
          <div>
            <label class="form-label" style="font-size:.72rem">Quantité</label>
            <input type="number" name="quantite[]" class="form-control qte-input" min="0" step="0.001"
              value="<?= $l['quantite']??'' ?>" oninput="calcLine(<?=$i?>)">
          </div>
          <div>
            <label class="form-label" style="font-size:.72rem">Unité</label>
            <input type="text" name="unite[]" class="form-control" value="<?= htmlspecialchars($l['unite']??'kg') ?>" style="width:70px">
          </div>
          <div>
            <label class="form-label" style="font-size:.72rem">Prix Unitaire (FCFA)</label>
            <input type="number" name="prix_unitaire[]" class="form-control pu-input" min="0" step="0.01"
              value="<?= $l['prix_unitaire']??'' ?>" oninput="calcLine(<?=$i?>)">
          </div>
          <div>
            <label class="form-label" style="font-size:.72rem">Total</label>
            <input type="text" class="form-control total-line" id="total<?=$i?>" readonly
              value="<?= $l['total_ligne']??0 ?>" style="background:rgba(212,172,13,.08);color:var(--or);font-weight:700">
          </div>
          <div style="padding-bottom:0">
            <label class="form-label" style="font-size:.72rem">&nbsp;</label><br>
            <button type="button" onclick="clearLine(this)" style="background:rgba(192,57,43,.2);border:1px solid rgba(192,57,43,.3);color:var(--rouge);border-radius:8px;padding:8px 10px;cursor:pointer;width:36px">✕</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <button type="button" onclick="addLine()" class="btn-omega btn-omega-outline" style="margin-bottom:20px">
        <i class="fas fa-plus"></i> Ajouter une ligne
      </button>

      <!-- TOTAUX -->
      <div style="display:flex;justify-content:flex-end;margin-bottom:25px">
        <div style="min-width:300px;background:rgba(212,172,13,.05);border:1px solid rgba(212,172,13,.2);border-radius:12px;padding:20px">
          <div style="display:flex;justify-content:space-between;padding:6px 0;color:var(--muted);font-size:.9rem">
            <span>Sous-total HT</span><strong id="dispHT">0 FCFA</strong>
          </div>
          <div style="display:flex;justify-content:space-between;padding:6px 0;color:var(--muted);font-size:.9rem">
            <span>TVA (<span id="tvaPct">18</span>%)</span><span id="dispTVA">0 FCFA</span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:10px 0 0;border-top:2px solid var(--or);margin-top:8px">
            <strong style="color:var(--or)">TOTAL TTC</strong>
            <strong style="color:var(--or);font-size:1.2rem" id="dispTTC">0 FCFA</strong>
          </div>
        </div>
      </div>
      <div style="display:flex;gap:12px">
        <button type="submit" class="btn-omega btn-omega-primary"><i class="fas fa-save"></i> Enregistrer la Facture</button>
        <a href="factures.php" class="btn-omega btn-omega-outline">Annuler</a>
      </div>
    </form>
  </div>
</div>

<script>
const produits = <?= json_encode(array_column($produits, null, 'id')) ?>;
let lineCount = <?= count($existLines) ?>;

function fillLine(sel, idx) {
  const pid = sel.value;
  const row = sel.closest('.ligne-row');
  if (pid && produits[pid]) {
    const p = produits[pid];
    row.querySelector('[name="designation[]"]').value = p.nom;
    row.querySelector('[name="prix_unitaire[]"]').value = p.prix_vente;
    row.querySelector('[name="unite[]"]').value = p.unite;
    calcLine(idx);
  }
}
function calcLine(idx) {
  const rows = document.querySelectorAll('.ligne-row');
  if (!rows[idx]) return;
  const row = rows[idx];
  const qte = parseFloat(row.querySelector('.qte-input').value)||0;
  const pu  = parseFloat(row.querySelector('.pu-input').value)||0;
  row.querySelector('.total-line').value = (qte*pu).toLocaleString('fr');
  calcTotals();
}
function clearLine(btn) {
  const row = btn.closest('.ligne-row');
  row.querySelectorAll('input').forEach(i=>i.value='');
  row.querySelectorAll('select').forEach(s=>s.selectedIndex=0);
  calcTotals();
}
function calcTotals() {
  let ht=0;
  document.querySelectorAll('.total-line').forEach(t=>{
    const v=parseFloat(t.value.replace(/\s/g,'').replace(',','.'))||0; ht+=v;
  });
  const tva=parseFloat(document.querySelector('[name=tva]').value)||0;
  const ttc=ht*(1+tva/100);
  document.getElementById('dispHT').textContent=ht.toLocaleString('fr')+' FCFA';
  document.getElementById('tvaPct').textContent=tva;
  document.getElementById('dispTVA').textContent=(ht*tva/100).toLocaleString('fr')+' FCFA';
  document.getElementById('dispTTC').textContent=ttc.toLocaleString('fr')+' FCFA';
}
document.querySelector('[name=tva]').addEventListener('input', calcTotals);

function addLine() {
  const idx = lineCount++;
  const html=`<div class="ligne-row" style="display:grid;grid-template-columns:2fr 3fr 1fr 1fr 1.5fr 1fr auto;gap:8px;align-items:end;margin-bottom:10px;padding:12px;background:rgba(255,255,255,.02);border-radius:10px;border:1px solid var(--border)">
    <div><label class="form-label" style="font-size:.72rem">Produit</label>
    <select name="produit_id[]" class="form-select prod-select" onchange="fillLine(this,${idx})">
      <option value="">-- Produit --</option>
      <?php foreach($produits as $p): echo '<option value="'.$p['id'].'" data-prix="'.$p['prix_vente'].'" data-unite="'.htmlspecialchars($p['unite']).'" data-nom="'.htmlspecialchars(addslashes($p['nom'])).'">'.htmlspecialchars($p['nom']).' – '.number_format($p['prix_vente'],0,',',' ').' FCFA/'.$p['unite'].'</option>'; endforeach; ?>
    </select></div>
    <div><label class="form-label" style="font-size:.72rem">Désignation</label><input type="text" name="designation[]" class="form-control" placeholder="Description..."></div>
    <div><label class="form-label" style="font-size:.72rem">Quantité</label><input type="number" name="quantite[]" class="form-control qte-input" min="0" step="0.001" oninput="calcLine(${idx})"></div>
    <div><label class="form-label" style="font-size:.72rem">Unité</label><input type="text" name="unite[]" class="form-control" value="kg" style="width:70px"></div>
    <div><label class="form-label" style="font-size:.72rem">Prix Unitaire</label><input type="number" name="prix_unitaire[]" class="form-control pu-input" min="0" step="0.01" oninput="calcLine(${idx})"></div>
    <div><label class="form-label" style="font-size:.72rem">Total</label><input type="text" class="form-control total-line" id="total${idx}" readonly style="background:rgba(212,172,13,.08);color:var(--or);font-weight:700"></div>
    <div><label class="form-label" style="font-size:.72rem">&nbsp;</label><br><button type="button" onclick="clearLine(this)" style="background:rgba(192,57,43,.2);border:1px solid rgba(192,57,43,.3);color:var(--rouge);border-radius:8px;padding:8px 10px;cursor:pointer;width:36px">✕</button></div>
  </div>`;
  document.getElementById('lignesFact').insertAdjacentHTML('beforeend',html);
}
calcTotals();
</script>

<?php else: ?>
<!-- ══ LISTE FACTURES ══ -->
<div class="card-omega">
  <div class="card-head">
    <h4><i class="fas fa-list"></i> Liste des Factures</h4>
    <a href="factures.php?action=new" class="btn-omega btn-omega-primary"><i class="fas fa-plus"></i> Nouvelle Facture</a>
  </div>
  <div style="overflow-x:auto">
    <table class="table-omega">
      <thead><tr><th>N° Facture</th><th>Client</th><th>Date</th><th>Total HT</th><th>TVA</th><th>Total TTC</th><th>Statut</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if(empty($factures)): ?>
          <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:40px">Aucune facture</td></tr>
        <?php else: foreach($factures as $f):
          $sCls=['emise'=>'b-warning','payee'=>'b-success','annulee'=>'b-danger','brouillon'=>'b-muted'];
          $sLbl=['emise'=>'📤 Émise','payee'=>'✅ Payée','annulee'=>'❌ Annulée','brouillon'=>'✏️ Brouillon'];
        ?>
        <tr>
          <td><strong><?= htmlspecialchars($f['numero']) ?></strong></td>
          <td><?= htmlspecialchars($f['client_nom']??'Client comptoir') ?></td>
          <td><?= date('d/m/Y',strtotime($f['date_facture'])) ?></td>
          <td><?= number_format($f['total_ht'],0,',',' ') ?></td>
          <td><?= $f['tva'] ?>%</td>
          <td><strong style="color:var(--or)"><?= number_format($f['total_ttc'],0,',',' ') ?> FCFA</strong></td>
          <td><span class="badge-stat <?= $sCls[$f['statut']] ?>"><?= $sLbl[$f['statut']] ?></span></td>
          <td>
            <div style="display:flex;gap:5px;flex-wrap:wrap">
              <a href="factures.php?action=view&id=<?=$f['id']?>" class="btn-omega btn-omega-outline" style="padding:5px 10px;font-size:.72rem"><i class="fas fa-eye"></i></a>
              <?php if($f['statut']==='emise'): ?>
                <a href="factures.php?action=statut&id=<?=$f['id']?>&s=payee" class="btn-omega btn-omega-success" style="padding:5px 10px;font-size:.72rem">✓ Payer</a>
              <?php endif; ?>
              <a href="factures.php?action=delete&id=<?=$f['id']?>" class="btn-omega btn-omega-danger btn-delete" style="padding:5px 10px;font-size:.72rem"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>
