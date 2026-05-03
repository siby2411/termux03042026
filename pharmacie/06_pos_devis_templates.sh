#!/bin/bash
# ============================================================
# SCRIPT 06 — FACTURATION POS + DEVIS + TEMPLATES UI
# Pharmacie & Revendeur Médical — Interface Avancée
# ============================================================

BASE="$HOME/shared/htdocs/apachewsl2026"

# ─────────────────────────────────────────────────────────────
# 6.1 PHARMACIE — modules/caisse/pos.php (Interface POS)
# ─────────────────────────────────────────────────────────────
cat << 'EOF' > "$BASE/pharmacie/modules/caisse/pos.php"
<?php
/**
 * Interface Point de Vente (POS) — PharmaSen
 * Caisse tactile | Mobile Money | Mutuelle
 */

require_once dirname(__DIR__, 2) . '/core/Auth.php';
require_once dirname(__DIR__, 2) . '/core/Helper.php';
require_once dirname(__DIR__, 2) . '/config/config.php';
Auth::requireRole('admin', 'pharmacien', 'caissier');
$user = Auth::getUser();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>PharmaSen — Caisse POS</title>
<style>
  :root {
    --primary: #1a7f5a; --primary-dark: #14614a;
    --accent:  #f0a500; --danger: #dc3545;
    --bg: #f4f7f6;      --card: #ffffff;
    --text: #2d3436;    --border: #e0e0e0;
    --success: #28a745; --info: #17a2b8;
  }
  * { box-sizing: border-box; margin:0; padding:0; font-family: 'Segoe UI', sans-serif; }
  body { background: var(--bg); color: var(--text); height: 100vh; display: flex; flex-direction: column; }
  header {
    background: var(--primary); color: white; padding: 10px 20px;
    display: flex; align-items: center; justify-content: space-between;
    box-shadow: 0 2px 8px rgba(0,0,0,.2);
  }
  header h1 { font-size: 1.2rem; }
  .pos-grid { display: grid; grid-template-columns: 1fr 420px; flex: 1; overflow: hidden; }

  /* Panneau gauche : catalogue */
  .catalogue { padding: 12px; overflow-y: auto; background: var(--bg); }
  .search-bar { display: flex; gap: 8px; margin-bottom: 12px; }
  .search-bar input {
    flex: 1; padding: 10px 14px; border: 2px solid var(--primary);
    border-radius: 8px; font-size: 1rem; outline: none;
  }
  .prod-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(160px,1fr)); gap: 10px; }
  .prod-card {
    background: var(--card); border-radius: 10px; padding: 12px;
    border: 1px solid var(--border); cursor: pointer; transition: .2s;
    text-align: center;
  }
  .prod-card:hover { border-color: var(--primary); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
  .prod-card .prix { color: var(--primary); font-weight: 700; font-size: 1rem; }
  .prod-card .stock { font-size: .75rem; color: #888; margin-top: 4px; }
  .stock-warn { color: var(--danger); }

  /* Panneau droit : panier */
  .panier { background: var(--card); display: flex; flex-direction: column; border-left: 1px solid var(--border); }
  .panier-header { padding: 14px; background: var(--primary); color: white; font-size: 1rem; font-weight: 600; }
  .panier-lignes { flex: 1; overflow-y: auto; padding: 8px; }
  .panier-ligne {
    display: flex; align-items: center; gap: 8px;
    padding: 8px; border-bottom: 1px solid var(--border);
  }
  .panier-ligne .nom { flex: 1; font-size: .85rem; }
  .panier-ligne .qty-ctrl { display: flex; align-items: center; gap: 4px; }
  .qty-btn { width: 26px; height: 26px; border: 1px solid var(--border); border-radius: 4px;
             background: var(--bg); cursor: pointer; font-size: .9rem; }
  .panier-ligne .montant { font-weight: 700; font-size: .9rem; color: var(--primary); min-width: 80px; text-align: right; }
  .btn-del { background: none; border: none; color: var(--danger); cursor: pointer; font-size: 1.1rem; }

  /* Footer panier */
  .panier-footer { padding: 14px; border-top: 2px solid var(--border); }
  .totaux { margin-bottom: 10px; }
  .totaux-row { display: flex; justify-content: space-between; padding: 3px 0; font-size: .9rem; }
  .totaux-row.net { font-size: 1.2rem; font-weight: 700; color: var(--primary); border-top: 1px solid var(--border); padding-top: 6px; margin-top: 4px; }

  .paiement-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; margin-bottom: 10px; }
  .btn-pmt {
    padding: 10px; border: 2px solid var(--border); border-radius: 8px;
    background: var(--bg); cursor: pointer; font-size: .8rem; font-weight: 600;
    transition: .2s;
  }
  .btn-pmt.active { border-color: var(--primary); background: rgba(26,127,90,.1); color: var(--primary); }
  .btn-pmt:hover { border-color: var(--primary); }

  .montant-input { width: 100%; padding: 10px; border: 2px solid var(--accent);
                   border-radius: 8px; font-size: 1.1rem; text-align: right; margin-bottom: 8px; }
  .monnaie-info { text-align: right; font-size: .9rem; color: var(--success); margin-bottom: 8px; }

  .btn-valider {
    width: 100%; padding: 14px; background: var(--success);
    color: white; border: none; border-radius: 10px;
    font-size: 1.1rem; font-weight: 700; cursor: pointer; transition: .2s;
  }
  .btn-valider:hover { background: #218838; }
  .btn-annuler { width: 100%; padding: 8px; background: none; border: 1px solid var(--danger);
                 color: var(--danger); border-radius: 8px; cursor: pointer; margin-top: 6px; }

  /* Client selector */
  .client-bar { padding: 8px 14px; border-bottom: 1px solid var(--border); }
  .client-bar select { width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 6px; }

  /* Notifications */
  .toast { position: fixed; bottom: 20px; right: 20px; background: var(--success);
           color: white; padding: 12px 20px; border-radius: 8px; display: none; z-index: 999; }
</style>
</head>
<body>

<header>
  <h1>🏥 PharmaSen — Point de Vente</h1>
  <div style="font-size:.85rem;">
    <span>👤 <?= htmlspecialchars($user['nom']) ?></span> |
    <span id="clock"></span> |
    <a href="/pharmacie/logout.php" style="color:white;">Déconnexion</a>
  </div>
</header>

<div class="pos-grid">
  <!-- CATALOGUE -->
  <div class="catalogue">
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="🔍 Rechercher médicament, code-barre…" autofocus>
    </div>
    <div class="prod-grid" id="prodGrid">
      <div style="color:#888;text-align:center;padding:30px;grid-column:1/-1;">
        Tapez pour rechercher un médicament…
      </div>
    </div>
  </div>

  <!-- PANIER -->
  <div class="panier">
    <div class="panier-header">🛒 Panier de vente</div>

    <div class="client-bar">
      <select id="clientSelect">
        <option value="">— Client anonyme —</option>
      </select>
    </div>

    <div class="panier-lignes" id="panierLignes">
      <div style="color:#aaa;text-align:center;padding:30px;">Panier vide</div>
    </div>

    <div class="panier-footer">
      <div class="totaux">
        <div class="totaux-row"><span>Sous-total :</span><span id="sousTotal">0 FCFA</span></div>
        <div class="totaux-row"><span>Remise (<input type="number" id="remisePct" value="0" min="0" max="100" style="width:40px;"> %) :</span><span id="montantRemise">0 FCFA</span></div>
        <div class="totaux-row net"><span>NET À PAYER :</span><span id="netAPayer">0 FCFA</span></div>
      </div>

      <div class="paiement-grid">
        <button class="btn-pmt active" data-mode="especes">💵 Espèces</button>
        <button class="btn-pmt" data-mode="wave">🌊 Wave</button>
        <button class="btn-pmt" data-mode="orange_money">🟠 Orange Money</button>
        <button class="btn-pmt" data-mode="mutuelle">🏥 Mutuelle</button>
      </div>

      <input type="number" class="montant-input" id="montantRecu" placeholder="Montant reçu (FCFA)">
      <div class="monnaie-info" id="monnaieInfo"></div>

      <button class="btn-valider" onclick="validerVente()">✅ VALIDER LA VENTE</button>
      <button class="btn-annuler" onclick="viderPanier()">🗑 Vider le panier</button>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
const FCFA = n => new Intl.NumberFormat('fr-SN').format(Math.round(n)) + ' FCFA';
let panier = [];
let modePaiement = 'especes';

// Horloge
setInterval(() => {
  const n = new Date();
  document.getElementById('clock').textContent =
    n.toLocaleDateString('fr-SN') + ' ' + n.toLocaleTimeString('fr-SN');
}, 1000);

// Recherche
let searchTimer;
document.getElementById('searchInput').addEventListener('input', function() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => rechercherMedicament(this.value), 300);
});

