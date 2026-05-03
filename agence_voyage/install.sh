#!/bin/bash
set -e

# ══════════════════════════════════════════════════════════════
#   ✈  OMEGA AGENCE VOYAGE — Script d'installation complet
#      Termux proot-distro · MariaDB · PHP · Apache
#      Base : agence_voyage  |  Répertoire : agence_voyage
# ══════════════════════════════════════════════════════════════

APP="/root/shared/htdocs/apachewsl2026/agence_voyage"
DB="agence_voyage"
G='\033[0;32m'; Y='\033[1;33m'; C='\033[0;36m'; R='\033[0m'

echo ""
echo -e "${C}  ╔══════════════════════════════════════════════╗"
echo -e "  ║   ✈  OMEGA AGENCE VOYAGE — Installation     ║"
echo -e "  ╚══════════════════════════════════════════════╝${R}"
echo ""

# ── 1. RÉPERTOIRES ───────────────────────────────────────────
mkdir -p $APP/{config,includes}
echo -e "${G}  ✓ Répertoires créés${R}"


# ══════════════════════════════════════════════════════════════
# 2. BASE DE DONNÉES
# ══════════════════════════════════════════════════════════════
mysql -u root << 'SQL'
DROP DATABASE IF EXISTS agence_voyage;
CREATE DATABASE agence_voyage CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agence_voyage;

CREATE TABLE compagnies_aeriennes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code_iata VARCHAR(3) NOT NULL,
  nom VARCHAR(255) NOT NULL,
  pays VARCHAR(100),
  hub VARCHAR(100),
  actif TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE destinations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code_iata VARCHAR(4) NOT NULL,
  ville VARCHAR(255) NOT NULL,
  pays VARCHAR(255) NOT NULL,
  continent VARCHAR(100),
  latitude DECIMAL(10,6),
  longitude DECIMAL(10,6),
  description TEXT,
  populaire TINYINT(1) DEFAULT 0
);

CREATE TABLE vols (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero_vol VARCHAR(20) NOT NULL,
  compagnie_id INT,
  origine_id INT,
  destination_id INT,
  date_depart DATETIME NOT NULL,
  date_arrivee DATETIME NOT NULL,
  type_appareil VARCHAR(100) DEFAULT 'Airbus A320',
  places_eco INT DEFAULT 150,
  places_business INT DEFAULT 20,
  places_first INT DEFAULT 0,
  prix_eco DECIMAL(10,2) DEFAULT 0,
  prix_business DECIMAL(10,2) DEFAULT 0,
  prix_first DECIMAL(10,2) DEFAULT 0,
  statut ENUM('PROGRAMME','EN_COURS','ARRIVE','ANNULE','RETARDE') DEFAULT 'PROGRAMME',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (compagnie_id) REFERENCES compagnies_aeriennes(id),
  FOREIGN KEY (origine_id) REFERENCES destinations(id),
  FOREIGN KEY (destination_id) REFERENCES destinations(id)
);

CREATE TABLE clients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(255) NOT NULL,
  prenom VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  telephone VARCHAR(100),
  passeport VARCHAR(100),
  nationalite VARCHAR(100) DEFAULT 'Sénégalaise',
  date_naissance DATE,
  adresse TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE reservations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(20) UNIQUE NOT NULL,
  vol_id INT,
  client_id INT,
  classe ENUM('ECONOMIQUE','BUSINESS','PREMIERE') DEFAULT 'ECONOMIQUE',
  nb_passagers INT DEFAULT 1,
  prix_total DECIMAL(10,2),
  statut ENUM('EN_ATTENTE','CONFIRMEE','PAYEE','ANNULEE') DEFAULT 'EN_ATTENTE',
  date_reservation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  notes TEXT,
  FOREIGN KEY (vol_id) REFERENCES vols(id),
  FOREIGN KEY (client_id) REFERENCES clients(id)
);

CREATE TABLE paiements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reservation_id INT,
  montant DECIMAL(10,2),
  methode ENUM('CASH','VIREMENT','CARTE','MOBILE_MONEY','CHEQUE') DEFAULT 'CASH',
  statut ENUM('EN_ATTENTE','VALIDE','REJETE') DEFAULT 'EN_ATTENTE',
  date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reference_paiement VARCHAR(100),
  FOREIGN KEY (reservation_id) REFERENCES reservations(id)
);

CREATE TABLE offres_promotionnelles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(255) NOT NULL,
  description TEXT,
  destination_id INT,
  prix_promo DECIMAL(10,2),
  prix_original DECIMAL(10,2),
  date_debut DATE,
  date_fin DATE,
  actif TINYINT(1) DEFAULT 1,
  FOREIGN KEY (destination_id) REFERENCES destinations(id)
);

-- ── DONNÉES INITIALES ─────────────────────────────────────

INSERT INTO compagnies_aeriennes (code_iata, nom, pays, hub) VALUES
('AF', 'Air France', 'France', 'Paris CDG'),
('EK', 'Emirates', 'Émirats Arabes Unis', 'Dubai DXB'),
('TK', 'Turkish Airlines', 'Turquie', 'Istanbul IST'),
('AT', 'Royal Air Maroc', 'Maroc', 'Casablanca CMN'),
('ET', 'Ethiopian Airlines', 'Éthiopie', 'Addis-Abeba ADD'),
('LH', 'Lufthansa', 'Allemagne', 'Francfort FRA'),
('HC', 'Air Sénégal', 'Sénégal', 'Dakar DSS'),
('SN', 'Brussels Airlines', 'Belgique', 'Bruxelles BRU'),
('IB', 'Iberia', 'Espagne', 'Madrid MAD');

INSERT INTO destinations (code_iata, ville, pays, continent, latitude, longitude, description, populaire) VALUES
('DSS', 'Dakar', 'Sénégal', 'Afrique', 14.739187, -17.490655, 'Porte de l''Afrique de l''Ouest, ville lumineuse et vibrante', 1),
('CDG', 'Paris', 'France', 'Europe', 48.856613, 2.352222, 'Ville Lumière, capitale culturelle du monde', 1),
('DXB', 'Dubaï', 'Émirats Arabes Unis', 'Asie', 25.204849, 55.270782, 'Métropole de luxe et d''innovation architecturale', 1),
('IST', 'Istanbul', 'Turquie', 'Europe', 41.008240, 28.978359, 'Carrefour de l''Orient et de l''Occident', 1),
('JFK', 'New York', 'États-Unis', 'Amérique', 40.712776, -74.005974, 'La ville qui ne dort jamais, capitale du monde', 1),
('CMN', 'Casablanca', 'Maroc', 'Afrique', 33.573110, -7.589843, 'Capitale économique du Maghreb', 1),
('ADD', 'Addis-Abeba', 'Éthiopie', 'Afrique', 9.145000, 40.489673, 'Hub stratégique d''Afrique de l''Est', 0),
('FRA', 'Francfort', 'Allemagne', 'Europe', 50.110924, 8.682127, 'Premier hub aérien d''Europe centrale', 0),
('LOS', 'Lagos', 'Nigeria', 'Afrique', 6.524379, 3.379206, 'Capitale économique d''Afrique de l''Ouest', 0),
('ACC', 'Accra', 'Ghana', 'Afrique', 5.603717, -0.186964, 'Porte de l''Afrique de l''Ouest anglophone', 0),
('ABJ', 'Abidjan', 'Côte d''Ivoire', 'Afrique', 5.359952, -4.008257, 'Capitale économique de la Côte d''Ivoire', 0),
('BRU', 'Bruxelles', 'Belgique', 'Europe', 50.846557, 4.351697, 'Capitale de l''Europe', 0),
('MAD', 'Madrid', 'Espagne', 'Europe', 40.416775, -3.703790, 'Capitale culturelle de l''Espagne', 0);

