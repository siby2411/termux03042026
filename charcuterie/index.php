<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>OMEGA INFORMATIQUE CONSULTING – Gestion Charcuterie</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Raleway:wght@300;400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root{
  --rouge:#c0392b;--rouge-dark:#922b21;--rouge-light:#e74c3c;
  --or:#d4ac0d;--or-light:#f1c40f;
  --noir:#0d0d0d;--gris:#1a1a2e;--gris2:#16213e;
  --blanc:#fafafa;--creme:#fdf6ec;
}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Raleway',sans-serif;background:var(--noir);color:var(--blanc);overflow-x:hidden}

/* ── BANDEAU ENTÊTE ── */
.topbar{
  background:linear-gradient(90deg,var(--rouge-dark),var(--rouge),var(--or),var(--rouge),var(--rouge-dark));
  background-size:300% 100%;
  animation:gradFlow 6s ease infinite;
  color:#fff;text-align:center;padding:10px 20px;
  font-family:'Playfair Display',serif;letter-spacing:2px;
  position:relative;overflow:hidden;
}
.topbar::before{content:'';position:absolute;inset:0;background:repeating-linear-gradient(
  90deg,transparent,transparent 20px,rgba(255,255,255,.06) 20px,rgba(255,255,255,.06) 21px);}
.topbar-inner{position:relative;z-index:1}
.topbar h1{font-size:clamp(1rem,3vw,1.6rem);font-weight:900;text-transform:uppercase;text-shadow:2px 2px 8px rgba(0,0,0,.5)}
.topbar p{font-size:.75rem;font-family:'Raleway',sans-serif;letter-spacing:4px;opacity:.9;margin-top:3px}
@keyframes gradFlow{0%{background-position:0%}50%{background-position:100%}100%{background-position:0%}}

/* ── MARQUEE TICKER ── */
.ticker{background:var(--noir);border-top:2px solid var(--or);border-bottom:2px solid var(--or);
  padding:8px 0;overflow:hidden;position:relative}
.ticker-inner{display:flex;animation:ticker 30s linear infinite;white-space:nowrap}
.ticker span{padding:0 40px;color:var(--or);font-size:.85rem;font-weight:600;letter-spacing:1px}
.ticker span i{margin-right:8px;color:var(--rouge-light)}
@keyframes ticker{from{transform:translateX(0)}to{transform:translateX(-50%)}}

/* ── NAV ── */
nav{background:rgba(10,10,10,.95);backdrop-filter:blur(10px);
  padding:15px 5%;display:flex;justify-content:space-between;align-items:center;
  position:sticky;top:0;z-index:999;border-bottom:1px solid rgba(212,172,13,.2)}
.logo{font-family:'Playfair Display',serif;color:var(--or);font-size:1.4rem;font-weight:900;
  text-decoration:none;display:flex;align-items:center;gap:10px}
.logo span{color:var(--rouge-light);font-size:.8rem;font-weight:400;display:block;
  font-family:'Raleway',sans-serif;letter-spacing:3px;text-transform:uppercase}
nav ul{list-style:none;display:flex;gap:30px}
nav ul a{color:#ccc;text-decoration:none;font-weight:600;font-size:.9rem;letter-spacing:1px;
  transition:.3s;text-transform:uppercase;position:relative}
nav ul a::after{content:'';position:absolute;bottom:-4px;left:0;width:0;height:2px;
  background:var(--or);transition:.3s}
nav ul a:hover{color:var(--or)}
nav ul a:hover::after{width:100%}
.nav-admin{background:var(--rouge);color:#fff !important;padding:8px 20px;
  border-radius:25px;letter-spacing:1px !important}
