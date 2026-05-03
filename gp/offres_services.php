<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');

// Création d'une offre
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $client_id = $_POST['client_id'];
    $type_offre = $_POST['type_offre'];
    $montant_ht = $_POST['montant_ht'];
    $montant_tva = $montant_ht * 0.20;
    $montant_ttc = $montant_ht + $montant_tva;
    $description = $_POST['description'];
    $conditions = $_POST['conditions'];
    $validite = $_POST['validite'];
    
    $stmt = $pdo->prepare("INSERT INTO offres_services (client_id, type_offre, montant_ht, montant_tva, montant_ttc, description, conditions, validite) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([$client_id, $type_offre, $montant_ht, $montant_tva, $montant_ttc, $description, $conditions, $validite]);
    echo "<div class='alert alert-success'>✅ Offre créée avec succès</div>";
}

// Marquage comme envoyé
if (isset($_GET['mark_sent'])) {
    $id = $_GET['mark_sent'];
    $pdo->prepare("UPDATE offres_services SET statut = 'envoye', date_envoi = NOW() WHERE id = ?")->execute([$id]);
    echo "<div class='alert alert-info'>📧 Offre marquée comme envoyée</div>";
}

$clients = $pdo->query("SELECT id, nom, telephone, email, code_client FROM clients ORDER BY nom")->fetchAll();
$offres = $pdo->query("
    SELECT o.*, c.nom, c.telephone, c.email, c.code_client 
    FROM offres_services o
    JOIN clients c ON o.client_id = c.id 
    ORDER BY o.date_creation DESC
")->fetchAll();
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<h2><i class="fas fa-file-pdf"></i> Offres de services - Dieynaba GP Holding</h2>

<div class="row">
    <div class="col-md-5">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-plus-circle"></i> Créer une offre personnalisée
            </div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-2">
                        <label>👤 Client</label>
                        <select name="client_id" class="form-select" required>
                            <option value="">Sélectionner un client</option>
                            <?php foreach ($clients as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['nom']) ?> (<?= $c['code_client'] ?>) - <?= $c['telephone'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>📦 Type d'offre</label>
                        <select name="type_offre" class="form-select" required>
                            <option value="fret">Offre Fret Standard</option>
                            <option value="premium">Offre Premium (VIP)</option>
                            <option value="abonnement">Abonnement Annuel</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label>💰 Montant HT (€)</label>
                        <input type="number" step="0.01" name="montant_ht" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label>📝 Description</label>
                        <textarea name="description" class="form-control" rows="2" required>Expédition de colis entre la France et le Sénégal, suivi GPS en temps réel, QR code personnalisé, notification WhatsApp.</textarea>
                    </div>
                    <div class="mb-2">
                        <label>📋 Conditions</label>
                        <textarea name="conditions" class="form-control" rows="2" required>Offre valable 30 jours. Paiement à réception. Livraison sous 48-72h.</textarea>
                    </div>
                    <div class="mb-2">
                        <label>📅 Date de validité</label>
                        <input type="date" name="validite" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Créer l'offre</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-success text-white">
                <i class="fas fa-list"></i> Offres créées
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Client</th><th>Type</th><th>Montant TTC</th><th>Statut</th><th>Date création</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($offres as $o): ?>
                            <tr>
                                <td><?= htmlspecialchars($o['nom']) ?><br><small><?= $o['code_client'] ?></small></td>
                                <td><?= $o['type_offre'] ?></td>
                                <td><?= number_format($o['montant_ttc'], 2) ?> €</div>
                                <td>
                                    <?php if ($o['statut'] == 'envoye'): ?>
                                        <span class="badge bg-info">📧 Envoyé</span>
                                    <?php elseif ($o['statut'] == 'accepte'): ?>
                                        <span class="badge bg-success">✅ Accepté</span>
                                    <?php elseif ($o['statut'] == 'refuse'): ?>
                                        <span class="badge bg-danger">❌ Refusé</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">⏳ En attente</span>
                                    <?php endif; ?>
                                 </div>
                                <td><?= date('d/m/Y', strtotime($o['date_creation'])) ?> </div>
                                <td>
                                    <a href="generer_offre_pdf.php?offre_id=<?= $o['id'] ?>" class="btn btn-sm btn-danger" target="_blank">
                                        <i class="fas fa-file-pdf"></i> PDF
                                    </a>
                                    <a href="offres_services.php?mark_sent=<?= $o['id'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-envelope"></i> Envoyé
                                    </a>
                                 </div>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <i class="fas fa-chart-line"></i> Arguments commerciaux clés
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <i class="fas fa-qrcode fa-2x text-primary"></i>
                        <strong>QR code personnalisé</strong>
                        <p class="small">Suivi instantané par scan, accessible à l'expéditeur et au destinataire</p>
                    </div>
                    <div class="col-md-4">
                        <i class="fab fa-whatsapp fa-2x text-success"></i>
                        <strong>Notification WhatsApp</strong>
                        <p class="small">Alertes automatiques à chaque changement de statut du colis</p>
                    </div>
                    <div class="col-md-4">
                        <i class="fas fa-map-marked-alt fa-2x text-danger"></i>
                        <strong>Suivi GPS temps réel</strong>
                        <p class="small">Localisation précise du colis à chaque étape du transport</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
