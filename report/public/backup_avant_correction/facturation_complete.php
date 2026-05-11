<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Facturation - Réductions, TVA, frais accessoires";
$page_icon = "file-invoice";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

// Récupération des clients
$clients = $pdo->query("SELECT * FROM TIERS WHERE type = 'CLIENT' ORDER BY raison_sociale")->fetchAll();
$articles = $pdo->query("SELECT * FROM ARTICLES_STOCK ORDER BY code_article")->fetchAll();

// Création d'un client par défaut si aucun
if(empty($clients)) {
    $pdo->exec("INSERT INTO TIERS (code, type, raison_sociale, adresse, telephone, email, identifiant_fiscal, numero_compte) VALUES ('CLI001', 'CLIENT', 'Client par défaut', 'Dakar', '33 123 45 67', 'client@test.com', '123456789A', 411)");
    $clients = $pdo->query("SELECT * FROM TIERS WHERE type = 'CLIENT' ORDER BY raison_sociale")->fetchAll();
}

// Traitement de la génération de facture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generer_facture') {
    $tiers_id = (int)$_POST['tiers_id'];
    $date_facture = $_POST['date_facture'];
    $articles_data = $_POST['articles'] ?? [];
    
    $total_ht = 0;
    $lignes = [];
    foreach ($articles_data as $art_id => $art) {
        $quantite = (int)($art['quantite'] ?? 0);
        if ($quantite > 0) {
            $prix = (float)($art['prix'] ?? 0);
            $remise = (float)($art['remise'] ?? 0);
            $montant_brut = $quantite * $prix;
            $remise_montant = $montant_brut * $remise / 100;
            $montant_net = $montant_brut - $remise_montant;
            $total_ht += $montant_net;
            $lignes[] = [
                'article_id' => $art_id,
                'quantite' => $quantite,
                'prix' => $prix,
                'remise' => $remise,
                'montant' => $montant_net
            ];
        }
    }
    
    if ($total_ht == 0) {
        $message = "⚠️ Aucun article sélectionné ou quantité nulle.";
    } else {
        $remise_globale = (float)($_POST['remise_globale'] ?? 0);
        $montant_remise_globale = $total_ht * $remise_globale / 100;
        $net_commercial = $total_ht - $montant_remise_globale;
        $frais_port = (float)($_POST['frais_port'] ?? 0);
        $frais_emballage = (float)($_POST['frais_emballage'] ?? 0);
        $total_ht_final = $net_commercial + $frais_port + $frais_emballage;
        $tva = $total_ht_final * 0.18;
        $montant_ttc = $total_ht_final + $tva;
        $emballage_consigne = (float)($_POST['emballage_consigne'] ?? 0);
        $total_facture = $montant_ttc + $emballage_consigne;
        
        $numero = "FACT-" . date('Ymd') . "-" . rand(100,999);
        
        // Insertion de la facture dans FACTURES_VENTE
        $stmt = $pdo->prepare("INSERT INTO FACTURES_VENTE (numero, date_facture, client_id, montant_ht, montant_net_commercial, total_ht, tva, montant_ttc) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$numero, $date_facture, $tiers_id, $total_ht, $net_commercial, $total_ht_final, $tva, $montant_ttc]);
        
        // ========== ÉCRITURES COMPTABLES CONFORMES SYSCOHADA ==========
        // 1. Enregistrement du HT (débit client, crédit ventes)
        // 2. Enregistrement de la TVA collectée (débit client, crédit TVA)
        $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES 
                (?, ?, 411, 701, ?, ?, 'VENTE'),
                (?, ?, 411, 4451, ?, ?, 'VENTE')";
        $stmt2 = $pdo->prepare($sql);
        $stmt2->execute([
            $date_facture, "Facture $numero - HT", $total_ht_final, $numero,
            $date_facture, "Facture $numero - TVA 18%", $tva, $numero
        ]);
        
        $resultats = [
            'numero' => $numero,
            'total_ht' => $total_ht_final,
            'tva' => $tva,
            'montant_ttc' => $montant_ttc,
            'total_facture' => $total_facture
        ];
        $message = "✅ Facture $numero générée - Montant TTC : " . number_format($total_facture, 0, ',', ' ') . " FCFA";
    }
}

