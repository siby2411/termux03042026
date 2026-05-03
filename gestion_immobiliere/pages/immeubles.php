<?php
$immeubles = $pdo->query("SELECT i.*, z.nom as zone_nom FROM immeubles i LEFT JOIN zones_geographiques z ON i.zone_id = z.id ORDER BY i.id DESC")->fetchAll();
?>

<div class="row g-4">
    <?php foreach($immeubles as $i): ?>
    <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 overflow-hidden">
            <div class="position-relative" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalImg<?= $i['id'] ?>">
                <img src="<?= $i['image_url'] ?>" class="card-img-top" style="height: 220px; object-fit: cover;" alt="<?= $i['titre'] ?>">
                <div class="position-absolute top-0 end-0 m-2">
                    <span class="badge <?= $i['statut'] == 'Disponible' ? 'bg-success' : 'bg-danger' ?> shadow">
                        <?= $i['statut'] ?>
                    </span>
                </div>
            </div>
            
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title fw-bold text-uppercase mb-0" style="font-family: 'Playfair Display', serif;"><?= $i['titre'] ?></h5>
                </div>
                <p class="text-muted small mb-3">
                    <i class="bi bi-geo-alt-fill text-danger"></i> <?= $i['zone_nom'] ?> 
                    <span class="mx-2">|</span> 
                    <i class="bi bi-rulers"></i> <?= $i['surface'] ?> m²
                </p>
                <h4 class="text-warning fw-bold mb-3"><?= number_format($i['prix'], 0, ',', ' ') ?> <small class="fs-6 text-muted">FCFA</small></h4>
                
                <div class="d-grid gap-2">
                    <a href="?page=visites&i_id=<?= $i['id'] ?>" class="btn btn-dark btn-sm rounded-pill">
                        <i class="bi bi-calendar-check"></i> Programmer une visite
                    </a>
                    
                    <?php
                    // Préparation du message WhatsApp
                    $message_wa = "Bonjour, voici un bien qui pourrait vous intéresser chez OMEGA IMMO : \n\n" 
                                . "🏠 *".$i['titre']."*\n"
                                . "📍 Localisation : ".$i['zone_nom']."\n"
                                . "💰 Prix : ".number_format($i['prix'], 0, ',', ' ')." FCFA\n\n"
                                . "Plus d'infos sur Omega Consulting.";
                    $wa_url = "https://api.whatsapp.com/send?text=" . urlencode($message_wa);
                    ?>
                    <a href="<?= $wa_url ?>" target="_blank" class="btn btn-success btn-sm rounded-pill">
                        <i class="bi bi-whatsapp"></i> Partager sur WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalImg<?= $i['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white"><?= $i['titre'] ?> - <?= $i['zone_nom'] ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0 text-center">
                    <img src="<?= $i['image_url'] ?>" class="img-fluid rounded shadow-lg" style="max-height: 85vh; border: 3px solid var(--gold);">
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<style>
    .card { transition: transform 0.3s ease, box-shadow 0.3s ease; }
    .card:hover { transform: translateY(-10px); box-shadow: 0 15px 35px rgba(0,0,0,0.2) !important; }
    .btn-success { background-color: #25D366; border: none; }
    .btn-success:hover { background-color: #128C7E; }
</style>
