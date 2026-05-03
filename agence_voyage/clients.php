<?php
require 'config/db.php';
$page_title = 'Clients';
require 'includes/header.php';

$q = trim($_GET['q'] ?? '');
$sql = "SELECT c.*, COUNT(r.id) as nb_res, COALESCE(SUM(r.prix_total),0) as total_depense
        FROM clients c LEFT JOIN reservations r ON c.id=r.client_id";
if ($q) $sql .= " WHERE c.nom LIKE :q OR c.prenom LIKE :q OR c.telephone LIKE :q OR c.email LIKE :q";
$sql .= " GROUP BY c.id ORDER BY c.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($q ? [':q' => "%$q%"] : []);
$clients = $stmt->fetchAll();
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Base Clients</div>
      <h1>Clients</h1>
      <p><?= count($clients) ?> client(s) trouvé(s)</p>
    </div>
    <a href="ajouter_client.php" class="btn btn-gold">+ Nouveau Client</a>
  </div>

  <form method="GET" style="margin-bottom:20px;display:flex;gap:10px">
    <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control" style="max-width:320px" placeholder="🔍 Rechercher par nom, tel, email…">
    <button type="submit" class="btn btn-ghost">Rechercher</button>
    <?php if($q): ?><a href="clients.php" class="btn btn-ghost">✕</a><?php endif; ?>
  </form>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr>
          <th>#</th><th>Client</th><th>Contact</th><th>Nationalité</th>
          <th>Réservations</th><th>Dépenses</th><th>Inscrit le</th><th>Action</th>
        </tr></thead>
        <tbody>
        <?php if(empty($clients)): ?>
        <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">👤</div>Aucun client trouvé</div></td></tr>
        <?php else: foreach($clients as $c): ?>
        <tr>
          <td style="color:var(--muted);font-size:0.78rem"><?= $c['id'] ?></td>
          <td>
            <div style="font-weight:600;color:var(--text)"><?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?></div>
            <?php if($c['passeport']): ?><div style="font-size:0.7rem;color:var(--muted)">🪪 <?= htmlspecialchars($c['passeport']) ?></div><?php endif; ?>
          </td>
          <td>
            <div style="font-size:0.82rem"><?= htmlspecialchars($c['telephone']??'—') ?></div>
            <div style="font-size:0.72rem;color:var(--muted)"><?= htmlspecialchars($c['email']??'') ?></div>
          </td>
          <td style="font-size:0.8rem;color:var(--muted)"><?= htmlspecialchars($c['nationalite']??'') ?></td>
          <td>
            <span style="font-family:'Bebas Neue',sans-serif;font-size:1.1rem;color:var(--cyan)"><?= $c['nb_res'] ?></span>
          </td>
          <td style="color:var(--gold);font-size:0.82rem;font-weight:600"><?= $c['total_depense'] > 0 ? money($c['total_depense']) : '—' ?></td>
          <td style="font-size:0.75rem;color:var(--muted)"><?= date('d/m/Y', strtotime($c['created_at'])) ?></td>
          <td><a href="ajouter_reservation.php?client_id=<?= $c['id'] ?>" class="btn btn-ghost btn-sm">+ Résa</a></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
