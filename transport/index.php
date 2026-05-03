<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMEGA Transport - Transport scolaire sécurisé à Dakar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #003366 0%, #006699 100%);
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        .slogan-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            height: 100%;
        }
        .slogan-card:hover { transform: translateY(-10px); }
        .slogan-card i { font-size: 48px; margin-bottom: 15px; }
        .tarif-card {
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            transition: transform 0.3s;
            height: 100%;
        }
        .tarif-card:hover { transform: translateY(-5px); }
        .tarif-card.blue { background: linear-gradient(135deg, #003366 0%, #006699 100%); color: white; }
        .tarif-card.yellow { background: linear-gradient(135deg, #ff9900 0%, #ffcc00 100%); color: #003366; }
        .tarif-card .prix { font-size: 2.5rem; font-weight: bold; margin: 20px 0; }
        .btn-omega { background: #ff9900; color: #003366; border-radius: 30px; padding: 12px 30px; font-weight: bold; }
        .contact-bar {
            background: #f8f9fa;
            padding: 20px 0;
            border-bottom: 3px solid #ff9900;
        }
        .info-inscription {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
<?php include_once 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Transport scolaire sécurisé à Dakar</h1>
        <p class="lead">OMEGA CONSULTING - La fiabilité au service de l'éducation</p>
        <div class="mt-4">
            <a href="modules/parents/inscription_parent.php" class="btn btn-omega btn-lg">
                <i class="fas fa-user-plus"></i> Inscrire mon enfant
            </a>
            <a href="modules/parents/login_parent.php" class="btn btn-outline-light btn-lg ms-3">
                <i class="fas fa-sign-in-alt"></i> Espace Parent
            </a>
        </div>
    </div>
</section>

<!-- Slogans -->
<div class="container mt-5">
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="slogan-card">
                <i class="fas fa-shield-alt" style="color: #003366;"></i>
                <h4>Sécurité</h4>
                <p>Suivi GPS en temps réel<br>Chauffeurs expérimentés</p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="slogan-card">
                <i class="fas fa-clock" style="color: #ff9900;"></i>
                <h4>Ponctualité</h4>
                <p>Respect strict des horaires<br>Matin: 06h30-08h30 | Soir: 16h00-18h30</p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="slogan-card">
                <i class="fas fa-credit-card" style="color: #28a745;"></i>
                <h4>Paiements simplifiés</h4>
                <p>Wave • Orange Money • Espèces<br>Paiement mensuel ou trimestriel</p>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="slogan-card">
                <i class="fas fa-map-marked-alt" style="color: #dc3545;"></i>
                <h4>Trajet Matin/Soir</h4>
                <p>Prise en charge domicile<br>Dépose à l'école et retour</p>
            </div>
        </div>
    </div>
</div>

<!-- Tarifs -->
<div class="container mt-5">
    <h2 class="text-center mb-4">Nos tarifs 2025</h2>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="tarif-card blue">
                <i class="fas fa-bus" style="font-size: 48px;"></i>
                <h3 class="mt-3">Moins de 5 km</h3>
                <div class="prix">15 000 FCFA</div>
                <p>/mois</p>
                <hr style="background: white;">
                <p><i class="fas fa-check-circle"></i> Trajet matin/soir</p>
                <p><i class="fas fa-check-circle"></i> Suivi GPS en temps réel</p>
                <p><i class="fas fa-check-circle"></i> Notification parents</p>
                <p><i class="fas fa-check-circle"></i> Code unique élève</p>
                <button class="btn btn-light mt-3" onclick="window.location.href='modules/parents/inscription_parent.php'">S'inscrire</button>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="tarif-card yellow">
                <i class="fas fa-bus" style="font-size: 48px;"></i>
                <h3 class="mt-3">5 km - 10 km</h3>
                <div class="prix">20 000 FCFA</div>
                <p>/mois</p>
                <hr>
                <p><i class="fas fa-check-circle"></i> Trajet matin/soir</p>
                <p><i class="fas fa-check-circle"></i> Suivi GPS en temps réel</p>
                <p><i class="fas fa-check-circle"></i> Notification parents</p>
                <p><i class="fas fa-check-circle"></i> Assurance incluse</p>
                <button class="btn btn-primary mt-3" onclick="window.location.href='modules/parents/inscription_parent.php'">S'inscrire</button>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="tarif-card blue">
                <i class="fas fa-bus" style="font-size: 48px;"></i>
                <h3 class="mt-3">10 km - 20 km</h3>
                <div class="prix">25 000 FCFA</div>
                <p>/mois</p>
                <hr style="background: white;">
                <p><i class="fas fa-check-circle"></i> Trajet matin/soir</p>
                <p><i class="fas fa-check-circle"></i> Suivi GPS prioritaire</p>
                <p><i class="fas fa-check-circle"></i> Cadeau de fin d'année</p>
                <p><i class="fas fa-check-circle"></i> Priorité en cas d'urgence</p>
                <button class="btn btn-light mt-3" onclick="window.location.href='modules/parents/inscription_parent.php'">S'inscrire</button>
            </div>
        </div>
    </div>
</div>

<!-- Informations inscriptions -->
<div class="container mt-5">
    <div class="info-inscription text-center">
        <div class="row">
            <div class="col-md-6">
                <h4><i class="fas fa-calendar-alt"></i> Début des inscriptions</h4>
                <p class="lead"><strong>1er Juin 2025</strong></p>
                <p>Date limite: <strong>30 Septembre 2025</strong></p>
            </div>
            <div class="col-md-6">
                <h4><i class="fas fa-phone-alt"></i> Contacts</h4>
                <p><strong>Secrétariat:</strong> +221 77 654 28 03</p>
                <p><strong>Responsable transport:</strong> +221 78 123 45 67</p>
                <p><strong>Urgence:</strong> +221 70 987 65 43</p>
            </div>
        </div>
    </div>
</div>

<!-- Contact bar -->
<div class="contact-bar mt-5">
    <div class="container text-center">
        <div class="row">
            <div class="col-md-4">
                <i class="fas fa-phone"></i> <strong>Appels:</strong> +221 77 654 28 03
            </div>
            <div class="col-md-4">
                <i class="fab fa-whatsapp"></i> <strong>WhatsApp:</strong> +221 77 654 28 03
            </div>
            <div class="col-md-4">
                <i class="fas fa-envelope"></i> <strong>Email:</strong> transport@omega-consulting.sn
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>
</body>
</html>
