<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pageTitle = 'Caisse POS';
$pdo = getPDO();

// ── API JSON pour recherche produits ──
if (isset($_GET['api']) && $_GET['api'] === 'produits') {
    header('Content-Type: application/json');
    $q = trim($_GET['q'] ?? '');
    $cat = (int)($_GET['cat'] ?? 0);
    $sql = "SELECT p.id,p.nom,p.prix_vente,p.stock_actuel,p.unite,p.image,c.nom as cat_nom,c.couleur
            FROM produits p LEFT JOIN categories c ON p.categorie_id=c.id
            WHERE p.actif=1 AND p.stock_actuel>0";
    $params = [];
    if ($q) { $sql .= " AND p.nom LIKE ?"; $params[] = "%$q%"; }
    if ($cat) { $sql .= " AND p.categorie_id=?"; $params[] = $cat; }
    $sql .= " ORDER BY p.nom LIMIT 60";
    $st = $pdo->prepare($sql); $st->execute($params);
    echo json_encode($st->fetchAll()); exit;
}

// ── ENREGISTRER VENTE POS ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pos_submit'])) {
    $clientId = (int)($_POST['client_id'] ?: 0);
    $items    = json_decode($_POST['panier'] ?? '[]', true);
    $paiement = $_POST['mode_paiement'] ?? 'especes';
    $remise   = (float)($_POST['remise'] ?? 0);

    if (!empty($items)) {
        // Créer facture
        $total_ht = 0;
        foreach ($items as $item) $total_ht += $item['prix'] * $item['qte'];
        $total_ht -= $remise;
        $tva = 18;
        $total_ttc = $total_ht * (1 + $tva/100);
        $numero = 'POS-' . date('Ymd-His');

        $pdo->prepare("INSERT INTO factures (client_id,numero,date_facture,total_ht,tva,total_ttc,statut,notes)
            VALUES (?,?,CURDATE(),?,?,?,'payee',?)")
            ->execute([$clientId ?: null, $numero, $total_ht, $tva, $total_ttc,
                       'POS – Paiement: '.$paiement.($remise?" – Remise: ".number_format($remise,0,',',' ')." FCFA":'')]);
        $fid = $pdo->lastInsertId();

        $vs = $pdo->prepare("INSERT INTO ventes (facture_id,produit_id,client_id,quantite,prix_unitaire,total,date_vente)
            VALUES (?,?,?,?,?,?,NOW())");
        $fl = $pdo->prepare("INSERT INTO facture_lignes (facture_id,produit_id,designation,quantite,unite,prix_unitaire,total_ligne)
            VALUES (?,?,?,?,?,?,?)");
        foreach ($items as $item) {
            $pid = (int)$item['id']; $qte = (float)$item['qte']; $pu = (float)$item['prix'];
            $vs->execute([$fid, $pid, $clientId ?: null, $qte, $pu, $qte*$pu]);
            $fl->execute([$fid, $pid, $item['nom'], $qte, $item['unite'], $pu, $qte*$pu]);
            $pdo->prepare("UPDATE produits SET stock_actuel=GREATEST(0,stock_actuel-?) WHERE id=?")->execute([$qte,$pid]);
        }
        $ticketId = $fid;
        flash("✅ Vente enregistrée – $numero – Total TTC : ".number_format($total_ttc,0,',',' ')." FCFA", 'success');
    }
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();
$clients    = $pdo->query("SELECT id,CONCAT(COALESCE(prenom,''),' ',nom) as nom FROM clients ORDER BY nom")->fetchAll();

// Stats du jour
$today = date('Y-m-d');
$caJour   = $pdo->query("SELECT COALESCE(SUM(total),0) FROM ventes WHERE DATE(date_vente)='$today'")->fetchColumn();
$nbJour   = $pdo->query("SELECT COUNT(*) FROM ventes WHERE DATE(date_vente)='$today'")->fetchColumn();
$panierMoy= $nbJour>0 ? $caJour/$nbJour : 0;

require_once 'header.php';
?>
<style>
.pos-layout{display:grid;grid-template-columns:1fr 360px;gap:15px;height:calc(100vh - 120px)}
.pos-left{display:flex;flex-direction:column;gap:10px;overflow:hidden}
.pos-right{background:var(--card);border:1px solid var(--border);border-radius:14px;display:flex;flex-direction:column;overflow:hidden}
.pos-search{display:flex;gap:8px;padding:0}
.pos-search input{flex:1;background:rgba(255,255,255,.06);border:1px solid var(--border);border-radius:10px;color:var(--text);padding:10px 15px;font-family:'Raleway',sans-serif;font-size:.9rem;outline:none}
.pos-search input:focus{border-color:var(--rouge);box-shadow:0 0 0 3px rgba(192,57,43,.15)}
.cats-bar{display:flex;gap:6px;flex-wrap:wrap}
.cat-btn{padding:6px 14px;border-radius:20px;border:1px solid var(--border);background:rgba(255,255,255,.04);color:#aaa;font-size:.75rem;cursor:pointer;transition:.2s;font-family:'Raleway',sans-serif;font-weight:600}
.cat-btn:hover,.cat-btn.active{background:var(--rouge);color:#fff;border-color:var(--rouge)}
.prod-grid-pos{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;overflow-y:auto;flex:1;padding:2px}
.prod-pos-card{background:rgba(255,255,255,.04);border:1px solid var(--border);border-radius:12px;padding:12px;cursor:pointer;transition:.3s;text-align:center}
.prod-pos-card:hover{border-color:var(--or);transform:translateY(-2px);box-shadow:0 6px 20px rgba(212,172,13,.15)}
.prod-pos-card .pname{font-size:.78rem;font-weight:700;margin:8px 0 4px;line-height:1.3;color:var(--text)}
.prod-pos-card .pprice{color:var(--or);font-weight:900;font-size:.95rem}
.prod-pos-card .pstock{font-size:.65rem;color:var(--muted);margin-top:3px}
.panier-head{padding:15px 18px;border-bottom:1px solid var(--border);background:rgba(192,57,43,.1)}
.panier-head h4{color:var(--text);font-size:.95rem;font-weight:700;margin:0;display:flex;align-items:center;justify-content:space-between}
.panier-items{flex:1;overflow-y:auto;padding:10px}
.panier-item{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:10px;background:rgba(255,255,255,.03);border:1px solid var(--border);margin-bottom:6px}
.panier-item .pitem-name{flex:1;font-size:.78rem;font-weight:600;color:var(--text)}
.panier-item .pitem-ctrl{display:flex;align-items:center;gap:4px}
.qty-btn{width:24px;height:24px;border-radius:6px;border:none;cursor:pointer;font-weight:700;font-size:.85rem;line-height:1}
.qty-btn.minus{background:rgba(192,57,43,.2);color:var(--rouge)}
.qty-btn.plus{background:rgba(39,174,96,.2);color:#27ae60}
.qty-input{width:40px;text-align:center;background:rgba(255,255,255,.06);border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:.8rem;padding:2px 4px}
.pitem-total{color:var(--or);font-weight:700;font-size:.82rem;min-width:65px;text-align:right}
.del-btn{background:rgba(192,57,43,.15);border:none;color:var(--rouge);border-radius:6px;width:22px;height:22px;cursor:pointer;font-size:.8rem}
.panier-foot{padding:15px 18px;border-top:1px solid var(--border)}
.total-line-pos{display:flex;justify-content:space-between;font-size:.85rem;color:var(--muted);padding:3px 0}
.total-ttc-pos{display:flex;justify-content:space-between;font-size:1.2rem;color:var(--or);font-weight:900;padding:8px 0;border-top:2px solid var(--or);margin-top:8px}
.pay-btns{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:10px}
.pay-btn{padding:10px;border:1px solid var(--border);border-radius:8px;background:rgba(255,255,255,.04);color:var(--text);cursor:pointer;font-size:.78rem;font-weight:700;transition:.2s;font-family:'Raleway',sans-serif}
.pay-btn.selected,.pay-btn:hover{border-color:var(--or);background:rgba(212,172,13,.1);color:var(--or)}
.pos-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:10px}
.pos-stat{background:var(--card);border:1px solid var(--border);border-radius:10px;padding:10px;text-align:center}
.pos-stat .sv{font-size:1.1rem;font-weight:900;color:var(--or)}
.pos-stat .sl{font-size:.65rem;color:var(--muted);text-transform:uppercase;letter-spacing:.5px}
</style>

<div class="page-header" style="margin-bottom:12px">
  <h1><i class="fas fa-cash-register" style="color:var(--or)"></i> <span>Caisse POS</span></h1>
  <p>Point de vente tactile – <?= date('d/m/Y H:i') ?></p>
</div>

<!-- STATS JOUR -->
<div class="pos-stats">
  <div class="pos-stat"><div class="sv"><?= number_format($caJour,0,',',' ') ?></div><div class="sl">CA Aujourd'hui (FCFA)</div></div>
  <div class="pos-stat"><div class="sv"><?= $nbJour ?></div><div class="sl">Transactions</div></div>
  <div class="pos-stat"><div class="sv"><?= number_format($panierMoy,0,',',' ') ?></div><div class="sl">Panier Moyen</div></div>
</div>

<div class="pos-layout">
  <!-- GAUCHE : catalogue -->
  <div class="pos-left">
    <div class="pos-search">
      <input type="text" id="posSearch" placeholder="🔍 Rechercher un produit..." oninput="searchProd()">
      <select id="clientSel" class="form-select form-omega" style="width:200px;font-size:.82rem">
        <option value="">Client comptoir</option>
        <?php foreach($clients as $c): ?>
        <option value="<?=$c['id']?>"><?= htmlspecialchars($c['nom']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="cats-bar">
      <button class="cat-btn active" onclick="filterCat(0,this)">Tous</button>
      <?php foreach($categories as $c): ?>
      <button class="cat-btn" onclick="filterCat(<?=$c['id']?>,this)"
        style="--cc:<?= htmlspecialchars($c['couleur']) ?>">
        <?= htmlspecialchars($c['nom']) ?>
      </button>
      <?php endforeach; ?>
    </div>
    <div class="prod-grid-pos" id="prodGrid">
      <div style="color:var(--muted);text-align:center;padding:40px;grid-column:1/-1">
        <i class="fas fa-spinner fa-spin" style="font-size:2rem"></i><br>Chargement...
      </div>
    </div>
  </div>

  <!-- DROITE : panier -->
  <div class="pos-right">
    <div class="panier-head">
      <h4>🛒 Panier <span id="nbItems" style="background:var(--rouge);color:#fff;font-size:.7rem;padding:2px 8px;border-radius:20px;margin-left:5px">0</span>
        <button onclick="clearPanier()" style="background:rgba(255,255,255,.1);border:none;color:#aaa;border-radius:6px;padding:4px 10px;cursor:pointer;font-size:.72rem">Vider</button>
      </h4>
    </div>
    <div class="panier-items" id="panierItems">
      <div id="panierVide" style="text-align:center;color:var(--muted);padding:40px;font-size:.85rem">
        🛒 Panier vide<br><small>Cliquez sur un produit pour l'ajouter</small>
      </div>
    </div>
    <div class="panier-foot">
      <div class="total-line-pos"><span>Sous-total HT</span><span id="dispHT">0 FCFA</span></div>
      <div class="total-line-pos">
        <span>Remise : <input type="number" id="remiseInput" min="0" step="100" value="0"
          oninput="calcTotal()"
          style="width:80px;background:rgba(255,255,255,.06);border:1px solid var(--border);border-radius:6px;color:var(--text);padding:2px 6px;font-size:.8rem"> FCFA</span>
        <span id="dispRemise" style="color:#e74c3c">-0 FCFA</span>
      </div>
      <div class="total-line-pos"><span>TVA 18%</span><span id="dispTVA">0 FCFA</span></div>
      <div class="total-ttc-pos"><span>TOTAL TTC</span><span id="dispTTC">0 FCFA</span></div>
      <div style="font-size:.75rem;color:var(--muted);margin-bottom:8px">Mode de paiement :</div>
      <div class="pay-btns">
        <button class="pay-btn selected" onclick="selPay('especes',this)">💵 Espèces</button>
        <button class="pay-btn" onclick="selPay('mobile_money',this)">📱 Mobile Money</button>
        <button class="pay-btn" onclick="selPay('carte',this)">💳 Carte</button>
        <button class="pay-btn" onclick="selPay('credit',this)">📋 Crédit</button>
      </div>
      <form method="POST" id="posForm" style="margin-top:10px">
        <input type="hidden" name="pos_submit" value="1">
        <input type="hidden" name="panier" id="panierData">
        <input type="hidden" name="client_id" id="clientIdHidden">
        <input type="hidden" name="mode_paiement" id="modePaiement" value="especes">
        <input type="hidden" name="remise" id="remiseHidden" value="0">
        <button type="submit" id="btnValider" class="btn-omega btn-omega-success w-100"
          style="font-size:1rem;padding:14px;border-radius:12px" disabled>
          <i class="fas fa-check"></i> Valider la Vente
        </button>
      </form>
    </div>
  </div>
</div>

<script>
let panier = [];
let modePay = 'especes';
let currentCat = 0;

// ── Charger les produits ──
async function loadProd(q='', cat=0) {
  const url = `caisse_pos.php?api=produits&q=${encodeURIComponent(q)}&cat=${cat}`;
  const res = await fetch(url);
  const prods = await res.json();
  const grid = document.getElementById('prodGrid');
  if (!prods.length) { grid.innerHTML='<div style="color:var(--muted);text-align:center;padding:40px;grid-column:1/-1">Aucun produit trouvé</div>'; return; }
  grid.innerHTML = prods.map(p=>`
    <div class="prod-pos-card" onclick="addToPanier(${p.id},'${escHtml(p.nom)}',${p.prix_vente},'${escHtml(p.unite)}',${p.stock_actuel})">
      <div style="font-size:2rem">${p.image?'🖼️':'🥩'}</div>
      <div class="pname">${escHtml(p.nom)}</div>
      <div class="pprice">${fmt(p.prix_vente)} F</div>
      <div class="pstock">Stock: ${parseFloat(p.stock_actuel).toFixed(2)} ${escHtml(p.unite)}</div>
    </div>`).join('');
}

function escHtml(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]))}
function fmt(n){return Math.round(n).toLocaleString('fr')}
let searchTimer;
function searchProd(){clearTimeout(searchTimer);searchTimer=setTimeout(()=>loadProd(document.getElementById('posSearch').value,currentCat),200)}
function filterCat(id,btn){currentCat=id;document.querySelectorAll('.cat-btn').forEach(b=>b.classList.remove('active'));btn.classList.add('active');loadProd(document.getElementById('posSearch').value,id)}

// ── Panier ──
function addToPanier(id,nom,prix,unite,stock){
  const ex=panier.find(i=>i.id===id);
  if(ex){if(ex.qte<stock){ex.qte=Math.min(parseFloat((ex.qte+1).toFixed(3)),stock)}}
  else panier.push({id,nom,prix,unite,qte:1,stock:parseFloat(stock)});
  renderPanier();
}
function updateQte(id,val){const i=panier.findIndex(p=>p.id===id);if(i>=0){const q=parseFloat(val)||0;if(q<=0)panier.splice(i,1);else panier[i].qte=Math.min(q,panier[i].stock);}renderPanier()}
function removeItem(id){panier=panier.filter(p=>p.id!==id);renderPanier()}
function clearPanier(){panier=[];renderPanier()}

function renderPanier(){
  const cont=document.getElementById('panierItems');
  const vide=document.getElementById('panierVide');
  document.getElementById('nbItems').textContent=panier.length;
  if(!panier.length){cont.innerHTML='';cont.appendChild(vide);vide.style.display='block';document.getElementById('btnValider').disabled=true;calcTotal();return;}
  vide.style.display='none';
  cont.innerHTML=panier.map(i=>`
    <div class="panier-item">
      <div class="pitem-name">${escHtml(i.nom)}<br><small style="color:var(--muted)">${fmt(i.prix)} F/${escHtml(i.unite)}</small></div>
      <div class="pitem-ctrl">
        <button class="qty-btn minus" onclick="updateQte(${i.id},${i.qte-0.5})">−</button>
        <input type="number" class="qty-input" value="${i.qte}" min="0.001" step="0.001"
          onchange="updateQte(${i.id},this.value)" style="background:rgba(255,255,255,.06);border:1px solid var(--border);border-radius:6px;color:var(--text)">
        <button class="qty-btn plus" onclick="updateQte(${i.id},${i.qte+0.5})">+</button>
      </div>
      <div class="pitem-total">${fmt(i.prix*i.qte)} F</div>
      <button class="del-btn" onclick="removeItem(${i.id})">✕</button>
    </div>`).join('');
  document.getElementById('btnValider').disabled=false;
  calcTotal();
}

function calcTotal(){
  const ht=panier.reduce((s,i)=>s+i.prix*i.qte,0);
  const remise=parseFloat(document.getElementById('remiseInput').value)||0;
  const net=Math.max(0,ht-remise);
  const tva=net*0.18;
  const ttc=net+tva;
  document.getElementById('dispHT').textContent=fmt(ht)+' FCFA';
  document.getElementById('dispRemise').textContent='-'+fmt(remise)+' FCFA';
  document.getElementById('dispTVA').textContent=fmt(tva)+' FCFA';
  document.getElementById('dispTTC').textContent=fmt(ttc)+' FCFA';
  document.getElementById('remiseHidden').value=remise;
}

function selPay(m,btn){modePay=m;document.querySelectorAll('.pay-btn').forEach(b=>b.classList.remove('selected'));btn.classList.add('selected');document.getElementById('modePaiement').value=m}

document.getElementById('posForm').addEventListener('submit',function(e){
  document.getElementById('panierData').value=JSON.stringify(panier);
  document.getElementById('clientIdHidden').value=document.getElementById('clientSel').value;
  if(!panier.length){e.preventDefault();alert('Panier vide !');}
});

// Init
loadProd();
</script>

<?php require_once 'footer.php'; ?>
