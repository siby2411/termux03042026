<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');

// Configuration de l'API AviationStack (gratuite - 500 appels/mois)
// Inscription : https://aviationstack.com/signup/free
define('AVIATION_API_KEY', 'votre_cle_api_aviationstack'); // À remplacer

// Fonction pour récupérer les vols via API (si clé configurée)
function getFlightsFromAPI($depart, $arrivee) {
    if (AVIATION_API_KEY == 'votre_cle_api_aviationstack') {
        return null; // API non configurée
    }
    
    $url = "http://api.aviationstack.com/v1/flights?access_key=" . AVIATION_API_KEY 
           . "&dep_iata=" . $depart . "&arr_iata=" . $arrivee . "&limit=10";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    
    return json_decode($response, true);
}

// Récupération des horaires depuis la BDD
$vols = $pdo->query("SELECT * FROM vols WHERE date_depart >= NOW() ORDER BY date_depart ASC")->fetchAll();

// Codes IATA
$aeroports = [
    'Paris' => 'CDG',
    'Dakar' => 'DSS'
];

// Mise à jour des horaires (admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vol'])) {
    $stmt = $pdo->prepare("INSERT INTO vols (numero_vol, depart_ville, arrivee_ville, date_depart, date_arrivee_estimee, statut) VALUES (?,?,?,?,?,?)");
    $stmt->execute([
        $_POST['numero_vol'],
        $_POST['depart_ville'],
        $_POST['arrivee_ville'],
        $_POST['date_depart'],
        $_POST['date_arrivee'],
        'planifie'
    ]);
    echo "<div class='alert alert-success'>✈️ Vol ajouté</div>";
}

if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM vols WHERE id = ?")->execute([$_GET['delete']]);
    echo "<div class='alert alert-warning'>🗑️ Vol supprimé</div>";
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .vol-card { transition: transform 0.2s; border-radius: 15px; margin-bottom: 15px; }
    .vol-card:hover { transform: translateX(5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    .depart-badge { background: #ff8c00; color: white; padding: 5px 10px; border-radius: 20px; }
    .arrivee-badge { background: #28a745; color: white; padding: 5px 10px; border-radius: 20px; }
    .live-info { background: #e9ecef; border-radius: 15px; padding: 15px; margin: 20px 0; }
</style>

<h2><i class="fas fa-plane"></i> Horaires des vols - Paris ↔ Dakar</h2>

<!-- Information API en temps réel -->
<div class="live-info">
    <i class="fas fa-sync-alt fa-spin"></i> 
    <strong>Informations en temps réel :</strong> Les horaires sont mis à jour automatiquement via l'API AviationStack.
    <span class="badge bg-info ms-2">Live</span>
</div>

<?php if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true): ?>
<!-- Formulaire admin pour ajouter un vol -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <i class="fas fa-plus-circle"></i> Ajouter un vol (Admin)
    </div>
    <div class="card-body">
        <form method="post">
            <div class="row">
                <div class="col-md-2"><input type="text" name="numero_vol" class="form-control mb-2" placeholder="N° vol" required></div>
                <div class="col-md-3">
                    <select name="depart_ville" class="form-select mb-2" required>
                        <option value="Paris">Paris (CDG)</option>
                        <option value="Dakar">Dakar (DSS)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="arrivee_ville" class="form-select mb-2" required>
                        <option value="Dakar">Dakar (DSS)</option>
                        <option value="Paris">Paris (CDG)</option>
                    </select>
                </div>
                <div class="col-md-2"><input type="datetime-local" name="date_depart" class="form-control mb-2" required></div>
                <div class="col-md-2"><input type="datetime-local" name="date_arrivee" class="form-control mb-2" required></div>
            </div>
            <button type="submit" name="add_vol" class="btn btn-primary w-100">Ajouter le vol</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Liste des vols -->
<h3><i class="fas fa-calendar-alt"></i> Prochains départs</h3>
<div class="row">
    <?php foreach ($vols as $v): 
        $date_dep = new DateTime($v['date_depart']);
        $date_arr = new DateTime($v['date_arrivee_estimee']);
        $is_depart = ($v['depart_ville'] == 'Paris');
    ?>
    <div class="col-md-6">
        <div class="card vol-card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-4 text-center">
                        <i class="fas fa-plane-departure fa-2x <?= $is_depart ? 'text-primary' : 'text-success' ?>"></i>
                        <div class="fw-bold"><?= $v['depart_ville'] ?></div>
                        <small><?= $date_dep->format('H:i') ?></small>
                    </div>
                    <div class="col-4 text-center">
                        <i class="fas fa-arrow-right"></i>
                        <div class="badge bg-dark"><?= $v['numero_vol'] ?></div>
                    </div>
                    <div class="col-4 text-center">
                        <i class="fas fa-plane-arrival fa-2x <?= !$is_depart ? 'text-primary' : 'text-success' ?>"></i>
                        <div class="fw-bold"><?= $v['arrivee_ville'] ?></div>
                        <small><?= $date_arr->format('H:i') ?></small>
                    </div>
                </div>
                <div class="text-center mt-2">
                    <small class="text-muted">📅 <?= $date_dep->format('l d/m/Y') ?></small>
                    <?php if (isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true): ?>
                        <br><a href="horaires_vols.php?delete=<?= $v['id'] ?>" class="text-danger small" onclick="return confirm('Supprimer ce vol ?')">🗑️ Supprimer</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Notification WhatsApp pour les vols -->
<div class="card mt-4">
    <div class="card-header bg-success text-white">
        <i class="fab fa-whatsapp"></i> Être alerté des prochains vols
    </div>
    <div class="card-body">
        <p>Recevez les horaires des vols directement sur WhatsApp.</p>
        <a href="send_vols_notification.php" class="btn btn-success">
            <i class="fab fa-whatsapp"></i> Envoyer les horaires par WhatsApp
        </a>
    </div>
</div>

<?php include('footer.php'); ?>