async function rechercherMedicament(q) {
  if (q.length < 2) return;
  const res = await fetch(`../medicaments/medicaments_api.php?action=pos_search&q=${encodeURIComponent(q)}`);
  const json = await res.json();
  const grid = document.getElementById('prodGrid');
  if (!json.data?.length) {
    grid.innerHTML = '<div style="color:#888;grid-column:1/-1;text-align:center;padding:20px;">Aucun résultat</div>';
    return;
  }
  grid.innerHTML = json.data.map(m => `
    <div class="prod-card" onclick="ajouterAuPanier(${m.id},'${m.denomination.replace(/'/g,"\\'")}',${m.prix_vente_ttc})">
      <div style="font-weight:600;font-size:.85rem;">${m.denomination}</div>
      <div style="font-size:.75rem;color:#888;">${m.nom_commercial||''}</div>
      <div class="prix">${FCFA(m.prix_vente_ttc)}</div>
      <div class="stock ${m.stock_actuel<=5?'stock-warn':''}">Stock: ${m.stock_actuel}</div>
      ${m.ordonnance_obligatoire?'<div style="color:red;font-size:.7rem;">⚕ Ordonnance</div>':''}
    </div>`).join('');
}

function ajouterAuPanier(id, nom, prix) {
  const idx = panier.findIndex(p => p.id === id);
  if (idx >= 0) {
    panier[idx].qty++;
  } else {
    panier.push({ id, nom, prix, qty: 1, remise: 0 });
  }
  renderPanier();
}

