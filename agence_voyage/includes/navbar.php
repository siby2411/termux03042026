<?php
$current = basename($_SERVER['PHP_SELF']);
function nav_a(string $href, string $label, string $icon, string $cur): string {
    $active = ($cur === $href) ? ' active' : '';
    return "<a href=\"$href\" class=\"$active\">
        <span class=\"nav-dot\"></span>$icon $label</a>";
}
// Count live flights for badge
global $pdo;
$live = 0;
try { $live = (int)$pdo->query("SELECT COUNT(*) FROM vols WHERE statut='EN_COURS'")->fetchColumn(); } catch(Exception $e){}
?>
<nav class="navbar">
  <a href="index.php" class="nav-brand">
    <span class="plane-icon">✈</span>
    <div class="brand-text">OMEGA <span>VOYAGES</span></div>
  </a>
  <div class="nav-links">
    <?= nav_a('index.php',         'Tableau de Bord', '◈', $current) ?>
    <?= nav_a('vols.php',          'Vols',            '✈', $current) ?>
    <?= nav_a('reservations.php',  'Réservations',    '📋', $current) ?>
    <?= nav_a('clients.php',       'Clients',         '👤', $current) ?>
    <?= nav_a('destinations.php',  'Destinations',    '🗺', $current) ?>
    <?= nav_a('offres.php',        'Offres',          '🏷', $current) ?>
    <?= nav_a('compagnies.php',    'Compagnies',      '🏢', $current) ?>
  </div>
  <div style="display:flex;align-items:center;gap:12px">
    <?php if($live > 0): ?>
    <span class="live-badge"><?= $live ?> EN VOL</span>
    <?php endif; ?>
    <div class="nav-user"><span>OMEGA</span> · Dakar 2026</div>
  </div>
</nav>