// Récupération des dernières factures
$factures = $pdo->query("SELECT fv.*, t.raison_sociale FROM FACTURES_VENTE fv JOIN TIERS t ON fv.client_id = t.id ORDER BY fv.date_facture DESC LIMIT 10")->fetchAll();
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-file-invoice"></i> Facturation - Réductions, frais accessoires et TVA</h5>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>
                
                <form method="POST" id="factureForm">
                    <input type="hidden" name="action" value="generer_facture">
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label>Client</label>
                            <select name="tiers_id" class="form-select" required>
                                <option value="">-- Sélectionner un client --</option>
                                <?php foreach($clients as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['raison_sociale']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Date facture</label>
                            <input type="date" name="date_facture" value="<?= date('Y-m-d') ?>" class="form-control">
                        </div>
                    </div>
                    
                    <h6>📦 Articles</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="articlesTable">
                            <thead class="table-light">
                                <tr><th>Article</th><th>Quantité</th><th>Prix unitaire (F)</th><th>Remise (%)</th><th>Montant HT (F)</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($articles as $a): ?>
                                <tr class="article-ligne" data-prix="<?= $a['prix_unitaire'] ?>">
                                    <td><?= htmlspecialchars($a['code_article']) ?> - <?= htmlspecialchars($a['libelle']) ?>
                                        <input type="hidden" name="articles[<?= $a['id'] ?>][id]" value="<?= $a['id'] ?>">
                                    </td>
                                    <td><input type="number" name="articles[<?= $a['id'] ?>][quantite]" class="form-control form-control-sm qty" value="0" min="0" step="1"></td>
                                    <td><input type="number" name="articles[<?= $a['id'] ?>][prix]" class="form-control form-control-sm price" value="<?= $a['prix_unitaire'] ?>" step="100"></td>
                                    <td><input type="number" name="articles[<?= $a['id'] ?>][remise]" class="form-control form-control-sm disc" value="0" step="1"></td>
                                    <td class="montant-ligne text-end">0 F</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-3"><label>Remise globale (%)</label><input type="number" name="remise_globale" id="remise_globale" class="form-control" value="0" step="1"></div>
                        <div class="col-md-3"><label>Frais de port (F)</label><input type="number" name="frais_port" id="frais_port" class="form-control" value="0" step="100"></div>
                        <div class="col-md-3"><label>Frais emballage (F)</label><input type="number" name="frais_emballage" id="frais_emballage" class="form-control" value="0" step="100"></div>
                        <div class="col-md-3"><label>Emballages consignés (F)</label><input type="number" name="emballage_consigne" id="emballage_consigne" class="form-control" value="0" step="100"></div>
                    </div>
                    
                    <div class="card bg-light mt-4">
                        <div class="card-header bg-secondary text-white">Récapitulatif facture</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr><th>Total HT</th><td class="text-end" id="total_ht">0 F</td></tr>
                                        <tr><th>Remise globale</th><td class="text-end text-danger" id="remise_globale_montant">0 F</td></tr>
                                        <tr><th>Net commercial</th><td class="text-end fw-bold" id="net_commercial">0 F</td></tr>
                                        <tr><th>+ Frais divers</th><td class="text-end" id="frais_divers">0 F</td></tr>
                                        <tr><th>Total HT après frais</th><td class="text-end" id="total_ht_final">0 F</td></tr>
                                        <tr><th>TVA (18%)</th><td class="text-end" id="tva">0 F</td></tr>
                                        <tr class="table-success"><th>TOTAL TTC</th><td class="text-end fw-bold" id="total_ttc">0 F</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn-omega">Générer la facture</button>
                    </div>
                </form>
                
                <?php if($resultats): ?>
                <div class="alert alert-success mt-4">
                    <strong>✅ Facture <?= $resultats['numero'] ?> créée</strong><br>
                    Total HT : <?= number_format($resultats['total_ht'], 0, ',', ' ') ?> F<br>
                    TVA (18%) : <?= number_format($resultats['tva'], 0, ',', ' ') ?> F<br>
                    <strong>Net à payer : <?= number_format($resultats['total_facture'], 0, ',', ' ') ?> FCFA</strong>
                </div>
                <?php endif; ?>
                
                <?php if($factures): ?>
                <hr>
                <h6>📜 Dernières factures générées</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light"><tr><th>N° facture</th><th>Date</th><th>Client</th><th>Montant TTC</th></tr></thead>
                        <tbody>
                            <?php foreach($factures as $f): ?>
                            <tr>
                                <td><?= htmlspecialchars($f['numero']) ?></td>
                                <td><?= date('d/m/Y', strtotime($f['date_facture'])) ?></td>
                                <td><?= htmlspecialchars($f['raison_sociale']) ?></td>
                                <td class="text-end"><?= number_format($f['montant_ttc'], 0, ',', ' ') ?> F</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Calcul automatique des lignes et du total
function recalculerTotal() {
    let totalHT = 0;
    document.querySelectorAll('.article-ligne').forEach(row => {
        let qty = parseFloat(row.querySelector('.qty').value) || 0;
        let price = parseFloat(row.querySelector('.price').value) || 0;
        let disc = parseFloat(row.querySelector('.disc').value) || 0;
        let montantBrut = qty * price;
        let remiseMontant = montantBrut * disc / 100;
        let montantNet = montantBrut - remiseMontant;
        row.querySelector('.montant-ligne').innerText = new Intl.NumberFormat().format(montantNet) + ' F';
        totalHT += montantNet;
    });
    
    let remiseGlobale = parseFloat(document.getElementById('remise_globale').value) || 0;
    let fraisPort = parseFloat(document.getElementById('frais_port').value) || 0;
    let fraisEmballage = parseFloat(document.getElementById('frais_emballage').value) || 0;
    let emballageConsigne = parseFloat(document.getElementById('emballage_consigne').value) || 0;
    
    let montantRemiseGlobale = totalHT * remiseGlobale / 100;
    let netCommercial = totalHT - montantRemiseGlobale;
    let fraisDivers = fraisPort + fraisEmballage;
    let totalHtFinal = netCommercial + fraisDivers;
    let tva = totalHtFinal * 0.18;
    let totalTtc = totalHtFinal + tva;
    let totalFacture = totalTtc + emballageConsigne;
    
    document.getElementById('total_ht').innerText = new Intl.NumberFormat().format(totalHT) + ' F';
    document.getElementById('remise_globale_montant').innerText = new Intl.NumberFormat().format(montantRemiseGlobale) + ' F';
    document.getElementById('net_commercial').innerText = new Intl.NumberFormat().format(netCommercial) + ' F';
    document.getElementById('frais_divers').innerText = new Intl.NumberFormat().format(fraisDivers) + ' F';
    document.getElementById('total_ht_final').innerText = new Intl.NumberFormat().format(totalHtFinal) + ' F';
    document.getElementById('tva').innerText = new Intl.NumberFormat().format(tva) + ' F';
    document.getElementById('total_ttc').innerText = new Intl.NumberFormat().format(totalFacture) + ' F';
}

// Événements
document.querySelectorAll('.qty, .price, .disc').forEach(input => {
    input.addEventListener('input', recalculerTotal);
});
document.getElementById('remise_globale').addEventListener('input', recalculerTotal);
document.getElementById('frais_port').addEventListener('input', recalculerTotal);
document.getElementById('frais_emballage').addEventListener('input', recalculerTotal);
document.getElementById('emballage_consigne').addEventListener('input', recalculerTotal);

// Initialisation
recalculerTotal();
</script>

<?php include 'inc_footer.php'; ?>
