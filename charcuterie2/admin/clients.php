<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Clients';
$pdo = getPDO();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

if ($action === 'delete' && $id) {
    $pdo->prepare("UPDATE ventes SET client_id=NULL WHERE client_id=?")->execute([$id]);
    $pdo->prepare("UPDATE factures SET client_id=NULL WHERE client_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM clients WHERE id=?")->execute([$id]);
    flash('Client supprimé.', 'success');
    secureRedirect('clients.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $d = [
        'nom'      => trim($_POST['nom']),
        'prenom'   => trim($_POST['prenom']),
        'telephone'=> trim($_POST['telephone']),
        'email'    => trim($_POST['email']),
        'adresse'  => trim($_POST['adresse']),
    ];
    if (!$d['nom']) { flash('Le nom est requis.', 'error'); secureRedirect('clients.php?action='.($id?'edit':'add').'&id='.$id); }
    if ($id) {
        $pdo->prepare("UPDATE clients SET nom=?,prenom=?,telephone=?,email=?,adresse=? WHERE id=?")
            ->execute(array_merge(array_values($d), [$id]));
        flash('Client mis à jour.', 'success');
    } else {
        $pdo->prepare("INSERT INTO clients (nom,prenom,telephone,email,adresse) VALUES (?,?,?,?,?)")
            ->execute(array_values($d));
        flash('Client ajouté.', 'success');
    }
    secureRedirect('clients.php');
}

$client = null;
if (in_array($action, ['edit','view']) && $id) {
    $s = $pdo->prepare("SELECT * FROM clients WHERE id=?"); $s->execute([$id]); $client = $s->fetch();
}

$search = trim($_GET['q'] ?? '');
$sql = "SELECT c.*,
    (SELECT COUNT(*) FROM ventes WHERE client_id=c.id) as nb_ventes,
    (SELECT COALESCE(SUM(total),0) FROM ventes WHERE client_id=c.id) as ca_total,
    (SELECT COUNT(*) FROM factures WHERE client_id=c.id) as nb_factures
    FROM clients c WHERE 1";
$params = [];
if ($search) { $sql .= " AND (c.nom LIKE :q OR c.prenom LIKE :q2 OR c.telephone LIKE :q3)";
  $params = ['q'=>"%$search%",'q2'=>"%$search%",'q3'=>"%$search%"]; }
$sql .= " ORDER BY c.nom";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$clients = $stmt->fetchAll();

require_once 'header.php';
?>
<div class="page-header">
  <h1><i class="fas fa-users" style="color:var(--or)"></i> <span>Clients</span></h1>
  <p><?= count($clients) ?> client<?= count($clients)>1?'s':'' ?> enregistré<?= count($clients)>1?'s':'' ?></p>
</div>

<?php if (in_array($action, ['add','edit'])): ?>
<div class="card-omega" style="max-width:700px">
  <div class="card-head">
    <h4><i class="fas fa-<?=$id?'user-edit':'user-plus'?>"></i> <?=$id?'Modifier':'Nouveau'?> Client</h4>
    <a href="clients.php" class="btn-omega btn-omega-outline">← Retour</a>
  </div>
  <div class="card-body">
    <form method="POST" class="form-omega">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Nom *</label>
          <input type="text" name="nom" class="form-control" required placeholder="Nom de famille / Raison sociale"
            value="<?= htmlspecialchars($client['nom'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Prénom</label>
          <input type="text" name="prenom" class="form-control" placeholder="Prénom (optionnel)"
            value="<?= htmlspecialchars($client['prenom'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Téléphone</label>
          <input type="tel" name="telephone" class="form-control" placeholder="77 XXX XX XX"
            value="<?= htmlspecialchars($client['telephone'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="email@exemple.com"
            value="<?= htmlspecialchars($client['email'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Adresse</label>
          <textarea name="adresse" class="form-control" rows="2" placeholder="Adresse complète..."><?= htmlspecialchars($client['adresse'] ?? '') ?></textarea>
        </div>
      </div>
      <div style="margin-top:20px;display:flex;gap:10px">
        <button type="submit" class="btn-omega btn-omega-primary"><i class="fas fa-save"></i> Enregistrer</button>
        <a href="clients.php" class="btn-omega btn-omega-outline">Annuler</a>
      </div>
    </form>
  </div>
</div>

<?php elseif ($action === 'view' && $client): ?>
<!-- FICHE CLIENT -->
<div style="display:flex;gap:10px;margin-bottom:20px">
  <a href="clients.php" class="btn-omega btn-omega-outline">← Retour</a>
  <a href="clients.php?action=edit&id=<?=$id?>" class="btn-omega btn-omega-gold"><i class="fas fa-edit"></i> Modifier</a>
