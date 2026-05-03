<?php
session_start();
include_once "db_connect.php";
include_once "header.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$res = $conn->query("SELECT * FROM voitures WHERE id = $id");
$row = $res->fetch_assoc();

if (!$row) { echo "<div class='container mt-5 alert alert-danger'>Véhicule introuvable.</div>"; exit(); }

// Requête unifiée pour la galerie
$photos = $conn->query("SELECT image_url as chemin FROM galerie WHERE voiture_id = $id
                        UNION
                        SELECT chemin_image as chemin FROM images_voitures WHERE voiture_id = $id");

$v_nom = strtoupper($row['marque'] . " " . $row['modele']);

// --- CONSTRUCTION DU MESSAGE WHATSAPP AVEC TOUS LES ATTRIBUTS ---
$msg_wa = "Bonjour OMEGA AUTO, je souhaite réserver :\n";
$msg_wa .= "🚗 *" . $v_nom . "*\n";
$msg_wa .= "⚙️ Boîte: " . ($row['boite_vitesse'] ?? 'N/A') . "\n";
$msg_wa .= "⛽ Énergie: " . ($row['carburant'] ?? 'N/A') . "\n";
$msg_wa .= "👥 Places: " . ($row['nb_places'] ?? '5') . "\n";
$msg_wa .= "💰 Prix: " . number_format($row['prix_journalier'], 0, ',', ' ') . " FCFA";

$url_whatsapp = "https://wa.me/221776542803?text=" . urlencode($msg_wa);
?>

<div class="container my-5">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
                <img src="<?php echo $row['image_url']; ?>" class="img-fluid w-100" style="height:450px; object-fit:cover;" onerror="this.src='https://via.placeholder.com/800x500?text=Omega+Auto'">
            </div>

            <h5 class="fw-bold mb-3 mt-4"><i class="fas fa-images text-primary me-2"></i>Galerie photos du véhicule</h5>
            <div class="row g-2">
                <?php while($p = $photos->fetch_assoc()): ?>
                    <div class="col-3">
                        <img src="<?php echo $p['chemin']; ?>" class="img-fluid rounded-3 border" style="height:110px; width:100%; object-fit:cover; cursor:pointer;" onclick="window.open(this.src)">
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 sticky-top" style="top:20px;">
                <h1 class="fw-bold h2"><?php echo $v_nom; ?></h1>
                <h2 class="text-primary fw-bold mb-4"><?php echo number_format($row['prix_journalier'], 0, ',', ' '); ?> FCFA <small class="text-muted h6">/ Jour</small></h2>

                <div class="row text-center mb-4 g-2">
                    <div class="col-4"><div class="p-2 border rounded bg-light"><i class="fas fa-cog d-block text-primary"></i><strong><?php echo $row['boite_vitesse']; ?></strong></div></div>
                    <div class="col-4"><div class="p-2 border rounded bg-light"><i class="fas fa-gas-pump d-block text-primary"></i><strong><?php echo $row['carburant']; ?></strong></div></div>
                    <div class="col-4"><div class="p-2 border rounded bg-light"><i class="fas fa-users d-block text-primary"></i><strong><?php echo $row['nb_places']; ?> Pl.</strong></div></div>
                </div>

                <a href="<?php echo $url_whatsapp; ?>" target="_blank" class="btn btn-success btn-lg w-100 py-3 fw-bold rounded-3 shadow">
                    <i class="fab fa-whatsapp me-2"></i> RÉSERVER SUR WHATSAPP
                </a>
                
                <p class="text-center mt-3"><a href="index.php" class="text-muted text-decoration-none">← Retour au catalogue</a></p>
            </div>
        </div>
    </div>
</div>
<?php include_once "footer.php"; ?>
