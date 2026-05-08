<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Facturation - Réductions, Frais accessoires";
$page_icon = "file-invoice";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

// Récupération clients et fournisseurs
$clients = $pdo->query("SELECT * FROM TIERS WHERE type = 'CLIENT' ORDER BY raison_sociale")->fetchAll();
$articles = $pdo->query("SELECT * FROM ARTICLES_STOCK ORDER BY libelle")->fetchAll();

// Calcul de la facture
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_facture = $_POST['type_facture'];
    $tiers_id = $_POST['tiers_id'];
    $date_facture = $_POST['date_facture'];
    $articles_data = $_POST['articles'] ?? [];
    
    $total_ht = 0;
    $total_remise = 0;
    $details = [];
    
    // Calcul des lignes de facture
    foreach($articles_data as $art) {
        if($art['quantite'] > 0) {
            $article = $pdo->prepare("SELECT * FROM ARTICLES_STOCK WHERE id = ?");
            $article->execute([$art['id']]);
            $a = $article->fetch();
            
            $prix = $art['prix'];
            $qte = $art['quantite'];
            $remise_ligne = $art['remise'] ?? 0;
            $montant_ligne = $prix * $qte;
            $montant_remise_ligne = $montant_ligne * $remise_ligne / 100;
            $montant_net = $montant_ligne - $montant_remise_ligne;
            
            $total_ht += $montant_net;
            $total_remise += $montant_remise_ligne;
            
            $details[] = [
                'libelle' => $a['libelle'],
                'quantite' => $qte,
                'prix' => $prix,
                'remise' => $remise_ligne,
                'montant' => $montant_net
            ];
        }
    }
    
    // Remise globale
    $remise_globale = (float)($_POST['remise_globale'] ?? 0);
    $montant_remise_globale = $total_ht * $remise_globale / 100;
    $net_commercial = $total_ht - $montant_remise_globale;
    
    // Frais accessoires
    $frais_port = (float)($_POST['frais_port'] ?? 0);
    $frais_emballage = (float)($_POST['frais_emballage'] ?? 0);
    $emballage_consigne = (float)($_POST['emballage_consigne'] ?? 0);
    
    $total_ht_final = $net_commercial + $frais_port + $frais_emballage;
    
    // TVA
    $tva = $total_ht_final * 0.18;
    $montant_ttc = $total_ht_final + $tva;
    
    // Emballages consignés (hors TVA, récupérables)
    $total_ttc_final = $montant_ttc + $emballage_consigne;
    
    $resultats = [
        'total_ht_brut' => $total_ht,
        'total_remise_lignes' => $total_remise,
        'net_avant_remise' => $total_ht,
        'remise_globale' => $remise_globale,
        'montant_remise_globale' => $montant_remise_globale,
        'net_commercial' => $net_commercial,
        'frais_port' => $frais_port,
        'frais_emballage' => $frais_emballage,
        'emballage_consigne' => $emballage_consigne,
        'total_ht' => $total_ht_final,
        'tva' => $tva,
        'total_ttc' => $montant_ttc,
        'total_facture' => $total_ttc_final,
        'details' => $details
    ];
    
    // Création de la facture
    $stmt = $pdo->prepare("INSERT INTO FACTURES_VENTE (numero, date_facture, client_id, montant_ht, taux_remise, montant_remise, montant_net_commercial, frais_port, frais_emballage, total_ht, tva, montant_ttc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        "FACT-" . date('Ymd') . "-" . rand(100,999),
        $date_facture,
        $tiers_id,
        $total_ht,
        $remise_globale,
        $montant_remise_globale,
        $net_commercial,
        $frais_port,
        $frais_emballage,
        $total_ht_final,
        $tva,
        $montant_ttc
    ]);
    $facture_id = $pdo->lastInsertId();
    
    // Écritures comptables
    // Débit client (411) pour le montant total TTC
    $ecriture1 = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'VENTE')");
    $ecriture1->execute([$date_facture, "Facture n°$facture_id", 411, 701, $montant_ttc, "FACT-$facture_id", 'VENTE']);
    
    // TVA collectée
    $ecriture2 = $pdo->prepare("INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'VENTE')");
    $ecriture2->execute([$date_facture, "TVA sur facture n°$facture_id", 701, 4451, $tva, "FACT-$facture_id", 'VENTE']);
    
    $message = "✅ Facture générée avec succès - Total TTC: " . number_format($total_ttc_final, 0, ',', ' ') . " FCFA";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-file-invoice"></i> Facturation commerciale - Réductions, frais accessoires</h5>
                <small>Conformité SYSCOHADA UEMOA</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 STRUCTURE D'UNE FACTURE SYSCOHADA :</strong><br>
                    Montant brut HT → (-) Remises lignes → (=) Net commercial → (+) Frais accessoires → (=) Net HT → (+) TVA → (=) Net TTC → (+) Emballages consignés → (=) Total facture
                </div>

                <!-- Formulaire -->
                <form method="POST" id="factureForm">
                    <div class="row g-3">
                        <div class="col-md-4"><label>Type de facture</label><select name="type_facture" class="form-select"><option value="VENTE">Facture de vente</option><option value="AVOIR">Avoir</option></select></div>
                        <div class="col-md-4"><label>Client/Fournisseur</label><select name="tiers_id" class="form-select" required><?php foreach($clients as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['raison_sociale']) ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-4"><label>Date facture</label><input type="date" name="date_facture" value="<?= date('Y-m-d') ?>" class="form-control"></div>
                    </div>
                    
                    <h6 class="mt-4">📦 Articles</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered" id="articlesTable">
                            <thead class="table-light"><tr><th>Article</th><th>Quantité</th><th>Prix unitaire (F)</th><th>Remise ligne (%)</th><th>Montant HT</th></tr></thead>
                            <tbody>
                                <?php foreach($articles as $a): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a['libelle']) ?><input type="hidden" name="articles[<?= $a['id'] ?>][id]" value="<?= $a['id'] ?>"></td>
                                    <td><input type="number" name="articles[<?= $a['id'] ?>][quantite]" class="form-control form-control-sm" value="0" min="0" onchange="calculerLigne(this)"></td>
                                    <td><input type="number" name="articles[<?= $a['id'] ?>][prix]" class="form-control form-control-sm" value="0" step="100" onchange="calculerLigne(this)"></td>
                                    <td><input type="number" name="articles[<?= $a['id'] ?>][remise]" class="form-control form-control-sm" value="0" onchange="calculerLigne(this)"></td>
                                    <td class="montant-ligne text-end">0 F</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-4"><label>Remise globale (%)</label><input type="number" name="remise_globale" class="form-control" value="0" step="1" onchange="calculerTotal()"></div>
                        <div class="col-md-4"><label>Frais de port (F)</label><input type="number" name="frais_port" class="form-control" value="0" onchange="calculerTotal()"></div>
                        <div class="col-md-4"><label>Frais emballage (F)</label><input type="number" name="frais_emballage" class="form-control" value="0" onchange="calculerTotal()"></div>
                        <div class="col-md-4"><label>Emballages consignés (F)</label><input type="number" name="emballage_consigne" class="form-control" value="0" onchange="calculerTotal()"></div>
                    </div>
                    
                    <div class="card bg-light mt-4">
                        <div class="card-header bg-secondary text-white">📊 Récapitulatif facture</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr><td>Total brut HT :</td><td class="text-end" id="total_brut_ht">0 F</td></tr>
                                        <tr><td>Remises lignes :</td><td class="text-end text-danger" id="total_remises">0 F</td></tr>
                                        <tr><td>Net avant remise :</td><td class="text-end" id="net_avant_remise">0 F</td></tr>
                                        <tr><td>Remise globale (<span id="remise_globale_val">0</span>%) :</td><td class="text-end text-danger" id="montant_remise_globale">0 F</td></tr>
                                        <tr class="table-primary"><td><strong>Net commercial :</strong></td><td class="text-end fw-bold" id="net_commercial">0 F</td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr><td>Frais de port :</td><td class="text-end" id="frais_port_aff">0 F</td></tr>
                                        <tr><td>Frais emballage :</td><td class="text-end" id="frais_emballage_aff">0 F</td></tr>
                                        <tr><td><strong>Total HT :</strong></td><td class="text-end fw-bold" id="total_ht">0 F</td></tr>
                                        <tr><td>TVA (18%) :</td><td class="text-end" id="tva">0 F</td></tr>
                                        <tr><td><strong>Total TTC :</strong></td><td class="text-end fw-bold text-primary" id="total_ttc">0 F</td></tr>
                                        <tr><td>Emballages consignés :</td><td class="text-end" id="emballage_consigne_aff">0 F</td></tr>
                                        <tr class="table-success"><td><strong>TOTAL FACTURE :</strong></td><td class="text-end fw-bold text-success" id="total_facture">0 F</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="submit" class="btn-omega"><i class="bi bi-check-lg"></i> Générer la facture</button>
                    </div>
                </form>
                
                <?php if($message): ?>
                <div class="alert alert-success mt-4"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if(!empty($resultats)): ?>
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">📋 Détails de la facture générée</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Calcul détaillé SYSCOHADA :</h6>
                                <table class="table table-sm">
                                    <tr><td>Total brut HT :</td><td class="text-end"><?= number_format($resultats['total_ht_brut'], 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Remises lignes :</td><td class="text-end text-danger">- <?= number_format($resultats['total_remise_lignes'], 0, ',', ' ') ?> F</td></tr>
                                    <tr><td class="fw-bold">Net commercial :</td><td class="text-end fw-bold"><?= number_format($resultats['net_commercial'], 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>+ Frais port :</td><td class="text-end">+ <?= number_format($resultats['frais_port'], 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>+ Frais emballage :</td><td class="text-end">+ <?= number_format($resultats['frais_emballage'], 0, ',', ' ') ?> F</td></tr>
                                    <tr class="fw-bold"><td>Total HT :</td><td class="text-end"><?= number_format($resultats['total_ht'], 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr><td>TVA (18%) :</td><td class="text-end"><?= number_format($resultats['tva'], 0, ',', ' ') ?> F</td></tr>
                                    <tr class="fw-bold"><td>Total TTC :</td><td class="text-end text-primary"><?= number_format($resultats['total_ttc'], 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>+ Emballages consignés :</td><td class="text-end">+ <?= number_format($resultats['emballage_consigne'], 0, ',', ' ') ?> F</td></tr>
                                    <tr class="table-success fw-bold"><td>TOTAL FACTURE :</td><td class="text-end text-success"><?= number_format($resultats['total_facture'], 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <strong>📝 Écritures comptables générées :</strong><br>
                            <code>Débit 411 - Clients : <?= number_format($resultats['total_ttc'], 0, ',', ' ') ?> F</code><br>
                            <code>Crédit 701 - Ventes : <?= number_format($resultats['total_ht'], 0, ',', ' ') ?> F</code><br>
                            <code>Crédit 4451 - TVA collectée : <?= number_format($resultats['tva'], 0, ',', ' ') ?> F</code>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function calculerLigne(elt) {
    let row = elt.closest('tr');
    let qte = parseFloat(row.querySelector('input[name*="[quantite]"]').value) || 0;
    let prix = parseFloat(row.querySelector('input[name*="[prix]"]').value) || 0;
    let remise = parseFloat(row.querySelector('input[name*="[remise]"]').value) || 0;
    let montant = qte * prix;
    let montantRemise = montant * remise / 100;
    let montantNet = montant - montantRemise;
    row.querySelector('.montant-ligne').innerText = new Intl.NumberFormat().format(montantNet) + ' F';
    calculerTotal();
}

function calculerTotal() {
    let totalBrut = 0;
    let totalRemises = 0;
    document.querySelectorAll('.montant-ligne').forEach(cell => {
        let val = parseFloat(cell.innerText.replace(/[^0-9]/g, '')) || 0;
        totalBrut += val;
    });
    let remiseGlobale = parseFloat(document.querySelector('input[name="remise_globale"]').value) || 0;
    let montantRemiseGlobale = totalBrut * remiseGlobale / 100;
    let netCommercial = totalBrut - montantRemiseGlobale;
    let fraisPort = parseFloat(document.querySelector('input[name="frais_port"]').value) || 0;
    let fraisEmballage = parseFloat(document.querySelector('input[name="frais_emballage"]').value) || 0;
    let totalHt = netCommercial + fraisPort + fraisEmballage;
    let tva = totalHt * 0.18;
    let totalTtc = totalHt + tva;
    let emballageConsigne = parseFloat(document.querySelector('input[name="emballage_consigne"]').value) || 0;
    let totalFacture = totalTtc + emballageConsigne;
    
    document.getElementById('total_brut_ht').innerText = new Intl.NumberFormat().format(totalBrut) + ' F';
    document.getElementById('net_avant_remise').innerText = new Intl.NumberFormat().format(totalBrut) + ' F';
    document.getElementById('remise_globale_val').innerText = remiseGlobale;
    document.getElementById('montant_remise_globale').innerText = new Intl.NumberFormat().format(montantRemiseGlobale) + ' F';
    document.getElementById('net_commercial').innerText = new Intl.NumberFormat().format(netCommercial) + ' F';
    document.getElementById('frais_port_aff').innerText = new Intl.NumberFormat().format(fraisPort) + ' F';
    document.getElementById('frais_emballage_aff').innerText = new Intl.NumberFormat().format(fraisEmballage) + ' F';
    document.getElementById('total_ht').innerText = new Intl.NumberFormat().format(totalHt) + ' F';
    document.getElementById('tva').innerText = new Intl.NumberFormat().format(tva) + ' F';
    document.getElementById('total_ttc').innerText = new Intl.NumberFormat().format(totalTtc) + ' F';
    document.getElementById('emballage_consigne_aff').innerText = new Intl.NumberFormat().format(emballageConsigne) + ' F';
    document.getElementById('total_facture').innerText = new Intl.NumberFormat().format(totalFacture) + ' F';
}
</script>

<?php include 'inc_footer.php'; ?>