function renderPanier() {
  const container = document.getElementById('panierLignes');
  if (!panier.length) {
    container.innerHTML = '<div style="color:#aaa;text-align:center;padding:30px;">Panier vide</div>';
    return;
  }
  container.innerHTML = panier.map((p, i) => `
    <div class="panier-ligne">
      <div class="nom">${p.nom}</div>
      <div class="qty-ctrl">
        <button class="qty-btn" onclick="changeQty(${i},-1)">−</button>
        <span style="min-width:24px;text-align:center;">${p.qty}</span>
        <button class="qty-btn" onclick="changeQty(${i},+1)">+</button>
      </div>
      <div class="montant">${FCFA(p.prix * p.qty)}</div>
      <button class="btn-del" onclick="supprimerLigne(${i})">✕</button>
    </div>`).join('');
  calcTotaux();
}

function changeQty(i, delta) {
  panier[i].qty += delta;
  if (panier[i].qty <= 0) panier.splice(i, 1);
  renderPanier();
}

function supprimerLigne(i) { panier.splice(i, 1); renderPanier(); }

function calcTotaux() {
  const sousTotal = panier.reduce((s, p) => s + p.prix * p.qty, 0);
  const remisePct = parseFloat(document.getElementById('remisePct').value) || 0;
  const remise    = sousTotal * remisePct / 100;
  const net       = sousTotal - remise;
  document.getElementById('sousTotal').textContent    = FCFA(sousTotal);
  document.getElementById('montantRemise').textContent= FCFA(remise);
  document.getElementById('netAPayer').textContent    = FCFA(net);
  const recu = parseFloat(document.getElementById('montantRecu').value) || 0;
  const monnaie = recu - net;
  document.getElementById('monnaieInfo').textContent =
    recu > 0 ? `Monnaie à rendre : ${FCFA(Math.max(0, monnaie))}` : '';
}

