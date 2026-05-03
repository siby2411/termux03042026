<?php
$page_title = $page_title ?? 'OMEGA Agence Voyage';
$current    = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($page_title) ?> — OMEGA ✈</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
/* ── RESET & BASE ───────────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:      #06091a;
  --bg2:     #0b1228;
  --bg3:     #101a35;
  --card:    rgba(255,255,255,0.04);
  --card2:   rgba(255,255,255,0.07);
  --border:  rgba(255,255,255,0.09);
  --gold:    #d4a848;
  --gold2:   #f0c86a;
  --cyan:    #00c8f0;
  --green:   #00e5a0;
  --red:     #ff4d6d;
  --orange:  #ff8c42;
  --blue:    #4d9fff;
  --text:    #dce5f5;
  --muted:   #6a7ba0;
  --radius:  12px;
  --nav-h:   60px;
}
html{scroll-behavior:smooth}
body{
  font-family:'Plus Jakarta Sans',sans-serif;
  background:var(--bg);
  color:var(--text);
  min-height:100vh;
  background-image:
    radial-gradient(ellipse 80% 50% at 50% -20%, rgba(0,100,200,0.18) 0%, transparent 60%),
    radial-gradient(ellipse 40% 30% at 80% 10%, rgba(212,168,72,0.06) 0%, transparent 50%);
}

/* ── SCROLLBAR ──────────────────────────────────────── */
::-webkit-scrollbar{width:6px;height:6px}
::-webkit-scrollbar-track{background:var(--bg2)}
::-webkit-scrollbar-thumb{background:rgba(255,255,255,0.12);border-radius:3px}

/* ── NAVBAR ─────────────────────────────────────────── */
.navbar{
  position:sticky;top:0;z-index:1000;
  height:var(--nav-h);
  background:rgba(6,9,26,0.92);
  backdrop-filter:blur(16px);
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  padding:0 28px;
  gap:20px;
}
.nav-brand{
  display:flex;align-items:center;gap:10px;
  text-decoration:none;flex-shrink:0;
}
.nav-brand .plane-icon{
  font-size:1.4rem;
  animation:planePulse 3s ease-in-out infinite;
}
@keyframes planePulse{0%,100%{transform:translateX(0) rotate(-10deg)}50%{transform:translateX(4px) rotate(-5deg)}}
.nav-brand .brand-text{
  font-family:'Bebas Neue',sans-serif;
  font-size:1.35rem;
  letter-spacing:0.06em;
  color:white;
  line-height:1;
}
.nav-brand .brand-text span{color:var(--gold)}

.nav-links{display:flex;align-items:center;gap:2px;flex-wrap:wrap}
.nav-links a{
  color:var(--muted);
  text-decoration:none;
  padding:7px 12px;
  font-size:0.78rem;
  font-weight:500;
  border-radius:8px;
  transition:all 0.2s;
  white-space:nowrap;
  display:flex;align-items:center;gap:5px;
}
.nav-links a:hover{background:var(--card2);color:var(--text)}
.nav-links a.active{background:rgba(212,168,72,0.12);color:var(--gold)}
.nav-links a.active .nav-dot{background:var(--gold)}
.nav-dot{width:5px;height:5px;border-radius:50%;background:var(--border);transition:0.2s}
.nav-sep{width:1px;height:20px;background:var(--border);margin:0 4px}

.nav-user{
  font-size:0.72rem;font-weight:700;
  letter-spacing:0.08em;text-transform:uppercase;
  color:var(--muted);flex-shrink:0;
}
.nav-user span{color:var(--gold)}
.live-badge{
  display:inline-flex;align-items:center;gap:5px;
  background:rgba(0,229,160,0.1);
  border:1px solid rgba(0,229,160,0.25);
  color:var(--green);
  font-size:0.65rem;font-weight:700;letter-spacing:0.08em;
  padding:3px 8px;border-radius:20px;
}
.live-badge::before{
  content:'';width:6px;height:6px;border-radius:50%;
  background:var(--green);
  animation:livePulse 1.5s ease-in-out infinite;
}
@keyframes livePulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:0.4;transform:scale(0.8)}}

/* ── PAGE WRAPPER ───────────────────────────────────── */
.page{max-width:1300px;margin:0 auto;padding:32px 24px 60px}

/* ── PAGE HEADER ────────────────────────────────────── */
.page-header{margin-bottom:28px;display:flex;align-items:flex-end;justify-content:space-between;gap:16px;flex-wrap:wrap}
.page-header .eyebrow{font-size:0.68rem;font-weight:700;letter-spacing:0.14em;text-transform:uppercase;color:var(--gold);margin-bottom:5px}
.page-header h1{font-family:'Bebas Neue',sans-serif;font-size:2.2rem;letter-spacing:0.04em;color:white;line-height:1}
.page-header p{margin-top:6px;font-size:0.85rem;color:var(--muted);font-weight:400}

