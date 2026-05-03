<?php include('header.php'); ?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .hero-section { background: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460); color: white; padding: 50px 30px; border-radius: 20px; text-align: center; margin-bottom: 30px; }
    .hero-section h1 { font-size: 2.5rem; font-weight: 800; }
    .hero-section h1 span { color: #ff8c00; }
    .hero-section .btn-hero { background: #ff8c00; color: #1a1a2e; padding: 12px 25px; border-radius: 30px; margin: 10px; font-weight: bold; text-decoration: none; display: inline-block; }
    .hero-section .btn-hero-outline { background: transparent; border: 2px solid #ff8c00; color: #ff8c00; padding: 12px 25px; border-radius: 30px; margin: 10px; font-weight: bold; text-decoration: none; display: inline-block; }
    .vol-banner { background: linear-gradient(135deg, #0a2b44, #1e4a76); border-radius: 15px; padding: 20px; margin-bottom: 30px; color: white; }
    .slogan-boutique { text-align: center; padding: 20px; margin: 20px 0; background: #f8f9fa; border-radius: 15px; }
    .boutique-row { margin-bottom: 30px; }
    .boutique-card { transition: transform 0.3s; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); height: 100%; }
    .boutique-card:hover { transform: translateY(-10px); }
    .showroom-card { background: linear-gradient(135deg, #ff8c00, #ffaa33); color: #1a1a2e; border-radius: 15px; padding: 25px; text-align: center; margin: 30px 0; }
    .offre-table { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin: 30px 0; }
    .offre-table th { background: #ff8c00; color: white; padding: 12px; }
    .offre-table td { padding: 10px; border-bottom: 1px solid #eee; }
    .partner-section { text-align: center; padding: 20px; background: #f8f9fa; border-radius: 15px; margin-top: 30px; }
</style>

<div class="hero-section">
    <h1>Dieynaba <span>GP Holding</span></h1>
    <p class="lead">Le pont entre l’Afrique et l’Europe – Transport international, E-commerce & Services</p>
    <a href="suivi.php" class="btn-hero"><i class="fas fa-search"></i> 📦 Suivez votre colis</a>
    <a href="creer_colis_holding.php" class="btn-hero-outline"><i class="fas fa-paper-plane"></i> ✈️ Envoyer un colis</a>
</div>

<!-- Bannière des vols -->
<div class="vol-banner">
    <div class="row text-center align-items-center">
        <div class="col-md-5">
            <i class="fas fa-plane-departure fa-3x text-primary"></i>
            <h3 class="text-danger mt-2">✈️ Départ de Paris</h3>
            <p class="fw-bold text-danger">📅 Tous les Mardis</p>
            <small>Vers Dakar, Sénégal</small>
        </div>
        <div class="col-md-2">
            <i class="fas fa-arrow-right fa-2x"></i>
            <i class="fas fa-arrow-left fa-2x mt-3"></i>
        </div>
        <div class="col-md-5">
            <i class="fas fa-plane-arrival fa-3x text-primary"></i>
            <h3 class="text-info mt-2">✈️ Retour de Dakar</h3>
            <p class="fw-bold text-info">📅 Tous les Jeudis</p>
            <small>Vers Paris, France</small>
        </div>
    </div>
    <p class="text-center mt-3 mb-0"><i class="fas fa-info-circle"></i> Transport de marchandises et colis – Profitez de nos vols réguliers</p>
</div>

<!-- Cartes de services -->
<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card text-center border-warning boutique-card">
            <div class="card-body">
                <i class="fas fa-ship fa-3x text-warning"></i>
                <h5 class="mt-2">Groupage de marchandises</h5>
                <p>Dakar ↔ Paris, Lyon, Marseille. Tarifs compétitifs, dédouanement inclus.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-success boutique-card">
            <div class="card-body">
                <i class="fas fa-map-marked-alt fa-3x text-success"></i>
                <h5 class="mt-2">Suivi GPS en temps réel</h5>
                <p>Localisez votre colis à chaque étape, alertes WhatsApp automatiques.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center border-info boutique-card">
            <div class="card-body">
                <i class="fas fa-shopping-basket fa-3x text-info"></i>
                <h5 class="mt-2">4 Boutiques intégrées</h5>
                <p>Épicerie, Mode, Joaillerie, Négoce – Livraison France & Sénégal.</p>
            </div>
        </div>
    </div>
</div>

<!-- Slogans des 4 boutiques -->
<div class="slogan-boutique">
    <h3><i class="fas fa-store"></i> Nos boutiques en ligne</h3>
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card border-success boutique-card">
                <div class="card-body">
                    <i class="fas fa-shopping-basket fa-2x text-success"></i>
                    <h5>🛒 Épicerie</h5>
                    <p class="small">"Les saveurs authentiques du Sénégal livrées chez vous"</p>
                    <a href="produits.php" class="btn btn-sm btn-success">Découvrir</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary boutique-card">
                <div class="card-body">
                    <i class="fas fa-tshirt fa-2x text-primary"></i>
                    <h5>👗 Mode</h5>
                    <p class="small">"L'élégance africaine pour toutes les occasions"</p>
                    <a href="vetements.php" class="btn btn-sm btn-primary">Découvrir</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning boutique-card">
                <div class="card-body">
                    <i class="fas fa-gem fa-2x text-warning"></i>
                    <h5>💎 Joaillerie</h5>
                    <p class="small">"L'excellence et l'élégance à portée de main"</p>
                    <a href="bijouterie.php" class="btn btn-sm btn-warning">Découvrir</a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info boutique-card">
                <div class="card-body">
                    <i class="fas fa-laptop fa-2x text-info"></i>
                    <h5>🏪 Négoce</h5>
                    <p class="small">"Des produits haut de gamme à prix défiant toute concurrence"</p>
                    <a href="negoce.php" class="btn btn-sm btn-info">Découvrir</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tableau des offres -->
<div class="offre-table">
    <table class="table table-bordered mb-0">
        <thead>
            <tr><th colspan="2" class="text-center bg-warning">🌍 Offre spéciale – Printemps 2026</th></tr>
        </thead>
        <tbody>
            <tr><td>Expédition d’un colis (&lt;5 kg)</td><td class="fw-bold">45 €</td></tr>
            <tr><td>Expédition d’un colis (5-10 kg)</td><td class="fw-bold">65 €</td></tr>
            <tr><td>Lot découverte (3 produits)</td><td class="fw-bold">29 € (livraison incluse)</td></tr>
            <tr><td>Abonnement annuel (10 colis)</td><td class="fw-bold">350 € (35 €/colis)</td></tr>
        </tbody>
    </table>
    <div class="text-center p-3">
        <a href="creer_colis_holding.php" class="btn btn-warning">Profiter de l'offre</a>
        <a href="produits.php" class="btn btn-outline-warning">Découvrir la boutique</a>
    </div>
</div>

<!-- Showroom Hann Maristes -->
<div class="showroom-card">
    <i class="fas fa-store fa-3x"></i>
    <h3>📍 Visitez notre showroom à Hann Maristes</h3>
    <p>À côté de l'École Franco-Japonaise - Dakar, Sénégal</p>
    <p><i class="fas fa-calendar-alt"></i> Ouvert du Lundi au Samedi - 9h à 19h</p>
    <p><i class="fas fa-phone-alt"></i> 📞 Contact: +221 77 654 28 03 | +33 7 58 68 63 48</p>
    <p class="mb-0"><strong>✨ Des produits haut de gamme à des prix qui défient toute concurrence ✨</strong></p>
</div>

<!-- Présentation -->
<div class="card mb-4 bg-light">
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <h3><i class="fas fa-handshake"></i> Pourquoi Dieynaba GP Holding ?</h3>
                <p>Basée en France et au Sénégal, notre entreprise facilite les échanges entre les deux continents depuis 2022. Chaque colis est tracé individuellement, chaque produit est sélectionné auprès de producteurs locaux.</p>
                <p><strong>Engagements :</strong> transparence, rapidité (48-72h), innovation (API WhatsApp, QR code, PDF).</p>
            </div>
            <div class="col-md-4 text-center">
                <i class="fas fa-plane-departure fa-4x text-primary"></i>
                <i class="fas fa-arrow-right fa-2x"></i>
                <i class="fas fa-plane-arrival fa-4x text-primary"></i>
                <p><strong>Pont Afrique ↔ Europe</strong></p>
            </div>
        </div>
    </div>
</div>

<!-- Partenaires -->
<div class="partner-section">
    <h5>Ils nous font confiance :</h5>
    <div>
        <img src="sponsor/logo1.png" class="mx-2" style="max-height:60px" onerror="this.src='https://placehold.co/120x60?text=DHL'">
        <img src="sponsor/logo2.png" class="mx-2" style="max-height:60px" onerror="this.src='https://placehold.co/120x60?text=Air+France'">
        <img src="sponsor/logo3.png" class="mx-2" style="max-height:60px" onerror="this.src='https://placehold.co/120x60?text=La+Poste'">
    </div>
    <p class="mt-2 small">DHL, Air France Cargo, La Poste Sénégal – partenaires officiels</p>
</div>

<?php include('footer.php'); ?>
