<?php
$message = "";
// Récupération des zones pour le menu déroulant
$zones = $pdo->query("SELECT * FROM zones_geographiques ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titre = $_POST['titre'];
    $zone_id = $_POST['zone_id'];
    $prix = $_POST['prix'];
    $surface = $_POST['surface'];
    $description = $_POST['description'];
    
    // Gestion de l'Upload d'image
    $image_path = "https://images.unsplash.com/photo-1580587771525-78b9dba3b914?q=80&w=1000"; // Image par défaut
    
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "uploads/biens/";
        $file_extension = pathinfo($_FILES["photo"]["name"], PATHINFO_EXTENSION);
        $file_name = "omega_" . time() . "." . $file_extension;
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
            $image_path = $target_file;
        }
    }

    try {
        $sql = "INSERT INTO immeubles (titre, zone_id, prix, surface, description, image_url, statut) 
                VALUES (?, ?, ?, ?, ?, ?, 'Disponible')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$titre, $zone_id, $prix, $surface, $description, $image_path]);
        $message = "<div class='alert alert-success'>✅ Immeuble ajouté avec succès !</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert alert-danger'>❌ Erreur : " . $e->getMessage() . "</div>";
    }
}
?>

<div class="card shadow-lg border-0">
    <div class="card-header bg-dark text-warning p-4">
        <h4 class="mb-0 fw-bold"><i class="bi bi-house-add"></i> Nouvel Enregistrement Immobilier</h4>
    </div>
    <div class="card-body p-4">
        <?= $message ?>
        
        <form action="" method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-8">
                <label class="form-label fw-bold">Nom du Bien / Immeuble</label>
                <input type="text" name="titre" class="form-control" placeholder="ex: Villa de Luxe Almadies" required>
            </div>
            
            <div class="col-md-4">
                <label class="form-label fw-bold">Zone Géographique (Dakar)</label>
                <select name="zone_id" class="form-select" required>
                    <option value="">Choisir la zone...</option>
                    <?php foreach($zones as $z): ?>
                        <option value="<?= $z['id'] ?>"><?= $z['nom'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Prix de Vente (FCFA)</label>
                <input type="number" name="prix" class="form-control" placeholder="ex: 150000000" required>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold">Surface (m²)</label>
                <input type="number" name="surface" class="form-control" placeholder="ex: 300" required>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-bold text-primary">Photo du Bien (Upload)</label>
                <input type="file" name="photo" class="form-control" accept="image/*" required>
            </div>

            <div class="col-12">
                <label class="form-label fw-bold">Description Marketing</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Décrivez les atouts du bien..."></textarea>
            </div>

            <div class="col-12 text-end mt-4">
                <button type="reset" class="btn btn-outline-secondary px-4">Réinitialiser</button>
                <button type="submit" class="btn btn-warning px-5 fw-bold shadow">Publier le Bien</button>
            </div>
        </form>
    </div>
</div>

<div class="mt-4 text-center">
    <p class="text-muted small">OMEGA INFORMATIQUE CONSULTING - Système d'Archivage Digital</p>
</div>