.nav-admin:hover{background:var(--rouge-dark) !important;color:#fff !important}
.nav-admin::after{display:none !important}

/* ── HERO ── */
.hero{min-height:92vh;background:
  radial-gradient(ellipse at 20% 50%,rgba(192,57,43,.3) 0%,transparent 60%),
  radial-gradient(ellipse at 80% 20%,rgba(212,172,13,.2) 0%,transparent 50%),
  linear-gradient(135deg,#0d0d0d 0%,#1a0a0a 50%,#0d0d0d 100%);
  display:flex;align-items:center;padding:60px 5%;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;inset:0;
  background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c0392b' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");}
.hero-content{max-width:600px;z-index:1;position:relative}
.hero-badge{display:inline-block;background:linear-gradient(90deg,var(--rouge),var(--or));
  color:#fff;padding:6px 20px;border-radius:20px;font-size:.75rem;font-weight:800;
  letter-spacing:3px;text-transform:uppercase;margin-bottom:25px}
.hero h2{font-family:'Playfair Display',serif;font-size:clamp(2.5rem,6vw,4.5rem);
  line-height:1.1;margin-bottom:20px}
.hero h2 em{color:var(--or);font-style:normal}
.hero p{font-size:1.1rem;color:#aaa;line-height:1.8;margin-bottom:35px;font-weight:300}
.hero-btns{display:flex;gap:15px;flex-wrap:wrap}
.btn-primary{background:linear-gradient(135deg,var(--rouge),var(--rouge-dark));
  color:#fff;padding:15px 35px;border-radius:30px;text-decoration:none;font-weight:700;
  font-size:.95rem;letter-spacing:1px;transition:.3s;box-shadow:0 5px 20px rgba(192,57,43,.4)}
.btn-primary:hover{transform:translateY(-3px);box-shadow:0 10px 30px rgba(192,57,43,.6)}
.btn-outline{border:2px solid var(--or);color:var(--or);padding:13px 35px;border-radius:30px;
  text-decoration:none;font-weight:700;font-size:.95rem;letter-spacing:1px;transition:.3s}
.btn-outline:hover{background:var(--or);color:var(--noir)}
.hero-img{position:absolute;right:-50px;top:50%;transform:translateY(-50%);
  width:55%;max-width:700px;opacity:.15;font-size:25vw;text-align:center;
  filter:drop-shadow(0 0 80px rgba(192,57,43,.3));pointer-events:none}

/* ── STATS BAND ── */
.stats-band{background:linear-gradient(90deg,var(--rouge-dark),var(--rouge),var(--or-light),var(--rouge),var(--rouge-dark));
  background-size:300%;animation:gradFlow 8s ease infinite;
  padding:30px 5%;display:grid;grid-template-columns:repeat(4,1fr);gap:20px;text-align:center}
.stat-item h3{font-family:'Playfair Display',serif;font-size:2.5rem;font-weight:900}
.stat-item p{font-size:.8rem;letter-spacing:2px;text-transform:uppercase;opacity:.9;margin-top:5px}

/* ── CATEGORIES GRID ── */
.section{padding:80px 5%}
.section-header{text-align:center;margin-bottom:60px}
.section-header h2{font-family:'Playfair Display',serif;font-size:clamp(2rem,4vw,3rem);margin-bottom:15px}
.section-header h2 span{color:var(--or)}
.section-header p{color:#888;font-size:1rem;max-width:600px;margin:0 auto}
.divider{width:80px;height:3px;background:linear-gradient(90deg,var(--rouge),var(--or));
  margin:20px auto;border-radius:2px}

.cat-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px}
.cat-card{background:linear-gradient(135deg,rgba(255,255,255,.04),rgba(255,255,255,.01));
  border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:30px 20px;
  text-align:center;cursor:pointer;transition:.4s;position:relative;overflow:hidden}
.cat-card::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,var(--cat-color),transparent);
  opacity:0;transition:.4s}
.cat-card:hover{transform:translateY(-8px);border-color:var(--cat-color)}
.cat-card:hover::before{opacity:.1}
.cat-icon{font-size:3rem;margin-bottom:15px;display:block}
.cat-card h3{font-size:1rem;font-weight:700;margin-bottom:8px;text-transform:uppercase;letter-spacing:1px}
.cat-card p{font-size:.8rem;color:#888;line-height:1.5}

/* ── PRODUCTS ── */
.prod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:25px}
.prod-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);
  border-radius:16px;overflow:hidden;transition:.4s;position:relative}