INSERT INTO vols (numero_vol, compagnie_id, origine_id, destination_id, date_depart, date_arrivee, type_appareil, places_eco, places_business, places_first, prix_eco, prix_business, prix_first, statut) VALUES
('AF718', 1, 1, 2, DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 2 DAY), INTERVAL 7 HOUR), 'Airbus A330-300', 250, 40, 0, 580000, 1450000, 0, 'PROGRAMME'),
('EK762', 2, 1, 3, DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 1 DAY), INTERVAL 9 HOUR), 'Boeing 777-300ER', 304, 42, 8, 750000, 2200000, 4500000, 'PROGRAMME'),
('TK501', 3, 1, 4, DATE_ADD(NOW(), INTERVAL 4 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 4 DAY), INTERVAL 8 HOUR), 'Boeing 737 MAX 9', 150, 20, 0, 520000, 1300000, 0, 'PROGRAMME'),
('HC100', 7, 1, 6, DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 1 DAY), INTERVAL 4 HOUR), 'ATR 72-600', 70, 0, 0, 185000, 0, 0, 'PROGRAMME'),
('ET551', 5, 1, 7, NOW(), DATE_ADD(NOW(), INTERVAL 5 HOUR), 'Boeing 787-9 Dreamliner', 280, 24, 0, 420000, 980000, 0, 'EN_COURS'),
('AF719', 1, 2, 1, DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 3 DAY), INTERVAL 6 HOUR), 'Airbus A330-200', 250, 40, 0, 620000, 1550000, 0, 'PROGRAMME'),
('LH566', 6, 1, 8, DATE_ADD(NOW(), INTERVAL 5 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 5 DAY), INTERVAL 9 HOUR), 'Airbus A340-600', 200, 30, 0, 610000, 1600000, 0, 'PROGRAMME'),
('HC200', 7, 1, 9, DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 2 DAY), INTERVAL 3 HOUR), 'De Havilland Q400', 78, 0, 0, 145000, 0, 0, 'RETARDE');

INSERT INTO clients (nom, prenom, email, telephone, nationalite) VALUES
('Diallo', 'Mamadou', 'mamadou.diallo@gmail.com', '+221 77 123 45 67', 'Sénégalaise'),
('Ndiaye', 'Fatou', 'fatou.ndiaye@gmail.com', '+221 78 234 56 78', 'Sénégalaise'),
('Sow', 'Ibrahima', 'ibrahima.sow@yahoo.fr', '+221 76 345 67 89', 'Sénégalaise'),
('Fall', 'Aissatou', 'aissatou.fall@hotmail.com', '+221 70 456 78 90', 'Sénégalaise'),
('Ba', 'Oumar', 'oumar.ba@gmail.com', '+221 77 567 89 01', 'Sénégalaise'),
('Mbaye', 'Seydou', 'seydou.mbaye@gmail.com', '+221 77 678 90 12', 'Sénégalaise'),
('Sarr', 'Mariama', 'mariama.sarr@outlook.com', '+221 78 789 01 23', 'Sénégalaise');

INSERT INTO reservations (reference, vol_id, client_id, classe, nb_passagers, prix_total, statut) VALUES
('OMG-2026-0001', 1, 1, 'ECONOMIQUE', 2, 1160000, 'PAYEE'),
('OMG-2026-0002', 2, 2, 'BUSINESS', 1, 2200000, 'CONFIRMEE'),
('OMG-2026-0003', 4, 3, 'ECONOMIQUE', 1, 185000, 'EN_ATTENTE'),
('OMG-2026-0004', 6, 4, 'ECONOMIQUE', 3, 1860000, 'CONFIRMEE'),
('OMG-2026-0005', 3, 5, 'BUSINESS', 1, 1300000, 'PAYEE'),
('OMG-2026-0006', 7, 6, 'ECONOMIQUE', 2, 1220000, 'EN_ATTENTE'),
('OMG-2026-0007', 5, 7, 'ECONOMIQUE', 1, 420000, 'CONFIRMEE');

INSERT INTO offres_promotionnelles (titre, description, destination_id, prix_promo, prix_original, date_debut, date_fin, actif) VALUES
('Paris Printemps 2026', 'Aller-retour Dakar-Paris en classe Économique, tarif promotionnel limité', 2, 850000, 1200000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1),
('Dubai Luxury Experience', 'Séjour premium Dakar-Dubai en classe Business, expérience 5 étoiles', 3, 1900000, 2800000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 1),
('Istanbul Découverte', 'Vol direct Dakar-Istanbul à prix réduit, découvrez le Bosphore', 4, 480000, 680000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 25 DAY), 1),
('Casablanca Weekend', 'Escapade de 3 jours au Maroc, tarif imbattable', 6, 290000, 420000, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 20 DAY), 1);

SQL
echo -e "${G}  ✓ Base de données '${DB}' initialisée${R}"


# ══════════════════════════════════════════════════════════════
# 3. config/db.php
# ══════════════════════════════════════════════════════════════
cat > $APP/config/db.php << 'EOF'
<?php
$host   = 'localhost';
$dbname = 'agence_voyage';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user, $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die('<div style="font-family:monospace;padding:20px;background:#1a0a0a;color:#ff6b6b;border-radius:8px">
        <strong>Erreur DB :</strong> ' . htmlspecialchars($e->getMessage()) . '</div>');
}

// Helpers
function money(float $n): string {
    return number_format($n, 0, ',', ' ') . ' FCFA';
}
function ago(string $dt): string {
    $diff = time() - strtotime($dt);
    if ($diff < 60) return 'À l\'instant';
    if ($diff < 3600) return floor($diff/60) . ' min';
    if ($diff < 86400) return floor($diff/3600) . 'h';
    return floor($diff/86400) . 'j';
}
EOF
echo -e "${G}  ✓ config/db.php${R}"


# ══════════════════════════════════════════════════════════════
# 4. includes/header.php — CSS global "Dark Sky Premium"
# ══════════════════════════════════════════════════════════════
cat > $APP/includes/header.php << 'EOF'
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
EOF
echo -e "${G}  ✓ includes/header.php${R}"


# ══════════════════════════════════════════════════════════════
# 5. includes/navbar.php
# ══════════════════════════════════════════════════════════════
cat > $APP/includes/navbar.php << 'EOF'
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
EOF
echo -e "${G}  ✓ includes/navbar.php${R}"


# ══════════════════════════════════════════════════════════════
# 6. includes/footer.php
# ══════════════════════════════════════════════════════════════
cat > $APP/includes/footer.php << 'EOF'
<footer style="text-align:center;padding:40px 20px;color:var(--muted);font-size:0.75rem;border-top:1px solid var(--border);margin-top:40px">
  <div style="font-family:'Bebas Neue',sans-serif;font-size:1.1rem;letter-spacing:0.1em;margin-bottom:4px">
    ✈ OMEGA <span style="color:var(--gold)">AGENCE VOYAGE</span>
  </div>
  Dakar, Sénégal — IATA Certifié · © 2026 · Tous droits réservés
