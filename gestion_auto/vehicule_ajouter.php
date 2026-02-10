<?php
// vehicule_ajouter.php - Version corrigée sans transaction
require_once 'config.php';
$db = Database::getInstance();

try {
    // Récupérer les marques et modèles pour les dropdowns
    $marques = $db->fetchAll("SELECT * FROM marques ORDER BY nom");
    $modeles = $db->fetchAll("SELECT * FROM modeles ORDER BY nom");
    
    // Traitement du formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération et validation des données
        $immatriculation = $_POST['immatriculation'] ?? '';
        $modele_id = $_POST['modele_id'] ?? '';
        $annee_circulation = $_POST['annee_circulation'] ?? '';
        $kilometrage = $_POST['kilometrage'] ?? '';
        $couleur = $_POST['couleur'] ?? '';
        $prix_achat = $_POST['prix_achat'] ?? '';
        $prix_vente = $_POST['prix_vente'] ?? '';
        $prix_location_jour = $_POST['prix_location_jour'] ?? '';
        $type_vehicule = $_POST['type_vehicule'] ?? 'vente';
        $carburant = $_POST['carburant'] ?? '';
        $boite_vitesse = $_POST['boite_vitesse'] ?? '';
        $portes = $_POST['portes'] ?? 5;
        $places = $_POST['places'] ?? 5;
        $puissance = $_POST['puissance'] ?? '';
        $options = $_POST['options'] ?? '';
        $description = $_POST['description'] ?? '';
        
        // Validation basique
        if (empty($immatriculation) || empty($modele_id)) {
            throw new Exception("L'immatriculation et le modèle sont obligatoires");
        }
        
        // Vérifier si l'immatriculation existe déjà
        $existing = $db->fetch("SELECT id FROM vehicules WHERE immatriculation = ?", [$immatriculation]);
        if ($existing) {
            throw new Exception("Cette immatriculation existe déjà");
        }
        
        // Insertion du véhicule (sans date_ajout, laissez MySQL gérer le DEFAULT)
        $sql = "INSERT INTO vehicules (
            immatriculation, modele_id, annee_circulation, kilometrage, 
            couleur, prix_achat, prix_vente, prix_location_jour, type_vehicule,
            carburant, boite_vitesse, portes, places, puissance, options, description
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->query($sql, [
            $immatriculation, $modele_id, $annee_circulation, $kilometrage,
            $couleur, $prix_achat, $prix_vente, $prix_location_jour, $type_vehicule,
            $carburant, $boite_vitesse, $portes, $places, $puissance, $options, $description
        ]);
        
        $vehicule_id = $db->getConnection()->lastInsertId();
        
        // Gestion de l'upload de photo
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            // Fonctions d'upload
            function validateUploadedFile($file) {
                define('MAX_FILE_SIZE', 5 * 1024 * 1024);
                $allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
                
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $error_messages = [
                        UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux',
                        UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux',
                        UPLOAD_ERR_PARTIAL => 'Upload partiel',
                        UPLOAD_ERR_NO_FILE => 'Aucun fichier',
                        UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
                        UPLOAD_ERR_CANT_WRITE => 'Erreur écriture disque',
                        UPLOAD_ERR_EXTENSION => 'Extension non autorisée'
                    ];
                    throw new Exception($error_messages[$file['error']] ?? 'Erreur inconnue');
                }
                
                if ($file['size'] > MAX_FILE_SIZE) {
                    throw new Exception("Fichier trop volumineux (>5MB)");
                }
                
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                
                if (!isset($allowed_types[$mime_type])) {
                    throw new Exception("Type de fichier non autorisé: " . $mime_type);
                }
                
                return [
                    'name' => $file['name'],
                    'tmp_name' => $file['tmp_name'],
                    'size' => $file['size'],
                    'type' => $mime_type,
                    'extension' => $allowed_types[$mime_type]
                ];
            }
            
            function saveUploadedFile($file_info, $subdirectory = '') {
                $extension = strtolower(pathinfo($file_info['name'], PATHINFO_EXTENSION));
                $filename = uniqid() . '_' . time() . '.' . $extension;
                
                if ($subdirectory) {
                    $destination_dir = UPLOAD_DIR . '/' . $subdirectory;
                    if (!file_exists($destination_dir)) {
                        mkdir($destination_dir, 0755, true);
                    }
                    $destination = $destination_dir . '/' . $filename;
                } else {
                    $destination = UPLOAD_DIR . '/' . $filename;
                }
                
                if (!move_uploaded_file($file_info['tmp_name'], $destination)) {
                    throw new Exception("Impossible de sauvegarder le fichier");
                }
                
                return $filename;
            }
            
            try {
                $file_info = validateUploadedFile($_FILES['photo']);
                $filename = saveUploadedFile($file_info, 'vehicules');
                
                // Insertion dans vehicule_images
                $db->query(
                    "INSERT INTO vehicule_images (vehicule_id, nom_fichier, est_principale, date_upload) VALUES (?, ?, 1, NOW())",
                    [$vehicule_id, $filename]
                );
                
            } catch (Exception $e) {
                // On continue même si l'upload échoue
                error_log("Erreur upload photo: " . $e->getMessage());
            }
        }
        
        // Message de succès
        $_SESSION['success_message'] = "Véhicule ajouté avec succès! ID: " . $vehicule_id;
        header('Location: vehicules.php');
        exit;
    }
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un Véhicule - AutoPro Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        .form-section { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .required-field::after { content: " *"; color: red; }
        .alert { margin: 20px 0; }
    </style>
</head>
<body>
    <?php 
    // Démarrer la session si pas déjà fait
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    include 'header.php'; 
    ?>
    
    <div class="container my-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">
                    <i class="bi bi-plus-circle me-2"></i>Ajouter un Véhicule
                </h1>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Erreur:</strong> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <form method="post" enctype="multipart/form-data" class="form-section">
                    <h4 class="mb-3 text-primary">📝 Informations générales</h4>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label required-field">Immatriculation</label>
                            <input type="text" class="form-control" name="immatriculation" required 
                                   value="<?php echo htmlspecialchars($_POST['immatriculation'] ?? ''); ?>"
                                   placeholder="AB-123-CD">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label required-field">Modèle</label>
                            <select class="form-select" name="modele_id" required>
                                <option value="">Sélectionnez un modèle</option>
                                <?php foreach ($modeles as $modele): ?>
                                    <option value="<?php echo $modele['id']; ?>" 
                                        <?php echo (($_POST['modele_id'] ?? '') == $modele['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($modele['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Année de circulation</label>
                            <input type="number" class="form-control" name="annee_circulation" 
                                   value="<?php echo htmlspecialchars($_POST['annee_circulation'] ?? ''); ?>"
                                   min="1990" max="<?php echo date('Y'); ?>" 
                                   placeholder="<?php echo date('Y'); ?>">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Kilométrage</label>
                            <input type="number" class="form-control" name="kilometrage" 
                                   value="<?php echo htmlspecialchars($_POST['kilometrage'] ?? ''); ?>"
                                   placeholder="50000">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Couleur</label>
                            <input type="text" class="form-control" name="couleur" 
                                   value="<?php echo htmlspecialchars($_POST['couleur'] ?? ''); ?>"
                                   placeholder="Rouge">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    <h4 class="mb-3 text-success">💰 Prix</h4>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Prix d'achat (€)</label>
                            <input type="number" step="0.01" class="form-control" name="prix_achat" 
                                   value="<?php echo htmlspecialchars($_POST['prix_achat'] ?? ''); ?>"
                                   placeholder="15000.00">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Prix de vente (€)</label>
                            <input type="number" step="0.01" class="form-control" name="prix_vente" 
                                   value="<?php echo htmlspecialchars($_POST['prix_vente'] ?? ''); ?>"
                                   placeholder="18000.00">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Prix location/jour (€)</label>
                            <input type="number" step="0.01" class="form-control" name="prix_location_jour" 
                                   value="<?php echo htmlspecialchars($_POST['prix_location_jour'] ?? ''); ?>"
                                   placeholder="50.00">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    <h4 class="mb-3 text-warning">🚗 Caractéristiques techniques</h4>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Type de véhicule</label>
                            <select class="form-select" name="type_vehicule">
                                <option value="vente" <?php echo (($_POST['type_vehicule'] ?? 'vente') == 'vente') ? 'selected' : ''; ?>>Vente</option>
                                <option value="location" <?php echo (($_POST['type_vehicule'] ?? '') == 'location') ? 'selected' : ''; ?>>Location</option>
                                <option value="mixte" <?php echo (($_POST['type_vehicule'] ?? '') == 'mixte') ? 'selected' : ''; ?>>Mixte</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Carburant</label>
                            <select class="form-select" name="carburant">
                                <option value="">Sélectionnez</option>
                                <option value="essence" <?php echo (($_POST['carburant'] ?? '') == 'essence') ? 'selected' : ''; ?>>Essence</option>
                                <option value="diesel" <?php echo (($_POST['carburant'] ?? '') == 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                                <option value="electrique" <?php echo (($_POST['carburant'] ?? '') == 'electrique') ? 'selected' : ''; ?>>Électrique</option>
                                <option value="hybride" <?php echo (($_POST['carburant'] ?? '') == 'hybride') ? 'selected' : ''; ?>>Hybride</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Boîte de vitesse</label>
                            <select class="form-select" name="boite_vitesse">
                                <option value="">Sélectionnez</option>
                                <option value="manuelle" <?php echo (($_POST['boite_vitesse'] ?? '') == 'manuelle') ? 'selected' : ''; ?>>Manuelle</option>
                                <option value="automatique" <?php echo (($_POST['boite_vitesse'] ?? '') == 'automatique') ? 'selected' : ''; ?>>Automatique</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Portes</label>
                            <select class="form-select" name="portes">
                                <option value="3" <?php echo (($_POST['portes'] ?? '5') == '3') ? 'selected' : ''; ?>>3</option>
                                <option value="5" <?php echo (($_POST['portes'] ?? '5') == '5') ? 'selected' : ''; ?>>5</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Places</label>
                            <select class="form-select" name="places">
                                <option value="2" <?php echo (($_POST['places'] ?? '5') == '2') ? 'selected' : ''; ?>>2</option>
                                <option value="4" <?php echo (($_POST['places'] ?? '5') == '4') ? 'selected' : ''; ?>>4</option>
                                <option value="5" <?php echo (($_POST['places'] ?? '5') == '5') ? 'selected' : ''; ?>>5</option>
                                <option value="7" <?php echo (($_POST['places'] ?? '5') == '7') ? 'selected' : ''; ?>>7</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Puissance (CV)</label>
                            <input type="number" class="form-control" name="puissance" 
                                   value="<?php echo htmlspecialchars($_POST['puissance'] ?? ''); ?>"
                                   placeholder="90">
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    <h4 class="mb-3 text-info">📸 Photo du véhicule</h4>
                    
                    <div class="mb-3">
                        <label class="form-label">Photo principale</label>
                        <input type="file" class="form-control" name="photo" accept="image/jpeg,image/png,image/gif">
                        <div class="form-text">Formats acceptés: JPG, PNG, GIF (max 5MB)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Options</label>
                        <textarea class="form-control" name="options" rows="3" placeholder="Climatisation, GPS, Radar de recul..."><?php echo htmlspecialchars($_POST['options'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="4" placeholder="Description du véhicule..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle me-2"></i>Ajouter le véhicule
                        </button>
                        <a href="vehicules.php" class="btn btn-secondary btn-lg">
                            <i class="bi bi-arrow-left me-2"></i>Retour à la liste
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