.prod-card:hover{transform:translateY(-6px);border-color:var(--rouge-light);
  box-shadow:0 20px 40px rgba(192,57,43,.2)}
.prod-img{width:100%;height:200px;object-fit:cover;background:rgba(255,255,255,.05);
  display:flex;align-items:center;justify-content:center;font-size:5rem}
.prod-img img{width:100%;height:100%;object-fit:cover}
.prod-body{padding:20px}
.prod-cat{font-size:.7rem;color:var(--or);text-transform:uppercase;letter-spacing:2px;
  font-weight:700;margin-bottom:8px}
.prod-name{font-size:1rem;font-weight:700;margin-bottom:8px;line-height:1.3}
.prod-desc{font-size:.8rem;color:#888;margin-bottom:15px;line-height:1.5}
.prod-footer{display:flex;justify-content:space-between;align-items:center}
.prod-price{color:var(--or);font-size:1.2rem;font-weight:800;
  font-family:'Playfair Display',serif}
.prod-unit{font-size:.7rem;color:#666;display:block;font-weight:400}
.badge-stock{font-size:.65rem;padding:4px 10px;border-radius:20px;font-weight:700}
.in-stock{background:rgba(39,174,96,.15);color:#27ae60;border:1px solid rgba(39,174,96,.3)}
.low-stock{background:rgba(230,126,34,.15);color:#e67e22;border:1px solid rgba(230,126,34,.3)}

/* ── SERVICES ── */
.services{background:linear-gradient(135deg,var(--gris),var(--gris2));padding:80px 5%}
.serv-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:30px}
.serv-card{padding:35px 30px;border-radius:16px;background:rgba(255,255,255,.03);
  border:1px solid rgba(255,255,255,.08);transition:.4s;position:relative;overflow:hidden}
.serv-card::after{content:'';position:absolute;bottom:0;left:0;width:0;height:3px;
  background:linear-gradient(90deg,var(--rouge),var(--or));transition:.5s}
.serv-card:hover::after{width:100%}
.serv-card:hover{transform:translateY(-5px)}
.serv-icon{width:60px;height:60px;border-radius:15px;
  background:linear-gradient(135deg,var(--rouge),var(--or));
  display:flex;align-items:center;justify-content:center;font-size:1.5rem;margin-bottom:20px}
.serv-card h3{font-size:1.1rem;font-weight:700;margin-bottom:12px;color:var(--blanc)}
.serv-card p{font-size:.85rem;color:#999;line-height:1.7}

/* ── PROMO BANNER ── */
.promo-band{background:linear-gradient(135deg,#1a0a0a,#2c0d0d,#1a0a0a);
  padding:60px 5%;text-align:center;border-top:2px solid var(--rouge);
  border-bottom:2px solid var(--or);position:relative;overflow:hidden}
.promo-band::before{content:'⭐ QUALITÉ PREMIUM ⭐ FRAÎCHEUR GARANTIE ⭐ LIVRAISON RAPIDE ⭐ QUALITÉ PREMIUM ⭐ FRAÎCHEUR GARANTIE ⭐ LIVRAISON RAPIDE ⭐';
  position:absolute;top:8px;left:0;right:0;font-size:.65rem;color:rgba(212,172,13,.4);
  letter-spacing:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.promo-band h2{font-family:'Playfair Display',serif;font-size:clamp(1.8rem,4vw,3rem);
  color:var(--blanc);margin-bottom:15px}
.promo-band h2 em{color:var(--or);font-style:normal}
.promo-band p{color:#aaa;font-size:1rem;max-width:600px;margin:0 auto 30px}

/* ── FOOTER ── */
footer{background:#050505;border-top:1px solid rgba(212,172,13,.2);padding:50px 5% 20px}
.footer-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:40px;margin-bottom:40px}
.footer-brand .logo{display:flex;align-items:center;gap:10px;margin-bottom:15px}
.footer-brand p{color:#666;font-size:.85rem;line-height:1.7}
.footer-col h4{color:var(--or);font-size:.9rem;text-transform:uppercase;letter-spacing:2px;
  margin-bottom:20px;font-weight:700}
.footer-col ul{list-style:none}
.footer-col ul li{margin-bottom:10px}
.footer-col ul li a{color:#666;text-decoration:none;font-size:.85rem;transition:.3s;display:flex;align-items:center;gap:8px}
.footer-col ul li a:hover{color:var(--or)}
.footer-bottom{border-top:1px solid rgba(255,255,255,.05);padding-top:20px;
  display:flex;justify-content:space-between;align-items:center;color:#444;font-size:.8rem}
.footer-bottom span{color:var(--rouge-light)}

/* ── SCROLL TO TOP ── */
.scroll-top{position:fixed;bottom:30px;right:30px;width:50px;height:50px;
  background:linear-gradient(135deg,var(--rouge),var(--or));border-radius:50%;
  display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.2rem;
  cursor:pointer;opacity:0;transition:.3s;z-index:999;text-decoration:none;
  box-shadow:0 5px 20px rgba(192,57,43,.5)}
.scroll-top.visible{opacity:1}

@media(max-width:768px){
  nav ul{display:none}
  .stats-band{grid-template-columns:repeat(2,1fr)}
  .hero-img{display:none}
}
</style>
</head>
<body>

<!-- BANDEAU ENTÊTE OMEGA -->
<div class="topbar">
  <div class="topbar-inner">
    <h1>🏆 OMEGA INFORMATIQUE CONSULTING — GESTION CHARCUTERIE 🏆</h1>
    <p>❖ Qualité · Service · Innovation · Excellence ❖</p>
  </div>
</div>

<!-- TICKER -->
<div class="ticker">
  <div class="ticker-inner">
    <span><i class="fas fa-star"></i> QUALITÉ PREMIUM GARANTIE</span>
    <span><i class="fas fa-truck"></i> LIVRAISON RAPIDE 24H</span>
    <span><i class="fas fa-award"></i> PRODUITS CERTIFIÉS HALAL</span>
    <span><i class="fas fa-leaf"></i> CHARCUTERIE VERTE & BIO</span>
    <span><i class="fas fa-fire"></i> NOUVELLES PROMOTIONS</span>
    <span><i class="fas fa-phone"></i> HOTLINE : +221 33 XXX XX XX</span>
    <span><i class="fas fa-clock"></i> OUVERT 7J/7 – 7H À 22H</span>
    <!-- duplicate for seamless loop -->
    <span><i class="fas fa-star"></i> QUALITÉ PREMIUM GARANTIE</span>
    <span><i class="fas fa-truck"></i> LIVRAISON RAPIDE 24H</span>
    <span><i class="fas fa-award"></i> PRODUITS CERTIFIÉS HALAL</span>
    <span><i class="fas fa-leaf"></i> CHARCUTERIE VERTE & BIO</span>
    <span><i class="fas fa-fire"></i> NOUVELLES PROMOTIONS</span>
    <span><i class="fas fa-phone"></i> HOTLINE : +221 33 XXX XX XX</span>
    <span><i class="fas fa-clock"></i> OUVERT 7J/7 – 7H À 22H</span>
  </div>
</div>

<!-- NAV -->
<nav>
  <a href="index.php" class="logo">
    🥩 OMEGA CHARCUTERIE
    <span>Qualité & Fraîcheur</span>
  </a>
  <ul>
    <li><a href="#produits">Produits</a></li>
    <li><a href="#categories">Catégories</a></li>
    <li><a href="#services">Services</a></li>
    <li><a href="#contact">Contact</a></li>
    <li><a href="admin/login.php" class="nav-admin"><i class="fas fa-lock"></i> Admin</a></li>
  </ul>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-badge">🏆 N°1 Charcuterie Premium au Sénégal</div>
    <h2>La <em>Qualité</em> au Cœur de Chaque <em>Saveur</em></h2>
    <p>Charcuteries artisanales, volailles fermières, fromages affinés et produits frais soigneusement sélectionnés. Une expérience gustative hors du commun.</p>
    <div class="hero-btns">
      <a href="#produits" class="btn-primary"><i class="fas fa-shopping-basket"></i> Découvrir nos produits</a>
      <a href="#contact" class="btn-outline"><i class="fas fa-phone"></i> Nous contacter</a>
    </div>
  </div>
  <div class="hero-img">🥩</div>
</section>

<!-- STATS -->
<div class="stats-band">
  <div class="stat-item"><h3>500+</h3><p>Produits disponibles</p></div>
  <div class="stat-item"><h3>1200+</h3><p>Clients satisfaits</p></div>
  <div class="stat-item"><h3>10+</h3><p>Années d'expérience</p></div>
  <div class="stat-item"><h3>24h</h3><p>Livraison express</p></div>
</div>

<?php
require_once 'includes/db.php';
$pdo = getPDO();
$cats = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();
$prods = $pdo->query("SELECT p.*,c.nom as cat_nom,c.couleur as cat_color FROM produits p LEFT JOIN categories c ON p.categorie_id=c.id WHERE p.actif=1 ORDER BY RAND() LIMIT 12")->fetchAll();
?>

<!-- CATÉGORIES -->
<section class="section" id="categories">
  <div class="section-header">
    <h2>Nos <span>Catégories</span></h2>
    <div class="divider"></div>
    <p>Une sélection complète de produits de qualité pour satisfaire tous vos besoins</p>
  </div>
  <div class="cat-grid">
    <?php foreach($cats as $c): ?>
    <div class="cat-card" style="--cat-color:<?= htmlspecialchars($c['couleur']) ?>">
      <span class="cat-icon"><?= $c['icone'] ?></span>
      <h3><?= htmlspecialchars($c['nom']) ?></h3>
      <p><?= htmlspecialchars($c['description'] ?? '') ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- PRODUITS -->
<section class="section" id="produits" style="background:rgba(255,255,255,.01);padding-top:0">
  <div class="section-header">
    <h2>Nos <span>Produits Vedettes</span></h2>
    <div class="divider"></div>
    <p>Fraîcheur quotidienne, traçabilité garantie, saveurs authentiques</p>
  </div>
  <div class="prod-grid">
    <?php foreach($prods as $p):
      $img = $p['image'] ? 'assets/uploads/'.htmlspecialchars($p['image']) : null;
    ?>
    <div class="prod-card">
      <div class="prod-img">
        <?php if($img): ?><img src="<?= $img ?>" alt="<?= htmlspecialchars($p['nom']) ?>" onerror="this.style.display='none';this.nextSibling.style.display='flex'"><?php endif; ?>
        <div style="width:100%;height:200px;display:<?= $img?'none':'flex' ?>;align-items:center;justify-content:center;font-size:4rem;background:rgba(192,57,43,.05)">🥩</div>
      </div>
      <div class="prod-body">
        <div class="prod-cat" style="color:<?= htmlspecialchars($p['cat_color']??'#d4ac0d') ?>"><?= htmlspecialchars($p['cat_nom']??'') ?></div>
        <div class="prod-name"><?= htmlspecialchars($p['nom']) ?></div>
        <div class="prod-desc"><?= htmlspecialchars(mb_substr($p['description']??'',0,80)) ?>...</div>
        <div class="prod-footer">
          <div class="prod-price">
            <?= number_format($p['prix_vente'],0,',',' ') ?> FCFA
            <span class="prod-unit">/ <?= htmlspecialchars($p['unite']) ?></span>
          </div>
          <?php if($p['stock_actuel'] > $p['stock_min']): ?>
            <span class="badge-stock in-stock">✓ En stock</span>
          <?php elseif($p['stock_actuel'] > 0): ?>
            <span class="badge-stock low-stock">⚠ Limité</span>
          <?php else: ?>
            <span class="badge-stock" style="background:rgba(192,57,43,.15);color:#e74c3c;border:1px solid rgba(192,57,43,.3)">✗ Épuisé</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- SERVICES -->
<section class="services" id="services">
  <div class="section-header">
    <h2>Nos <span>Services Premium</span></h2>
    <div class="divider"></div>
    <p>Un accompagnement complet pour votre satisfaction</p>
  </div>
  <div class="serv-grid">
    <?php $services = [
      ['fas fa-award','Qualité Certifiée','Tous nos produits sont certifiés et conformes aux normes sanitaires les plus strictes. Traçabilité complète de la ferme à votre table.'],
      ['fas fa-truck','Livraison Express','Livraison rapide à domicile et en entreprise dans toute la région de Dakar. Conditionnement isotherme pour préserver la fraîcheur.'],
      ['fas fa-snowflake','Chaîne du Froid','Maintien de la chaîne du froid 24h/24. Stockage à températures contrôlées, garantie de fraîcheur absolue.'],
      ['fas fa-cut','Découpe Sur Mesure','Service de découpe artisanale selon vos préférences. Tranches fines, épaisses, désossage sur demande.'],
      ['fas fa-store','Vente en Gros','Tarifs préférentiels pour les professionnels, restaurants, hôtels et grandes surfaces. Facturation mensuelle.'],
      ['fas fa-headset','SAV Premium','Service après-vente réactif 7j/7. Remplacement garanti en cas de non-conformité. Satisfaction client prioritaire.'],
    ]; foreach($services as $s): ?>
    <div class="serv-card">
      <div class="serv-icon"><i class="<?= $s[0] ?>"></i></div>
      <h3><?= $s[1] ?></h3>
      <p><?= $s[2] ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- PROMO -->
<div class="promo-band" id="contact">
  <h2>Commandez <em>Maintenant</em> — Livraison en <em>24h</em></h2>
  <p>Profitez de nos offres exclusives et de la livraison gratuite pour toute commande supérieure à 25 000 FCFA</p>
  <a href="tel:+221331234567" class="btn-primary" style="display:inline-flex;align-items:center;gap:10px">
    <i class="fas fa-phone-alt"></i> Appeler maintenant
  </a>
</div>

<!-- FOOTER -->
<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="logo">🥩 OMEGA CHARCUTERIE</div>
      <p>Votre partenaire de confiance pour les produits charcutiers, volailles et fromages de qualité premium depuis 2014.</p>
      <br>
      <p style="color:#555"><i class="fas fa-map-marker-alt" style="color:var(--rouge-light)"></i> Zone Commerciale, Dakar, Sénégal</p>
    </div>
    <div class="footer-col">
      <h4>Produits</h4>
      <ul>
        <?php foreach(array_slice($cats,0,6) as $c): ?>
        <li><a href="#categories"><?= $c['icone'] ?> <?= htmlspecialchars($c['nom']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Liens Rapides</h4>
      <ul>
        <li><a href="index.php"><i class="fas fa-home"></i> Accueil</a></li>
        <li><a href="#produits"><i class="fas fa-box"></i> Produits</a></li>
        <li><a href="#services"><i class="fas fa-cogs"></i> Services</a></li>
        <li><a href="admin/login.php"><i class="fas fa-lock"></i> Administration</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Contact</h4>
      <ul>
        <li><a href="tel:+221331234567"><i class="fas fa-phone"></i> +221 33 123 45 67</a></li>
        <li><a href="mailto:info@omega-charcuterie.sn"><i class="fas fa-envelope"></i> info@omega.sn</a></li>
        <li><a href="#"><i class="fab fa-whatsapp"></i> WhatsApp</a></li>
        <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <span>© 2026 <span>OMEGA INFORMATIQUE CONSULTING</span> — Tous droits réservés</span>
    <span>Développé avec ❤️ par OMEGA INFORMATIQUE</span>
  </div>
</footer>

<a href="#" class="scroll-top" id="scrollTop"><i class="fas fa-chevron-up"></i></a>
<script>
const st = document.getElementById('scrollTop');
window.addEventListener('scroll', () => {
  st.classList.toggle('visible', window.scrollY > 300);
});
</script>
</body>
</html>