</footer>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Counter animation
document.querySelectorAll('[data-count]').forEach(el => {
  const target = parseInt(el.dataset.count);
  const duration = 1200;
  const step = target / (duration / 16);
  let current = 0;
  const timer = setInterval(() => {
    current = Math.min(current + step, target);
    el.textContent = Math.floor(current).toLocaleString('fr-FR');
    if (current >= target) clearInterval(timer);
  }, 16);
});
</script>
</body></html>
EOF
echo -e "${G}  ✓ includes/footer.php${R}"


# ══════════════════════════════════════════════════════════════
# 7. index.php — TABLEAU DE BORD
# ══════════════════════════════════════════════════════════════
cat > $APP/index.php << 'EOF'
<?php
require 'config/db.php';
$page_title = 'Tableau de Bord';
require 'includes/header.php';

// KPIs
$total_vols      = $pdo->query("SELECT COUNT(*) FROM vols")->fetchColumn();
$vols_en_cours   = $pdo->query("SELECT COUNT(*) FROM vols WHERE statut='EN_COURS'")->fetchColumn();
$total_clients   = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$total_res       = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$ca_total        = $pdo->query("SELECT COALESCE(SUM(prix_total),0) FROM reservations WHERE statut IN('CONFIRMEE','PAYEE')")->fetchColumn();
$res_mois        = $pdo->query("SELECT COUNT(*) FROM reservations WHERE MONTH(date_reservation)=MONTH(NOW())")->fetchColumn();