document.getElementById('remisePct').addEventListener('input', calcTotaux);
document.getElementById('montantRecu').addEventListener('input', calcTotaux);

// Sélection mode paiement
document.querySelectorAll('.btn-pmt').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.btn-pmt').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
    modePaiement = this.dataset.mode;
  });
});

async function validerVente() {
  if (!panier.length) { showToast('Panier vide !', 'error'); return; }
  const remisePct  = parseFloat(document.getElementById('remisePct').value) || 0;
  const montantRecu = parseFloat(document.getElementById('montantRecu').value) || 0;
  const clientId   = document.getElementById('clientSelect').value || null;

  const body = {
    client_id: clientId ? parseInt(clientId) : null,
    mode_paiement: modePaiement,
    remise_pct: remisePct,
    montant_recu: montantRecu,
    lignes: panier.map(p => ({
      medicament_id: p.id,
      quantite: p.qty,
      prix_unitaire: p.prix,
      tva_taux: 0,
      remise_pct: 0
    }))
  };

  const res  = await fetch('../ventes/ventes_api.php?action=create', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(body)
  });
  const json = await res.json();
  if (json.success) {
    showToast('✅ Vente validée ! Ref: ' + (json.data?.reference || ''));
    viderPanier();
    // Ouvrir ticket dans un nouvel onglet
    if (json.data?.id) window.open(`../ventes/ticket.php?id=${json.data.id}`, '_blank');
  } else {
    showToast('❌ ' + json.message, 'error');
  }
}

function viderPanier() {
  panier = [];
  document.getElementById('remisePct').value = 0;
  document.getElementById('montantRecu').value = '';
  renderPanier();
}

function showToast(msg, type = 'ok') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.background = type === 'error' ? '#dc3545' : '#28a745';
  t.style.display = 'block';
  setTimeout(() => { t.style.display = 'none'; }, 3500);
}
</script>
</body>
</html>
EOF

echo "✅ Interface POS pharmacie créée"

# ─────────────────────────────────────────────────────────────
# 6.2 REVENDEUR — modules/devis/DevisModel.php
# ─────────────────────────────────────────────────────────────
cat << 'EOF' > "$BASE/revendeur_medical/modules/devis/DevisModel.php"
<?php
/**
 * Modèle Devis — MedEquip Pro
 */

require_once dirname(__DIR__, 2) . '/core/Database.php';