/* ── STAT CARDS ─────────────────────────────────────── */
.stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px}
.stat-card{
  background:var(--card);
  border:1px solid var(--border);
  border-radius:var(--radius);
  padding:20px 22px;
  position:relative;overflow:hidden;
  transition:transform 0.2s,border-color 0.2s;
}
.stat-card:hover{transform:translateY(-2px);border-color:rgba(255,255,255,0.16)}
.stat-card::before{
  content:'';position:absolute;inset:0;
  background:linear-gradient(135deg,rgba(255,255,255,0.03) 0%,transparent 60%);
  pointer-events:none;
}
.stat-icon{font-size:1.6rem;margin-bottom:10px;display:block}
.stat-label{font-size:0.72rem;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--muted);margin-bottom:4px}
.stat-value{font-family:'Bebas Neue',sans-serif;font-size:2rem;letter-spacing:0.04em;color:white;line-height:1}
.stat-sub{font-size:0.75rem;color:var(--muted);margin-top:5px}
.stat-card.gold .stat-value{color:var(--gold)}
.stat-card.cyan .stat-value{color:var(--cyan)}
.stat-card.green .stat-value{color:var(--green)}
.stat-card.red .stat-value{color:var(--red)}

/* ── CARDS ──────────────────────────────────────────── */
.card{
  background:var(--card);
  border:1px solid var(--border);
  border-radius:var(--radius);
  overflow:hidden;
}
.card-header{
  padding:16px 20px;
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;gap:12px;
}
.card-title{font-size:0.82rem;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:var(--text)}
.card-body{padding:20px}

/* ── TABLES ─────────────────────────────────────────── */
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:0.83rem}
thead th{
  padding:10px 14px;text-align:left;
  font-size:0.68rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;
  color:var(--muted);border-bottom:1px solid var(--border);
  background:rgba(0,0,0,0.2);
}
tbody tr{border-bottom:1px solid rgba(255,255,255,0.04);transition:background 0.15s}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:rgba(255,255,255,0.03)}
td{padding:11px 14px;vertical-align:middle}

/* ── BADGES ─────────────────────────────────────────── */
.badge{
  display:inline-flex;align-items:center;gap:4px;
  padding:3px 10px;border-radius:20px;
  font-size:0.68rem;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;
  white-space:nowrap;
}
.badge-programme{background:rgba(77,159,255,0.12);color:var(--blue);border:1px solid rgba(77,159,255,0.25)}
.badge-en_cours{background:rgba(0,229,160,0.12);color:var(--green);border:1px solid rgba(0,229,160,0.25)}
.badge-arrive{background:rgba(106,123,160,0.12);color:var(--muted);border:1px solid rgba(106,123,160,0.25)}
.badge-annule{background:rgba(255,77,109,0.12);color:var(--red);border:1px solid rgba(255,77,109,0.25)}
.badge-retarde{background:rgba(255,140,66,0.12);color:var(--orange);border:1px solid rgba(255,140,66,0.25)}
.badge-payee{background:rgba(0,229,160,0.12);color:var(--green);border:1px solid rgba(0,229,160,0.25)}
.badge-confirmee{background:rgba(77,159,255,0.12);color:var(--blue);border:1px solid rgba(77,159,255,0.25)}
.badge-en_attente{background:rgba(255,140,66,0.12);color:var(--orange);border:1px solid rgba(255,140,66,0.25)}
.badge-economique{background:rgba(106,123,160,0.1);color:var(--muted);border:1px solid var(--border)}
.badge-business{background:rgba(212,168,72,0.12);color:var(--gold);border:1px solid rgba(212,168,72,0.25)}
.badge-premiere{background:rgba(240,200,106,0.18);color:var(--gold2);border:1px solid rgba(240,200,106,0.35)}
.badge-dot::before{content:'';display:inline-block;width:5px;height:5px;border-radius:50%;background:currentColor;margin-right:2px}

