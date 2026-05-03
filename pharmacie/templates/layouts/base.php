<?php
/**
 * Layout principal PharmaSen
 * Variables attendues : $pageTitle, $activeMenu
 */
Auth::check();
$user = Auth::getUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle ?? 'PharmaSen') ?> — PharmaSen</title>
  <link rel="stylesheet" href="/pharmacie/assets/css/main.css">
</head>
<body>
<nav class="sidebar">
  <div class="sidebar-logo">
    <span class="logo-icon">💊</span>
    <span class="logo-text">PharmaSen</span>
  </div>
  <ul class="nav-menu">
    <li class="<?= ($activeMenu??'')==='dashboard' ? 'active':'' ?>">
      <a href="/pharmacie/index.php">📊 Tableau de bord</a></li>
    <li class="<?= ($activeMenu??'')==='pos' ? 'active':'' ?>">
      <a href="/pharmacie/modules/caisse/pos.php">🛒 Point de Vente</a></li>
    <li class="<?= ($activeMenu??'')==='medicaments' ? 'active':'' ?>">
      <a href="/pharmacie/modules/medicaments/">💊 Médicaments</a></li>
    <li class="<?= ($activeMenu??'')==='stock' ? 'active':'' ?>">
      <a href="/pharmacie/modules/stock/">📦 Stock</a></li>
    <li class="<?= ($activeMenu??'')==='ordonnances' ? 'active':'' ?>">
      <a href="/pharmacie/modules/ordonnances/">📋 Ordonnances</a></li>
    <li class="<?= ($activeMenu??'')==='clients' ? 'active':'' ?>">
      <a href="/pharmacie/modules/clients/">👥 Clients</a></li>
    <li class="<?= ($activeMenu??'')==='achats' ? 'active':'' ?>">
      <a href="/pharmacie/modules/fournisseurs/">🏭 Fournisseurs</a></li>
    <li class="<?= ($activeMenu??'')==='rapports' ? 'active':'' ?>">
      <a href="/pharmacie/modules/rapports/">📈 Rapports</a></li>
    <?php if(Auth::hasRole('admin')): ?>
    <li class="<?= ($activeMenu??'')==='utilisateurs' ? 'active':'' ?>">
      <a href="/pharmacie/modules/utilisateurs/">⚙️ Utilisateurs</a></li>
    <?php endif; ?>
  </ul>
  <div class="sidebar-footer">
    <div class="user-info">👤 <?= htmlspecialchars($user['nom']) ?></div>
    <div class="user-role"><?= htmlspecialchars($user['role']) ?></div>
    <a href="/pharmacie/logout.php" class="btn-logout">🚪 Déconnexion</a>
  </div>
</nav>
<main class="main-content">
  <div class="topbar">
    <h1 class="page-title"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
    <div class="topbar-right">
      <span id="datetime" class="datetime"></span>
      <a href="/pharmacie/modules/caisse/pos.php" class="btn btn-primary btn-sm">🛒 Caisse</a>
    </div>
  </div>
  <div class="content-area">
    <?= $content ?? '' ?>
  </div>
</main>
<script src="/pharmacie/assets/js/main.js"></script>
<script>
  setInterval(()=>{
    document.getElementById('datetime').textContent =
      new Date().toLocaleString('fr-SN');
  },1000);
</script>
</body>
</html>
