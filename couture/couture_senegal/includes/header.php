<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!defined('APP_NAME')) {
    require_once dirname(__DIR__) . '/config.php';
}

$currentPage = basename($_SERVER['PHP_SELF'], '.php');

$navItems = [
    ['page' => 'index',     'icon' => '📊', 'label' => 'Tableau de bord', 'section' => 'PRINCIPAL'],
    ['page' => 'clients',   'icon' => '👥', 'label' => 'Clients',         'section' => 'GESTION'],
    ['page' => 'commandes', 'icon' => '🧵', 'label' => 'Commandes'],
    ['page' => 'factures',  'icon' => '📄', 'label' => 'Factures'],
    ['page' => 'paiements', 'icon' => '💰', 'label' => 'Paiements'],
    ['page' => 'depenses',  'icon' => '🧾', 'label' => 'Dépenses',        'section' => 'FINANCE'],
    ['page' => 'finances',  'icon' => '📈', 'label' => 'État Financier'],
    ['page' => 'modeles',   'icon' => '👗', 'label' => 'Modèles',         'section' => 'CATALOGUE'],
    ['page' => 'tissus',    'icon' => '🎨', 'label' => 'Tissus & Stock'],
];

$pageTitles = [
    'index'     => ['Tableau de Bord', 'Vue générale de votre atelier'],
    'clients'   => ['Gestion des Clients', 'Fichier client & mesures'],
    'commandes' => ['Commandes', 'Suivi des travaux en cours'],
    'factures'  => ['Factures', 'Facturation & paiements'],
    'paiements' => ['Paiements', 'Historique des encaissements'],
    'depenses'  => ['Dépenses', 'Charges & frais de l\'atelier'],
    'finances'  => ['État Financier', 'Bilan & analyse financière'],
    'modeles'   => ['Modèles', 'Catalogue des créations'],
    'tissus'    => ['Tissus & Stock', 'Gestion des matières'],
];
$pageInfo = $pageTitles[$currentPage] ?? ['Application', ''];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageInfo[0]) ?> — <?= APP_NAME ?></title>
  <link rel="stylesheet" href="/couture_senegal/assets/css/style.css">
</head>
<body>

<!-- BANDEAU OMEGA INFORMATIQUE CONSULTING -->
<div class="omega-banner">
  <span>★</span>
  <span>OMEGA INFORMATIQUE CONSULTING</span>
  <span class="sep">✦</span>
  <span>SOLUTIONS DIGITALES DE HAUTE QUALITÉ</span>
  <span class="sep">✦</span>
  <span>MODE & COUTURE SÉNÉGAL</span>
  <span class="sep">✦</span>
  <span>DAKAR — SÉNÉGAL</span>
  <span>★</span>
</div>

<!-- SIDEBAR -->
<nav class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">✂️</div>
    <div class="logo-title">CoutureSn Pro</div>
    <div class="logo-sub">Atelier Mode Sénégal</div>
    <span class="omega-tag">Omega Informatique</span>
  </div>

  <div class="sidebar-nav">
    <?php
    $lastSection = '';
    foreach ($navItems as $item):
      if (!empty($item['section']) && $item['section'] !== $lastSection):
        $lastSection = $item['section'];
    ?>
      <div class="nav-section"><?= $item['section'] ?></div>
    <?php endif; ?>
    <a href="/couture_senegal/<?= $item['page'] ?>.php"
       class="nav-item <?= $currentPage === $item['page'] ? 'active' : '' ?>">
      <span class="nav-icon"><?= $item['icon'] ?></span>
      <?= $item['label'] ?>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="sidebar-footer">
    <div><?= APP_NAME ?> v<?= APP_VERSION ?></div>
    <div><?= date('d/m/Y') ?> &middot; <?= date('H:i') ?></div>
    <div style="margin-top:6px;color:rgba(255,255,255,.4);font-size:.65rem;">
      © <?= date('Y') ?> Omega Informatique Consulting
    </div>
  </div>
</nav>

<!-- MAIN WRAPPER -->
<main class="main">

  <!-- TOPBAR -->
  <div class="topbar">
    <div class="topbar-title">
      <?= htmlspecialchars($pageInfo[0]) ?>
      <?php if ($pageInfo[1]): ?>
        <small><?= htmlspecialchars($pageInfo[1]) ?></small>
      <?php endif; ?>
    </div>
    <div class="topbar-date">📅 <?= strftime('%A %d %B %Y') ?: date('d/m/Y') ?></div>
    <?php if (isset($topbarActions)): echo $topbarActions; endif; ?>
  </div>
  <div class="sn-stripe"></div>

  <!-- CONTENT -->
  <div class="content fade-in">

    <?php
    $flash = getFlash();
    if ($flash):
      $cls = $flash['type'] === 'success' ? 'flash-success' : ($flash['type'] === 'error' ? 'flash-error' : 'flash-info');
      $ico = $flash['type'] === 'success' ? '✅' : ($flash['type'] === 'error' ? '❌' : 'ℹ️');
    ?>
    <div class="flash <?= $cls ?>"><?= $ico ?> <?= htmlspecialchars($flash['msg']) ?></div>
    <?php endif; ?>
