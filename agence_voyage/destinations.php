<?php
require 'config/db.php';
$page_title = 'Carte des Destinations';
require 'includes/header.php';

$destinations = $pdo->query("SELECT * FROM destinations ORDER BY populaire DESC, ville ASC")->fetchAll();
$dakar = null;
foreach ($destinations as $d) { if ($d['code_iata'] === 'DSS') { $dakar = $d; break; } }
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Réseau Mondial</div>
      <h1>Destinations</h1>
      <p><?= count($destinations) ?> destinations opérées depuis Dakar</p>
    </div>
    <div style="display:flex;gap:10px">
      <a href="ajouter_vol.php" class="btn btn-gold">✈ Programmer un vol</a>
    </div>
  </div>

  <!-- Map -->
  <div class="card" style="margin-bottom:24px">
    <div class="card-header">
      <span class="card-title">🗺 Carte des Routes Aériennes</span>
      <div style="display:flex;gap:12px;font-size:0.72rem;color:var(--muted);align-items:center">
        <span style="display:flex;align-items:center;gap:4px"><span style="color:var(--gold)">●</span> Populaire</span>
        <span style="display:flex;align-items:center;gap:4px"><span style="color:var(--cyan)">●</span> Standard</span>
        <span style="display:flex;align-items:center;gap:4px"><span style="color:var(--gold);opacity:0.5">—</span> Route Dakar</span>
      </div>
    </div>
    <div id="map" style="height:460px;border-radius:0 0 var(--radius) var(--radius)"></div>
  </div>

  <!-- Destination cards -->
  <div class="grid-4">
    <?php
    $emojis = ['Afrique'=>'🌍','Europe'=>'🏰','Asie'=>'🌏','Amérique'=>'🗽'];
    foreach($destinations as $dest):
      $emoji = $emojis[$dest['continent']] ?? '✈';
    ?>
    <div class="offer-card">
      <div class="offer-card-img" style="font-size:3rem">
        <?= $emoji ?>
        <div style="position:absolute;bottom:8px;left:10px;z-index:1;font-family:'Bebas Neue',sans-serif;font-size:1.5rem;letter-spacing:0.08em;color:white"><?= htmlspecialchars($dest['code_iata']) ?></div>
        <?php if($dest['populaire']): ?>
        <div style="position:absolute;top:8px;right:8px;z-index:1;background:rgba(212,168,72,0.9);color:#0d0a00;font-size:0.62rem;font-weight:800;padding:3px 7px;border-radius:20px;letter-spacing:0.06em">★ POPULAIRE</div>
        <?php endif; ?>
      </div>
      <div class="offer-card-body">
        <div style="font-weight:700;font-size:0.92rem;color:var(--text)"><?= htmlspecialchars($dest['ville']) ?></div>
        <div style="font-size:0.78rem;color:var(--muted);margin-top:2px"><?= htmlspecialchars($dest['pays']) ?> · <?= htmlspecialchars($dest['continent']) ?></div>
        <?php if($dest['description']): ?>
        <div style="font-size:0.73rem;color:var(--muted);margin-top:7px;line-height:1.5"><?= htmlspecialchars(mb_substr($dest['description'],0,80)).'…' ?></div>
        <?php endif; ?>
        <div style="margin-top:10px">
          <a href="ajouter_vol.php" class="btn btn-ghost btn-sm" style="font-size:0.72rem">✈ Programmer</a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<script>
(function(){
  const destinations = <?= json_encode($destinations, JSON_UNESCAPED_UNICODE) ?>;
  const dakar = <?= json_encode($dakar, JSON_UNESCAPED_UNICODE) ?>;

  const map = L.map('map', {
    center: [20, 5],
    zoom: 2,
    minZoom: 1,
    maxZoom: 8,
    zoomControl: true,
  });

  // Dark tile layer
  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '© OpenStreetMap © CARTO',
    subdomains: 'abcd', maxZoom: 19
  }).addTo(map);

  const goldIcon = L.divIcon({
    html: '<div style="width:14px;height:14px;border-radius:50%;background:#d4a848;border:2px solid #f0c86a;box-shadow:0 0 10px rgba(212,168,72,0.7)"></div>',
    className: '', iconSize: [14,14], iconAnchor: [7,7]
  });
  const cyanIcon = L.divIcon({
    html: '<div style="width:10px;height:10px;border-radius:50%;background:#00c8f0;border:2px solid rgba(0,200,240,0.5);box-shadow:0 0 8px rgba(0,200,240,0.5)"></div>',
    className: '', iconSize: [10,10], iconAnchor: [5,5]
  });
  const dakarIcon = L.divIcon({
    html: '<div style="width:16px;height:16px;border-radius:50%;background:#d4a848;border:3px solid #fff;box-shadow:0 0 16px rgba(212,168,72,0.9);animation:pulse 2s infinite"></div>',
    className: '', iconSize: [16,16], iconAnchor: [8,8]
  });

  destinations.forEach(dest => {
    if (!dest.latitude || !dest.longitude) return;
    const icon = dest.code_iata === 'DSS' ? dakarIcon : (dest.populaire ? goldIcon : cyanIcon);
    const marker = L.marker([dest.latitude, dest.longitude], {icon}).addTo(map);
    marker.bindPopup(`
      <div style="font-family:'Plus Jakarta Sans',sans-serif;background:#0b1228;color:#dce5f5;padding:10px 12px;border-radius:8px;min-width:160px;border:1px solid rgba(255,255,255,0.1)">
        <div style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;letter-spacing:0.06em;color:#d4a848">${dest.code_iata}</div>
        <div style="font-weight:700;font-size:0.9rem">${dest.ville}</div>
        <div style="font-size:0.75rem;color:#6a7ba0">${dest.pays} · ${dest.continent}</div>
      </div>
    `, {className:'dark-popup'});

    // Draw animated route from Dakar
    if (dakar && dest.code_iata !== 'DSS') {
      const latlngs = [[dakar.latitude, dakar.longitude],[dest.latitude, dest.longitude]];
      L.polyline(latlngs, {
        color: dest.populaire ? 'rgba(212,168,72,0.35)' : 'rgba(0,200,240,0.2)',
        weight: dest.populaire ? 1.5 : 1,
        dashArray: '5,8',
      }).addTo(map);
    }
  });

  // Leaflet popup dark style
  const style = document.createElement('style');
  style.textContent = '.leaflet-popup-content-wrapper,.leaflet-popup-tip{background:#0b1228;border:1px solid rgba(255,255,255,0.1);box-shadow:0 8px 32px rgba(0,0,0,0.5)}.leaflet-popup-content{margin:0}';
  document.head.appendChild(style);
})();
</script>

<?php require 'includes/footer.php'; ?>