</div>
<div class="row g-4">
  <div class="col-md-4">
    <div class="card-omega">
      <div class="card-body" style="text-align:center;padding:30px">
        <div style="width:70px;height:70px;border-radius:50%;background:linear-gradient(135deg,var(--rouge),var(--or));
          display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:900;margin:0 auto 15px">
          <?= strtoupper(substr($client['nom'],0,1)) ?>
        </div>
        <h3 style="color:var(--text);margin-bottom:5px"><?= htmlspecialchars($client['prenom'].' '.$client['nom']) ?></h3>
        <?php if($client['telephone']): ?>
        <p style="color:var(--muted)"><i class="fas fa-phone"></i> <?= htmlspecialchars($client['telephone']) ?></p>
        <?php endif; ?>
        <?php if($client['email']): ?>
        <p style="color:var(--muted)"><i class="fas fa-envelope"></i> <?= htmlspecialchars($client['email']) ?></p>
        <?php endif; ?>
        <?php if($client['adresse']): ?>
        <p style="color:var(--muted);font-size:.85rem"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($client['adresse']) ?></p>
        <?php endif; ?>
        <p style="color:#555;font-size:.75rem;margin-top:10px">Client depuis le <?= date('d/m/Y',strtotime($client['created_at'])) ?></p>
      </div>
    </div>
  </div>
  <div class="col-md-8">
    <?php
    $hist = $pdo->prepare("SELECT v.*,p.nom as prod_nom FROM ventes v LEFT JOIN produits p ON v.produit_id=p.id WHERE v.client_id=? ORDER BY v.date_vente DESC LIMIT 10");
    $hist->execute([$id]); $hist = $hist->fetchAll();
    $caClient = array_sum(array_column($hist,'total'));
    ?>
    <div class="row g-3 mb-3">
      <div class="col-6">
        <div class="stat-card" style="--color1:#c0392b;--color2:#e74c3c">
          <div class="label">CA Total</div>
          <div class="value" style="font-size:1.3rem"><?= number_format($caClient,0,',',' ') ?></div>
          <small style="color:var(--muted)">FCFA</small>
        </div>
      </div>
      <div class="col-6">
        <div class="stat-card" style="--color1:#d4ac0d;--color2:#f1c40f">
          <div class="label">Transactions</div>
          <div class="value"><?= count($hist) ?></div>
          <small style="color:var(--muted)">achats</small>
        </div>
      </div>
    </div>
    <div class="card-omega">
      <div class="card-head"><h4><i class="fas fa-history"></i> Derniers Achats</h4></div>
      <div style="overflow-x:auto">
        <table class="table-omega">
          <thead><tr><th>Date</th><th>Produit</th><th>Qté</th><th>Total</th></tr></thead>
          <tbody>
            <?php if(empty($hist)): ?>
              <tr><td colspan="4" style="text-align:center;color:var(--muted);padding:20px">Aucun achat</td></tr>
            <?php else: foreach($hist as $h): ?>
            <tr>
              <td><small><?= date('d/m/Y',strtotime($h['date_vente'])) ?></small></td>
              <td><?= htmlspecialchars($h['prod_nom']??'—') ?></td>
              <td><?= number_format($h['quantite'],3) ?></td>
              <td><strong style="color:var(--or)"><?= number_format($h['total'],0,',',' ') ?> FCFA</strong></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php else: ?>
<!-- LISTE CLIENTS -->
<div class="card-omega">
  <div class="card-head">
    <h4><i class="fas fa-list"></i> Liste des Clients</h4>
    <a href="clients.php?action=add" class="btn-omega btn-omega-primary"><i class="fas fa-plus"></i> Nouveau client</a>
  </div>
  <div class="card-body" style="padding:15px">
    <form method="GET" class="form-omega" style="display:flex;gap:10px;margin-bottom:10px">
      <input type="text" name="q" class="form-control" placeholder="🔍 Rechercher par nom, prénom, téléphone..."
        value="<?= htmlspecialchars($search) ?>" style="max-width:350px">
      <button type="submit" class="btn-omega btn-omega-gold">Chercher</button>
      <a href="clients.php" class="btn-omega btn-omega-outline">Reset</a>
    </form>
  </div>
  <div style="overflow-x:auto">
    <table class="table-omega">
      <thead><tr><th>Client</th><th>Téléphone</th><th>Email</th><th>Achats</th><th>CA Total</th><th>Factures</th><th>Actions</th></tr></thead>
      <tbody>
        <?php if(empty($clients)): ?>
          <tr><td colspan="7" style="text-align:center;color:var(--muted);padding:40px">Aucun client</td></tr>
        <?php else: foreach($clients as $c): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--rouge),var(--or));
                display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;flex-shrink:0">
                <?= strtoupper(substr($c['nom'],0,1)) ?>
              </div>
              <div>
                <strong><?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?></strong><br>
                <small style="color:var(--muted)"><?= htmlspecialchars(mb_substr($c['adresse']??'',0,30)) ?></small>
              </div>
            </div>
          </td>
          <td><?= htmlspecialchars($c['telephone']??'—') ?></td>
          <td><small><?= htmlspecialchars($c['email']??'—') ?></small></td>
          <td><span class="badge-stat b-info"><?= $c['nb_ventes'] ?> achat<?=$c['nb_ventes']>1?'s':''?></span></td>
          <td><strong style="color:var(--or)"><?= number_format($c['ca_total'],0,',',' ') ?> FCFA</strong></td>
          <td><?= $c['nb_factures'] ?> fact.</td>
          <td>
            <div style="display:flex;gap:5px">
              <a href="clients.php?action=view&id=<?=$c['id']?>" class="btn-omega btn-omega-outline" style="padding:5px 10px;font-size:.75rem"><i class="fas fa-eye"></i></a>
              <a href="clients.php?action=edit&id=<?=$c['id']?>" class="btn-omega btn-omega-gold" style="padding:5px 10px;font-size:.75rem"><i class="fas fa-edit"></i></a>
              <a href="clients.php?action=delete&id=<?=$c['id']?>" class="btn-omega btn-omega-danger btn-delete" style="padding:5px 10px;font-size:.75rem"><i class="fas fa-trash"></i></a>
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