// Prochains vols
$prochains = $pdo->query("
  SELECT v.*, c.nom as compagnie, o.ville as origine_v, d.ville as dest_v,
         o.code_iata as orig_code, d.code_iata as dest_code
  FROM vols v
  LEFT JOIN compagnies_aeriennes c ON v.compagnie_id=c.id
  LEFT JOIN destinations o ON v.origine_id=o.id
  LEFT JOIN destinations d ON v.destination_id=d.id
  WHERE v.date_depart >= NOW()
  ORDER BY v.date_depart ASC LIMIT 6
")->fetchAll();

// Dernières réservations
$dernieres = $pdo->query("
  SELECT r.*, CONCAT(cl.prenom,' ',cl.nom) as client_nom,
         CONCAT(o.code_iata,' → ',d.code_iata) as trajet,
         v.numero_vol
  FROM reservations r
  LEFT JOIN clients cl ON r.client_id=cl.id
  LEFT JOIN vols v ON r.vol_id=v.id
  LEFT JOIN destinations o ON v.origine_id=o.id
  LEFT JOIN destinations d ON v.destination_id=d.id
  ORDER BY r.date_reservation DESC LIMIT 7
")->fetchAll();

function statut_badge(string $s): string {
    $map = [
        'PROGRAMME' => 'programme','EN_COURS' => 'en_cours','ARRIVE' => 'arrive',
        'ANNULE' => 'annule','RETARDE' => 'retarde',
        'PAYEE' => 'payee','CONFIRMEE' => 'confirmee','EN_ATTENTE' => 'en_attente',
        'ANNULEE' => 'annule',
    ];
    $labels = [
        'PROGRAMME' => '▷ Programmé','EN_COURS' => '▶ En vol','ARRIVE' => '✓ Arrivé',
        'ANNULE' => '✕ Annulé','RETARDE' => '⚠ Retardé',
        'PAYEE' => '✓ Payée','CONFIRMEE' => '◈ Confirmée','EN_ATTENTE' => '○ Attente',
        'ANNULEE' => '✕ Annulée',
    ];
    $cls = $map[$s] ?? 'en_attente';
    $lbl = $labels[$s] ?? $s;
    return "<span class=\"badge badge-{$cls} badge-dot\">{$lbl}</span>";
}
?>
<?php require 'includes/navbar.php'; ?>

<div class="page">
  <!-- Hero -->
  <div style="margin-bottom:32px;padding:32px 28px;background:linear-gradient(135deg,rgba(212,168,72,0.08) 0%,transparent 60%),var(--card);border:1px solid var(--border);border-radius:16px;position:relative;overflow:hidden">
    <div style="position:absolute;right:-20px;top:-20px;font-size:8rem;opacity:0.04;transform:rotate(-15deg);pointer-events:none">✈</div>
    <div style="font-size:0.7rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:var(--gold);margin-bottom:6px">Bienvenue sur la plateforme</div>
    <div style="font-family:'Bebas Neue',sans-serif;font-size:2.4rem;letter-spacing:0.04em;color:white;line-height:1">
      OMEGA <span style="color:var(--gold)">AGENCE VOYAGE</span>
    </div>
    <p style="margin-top:8px;color:var(--muted);font-size:0.88rem">Gestion complète des vols, réservations et clients · Dakar, Sénégal</p>
    <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap">
      <a href="ajouter_vol.php" class="btn btn-gold">✈ Nouveau vol</a>
      <a href="ajouter_reservation.php" class="btn btn-ghost">+ Réservation</a>
      <a href="ajouter_client.php" class="btn btn-ghost">+ Client</a>
    </div>
  </div>

  <!-- KPIs -->
  <div class="stats-grid">
    <div class="stat-card gold">
      <span class="stat-icon">✈</span>
      <div class="stat-label">Total Vols</div>
      <div class="stat-value" data-count="<?= $total_vols ?>">0</div>
      <div class="stat-sub"><?= $vols_en_cours ?> en vol actuellement</div>
    </div>
    <div class="stat-card cyan">
      <span class="stat-icon">📋</span>
      <div class="stat-label">Réservations</div>
      <div class="stat-value" data-count="<?= $total_res ?>">0</div>
      <div class="stat-sub"><?= $res_mois ?> ce mois-ci</div>
    </div>
    <div class="stat-card">
      <span class="stat-icon">👤</span>
      <div class="stat-label">Clients</div>
      <div class="stat-value" data-count="<?= $total_clients ?>">0</div>
      <div class="stat-sub">Base clients enregistrés</div>
    </div>
    <div class="stat-card green">
      <span class="stat-icon">💰</span>
      <div class="stat-label">Chiffre d'Affaires</div>
      <div class="stat-value" style="font-size:1.3rem"><?= money($ca_total) ?></div>
      <div class="stat-sub">Confirmé + Payé</div>
    </div>
  </div>

  <div class="grid-2" style="gap:20px;margin-bottom:24px">
    <!-- Prochains départs -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">✈ Prochains Départs</span>
        <a href="vols.php" class="btn btn-ghost btn-sm">Tous les vols →</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr>
            <th>Vol</th><th>Trajet</th><th>Départ</th><th>Statut</th>
          </tr></thead>
          <tbody>
          <?php foreach($prochains as $v): ?>
          <tr>
            <td>
              <div style="font-weight:700;color:var(--text)"><?= htmlspecialchars($v['numero_vol']) ?></div>
              <div style="font-size:0.72rem;color:var(--muted)"><?= htmlspecialchars($v['compagnie'] ?? '') ?></div>
            </td>
            <td>
              <div class="route-display">
                <span class="route-code"><?= $v['orig_code'] ?></span>
                <span class="route-arrow">→</span>
                <span class="route-code"><?= $v['dest_code'] ?></span>
              </div>
            </td>
            <td>
              <div style="font-size:0.8rem"><?= date('d M', strtotime($v['date_depart'])) ?></div>
              <div style="font-size:0.7rem;color:var(--muted)"><?= date('H:i', strtotime($v['date_depart'])) ?></div>
            </td>
            <td><?= statut_badge($v['statut']) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Dernières réservations -->
    <div class="card">
      <div class="card-header">
        <span class="card-title">📋 Dernières Réservations</span>
        <a href="reservations.php" class="btn btn-ghost btn-sm">Toutes →</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr>
            <th>Réf.</th><th>Client</th><th>Vol</th><th>Statut</th>
          </tr></thead>
          <tbody>
          <?php foreach($dernieres as $r): ?>
          <tr>
            <td style="font-family:'Bebas Neue',sans-serif;font-size:0.85rem;letter-spacing:0.04em;color:var(--gold)"><?= $r['reference'] ?></td>
            <td style="font-size:0.82rem"><?= htmlspecialchars($r['client_nom'] ?? '—') ?></td>
            <td style="font-size:0.78rem;color:var(--muted)"><?= htmlspecialchars($r['trajet'] ?? '—') ?></td>
            <td><?= statut_badge($r['statut']) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require 'includes/footer.php'; ?>
EOF
echo -e "${G}  ✓ index.php${R}"


# ══════════════════════════════════════════════════════════════
# 8. vols.php — LISTE DES VOLS
# ══════════════════════════════════════════════════════════════
cat > $APP/vols.php << 'EOF'
<?php
require 'config/db.php';
$page_title = 'Gestion des Vols';
require 'includes/header.php';

$filtre = $_GET['statut'] ?? '';
$sql = "SELECT v.*, c.nom as compagnie, c.code_iata as comp_iata,
               o.ville as orig_v, o.code_iata as orig_code,
               d.ville as dest_v, d.code_iata as dest_code
        FROM vols v
        LEFT JOIN compagnies_aeriennes c ON v.compagnie_id=c.id
        LEFT JOIN destinations o ON v.origine_id=o.id
        LEFT JOIN destinations d ON v.destination_id=d.id";
$params = [];
if ($filtre) { $sql .= " WHERE v.statut = :s"; $params[':s'] = $filtre; }
$sql .= " ORDER BY v.date_depart ASC";
$vols = $pdo->prepare($sql); $vols->execute($params); $vols = $vols->fetchAll();

$statuts = ['','PROGRAMME','EN_COURS','ARRIVE','RETARDE','ANNULE'];
$labels_s = ['' => 'Tous', 'PROGRAMME' => 'Programmés', 'EN_COURS' => 'En vol',
             'ARRIVE' => 'Arrivés', 'RETARDE' => 'Retardés', 'ANNULE' => 'Annulés'];

function statut_badge(string $s): string {
    $map=['PROGRAMME'=>'programme','EN_COURS'=>'en_cours','ARRIVE'=>'arrive','ANNULE'=>'annule','RETARDE'=>'retarde'];
    $labels=['PROGRAMME'=>'▷ Programmé','EN_COURS'=>'▶ En vol','ARRIVE'=>'✓ Arrivé','ANNULE'=>'✕ Annulé','RETARDE'=>'⚠ Retardé'];
    return "<span class=\"badge badge-".($map[$s]??'en_attente')." badge-dot\">".($labels[$s]??$s)."</span>";
}
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Gestion</div>
      <h1>Vols</h1>
      <p><?= count($vols) ?> vol(s) enregistré(s)</p>
    </div>
    <a href="ajouter_vol.php" class="btn btn-gold">✈ Ajouter un vol</a>
  </div>

  <!-- Filtres statut -->
  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
    <?php foreach($statuts as $s): ?>
    <a href="?statut=<?= $s ?>"
       style="padding:6px 14px;border-radius:8px;font-size:0.76rem;font-weight:600;text-decoration:none;transition:all 0.2s;
              <?= $filtre===$s ? 'background:rgba(212,168,72,0.15);color:var(--gold);border:1px solid rgba(212,168,72,0.3)' : 'background:var(--card);color:var(--muted);border:1px solid var(--border)' ?>">
      <?= $labels_s[$s] ?>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr>
          <th>Vol</th><th>Compagnie</th><th>Trajet</th>
          <th>Départ</th><th>Arrivée</th><th>Appareil</th>
          <th>Prix Éco</th><th>Statut</th><th>Action</th>
        </tr></thead>
        <tbody>
        <?php if(empty($vols)): ?>
        <tr><td colspan="9"><div class="empty-state"><div class="empty-icon">✈</div>Aucun vol trouvé</div></td></tr>
        <?php else: foreach($vols as $v): ?>
        <tr>
          <td><strong style="color:var(--text);font-weight:700"><?= htmlspecialchars($v['numero_vol']) ?></strong></td>
          <td>
            <span style="background:rgba(255,255,255,0.06);padding:2px 7px;border-radius:5px;font-family:'Bebas Neue',sans-serif;font-size:0.9rem;letter-spacing:0.06em"><?= htmlspecialchars($v['comp_iata']??'??') ?></span>
            <div style="font-size:0.7rem;color:var(--muted);margin-top:2px"><?= htmlspecialchars($v['compagnie']??'') ?></div>
          </td>
          <td>
            <div class="route-display">
              <span class="route-code"><?= $v['orig_code'] ?></span>
              <span class="route-arrow">→</span>
              <span class="route-code"><?= $v['dest_code'] ?></span>
            </div>
            <div style="font-size:0.7rem;color:var(--muted);margin-top:2px"><?= htmlspecialchars($v['orig_v']??'') ?> → <?= htmlspecialchars($v['dest_v']??'') ?></div>
          </td>
          <td>
            <div style="font-size:0.82rem"><?= date('d/m/Y', strtotime($v['date_depart'])) ?></div>
            <div style="font-size:0.7rem;color:var(--muted)"><?= date('H:i', strtotime($v['date_depart'])) ?></div>
          </td>
          <td>
            <div style="font-size:0.82rem"><?= date('d/m/Y', strtotime($v['date_arrivee'])) ?></div>
            <div style="font-size:0.7rem;color:var(--muted)"><?= date('H:i', strtotime($v['date_arrivee'])) ?></div>
          </td>
          <td style="font-size:0.78rem;color:var(--muted)"><?= htmlspecialchars($v['type_appareil']) ?></td>
          <td style="color:var(--gold);font-weight:600;font-size:0.82rem"><?= $v['prix_eco'] > 0 ? money($v['prix_eco']) : '—' ?></td>
          <td><?= statut_badge($v['statut']) ?></td>
          <td>
            <a href="ajouter_reservation.php?vol_id=<?= $v['id'] ?>" class="btn btn-ghost btn-sm btn-icon" title="Réserver">+📋</a>
          </td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
EOF
echo -e "${G}  ✓ vols.php${R}"


# ══════════════════════════════════════════════════════════════
# 9. ajouter_vol.php
# ══════════════════════════════════════════════════════════════
cat > $APP/ajouter_vol.php << 'EOF'
<?php
require 'config/db.php';
$page_title = 'Ajouter un Vol';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = $_POST;
    if (empty($f['numero_vol']) || empty($f['date_depart']) || empty($f['date_arrivee'])) {
        $error = 'Les champs obligatoires sont incomplets.';
    } else {
        try {
            $pdo->prepare("INSERT INTO vols (numero_vol,compagnie_id,origine_id,destination_id,date_depart,date_arrivee,type_appareil,places_eco,places_business,places_first,prix_eco,prix_business,prix_first,statut)
            VALUES(:n,:c,:o,:d,:dep,:arr,:ap,:pe,:pb,:pf,:xe,:xb,:xf,:s)")->execute([
                ':n'=>$f['numero_vol'],':c'=>$f['compagnie_id']??null,
                ':o'=>$f['origine_id'],':d'=>$f['destination_id'],
                ':dep'=>$f['date_depart'],':arr'=>$f['date_arrivee'],
                ':ap'=>$f['type_appareil'],
                ':pe'=>(int)($f['places_eco']??0),':pb'=>(int)($f['places_business']??0),':pf'=>(int)($f['places_first']??0),
                ':xe'=>(float)($f['prix_eco']??0),':xb'=>(float)($f['prix_business']??0),':xf'=>(float)($f['prix_first']??0),
                ':s'=>$f['statut']??'PROGRAMME',
            ]);
            $success = 'Vol <strong>'.htmlspecialchars($f['numero_vol']).'</strong> créé avec succès.';
        } catch(PDOException $e){ $error = 'Erreur: '.$e->getMessage(); }
    }
}

