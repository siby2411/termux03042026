<?php
// upload_vehicule.php - Version ultime
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

// Fonctions d'upload
function validateUploadedFile($file) {
    define('MAX_FILE_SIZE', 5 * 1024 * 1024);
    
    $allowed_types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png', 
        'image/gif' => 'gif'
    ];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'Fichier trop volumineux (configuration PHP)',
            UPLOAD_ERR_FORM_SIZE => 'Fichier trop volumineux (formulaire)',
            UPLOAD_ERR_PARTIAL => 'Upload partiel',
            UPLOAD_ERR_NO_FILE => 'Aucun fichier',
            UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant',
            UPLOAD_ERR_CANT_WRITE => 'Erreur écriture disque',
            UPLOAD_ERR_EXTENSION => 'Extension PHP a arrêté l\'upload'
        ];
        throw new Exception($error_messages[$file['error']] ?? 'Erreur inconnue: ' . $file['error']);
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
            if (!mkdir($destination_dir, 0755, true)) {
                throw new Exception("Impossible de créer le dossier: " . $destination_dir);
            }
        }
        $destination = $destination_dir . '/' . $filename;
    } else {
        $destination = UPLOAD_DIR . '/' . $filename;
    }
    
    if (!move_uploaded_file($file_info['tmp_name'], $destination)) {
        // Debug détaillé
        $error = error_get_last();
        throw new Exception("move_uploaded_file a échoué. Dernière erreur: " . ($error['message'] ?? 'Inconnue'));
    }
    
    return $filename;
}

// Debug initial
echo "<div class='debug-info'>";
echo "<h3>🔧 Informations de débogage</h3>";
echo "<p><strong>Upload Dir:</strong> " . UPLOAD_DIR . "</p>";
echo "<p><strong>Writable:</strong> " . (is_writable(UPLOAD_DIR) ? '✅ OUI' : '❌ NON') . "</p>";
echo "<p><strong>Vehicules Dir:</strong> " . UPLOAD_DIR . "/vehicules</p>";
echo "<p><strong>Writable:</strong> " . (is_writable(UPLOAD_DIR . '/vehicules') ? '✅ OUI' : '❌ NON') . "</p>";
echo "<p><strong>Propriétaire:</strong> " . exec('stat -c "%U:%G" ' . UPLOAD_DIR . '/vehicules 2>/dev/null') . "</p>";
echo "<p><strong>Permissions:</strong> " . exec('stat -c "%a" ' . UPLOAD_DIR . '/vehicules 2>/dev/null') . "</p>";
echo "</div>";

// Traitement de l'upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    echo "<div class='upload-result'>";
    echo "<h3>📤 Résultat de l'upload</h3>";
    
    try {
        // Validation
        $file_info = validateUploadedFile($_FILES['photo']);
        echo "<p>✅ <strong>Fichier validé:</strong> " . htmlspecialchars($file_info['name']) . " (" . number_format($file_info['size'] / 1024, 2) . " KB)</p>";
        
        // Sauvegarde
        $filename = saveUploadedFile($file_info, 'vehicules');
        echo "<p>✅ <strong>Fichier sauvegardé:</strong> " . htmlspecialchars($filename) . "</p>";
        
        // Enregistrement en base
        $db = Database::getInstance();
        
        // Utiliser le bon nom de colonne (date_upload au lieu de created_at)
        $stmt = $db->query("INSERT INTO vehicule_images (vehicule_id, nom_fichier, est_principale, date_upload) VALUES (1, ?, 1, NOW())", [$filename]);
        
        echo "<div class='success-message'>";
        echo "🎉 <strong>UPLOAD COMPLÈTEMENT RÉUSSI!</strong>";
        echo "</div>";
        
        // Affichage du fichier
        echo "<div class='file-preview'>";
        echo "<p><strong>📁 Fichier:</strong> " . htmlspecialchars($filename) . "</p>";
        echo "<p><strong>📍 Emplacement:</strong> " . UPLOAD_DIR . "/vehicules/" . htmlspecialchars($filename) . "</p>";
        echo "<p><strong>👀 Aperçu:</strong></p>";
        echo "<img src='/gestion_auto/uploads/vehicules/" . htmlspecialchars($filename) . "' alt='Image uploadée'>";
        echo "</div>";
        
    } catch (Exception $e) {
        echo "<div class='error-message'>";
        echo "❌ <strong>ERREUR:</strong> " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
    
    echo "</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload Véhicule - AutoPro Manager</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .form-container { max-width: 600px; padding: 25px; border: 1px solid #ddd; border-radius: 10px; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        input[type="file"] { margin: 15px 0; padding: 12px; border: 2px dashed #ccc; border-radius: 5px; width: 100%; background: #fafafa; }
        input[type="file"]:hover { border-color: #007cba; }
        button { background: #007cba; color: white; padding: 12px 25px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; transition: all 0.3s; }
        button:hover { background: #005a87; transform: translateY(-1px); }
        
        .debug-info { background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #007cba; }
        .upload-result { background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0; border: 1px solid #ddd; }
        .success-message { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border: 1px solid #c3e6cb; margin: 15px 0; }
        .error-message { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border: 1px solid #f5c6cb; margin: 15px 0; }
        .file-preview { background: white; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .file-preview img { max-width: 300px; border: 2px solid #4CAF50; border-radius: 5px; display: block; margin: 10px 0; }
        
        .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 20px; }
        .gallery-item { border: 1px solid #ddd; border-radius: 5px; padding: 10px; background: white; text-align: center; }
        .gallery-item img { max-width: 100%; height: 120px; object-fit: cover; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🚗 Upload Photo Véhicule</h1>
    
    <div class="form-container">
        <form action="" method="post" enctype="multipart/form-data">
            <label for="photo" style="font-weight: bold; font-size: 16px;">Sélectionnez une photo du véhicule :</label>
            <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/gif" required>
            <br>
            <button type="submit">📤 Uploader la photo</button>
        </form>
    </div>

    <div style="background: white; padding: 20px; border-radius: 5px; border: 1px solid #ddd;">
        <h3>📂 Galerie des photos uploadées</h3>
        <?php
        $files = glob(UPLOAD_DIR . '/vehicules/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        if (!empty($files)) {
            echo "<div class='gallery'>";
            foreach ($files as $file) {
                $filename = basename($file);
                $filesize = round(filesize($file) / 1024, 2);
                $filetime = date('d/m/Y H:i', filemtime($file));
                
                echo "<div class='gallery-item'>";
                echo "<img src='/gestion_auto/uploads/vehicules/" . htmlspecialchars($filename) . "' alt=''>";
                echo "<div style='margin-top: 8px; font-size: 12px;'>";
                echo "<div><strong>" . htmlspecialchars($filename) . "</strong></div>";
                echo "<div>" . $filesize . " KB</div>";
                echo "<div>" . $filetime . "</div>";
                echo "</div>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            echo "<p style='text-align: center; color: #666; padding: 40px;'>Aucune photo uploadée pour le moment</p>";
        }
        ?>
    </div>
</body>
</html>