/* ── BUTTONS ────────────────────────────────────────── */
.btn{
  display:inline-flex;align-items:center;gap:6px;
  padding:9px 18px;border-radius:9px;font-family:inherit;
  font-size:0.82rem;font-weight:600;cursor:pointer;
  text-decoration:none;transition:all 0.2s;border:1px solid transparent;
  white-space:nowrap;
}
.btn-gold{background:var(--gold);color:#0d0a00;border-color:var(--gold)}
.btn-gold:hover{background:var(--gold2);border-color:var(--gold2)}
.btn-ghost{background:transparent;color:var(--muted);border-color:var(--border)}
.btn-ghost:hover{background:var(--card2);color:var(--text);border-color:rgba(255,255,255,0.18)}
.btn-danger{background:rgba(255,77,109,0.1);color:var(--red);border-color:rgba(255,77,109,0.3)}
.btn-danger:hover{background:rgba(255,77,109,0.2)}
.btn-sm{padding:6px 12px;font-size:0.75rem;border-radius:7px}
.btn-icon{width:32px;height:32px;padding:0;justify-content:center;font-size:0.9rem}

/* ── FORMS ──────────────────────────────────────────── */
.form-grid{display:grid;gap:18px}
.form-grid-2{grid-template-columns:1fr 1fr}
.form-group{display:flex;flex-direction:column;gap:7px}
.form-label{font-size:0.75rem;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:var(--muted)}
.form-label .req{color:var(--gold)}
.form-control{
  width:100%;padding:10px 13px;
  background:rgba(255,255,255,0.04);
  border:1px solid var(--border);
  border-radius:8px;
  font-family:inherit;font-size:0.88rem;
  color:var(--text);outline:none;
  transition:border-color 0.2s,box-shadow 0.2s;
  -webkit-appearance:none;
}
.form-control:focus{
  border-color:var(--gold);
  box-shadow:0 0 0 3px rgba(212,168,72,0.14);
}
.form-control::placeholder{color:var(--muted)}
textarea.form-control{min-height:80px;resize:vertical}
select.form-control{
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236a7ba0' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat:no-repeat;background-position:right 13px center;
  padding-right:36px;cursor:pointer;
}
.form-hint{font-size:0.73rem;color:var(--muted)}
.form-section{
  background:var(--card);border:1px solid var(--border);
  border-radius:var(--radius);overflow:hidden;margin-bottom:16px;
}
.form-section-title{
  padding:12px 18px;border-bottom:1px solid var(--border);
  font-size:0.68rem;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;
  color:var(--muted);background:rgba(0,0,0,0.15);
}
.form-section-body{padding:20px 18px;display:flex;flex-direction:column;gap:16px}

/* ── ALERTS ─────────────────────────────────────────── */
.alert{
  padding:13px 16px;border-radius:9px;
  font-size:0.84rem;font-weight:500;margin-bottom:20px;
  display:flex;align-items:flex-start;gap:9px;
}
.alert-success{background:rgba(0,229,160,0.08);border:1px solid rgba(0,229,160,0.25);color:#00e5a0}
.alert-error{background:rgba(255,77,109,0.08);border:1px solid rgba(255,77,109,0.25);color:var(--red)}

/* ── GRID LAYOUTS ───────────────────────────────────── */
.grid-2{display:grid;grid-template-columns:repeat(2,1fr);gap:20px}
.grid-3{display:grid;grid-template-columns:repeat(3,1fr);gap:20px}
.grid-4{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}

/* ── DESTINATION MAP CARD ───────────────────────────── */
#map{height:480px;border-radius:var(--radius);overflow:hidden}
.leaflet-container{background:#06091a!important}

/* ── OFFER CARDS ────────────────────────────────────── */
.offer-card{
  background:var(--card);border:1px solid var(--border);
  border-radius:var(--radius);overflow:hidden;
  transition:transform 0.2s,border-color 0.2s;
}
.offer-card:hover{transform:translateY(-3px);border-color:rgba(212,168,72,0.3)}
.offer-card-img{
  height:140px;background:linear-gradient(135deg,var(--bg3),var(--bg2));
  display:flex;align-items:center;justify-content:center;
  font-size:3.5rem;position:relative;overflow:hidden;
}
.offer-card-img::after{
  content:'';position:absolute;inset:0;
  background:linear-gradient(to bottom,transparent 40%,rgba(6,9,26,0.8));
}
.offer-card-body{padding:14px 16px}
.offer-price-row{display:flex;align-items:baseline;gap:8px;margin-top:8px}
.offer-price{font-family:'Bebas Neue',sans-serif;font-size:1.5rem;color:var(--gold)}
.offer-price-old{font-size:0.78rem;color:var(--muted);text-decoration:line-through}
.offer-discount{
  background:rgba(255,77,109,0.12);color:var(--red);
  border:1px solid rgba(255,77,109,0.25);
  font-size:0.68rem;font-weight:700;padding:2px 7px;border-radius:20px;
}

/* ── FLIGHT ROUTE DISPLAY ───────────────────────────── */
.route-display{
  display:flex;align-items:center;gap:8px;
  font-size:0.8rem;font-weight:600;
}
.route-code{
  background:rgba(255,255,255,0.06);
  padding:4px 8px;border-radius:6px;
  font-family:'Bebas Neue',sans-serif;font-size:1rem;letter-spacing:0.06em;
}
.route-arrow{color:var(--gold);font-size:1rem}

/* ── EMPTY STATE ────────────────────────────────────── */
.empty-state{
  text-align:center;padding:48px 20px;color:var(--muted);
}
.empty-state .empty-icon{font-size:3rem;margin-bottom:12px;opacity:0.4}

/* ── RESPONSIVE ─────────────────────────────────────── */
@media(max-width:768px){
  .navbar{padding:0 16px}
  .nav-links a{padding:6px 9px}
  .page{padding:20px 16px 40px}
  .stats-grid{grid-template-columns:repeat(2,1fr)}
  .grid-2,.grid-3,.grid-4{grid-template-columns:1fr}
  .form-grid-2{grid-template-columns:1fr}
  .page-header{flex-direction:column;align-items:flex-start}
}
</style>
