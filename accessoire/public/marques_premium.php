<?php
// /var/www/piece_auto/public/marques_premium.php

include '../config/Database.php';
// Nous incluons auth_check.php si vous avez rétabli la sécurité. 
// Si la sécurité est désactivée, c'est pour simuler la session admin.
include '../includes/auth_check.php'; 
include '../includes/header.php'; 
$page_title = "Nos Marques de Haute Qualité";

$database = new Database();
$db = $database->getConnection(); 
$marques = [];
$error_message = "";

// CORRECTION SQL : Sélectionne uniquement les colonnes existantes
$query = "SELECT id_marque, nom_marque, logo_url FROM MARQUES_AUTO ORDER BY nom_marque ASC";
try {
    $stmt = $db->prepare($query);
    $stmt->execute();
    $marques = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Erreur de base de données: " . $e->getMessage();
}

// Fonction utilitaire pour obtenir l'URL de l'image (utilise logo_url si disponible)
function get_brand_image($marque) {
    // Si logo_url est définie et non nulle, on l'utilise
    if (!empty($marque['logo_url'])) {
        return htmlspecialchars($marque['logo_url']);
    }
    // Sinon, on utilise un placeholder basé sur le nom
    $clean_name = str_replace([' ', '-'], '', $marque['nom_marque']);
    return "https://via.placeholder.com/600x400/0d6efd/FFFFFF?text=" . urlencode($clean_name);
}

?>

<div class="row">
    <div class="col-12 text-center">
        <h2 class="mb-3"><i class="fas fa-gem text-warning"></i> Nos Partenaires Premium</h2>
        <p class="lead text-muted">Nous sélectionnons rigoureusement les leaders mondiaux pour garantir la performance et la fiabilité.</p>
    </div>
</div>

<?php if ($error_message): ?>
    <div class="alert alert-danger mt-3"><?= $error_message ?></div>
<?php elseif (empty($marques)): ?>
    <div class="alert alert-info mt-3">Aucune marque trouvée dans la base de données.</div>
<?php else: ?>

<div id="marquesCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-inner shadow-lg rounded-3">
        
        <?php foreach ($marques as $index => $marque): ?>
        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
            <img src="<?= get_brand_image($marque) ?>" class="d-block w-100" alt="<?= $marque['nom_marque'] ?>" style="height: 400px; object-fit: cover;">
            
            <div class="carousel-caption d-none d-md-block" style="background-color: rgba(0, 0, 0, 0.6); padding: 15px; border-radius: 5px;">
                <h3 class="fw-bold"><?= htmlspecialchars($marque['nom_marque']) ?></h3>
                <p>Qualité certifiée. Pièces garanties pour une performance optimale.</p> 
                <a href="../modules/gestion_pieces.php?id_marque=<?= urlencode($marque['id_marque']) ?>" class="btn btn-warning btn-sm mt-2">Voir les Pièces disponibles <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
    
    <button class="carousel-control-prev" type="button" data-bs-target="#marquesCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Précédent</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#marquesCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Suivant</span>
    </button>
    
</div>

<?php endif; ?>

<div class="mt-5">
    <?php 
    // Ici, vous pouvez ajouter d'autres éléments marketing statiques 
    ?>
</div>

<?php 
include '../includes/footer.php'; 
?>
