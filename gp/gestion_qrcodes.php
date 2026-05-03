<?php
require_once 'auth.php';
require_once 'db_connect.php';
require_once 'qrcode_generator.php';
include('header.php');
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<h2><i class="fas fa-qrcode"></i> Gestion des QR codes</h2>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-info">
            <strong>📌 Informations :</strong>
            <ul class="mb-0 mt-2">
                <li>Chaque colis génère automatiquement deux QR codes : 
                    <strong>QR complet</strong> (contient toutes les infos du colis - expéditeur, destinataire, poids, etc.) et 
                    <strong>QR simple lien</strong> (redirige vers la page de suivi).
                </li>
                <li>Les QR codes sont stockés dans le dossier <code>/qrcodes/</code>.</li>
                <li>Expéditeur et destinataire reçoivent automatiquement leur QR code par WhatsApp.</li>
            </ul>
        </div>
    </div>
</div>

<?php
// Regénération massive des QR codes
if (isset($_GET['regenerate_all'])) {
    $colis_list = $pdo->query("SELECT id FROM colis")->fetchAll();
    $count = 0;
    foreach ($colis_list as $colis) {
        $result = generateColisQRCode($colis['id'], true);
        if ($result['success']) $count++;
        generateSimpleQRCode($result['colis']['numero_suivi']);
    }
    echo "<div class='alert alert-success'>✅ $count QR codes régénérés.</div>";
}

// Supprimer les anciens QR codes
if (isset($_GET['clean'])) {
    $files = glob(__DIR__ . '/qrcodes/*.png');
    $count = 0;
    foreach ($files as $file) {
        if (unlink($file)) $count++;
    }
    echo "<div class='alert alert-warning'>🗑️ $count anciens QR codes supprimés.</div>";
}

$colis_list = $pdo->query("
    SELECT c.id, c.numero_suivi, c.statut, e.nom as expediteur, d.nom as destinataire 
    FROM colis c 
    LEFT JOIN clients e ON c.client_expediteur_id = e.id 
    LEFT JOIN clients d ON c.client_destinataire_id = d.id 
    ORDER BY c.id DESC
")->fetchAll();

// Statistiques des QR codes
$qr_count = count(glob(__DIR__ . '/qrcodes/*.png'));
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5>Total colis</h5>
                <h2><?= count($colis_list) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5>QR codes générés</h5>
                <h2><?= $qr_count ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5>Actions disponibles</h5>
                <h2>
                    <a href="?regenerate_all=1" class="btn btn-light btn-sm">Regénérer tous</a>
                    <a href="?clean=1" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer tous les QR codes ?')">Nettoyer</a>
                </h2>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-dark text-white">Liste des QR codes par colis</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>N° suivi</th>
                        <th>Expéditeur</th>
                        <th>Destinataire</th>
                        <th>Statut</th>
                        <th>QR complet</th>
                        <th>QR lien simple</th>
                        <th>Envoyer par WhatsApp</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($colis_list as $c): 
                    $qr_complet = generateColisQRCode($c['id']);
                    $qr_simple = generateSimpleQRCode($c['numero_suivi']);
                ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['numero_suivi']) ?></td>
                        <td><?= htmlspecialchars($c['expediteur']) ?></td>
                        <td><?= htmlspecialchars($c['destinataire']) ?></td>
                        <td><?= $c['statut'] ?></td>
                        <td>
                            <a href="<?= $qr_complet['filepath'] ?>" target="_blank">
                                <img src="<?= $qr_complet['filepath'] ?>" width="50" height="50">
                            </a>
                        </td>
                        <td>
                            <a href="<?= $qr_simple ?>" target="_blank">
                                <img src="<?= $qr_simple ?>" width="50" height="50">
                            </a>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-success send-wa" data-colis="<?= $c['id'] ?>" data-numero="<?= htmlspecialchars($c['numero_suivi']) ?>">
                                <i class="fab fa-whatsapp"></i> Envoyer
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.send-wa').forEach(btn => {
    btn.addEventListener('click', async () => {
        const colisId = btn.dataset.colis;
        const numero = btn.dataset.numero;
        const phone = prompt("Entrez le numéro WhatsApp du destinataire (format international, ex: 33758686348):", "33758686348");
        if (phone) {
            const response = await fetch('api_whatsapp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=send_qr&colis_id=${colisId}&phone=${phone}`
            });
            const result = await response.json();
            alert(result.success ? '✅ QR code envoyé !' : '❌ Erreur: ' + (result.error || 'Inconnue'));
        }
    });
});
</script>

<?php include('footer.php'); ?>