class DevisModel
{
    public static function creer(array $devis, array $lignes): int
    {
        Database::beginTransaction();
        try {
            Database::execute(
                "INSERT INTO devis (client_id, commercial_id, date_devis, date_validite,
                  remise_pct, notes)
                 VALUES (?,?,?,?,?,?)",
                [
                    $devis['client_id'],
                    $devis['commercial_id'],
                    $devis['date_devis'],
                    $devis['date_validite'] ?? date('Y-m-d', strtotime('+30 days')),
                    $devis['remise_pct']    ?? 0,
                    $devis['notes']         ?? null,
                ]
            );
            $devisId = (int)Database::lastId();

            $montantHT = 0;
            foreach ($lignes as $l) {
                $ht     = $l['quantite'] * $l['prix_unitaire'] * (1 - ($l['remise_pct'] ?? 0) / 100);
                $tva    = $ht * ($l['tva_taux'] ?? 18) / 100;
                $total  = $ht + $tva;
                $montantHT += $ht;

                Database::execute(
                    "INSERT INTO devis_lignes
                     (devis_id, produit_id, designation, quantite, prix_unitaire,
                      tva_taux, remise_pct, montant_ligne)
                     VALUES (?,?,?,?,?,?,?,?)",
                    [
                        $devisId,
                        $l['produit_id'],
                        $l['designation'] ?? '',
                        $l['quantite'],
                        $l['prix_unitaire'],
                        $l['tva_taux']   ?? 18.00,
                        $l['remise_pct'] ?? 0,
                        round($total, 2),
                    ]
                );
            }

            $tvaTotal = $montantHT * 0.18;
            $ttc      = $montantHT + $tvaTotal;
            $remise   = $ttc * ($devis['remise_pct'] ?? 0) / 100;
            $net      = $ttc - $remise;

            Database::execute(
                "UPDATE devis SET montant_ht=?, tva_montant=?, montant_ttc=?, net_a_payer=? WHERE id=?",
                [round($montantHT,2), round($tvaTotal,2), round($ttc,2), round($net,2), $devisId]
            );

            Database::commit();
            return $devisId;
        } catch (Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    public static function getById(int $id): ?array
    {
        $devis = Database::queryOne(
            "SELECT d.*, cl.raison_sociale AS client, cl.adresse AS client_adresse,
                    cl.telephone AS client_tel, cl.ninea AS client_ninea,
                    CONCAT(u.prenom,' ',u.nom) AS commercial
             FROM devis d
             JOIN clients cl     ON cl.id = d.client_id
             JOIN utilisateurs u ON u.id  = d.commercial_id
             WHERE d.id = ?", [$id]
        );
        if (!$devis) return null;
        $devis['lignes'] = Database::query(
            "SELECT dl.*, p.reference AS ref_produit, p.marque
             FROM devis_lignes dl
             LEFT JOIN produits p ON p.id = dl.produit_id
             WHERE dl.devis_id = ?", [$id]
        );
        return $devis;
    }

    public static function convertirEnCommande(int $devisId, int $commercialId): int
    {
        $devis = self::getById($devisId);
        if (!$devis || $devis['statut'] !== 'accepte')
            throw new RuntimeException('Devis non accepté ou introuvable');

        Database::beginTransaction();
        try {
            Database::execute(
                "INSERT INTO commandes
                 (devis_id, client_id, commercial_id, date_commande, net_a_payer, statut)
                 VALUES (?,?,?,CURDATE(),?,'confirmee')",
                [$devisId, $devis['client_id'], $commercialId, $devis['net_a_payer']]
            );
            $cmdId = (int)Database::lastId();

            foreach ($devis['lignes'] as $l) {
                Database::execute(
                    "INSERT INTO commande_lignes (commande_id, produit_id, quantite, prix_unitaire, montant_ligne)
                     VALUES (?,?,?,?,?)",
                    [$cmdId, $l['produit_id'], $l['quantite'], $l['prix_unitaire'], $l['montant_ligne']]
                );
            }

            Database::execute("UPDATE devis SET statut='converti' WHERE id=?", [$devisId]);
            Database::commit();
            return $cmdId;
        } catch (Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    public static function getPipeline(): array
    {
        return Database::query("SELECT * FROM v_pipeline_devis");
    }
}
EOF

echo "✅ Module devis revendeur médical créé"

# ─────────────────────────────────────────────────────────────
# 6.3 TEMPLATE PARTAGÉ — templates/layouts/base.php (Pharmacie)
# ─────────────────────────────────────────────────────────────
cat << 'EOF' > "$BASE/pharmacie/templates/layouts/base.php"
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
EOF

echo "✅ Templates layouts créés"