$compagnies = $pdo->query("SELECT * FROM compagnies_aeriennes WHERE actif=1 ORDER BY nom")->fetchAll();
$destinations = $pdo->query("SELECT * FROM destinations ORDER BY ville")->fetchAll();

require 'includes/header.php';
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Gestion des Vols</div>
      <h1>Nouveau Vol</h1>
    </div>
    <a href="vols.php" class="btn btn-ghost">← Retour</a>
  </div>

  <?php if($success) echo "<div class='alert alert-success'>✓ $success</div>"; ?>
  <?php if($error) echo "<div class='alert alert-error'>⚠ ".htmlspecialchars($error)."</div>"; ?>

  <form method="POST">
    <div class="form-section">
      <div class="form-section-title">✈ Identification du Vol</div>
      <div class="form-section-body">
        <div class="form-grid form-grid-2">
          <div class="form-group">
            <label class="form-label">Numéro de vol <span class="req">*</span></label>
            <input type="text" name="numero_vol" class="form-control" value="<?= htmlspecialchars($_POST['numero_vol']??'') ?>" placeholder="Ex: AF718" required>
          </div>
          <div class="form-group">
            <label class="form-label">Compagnie aérienne</label>
            <select name="compagnie_id" class="form-control">
              <option value="">— Sélectionner —</option>
              <?php foreach($compagnies as $c): ?>
              <option value="<?= $c['id'] ?>" <?= (($_POST['compagnie_id']??'')==$c['id'])?'selected':'' ?>>
                [<?= $c['code_iata'] ?>] <?= htmlspecialchars($c['nom']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Type d'appareil</label>
          <input type="text" name="type_appareil" class="form-control" value="<?= htmlspecialchars($_POST['type_appareil']??'') ?>" placeholder="Ex: Airbus A330-300, Boeing 777">
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">🗺 Trajet</div>
      <div class="form-section-body">
        <div class="form-grid form-grid-2">
          <div class="form-group">
            <label class="form-label">Origine <span class="req">*</span></label>
            <select name="origine_id" class="form-control" required>
              <option value="">— Aéroport de départ —</option>
              <?php foreach($destinations as $d): ?>
              <option value="<?= $d['id'] ?>" <?= (($_POST['origine_id']??'')==$d['id'])?'selected':'' ?>>
                [<?= $d['code_iata'] ?>] <?= htmlspecialchars($d['ville']) ?>, <?= htmlspecialchars($d['pays']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Destination <span class="req">*</span></label>
            <select name="destination_id" class="form-control" required>
              <option value="">— Aéroport d'arrivée —</option>
              <?php foreach($destinations as $d): ?>
              <option value="<?= $d['id'] ?>" <?= (($_POST['destination_id']??'')==$d['id'])?'selected':'' ?>>
                [<?= $d['code_iata'] ?>] <?= htmlspecialchars($d['ville']) ?>, <?= htmlspecialchars($d['pays']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-grid form-grid-2">
          <div class="form-group">
            <label class="form-label">Date & Heure de départ <span class="req">*</span></label>
            <input type="datetime-local" name="date_depart" class="form-control" value="<?= htmlspecialchars($_POST['date_depart']??'') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Date & Heure d'arrivée <span class="req">*</span></label>
            <input type="datetime-local" name="date_arrivee" class="form-control" value="<?= htmlspecialchars($_POST['date_arrivee']??'') ?>" required>
          </div>
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">💺 Capacité & Tarifs</div>
      <div class="form-section-body">
        <div class="form-grid" style="grid-template-columns:repeat(3,1fr)">
          <div class="form-group">
            <label class="form-label">Places Économique</label>
            <input type="number" name="places_eco" class="form-control" value="<?= htmlspecialchars($_POST['places_eco']??150) ?>" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">Places Business</label>
            <input type="number" name="places_business" class="form-control" value="<?= htmlspecialchars($_POST['places_business']??20) ?>" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">Places Première</label>
            <input type="number" name="places_first" class="form-control" value="<?= htmlspecialchars($_POST['places_first']??0) ?>" min="0">
          </div>
        </div>
        <div class="form-grid" style="grid-template-columns:repeat(3,1fr)">
          <div class="form-group">
            <label class="form-label">Prix Éco (FCFA)</label>
            <input type="number" name="prix_eco" class="form-control" value="<?= htmlspecialchars($_POST['prix_eco']??0) ?>" min="0" step="1000">
          </div>
          <div class="form-group">
            <label class="form-label">Prix Business (FCFA)</label>
            <input type="number" name="prix_business" class="form-control" value="<?= htmlspecialchars($_POST['prix_business']??0) ?>" min="0" step="1000">
          </div>
          <div class="form-group">
            <label class="form-label">Prix Première (FCFA)</label>
            <input type="number" name="prix_first" class="form-control" value="<?= htmlspecialchars($_POST['prix_first']??0) ?>" min="0" step="1000">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Statut</label>
          <select name="statut" class="form-control">
            <?php foreach(['PROGRAMME'=>'▷ Programmé','EN_COURS'=>'▶ En vol','ARRIVE'=>'✓ Arrivé','RETARDE'=>'⚠ Retardé','ANNULE'=>'✕ Annulé'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= (($_POST['statut']??'PROGRAMME')===$v)?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:4px">
      <a href="vols.php" class="btn btn-ghost">Annuler</a>
      <button type="submit" class="btn btn-gold">✈ Enregistrer le Vol</button>
    </div>
  </form>
</div>
<?php require 'includes/footer.php'; ?>
EOF
echo -e "${G}  ✓ ajouter_vol.php${R}"


# ══════════════════════════════════════════════════════════════
# 10. clients.php
# ══════════════════════════════════════════════════════════════
cat > $APP/clients.php << 'EOF'
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
EOF
echo -e "${G}  ✓ clients.php${R}"


# ══════════════════════════════════════════════════════════════
# 11. ajouter_client.php
# ══════════════════════════════════════════════════════════════
cat > $APP/ajouter_client.php << 'EOF'
<?php
require 'config/db.php';
$page_title = 'Nouveau Client';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = $_POST;
    if (empty($f['nom']) || empty($f['prenom'])) {
        $error = 'Nom et prénom obligatoires.';
    } else {
        try {
            $pdo->prepare("INSERT INTO clients (nom,prenom,email,telephone,passeport,nationalite,date_naissance,adresse)
            VALUES(:nom,:prenom,:email,:tel,:pp,:nat,:dob,:adr)")->execute([
                ':nom'=>$f['nom'],':prenom'=>$f['prenom'],
                ':email'=>$f['email']??null,':tel'=>$f['telephone']??null,
                ':pp'=>$f['passeport']??null,':nat'=>$f['nationalite']??'Sénégalaise',
                ':dob'=>$f['date_naissance']??null,':adr'=>$f['adresse']??null,
            ]);
            $success = 'Client <strong>'.htmlspecialchars($f['prenom'].' '.$f['nom']).'</strong> enregistré.';
        } catch(PDOException $e){ $error='Erreur: '.$e->getMessage(); }
    }
}
require 'includes/header.php';
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Base Clients</div>
      <h1>Nouveau Client</h1>
    </div>
    <a href="clients.php" class="btn btn-ghost">← Retour</a>
  </div>
  <?php if($success) echo "<div class='alert alert-success'>✓ $success &nbsp;<a href='clients.php' style='color:inherit;text-decoration:underline'>Voir les clients →</a></div>"; ?>
  <?php if($error) echo "<div class='alert alert-error'>⚠ ".htmlspecialchars($error)."</div>"; ?>

  <form method="POST" style="max-width:700px">
    <div class="form-section">
      <div class="form-section-title">👤 Identité</div>
      <div class="form-section-body">
        <div class="form-grid form-grid-2">
          <div class="form-group">
            <label class="form-label">Prénom <span class="req">*</span></label>
            <input type="text" name="prenom" class="form-control" value="<?= htmlspecialchars($_POST['prenom']??'') ?>" placeholder="Mamadou" required>
          </div>
          <div class="form-group">
            <label class="form-label">Nom <span class="req">*</span></label>
            <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($_POST['nom']??'') ?>" placeholder="DIALLO" required>
          </div>
        </div>
        <div class="form-grid form-grid-2">
          <div class="form-group">
            <label class="form-label">Date de naissance</label>
            <input type="date" name="date_naissance" class="form-control" value="<?= htmlspecialchars($_POST['date_naissance']??'') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Nationalité</label>
            <input type="text" name="nationalite" class="form-control" value="<?= htmlspecialchars($_POST['nationalite']??'Sénégalaise') ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Numéro de Passeport</label>
          <input type="text" name="passeport" class="form-control" value="<?= htmlspecialchars($_POST['passeport']??'') ?>" placeholder="Ex: A1234567">
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">📞 Contact</div>
      <div class="form-section-body">
        <div class="form-grid form-grid-2">
          <div class="form-group">
            <label class="form-label">Téléphone</label>
            <input type="tel" name="telephone" class="form-control" value="<?= htmlspecialchars($_POST['telephone']??'') ?>" placeholder="+221 77 000 00 00">
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email']??'') ?>" placeholder="prenom.nom@exemple.com">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Adresse</label>
          <textarea name="adresse" class="form-control" placeholder="Adresse complète"><?= htmlspecialchars($_POST['adresse']??'') ?></textarea>
        </div>
      </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end">
      <a href="clients.php" class="btn btn-ghost">Annuler</a>
      <button type="submit" class="btn btn-gold">+ Enregistrer le Client</button>
    </div>
  </form>
</div>
<?php require 'includes/footer.php'; ?>
EOF
echo -e "${G}  ✓ ajouter_client.php${R}"


# ══════════════════════════════════════════════════════════════
# 12. reservations.php
# ══════════════════════════════════════════════════════════════
cat > $APP/reservations.php << 'EOF'
<?php
require 'config/db.php';
$page_title = 'Réservations';
require 'includes/header.php';

$filtre = $_GET['statut'] ?? '';
$sql = "SELECT r.*, CONCAT(cl.prenom,' ',cl.nom) as client_nom, cl.telephone as client_tel,
               v.numero_vol, c.nom as compagnie, c.code_iata as comp_iata,
               o.code_iata as orig_code, d.code_iata as dest_code,
               o.ville as orig_v, d.ville as dest_v
        FROM reservations r
        LEFT JOIN clients cl ON r.client_id=cl.id
        LEFT JOIN vols v ON r.vol_id=v.id
        LEFT JOIN compagnies_aeriennes c ON v.compagnie_id=c.id
        LEFT JOIN destinations o ON v.origine_id=o.id
        LEFT JOIN destinations d ON v.destination_id=d.id";
$params = [];
if ($filtre) { $sql .= " WHERE r.statut=:s"; $params[':s']=$filtre; }
$sql .= " ORDER BY r.date_reservation DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $reservations = $stmt->fetchAll();

function badge(string $s): string {
    $map=['PAYEE'=>'payee','CONFIRMEE'=>'confirmee','EN_ATTENTE'=>'en_attente','ANNULEE'=>'annule'];
    $labels=['PAYEE'=>'✓ Payée','CONFIRMEE'=>'◈ Confirmée','EN_ATTENTE'=>'○ En attente','ANNULEE'=>'✕ Annulée'];
    return "<span class='badge badge-".($map[$s]??'en_attente')." badge-dot'>".($labels[$s]??$s)."</span>";
}
function classe_badge(string $c): string {
    $m=['ECONOMIQUE'=>'économique','BUSINESS'=>'business','PREMIERE'=>'premiere'];
    return "<span class='badge badge-".($m[$c]??'economique')."'>$c</span>";
}
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Gestion</div>
      <h1>Réservations</h1>
      <p><?= count($reservations) ?> réservation(s)</p>
    </div>
    <a href="ajouter_reservation.php" class="btn btn-gold">+ Nouvelle Réservation</a>
  </div>

  <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px">
    <?php foreach(['' => 'Toutes', 'EN_ATTENTE' => 'En attente', 'CONFIRMEE' => 'Confirmées', 'PAYEE' => 'Payées', 'ANNULEE' => 'Annulées'] as $v => $l): ?>
    <a href="?statut=<?= $v ?>"
       style="padding:6px 14px;border-radius:8px;font-size:0.76rem;font-weight:600;text-decoration:none;transition:all 0.2s;
              <?= $filtre===$v ? 'background:rgba(212,168,72,0.15);color:var(--gold);border:1px solid rgba(212,168,72,0.3)' : 'background:var(--card);color:var(--muted);border:1px solid var(--border)' ?>">
      <?= $l ?>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr>
          <th>Référence</th><th>Client</th><th>Vol / Trajet</th>
          <th>Classe</th><th>Pax</th><th>Total</th><th>Statut</th><th>Date</th>
        </tr></thead>
        <tbody>
        <?php if(empty($reservations)): ?>
        <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📋</div>Aucune réservation</div></td></tr>
        <?php else: foreach($reservations as $r): ?>
        <tr>
          <td style="font-family:'Bebas Neue',sans-serif;font-size:0.85rem;letter-spacing:0.04em;color:var(--gold)"><?= $r['reference'] ?></td>
          <td>
            <div style="font-weight:600;font-size:0.85rem"><?= htmlspecialchars($r['client_nom']??'—') ?></div>
            <div style="font-size:0.7rem;color:var(--muted)"><?= htmlspecialchars($r['client_tel']??'') ?></div>
          </td>
          <td>
            <div style="font-weight:600;font-size:0.82rem"><?= htmlspecialchars($r['numero_vol']??'—') ?></div>
            <?php if($r['orig_code'] && $r['dest_code']): ?>
            <div class="route-display" style="margin-top:3px">
              <span class="route-code" style="font-size:0.75rem"><?= $r['orig_code'] ?></span>
              <span class="route-arrow" style="font-size:0.8rem">→</span>
              <span class="route-code" style="font-size:0.75rem"><?= $r['dest_code'] ?></span>
            </div>
            <?php endif; ?>
          </td>
          <td><?= classe_badge($r['classe']) ?></td>
          <td style="text-align:center;font-weight:700;color:var(--text)"><?= $r['nb_passagers'] ?></td>
          <td style="font-weight:700;color:var(--gold);font-size:0.85rem"><?= money($r['prix_total']) ?></td>
          <td><?= badge($r['statut']) ?></td>
          <td style="font-size:0.75rem;color:var(--muted)"><?= date('d/m/Y', strtotime($r['date_reservation'])) ?></td>
        </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
EOF
echo -e "${G}  ✓ reservations.php${R}"


# ══════════════════════════════════════════════════════════════
# 13. ajouter_reservation.php
# ══════════════════════════════════════════════════════════════
cat > $APP/ajouter_reservation.php << 'EOF'
<?php
require 'config/db.php';
$page_title = 'Nouvelle Réservation';
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = $_POST;
    if (empty($f['vol_id']) || empty($f['client_id'])) {
        $error = 'Vol et client obligatoires.';
    } else {
        $ref = 'OMG-' . date('Y') . '-' . str_pad($pdo->query("SELECT COUNT(*)+1 FROM reservations")->fetchColumn(), 4, '0', STR_PAD_LEFT);
        $prixMap = ['ECONOMIQUE' => 'prix_eco', 'BUSINESS' => 'prix_business', 'PREMIERE' => 'prix_first'];
        $col = $prixMap[$f['classe'] ?? 'ECONOMIQUE'];
        $prix_unit = (float)$pdo->query("SELECT $col FROM vols WHERE id=" . (int)$f['vol_id'])->fetchColumn();
        $total = $prix_unit * (int)($f['nb_passagers'] ?? 1);
        try {
            $pdo->prepare("INSERT INTO reservations (reference,vol_id,client_id,classe,nb_passagers,prix_total,statut,notes)
            VALUES(:ref,:v,:c,:cl,:nb,:tot,:s,:n)")->execute([
                ':ref'=>$ref,':v'=>$f['vol_id'],':c'=>$f['client_id'],
                ':cl'=>$f['classe']??'ECONOMIQUE',':nb'=>(int)($f['nb_passagers']??1),
                ':tot'=>$total,':s'=>$f['statut']??'EN_ATTENTE',':n'=>$f['notes']??null,
            ]);
            $success = "Réservation <strong>$ref</strong> créée — Total : ".money($total);
        } catch(PDOException $e){ $error='Erreur: '.$e->getMessage(); }
    }
}

$vols = $pdo->query("SELECT v.*, o.code_iata as oc, d.code_iata as dc, o.ville as ov, d.ville as dv
    FROM vols v LEFT JOIN destinations o ON v.origine_id=o.id LEFT JOIN destinations d ON v.destination_id=d.id
    WHERE v.statut IN('PROGRAMME','EN_COURS') ORDER BY v.date_depart ASC")->fetchAll();
$clients = $pdo->query("SELECT * FROM clients ORDER BY nom,prenom")->fetchAll();

require 'includes/header.php';
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Réservations</div>
      <h1>Nouvelle Réservation</h1>
    </div>
    <a href="reservations.php" class="btn btn-ghost">← Retour</a>
  </div>
  <?php if($success) echo "<div class='alert alert-success'>✓ $success</div>"; ?>
  <?php if($error) echo "<div class='alert alert-error'>⚠ ".htmlspecialchars($error)."</div>"; ?>

  <form method="POST" style="max-width:720px">
    <div class="form-section">
      <div class="form-section-title">✈ Vol & Client</div>
      <div class="form-section-body">
        <div class="form-group">
          <label class="form-label">Vol <span class="req">*</span></label>
          <select name="vol_id" class="form-control" required>
            <option value="">— Sélectionner un vol —</option>
            <?php foreach($vols as $v):
              $presel = (($_POST['vol_id']??$_GET['vol_id']??'')==$v['id'])?'selected':''; ?>
            <option value="<?= $v['id'] ?>" <?= $presel ?>>
              <?= htmlspecialchars($v['numero_vol']) ?> — <?= $v['oc'] ?> → <?= $v['dc'] ?>
              (<?= htmlspecialchars($v['ov']) ?> → <?= htmlspecialchars($v['dv']) ?>)
              · <?= date('d/m/Y H:i', strtotime($v['date_depart'])) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Client <span class="req">*</span></label>
          <select name="client_id" class="form-control" required>
            <option value="">— Sélectionner un client —</option>
            <?php foreach($clients as $c):
              $presel = (($_POST['client_id']??$_GET['client_id']??'')==$c['id'])?'selected':''; ?>
            <option value="<?= $c['id'] ?>" <?= $presel ?>>
              <?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?> · <?= htmlspecialchars($c['telephone']??'') ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <div class="form-section">
      <div class="form-section-title">💺 Détails du Voyage</div>
      <div class="form-section-body">
        <div class="form-grid form-grid-2">
          <div class="form-group">
            <label class="form-label">Classe</label>
            <select name="classe" class="form-control">
              <?php foreach(['ECONOMIQUE'=>'Économique','BUSINESS'=>'Business','PREMIERE'=>'Première'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= (($_POST['classe']??'ECONOMIQUE')===$v)?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Nombre de passagers</label>
            <input type="number" name="nb_passagers" class="form-control" value="<?= htmlspecialchars($_POST['nb_passagers']??1) ?>" min="1" max="9">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Statut</label>
          <select name="statut" class="form-control">
            <?php foreach(['EN_ATTENTE'=>'En attente','CONFIRMEE'=>'Confirmée','PAYEE'=>'Payée'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= (($_POST['statut']??'EN_ATTENTE')===$v)?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Notes internes</label>
          <textarea name="notes" class="form-control" placeholder="Instructions spéciales, demandes particulières…"><?= htmlspecialchars($_POST['notes']??'') ?></textarea>
        </div>
      </div>
    </div>

    <div style="display:flex;gap:12px;justify-content:flex-end">
      <a href="reservations.php" class="btn btn-ghost">Annuler</a>
      <button type="submit" class="btn btn-gold">📋 Créer la Réservation</button>
    </div>
  </form>
</div>
<?php require 'includes/footer.php'; ?>
EOF
echo -e "${G}  ✓ ajouter_reservation.php${R}"


# ══════════════════════════════════════════════════════════════
# 14. destinations.php — CARTE LEAFLET INTERACTIVE
# ══════════════════════════════════════════════════════════════
cat > $APP/destinations.php << 'EOF'
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
EOF
echo -e "${G}  ✓ destinations.php${R}"


# ══════════════════════════════════════════════════════════════
# 15. offres.php — OFFRES PROMOTIONNELLES
# ══════════════════════════════════════════════════════════════
cat > $APP/offres.php << 'EOF'
<?php
require 'config/db.php';
$page_title = 'Offres Promotionnelles';
require 'includes/header.php';

// Ajouter offre
$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = $_POST;
    if (empty($f['titre'])) { $error = 'Titre obligatoire.'; }
    else {
        try {
            $pdo->prepare("INSERT INTO offres_promotionnelles (titre,description,destination_id,prix_promo,prix_original,date_debut,date_fin,actif)
            VALUES(:t,:d,:dest,:pp,:po,:db,:df,1)")->execute([
                ':t'=>$f['titre'],':d'=>$f['description']??null,
                ':dest'=>$f['destination_id']??null,
                ':pp'=>(float)($f['prix_promo']??0),':po'=>(float)($f['prix_original']??0),
                ':db'=>$f['date_debut']??null,':df'=>$f['date_fin']??null,
            ]);
            $success = 'Offre créée avec succès.';
        } catch(PDOException $e){ $error='Erreur: '.$e->getMessage(); }
    }
}

$offres = $pdo->query("SELECT o.*, d.ville, d.code_iata, d.continent
    FROM offres_promotionnelles o LEFT JOIN destinations d ON o.destination_id=d.id
    WHERE o.actif=1 ORDER BY o.id DESC")->fetchAll();
$destinations = $pdo->query("SELECT * FROM destinations ORDER BY ville")->fetchAll();

$emojis_continent = ['Afrique'=>'🌍','Europe'=>'🏰','Asie'=>'🌏','Amérique'=>'🗽'];
?>
<?php require 'includes/navbar.php'; ?>
<div class="page">
  <div class="page-header">
    <div>
      <div class="eyebrow">Marketing</div>
      <h1>Offres Promotionnelles</h1>
      <p><?= count($offres) ?> offre(s) active(s)</p>
    </div>
  </div>

  <?php if($success) echo "<div class='alert alert-success'>✓ $success</div>"; ?>
  <?php if($error) echo "<div class='alert alert-error'>⚠ ".htmlspecialchars($error)."</div>"; ?>

  <div class="grid-2" style="gap:24px;align-items:start">

    <!-- Grille des offres -->
    <div>
      <div class="grid-2" style="gap:14px">
        <?php if(empty($offres)): ?>
        <div class="card" style="grid-column:span 2"><div class="empty-state"><div class="empty-icon">🏷</div>Aucune offre active</div></div>
        <?php else: foreach($offres as $o):
          $emoji = $emojis_continent[$o['continent']??''] ?? '✈';
          $pct = $o['prix_original'] > 0 ? round((1 - $o['prix_promo']/$o['prix_original'])*100) : 0;
        ?>
        <div class="offer-card">
          <div class="offer-card-img"><?= $emoji ?></div>
          <div class="offer-card-body">
            <div style="font-weight:700;font-size:0.88rem;color:var(--text)"><?= htmlspecialchars($o['titre']) ?></div>
            <div style="font-size:0.75rem;color:var(--muted);margin-top:3px">
              📍 <?= htmlspecialchars($o['ville']??'Toutes destinations') ?>
            </div>
            <?php if($o['description']): ?>
            <div style="font-size:0.72rem;color:var(--muted);margin-top:6px;line-height:1.5"><?= htmlspecialchars(mb_substr($o['description'],0,70)).'…' ?></div>
            <?php endif; ?>
            <div class="offer-price-row">
              <span class="offer-price"><?= money($o['prix_promo']) ?></span>
              <?php if($o['prix_original']): ?>
              <span class="offer-price-old"><?= money($o['prix_original']) ?></span>
              <?php if($pct > 0): ?><span class="offer-discount">-<?= $pct ?>%</span><?php endif; ?>
              <?php endif; ?>
            </div>
            <div style="font-size:0.7rem;color:var(--muted);margin-top:6px">
              Du <?= $o['date_debut'] ? date('d/m/Y',strtotime($o['date_debut'])) : '?' ?>
              au <?= $o['date_fin'] ? date('d/m/Y',strtotime($o['date_fin'])) : '?' ?>
            </div>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- Formulaire ajout -->
    <div>
      <div class="card">
        <div class="card-header"><span class="card-title">+ Nouvelle Offre</span></div>
        <div class="card-body">
          <form method="POST">
            <div class="form-grid" style="gap:14px">
              <div class="form-group">
                <label class="form-label">Titre <span class="req">*</span></label>
                <input type="text" name="titre" class="form-control" placeholder="Ex: Paris Printemps 2026" value="<?= htmlspecialchars($_POST['titre']??'') ?>">
              </div>
              <div class="form-group">
                <label class="form-label">Destination</label>
                <select name="destination_id" class="form-control">
                  <option value="">— Toutes destinations —</option>
                  <?php foreach($destinations as $d): ?>
                  <option value="<?= $d['id'] ?>" <?= (($_POST['destination_id']??'')==$d['id'])?'selected':'' ?>>
                    [<?= $d['code_iata'] ?>] <?= htmlspecialchars($d['ville']) ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-grid form-grid-2">
                <div class="form-group">
                  <label class="form-label">Prix promo (FCFA)</label>
                  <input type="number" name="prix_promo" class="form-control" value="<?= htmlspecialchars($_POST['prix_promo']??'') ?>" step="1000" min="0">
                </div>
                <div class="form-group">
                  <label class="form-label">Prix original (FCFA)</label>
                  <input type="number" name="prix_original" class="form-control" value="<?= htmlspecialchars($_POST['prix_original']??'') ?>" step="1000" min="0">
                </div>
              </div>
              <div class="form-grid form-grid-2">
                <div class="form-group">
                  <label class="form-label">Date début</label>
                  <input type="date" name="date_debut" class="form-control" value="<?= htmlspecialchars($_POST['date_debut']??'') ?>">
                </div>
                <div class="form-group">
                  <label class="form-label">Date fin</label>
                  <input type="date" name="date_fin" class="form-control" value="<?= htmlspecialchars($_POST['date_fin']??'') ?>">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Description de l'offre…"><?= htmlspecialchars($_POST['description']??'') ?></textarea>
              </div>
              <button type="submit" class="btn btn-gold" style="width:100%">🏷 Créer l'offre</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require 'includes/footer.php'; ?>
EOF
echo -e "${G}  ✓ offres.php${R}"


# ══════════════════════════════════════════════════════════════
# 16. compagnies.php
# ══════════════════════════════════════════════════════════════
cat > $APP/compagnies.php << 'EOF'
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
EOF
echo -e "${G}  ✓ compagnies.php${R}"


# ══════════════════════════════════════════════════════════════
# FIN — Résumé
# ══════════════════════════════════════════════════════════════
echo ""
echo -e "${C}  ══════════════════════════════════════════════"
echo -e "  ✈  Installation terminée avec succès !"
echo -e "  ══════════════════════════════════════════════${R}"
echo ""
echo -e "  ${Y}Application :${R} http://localhost/agence_voyage/"
echo -e "  ${Y}Base de données :${R} agence_voyage (MariaDB)"
echo ""
echo -e "  ${G}Modules installés :${R}"
echo "    ◈ index.php           → Tableau de bord"
echo "    ✈ vols.php            → Gestion des vols"
echo "    ✈ ajouter_vol.php     → Nouveau vol"
echo "    👤 clients.php        → Base clients"
echo "    👤 ajouter_client.php → Nouveau client"
echo "    📋 reservations.php   → Réservations"
echo "    📋 ajouter_reservation.php → Nouvelle résa"
echo "    🗺 destinations.php   → Carte Leaflet interactive"
echo "    🏷 offres.php         → Offres promotionnelles"
echo "    🏢 compagnies.php     → Compagnies aériennes"
echo ""
echo -e "  ${G}Données de démo insérées :${R}"
echo "    • 9 compagnies (Air France, Emirates, Turkish…)"
echo "    • 13 destinations mondiales avec coordonnées GPS"
echo "    • 8 vols programmés depuis Dakar"
echo "    • 7 clients sénégalais"
echo "    • 7 réservations variées"
echo "    • 4 offres promotionnelles actives"
echo ""
echo -e "  ${Y}Usage Termux :${R} bash install.sh"
echo ""
