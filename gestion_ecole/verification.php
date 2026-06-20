<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();
$id = intval($_GET['id'] ?? 0);

// Récupération des données étudiant
$query = "SELECT e.*, c.nom_class, c.montant_scolarite FROM etudiants e JOIN classes c ON e.classe_id = c.id WHERE e.id = $id";
$res = $conn->query($query);
$e = $res->fetch_assoc();

if (!$e) { die("<div class='container mt-5 alert alert-danger'>Étudiant introuvable.</div>"); }

// Calculs financiers
$code = $e['code_etudiant'];
$scolarite_mensuelle = $e['montant_scolarite'];
$mois_inscription = (int)date('m', strtotime($e['date_inscription'] ?? '2025-10-01'));
$nb_mois_du = (6 >= $mois_inscription) ? (9 - ($mois_inscription - 10)) : 9;
$total_du = $nb_mois_du * $scolarite_mensuelle;
$total_verse = ($conn->query("SELECT SUM(montant_verse) FROM paiements_inscription WHERE code_etudiant='$code'")->fetch_row()[0] ?? 0) + ($conn->query("SELECT SUM(montant_verse) FROM paiements_scolarite WHERE etudiant_id=$id")->fetch_row()[0] ?? 0);
$reste_a_payer = $total_du - $total_verse;

include 'header_ecole.php';
?>
<div class="container mt-4">
    <div class="p-4 mb-4 bg-dark text-white rounded shadow-sm text-center border-bottom border-warning border-4">
        <h2 class="text-warning">ESPACE SUIVI PARENT</h2>
        <h4><?= htmlspecialchars($e['nom'].' '.$e['prenom']) ?></h4>
        
        <div class="bg-white p-2 mt-3 d-inline-block shadow" id="qrcode"></div>
        <p class="mt-2 small text-white-50">Scannez pour vérifier l'authenticité</p>
        
        <div class="mt-3">
            <a href="bulletin.php?id=<?= $id ?>" class="btn btn-warning btn-lg fw-bold shadow-sm">
                <i class="bi bi-file-earmark-bar-graph"></i> Consulter le Bulletin
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white fw-bold"><i class="bi bi-wallet2"></i> Traçabilité Financière</div>
                <div class="card-body">
                    <table class="table table-hover">
                        <tr><td>Total Dû</td><td class="text-end fw-bold"><?= number_format($total_du, 0, ',', ' ') ?> FCFA</td></tr>
                        <tr><td>Total Versé</td><td class="text-end text-success fw-bold"><?= number_format($total_verse, 0, ',', ' ') ?> FCFA</td></tr>
                        <tr class="table-danger"><td>Reste à payer</td><td class="text-end fw-bold text-danger"><?= number_format($reste_a_payer, 0, ',', ' ') ?> FCFA</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-secondary text-white fw-bold"><i class="bi bi-people-fill"></i> Corps Enseignant</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php
                        $sql = "SELECT p.nom, p.prenom, p.telephone, u.nom_uv
                                FROM affectations a
                                JOIN professeurs p ON a.prof_id = p.id_prof
                                JOIN uvs u ON a.uv_id = u.id
                                WHERE a.classe_id = {$e['classe_id']}
                                GROUP BY u.nom_uv";
                        $profs = $conn->query($sql);
                        while($p = $profs->fetch_assoc()): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div><strong><?= htmlspecialchars($p['nom_uv']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($p['nom'].' '.$p['prenom']) ?></small></div>
                            <a href="tel:<?= str_replace(' ', '', $p['telephone']) ?>" class="btn btn-sm btn-outline-info"><i class="bi bi-telephone"></i></a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. On récupère le nom d'hôte de la page actuelle (ex: root-pull-neighborhood-remember.trycloudflare.com)
    var host = window.location.hostname;
    
    // 2. Si on est sur localhost, on affiche une alerte pour vous prévenir que le scan ne marchera pas
    if(host === "localhost" || host === "127.0.0.1") {
        console.warn("Attention : Vous utilisez localhost, le QR code ne sera pas scannable par des appareils externes.");
    }

    // 3. On construit l'URL avec le host dynamique (protocole + hôte public)
    var fullUrl = window.location.protocol + "//" + host + "/verification.php?id=<?= $id ?>";
    
    console.log("URL encodée dans le QR : " + fullUrl);

    // 4. Génération avec un niveau de correction d'erreur élevé pour Google Lens
    new QRCode(document.getElementById("qrcode"), {
        text: fullUrl,
        width: 180,
        height: 180,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H 
    });
</script>

<?php include 'footer_ecole.php'; ?>
