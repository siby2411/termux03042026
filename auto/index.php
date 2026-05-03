<?php
include_once 'db_connect.php';
include_once 'header.php'; // Intégration de la bannière Omega Consulting

// Récupération plus large pour s'assurer que les voitures s'affichent
$voitures_loc = $conn->query("SELECT * FROM voitures WHERE type_usage='Location' LIMIT 6");
$voitures_vente = $conn->query("SELECT * FROM voitures WHERE type_usage='Vente' LIMIT 6");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Omega Auto | Excellence Automobile</title>
    <style>
        :root { --primary: #D4AF37; --dark: #0f172a; --blue-accent: #2563eb; }
        
        /* Ajustement de la bannière Hero (Taille diminuée) */
        .hero-compact { 
            height: 350px; 
            background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&q=80&w=1200'); 
            background-size: cover; 
            background-position: center; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            text-align: center; 
            color: white;
            border-bottom: 3px solid var(--primary);
        }

        .car-card { 
            background: white; 
            border-radius: 15px; 
            overflow: hidden; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            transition: 0.3s; 
            border: 1px solid #e2e8f0;
        }
        .car-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .car-img { width: 100%; height: 200px; object-fit: cover; }
        .car-body { padding: 20px; }
        .price-tag { color: var(--blue-accent); font-weight: 800; font-size: 1.2rem; }
        .badge-type { background: var(--dark); color: var(--primary); font-size: 0.7rem; padding: 4px 10px; border-radius: 5px; text-transform: uppercase; font-weight: bold; }
        
        .section-title { border-left: 5px solid var(--primary); padding-left: 15px; margin-bottom: 30px; }
    </style>
</head>
<body>

    <header class="hero-compact">
        <div class="container">
            <h1 class="display-4 fw-bold">L'Excellence Automobile</h1>
            <p class="lead opacity-75">Location premium et gestion de parc par OMEGA INFORMATIQUE CONSULTING</p>
            <div class="mt-3">
                <a href="#location" class="btn btn-primary px-4 py-2 fw-bold shadow">Réserver un véhicule</a>
            </div>
        </div>
    </header>

    <div class="container my-5" id="location">
        <div class="section-title">
            <h2 class="fw-bold mb-0">Parc de Location</h2>
            <p class="text-muted">Sélection de véhicules disponibles à Dakar</p>
        </div>

        <div class="row g-4">
            <?php if($voitures_loc && $voitures_loc->num_rows > 0): ?>
                <?php while($row = $voitures_loc->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="car-card">
                        <img src="<?php echo $row['image_url']; ?>" class="car-img" onerror="this.src='https://via.placeholder.com/400x250?text=Omega+Auto'">
                        <div class="car-body">
                            <span class="badge-type">Location</span>
                            <h4 class="fw-bold mt-2 mb-1"><?php echo $row['marque']." ".$row['modele']; ?></h4>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="price-tag"><?php echo number_format($row['prix_journalier'], 0, ',', ' '); ?> <small style="font-size:0.6rem; color:#64748b;">FCFA/J</small></div>
                                <a href="detail_voiture.php?id=<?php echo $row['id']; ?>" class="btn btn-dark btn-sm rounded-3 px-3">Détails</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12"><div class="alert alert-light border">Aucun véhicule en location pour le moment.</div></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="container my-5 pt-4" id="vente">
        <div class="section-title">
            <h2 class="fw-bold mb-0">Véhicules en Vente</h2>
            <p class="text-muted">Opportunités d'achat OMEGA CONSULTING</p>
        </div>

        <div class="row g-4">
            <?php if($voitures_vente && $voitures_vente->num_rows > 0): ?>
                <?php while($row = $voitures_vente->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="car-card">
                        <img src="<?php echo $row['image_url']; ?>" class="car-img" onerror="this.src='https://via.placeholder.com/400x250?text=Vente+Omega'">
                        <div class="car-body">
                            <span class="badge-type" style="background:#059669; color:white;">Vente</span>
                            <h4 class="fw-bold mt-2 mb-1"><?php echo $row['marque']." ".$row['modele']; ?></h4>
                            <p class="small text-muted mb-3"><i class="fas fa-check-circle me-1"></i> Expertise technique certifiée</p>
                            <a href="detail_voiture.php?id=<?php echo $row['id']; ?>" class="btn btn-outline-primary w-100 fw-bold">Demander un Devis</a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12"><p class="text-muted italic text-center">Le catalogue de vente sera bientôt disponible.</p></div>
            <?php endif; ?>
        </div>
    </div>

    <?php include_once 'footer.php'; ?>

</body>
</html>
