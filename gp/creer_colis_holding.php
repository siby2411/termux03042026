<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .direction-card { cursor: pointer; transition: all 0.3s; border: 2px solid transparent; border-radius: 15px; margin: 10px; }
    .direction-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .direction-selected { border-color: #ff8c00; background: #fff3e0; }
    .sens-badge { font-size: 1.2rem; padding: 8px 15px; border-radius: 25px; }
</style>

<h2><i class="fas fa-exchange-alt"></i> Gestion bidirectionnelle des colis</h2>

<?php
$entites = $pdo->query("SELECT * FROM entites WHERE statut = 'active'")->fetchAll();
$clients = $pdo->query("SELECT id, nom, telephone, code_client FROM clients ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sens = $_POST['sens'];
    $expediteur_id = $_POST['expediteur_id'];
    $destinataire_id = $_POST['destinataire_id'];
    $description = $_POST['description'];
    $poids = $_POST['poids_kg'];
    $valeur_declaree = $_POST['valeur_declaree'] ?? 0;
    $frais_expedition = $_POST['frais_expedition'];
    $frais_douane = $_POST['frais_douane'] ?? 0;
    $taxes = $_POST['taxes'] ?? 0;
    $date_depart_reel = $_POST['date_depart_reel'] ?? null;
    $date_arrivee_reelle = $_POST['date_arrivee_reelle'] ?? null;
    
    // Déterminer entités et lieux selon le sens
    if ($sens == 'paris_dakar') {
        $entite_origine = 1;
        $entite_destination = 2;
        $lieu_depart = 'Paris, France';
        $lieu_arrivee = 'Dakar, Sénégal';
        $sens_label = 'Paris → Dakar';
    } else {
        $entite_origine = 2;
        $entite_destination = 1;
        $lieu_depart = 'Dakar, Sénégal';
        $lieu_arrivee = 'Paris, France';
        $sens_label = 'Dakar → Paris';
    }
    
    // Récupérer les infos de l'expéditeur et destinataire
    $stmt = $pdo->prepare("SELECT nom, telephone FROM clients WHERE id = ?");
    $stmt->execute([$expediteur_id]);
    $expediteur = $stmt->fetch();
    $stmt->execute([$destinataire_id]);
    $destinataire = $stmt->fetch();
    
    $montant_total = $frais_expedition + $frais_douane + $taxes;
    
    // Insertion du colis
    $stmt = $pdo->prepare("INSERT INTO colis (
        numero_suivi, client_expediteur_id, client_destinataire_id, description, poids_kg, statut, 
        lieu_depart, lieu_arrivee, entite_origine_id, entite_destination_id, sens, 
        destinataire_nom, destinataire_telephone, destinataire_adresse,
        frais_expedition, frais_douane, montant_encaisse,
        date_depart_reel, date_arrivee_reelle, created_at
    ) VALUES (
        NULL, ?, ?, ?, ?, 'enregistre',
        ?, ?, ?, ?, ?,
        ?, ?, '',
        ?, ?, ?,
        ?, ?, NOW()
    )");
    
    $stmt->execute([
        $expediteur_id, $destinataire_id, $description, $poids,
        $lieu_depart, $lieu_arrivee, $entite_origine, $entite_destination, $sens,
        $destinataire['nom'], $destinataire['telephone'],
        $frais_expedition, $frais_douane, $montant_total,
        $date_depart_reel, $date_arrivee_reelle
    ]);
    
    $colis_id = $pdo->lastInsertId();
    $numero = $pdo->query("SELECT numero_suivi FROM colis WHERE id = $colis_id")->fetchColumn();
    
    // Enregistrement opération financière
    $stmt = $pdo->prepare("INSERT INTO operations_financieres (
        colis_id, entite_origine, entite_destination, type_operation, 
        montant_expedition, montant_douane, montant_taxe, montant_total, devise, date_operation
    ) VALUES (?,?,?,'expedition',?,?,?,?,'EUR',NOW())");
    $stmt->execute([$colis_id, $entite_origine, $entite_destination, $frais_expedition, $frais_douane, $taxes, $montant_total]);
    
    // Générer les QR codes pour expéditeur et destinataire
    require_once 'qrcode_generator.php';
    generateColisQRCode($colis_id, true);
    generateSimpleQRCode($numero);
    
    echo "<div class='alert alert-success'>
            ✅ Colis créé !<br>
            N° de suivi : <strong>$numero</strong><br>
            Sens : <strong>$sens_label</strong><br>
            Expéditeur: {$expediteur['nom']} → Destinataire: {$destinataire['nom']}
          </div>";
}
?>

<!-- Sélecteur de direction -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card direction-card text-center" id="card-paris-dakar" onclick="setDirection('paris_dakar')">
            <div class="card-body">
                <i class="fas fa-plane-departure fa-3x text-primary"></i>
                <h4>🇫🇷 Paris → Dakar 🇸🇳</h4>
                <p>Expédition depuis la France vers le Sénégal</p>
                <small>TVA 20% • Frais douane inclus</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card direction-card text-center" id="card-dakar-paris" onclick="setDirection('dakar_paris')">
            <div class="card-body">
                <i class="fas fa-plane-arrival fa-3x text-success"></i>
                <h4>🇸🇳 Dakar → Paris 🇫🇷</h4>
                <p>Expédition depuis le Sénégal vers la France</p>
                <small>TVA 18% • Droits de douane</small>
            </div>
        </div>
    </div>
</div>

<!-- Formulaire principal -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-box"></i> <span id="form-title">Formulaire d'expédition Paris → Dakar</span>
    </div>
    <div class="card-body">
        <form method="post" id="colisForm">
            <input type="hidden" name="sens" id="sens" value="paris_dakar">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>👤 Expéditeur</label>
                        <select name="expediteur_id" class="form-select" required>
                            <option value="">Choisir un expéditeur</option>
                            <?php foreach ($clients as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?> (<?= $c['code_client'] ?>) - <?= $c['telephone'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>🎯 Destinataire</label>
                        <select name="destinataire_id" class="form-select" required>
                            <option value="">Choisir un destinataire</option>
                            <?php foreach ($clients as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?> - <?= $c['telephone'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>📦 Description du colis</label>
                        <textarea name="description" class="form-control" rows="2" required></textarea>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label>⚖️ Poids (kg)</label>
                        <input type="number" step="0.1" name="poids_kg" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label>💰 Valeur déclarée (€)</label>
                        <input type="number" step="0.01" name="valeur_declaree" class="form-control" value="0">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label>✈️ Frais d'expédition (€)</label>
                        <input type="number" step="0.01" name="frais_expedition" id="frais_expedition" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label>🛃 Frais de douane (€)</label>
                        <input type="number" step="0.01" name="frais_douane" id="frais_douane" class="form-control" value="0">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label>📊 Taxes (€)</label>
                        <input type="number" step="0.01" name="taxes" id="taxes" class="form-control" readonly>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>📅 Date d'envoi</label>
                        <input type="date" name="date_depart_reel" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label>📅 Date d'arrivée prévue</label>
                        <input type="date" name="date_arrivee_reelle" class="form-control">
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info" id="recap-total">
                <strong>💰 Récapitulatif :</strong> Total à encaisser : <span id="total-montant">0.00</span> €
            </div>
            
            <button type="submit" class="btn btn-primary btn-lg w-100">
                <i class="fas fa-save"></i> Créer le colis et générer les QR codes
            </button>
        </form>
    </div>
</div>

<script>
function setDirection(sens) {
    document.getElementById('sens').value = sens;
    const title = document.getElementById('form-title');
    const cardParis = document.getElementById('card-paris-dakar');
    const cardDakar = document.getElementById('card-dakar-paris');
    
    if (sens === 'paris_dakar') {
        title.innerHTML = '🇫🇷 Formulaire d\'expédition Paris → Dakar 🇸🇳';
        cardParis.classList.add('direction-selected');
        cardDakar.classList.remove('direction-selected');
        document.getElementById('frais_expedition').placeholder = 'Ex: 45.00 (TVA 20% incluse)';
    } else {
        title.innerHTML = '🇸🇳 Formulaire d\'expédition Dakar → Paris 🇫🇷';
        cardDakar.classList.add('direction-selected');
        cardParis.classList.remove('direction-selected');
        document.getElementById('frais_expedition').placeholder = 'Ex: 55.00 (TVA 18% incluse)';
    }
    recalculerTaxes();
}

function recalculerTaxes() {
    const sens = document.getElementById('sens').value;
    let fraisExp = parseFloat(document.getElementById('frais_expedition').value) || 0;
    let fraisDouane = parseFloat(document.getElementById('frais_douane').value) || 0;
    let taxes = 0;
    
    if (sens === 'paris_dakar') {
        taxes = fraisExp * 0.20;
    } else {
        taxes = fraisExp * 0.18;
    }
    
    document.getElementById('taxes').value = taxes.toFixed(2);
    const total = fraisExp + fraisDouane + taxes;
    document.getElementById('total-montant').innerHTML = total.toFixed(2);
}

document.getElementById('frais_expedition').addEventListener('input', recalculerTaxes);
document.getElementById('frais_douane').addEventListener('input', recalculerTaxes);

setDirection('paris_dakar');
</script>

<?php include('footer.php'); ?>
