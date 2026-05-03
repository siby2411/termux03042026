<?php
/**
 * OMEGA TECH - Générateur automatique de plan de site (Sitemap)
 * Ce script scanne le répertoire /scripts et génère tous les liens HTTP
 */

$rootUrl = "http://127.0.0.1:8080/scripts/";
$directory = __DIR__ . '/scripts';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <title>OMEGA TECH - Plan des Liens</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { background: #f4f7f6; padding: 40px; }
        .folder-card { margin-bottom: 20px; border-left: 5px solid #ffc107; }
        .link-item { text-decoration: none; color: #333; transition: 0.2s; }
        .link-item:hover { color: #0d6efd; padding-left: 10px; }
    </style>
</head>
<body>
<div class='container'>
    <div class='text-center mb-5'>
        <h1 class='fw-bold'>SYSTÈME OMEGA TECH</h1>
        <p class='text-muted'>Inventaire automatique des ressources du serveur</p>
    </div>";

if (is_dir($directory)) {
    $folders = array_diff(scandir($directory), array('..', '.'));

    foreach ($folders as $folder) {
        $path = $directory . '/' . $folder;
        
        if (is_dir($path)) {
            echo "<div class='card shadow-sm folder-card'>
                    <div class='card-header bg-dark text-white fw-bold'>
                        <i class='fas fa-folder me-2'></i> " . strtoupper($folder) . "
                    </div>
                    <div class='card-body'>
                        <ul class='list-group list-group-flush'>";
            
            $files = scandir($path);
            foreach ($files as $file) {
                // On ne liste que les fichiers PHP et on ignore les fichiers de traitement
                if (pathinfo($file, PATHINFO_EXTENSION) == 'php' && strpos($file, 'traitement') === false && strpos($file, 'save') === false) {
                    $fullUrl = $rootUrl . $folder . '/' . $file;
                    echo "<li class='list-group-item'>
                            <i class='fas fa-link me-2 text-secondary'></i>
                            <a href='$fullUrl' target='_blank' class='link-item'>$fullUrl</a>
                          </li>";
                }
            }
            
            echo "      </ul>
                    </div>
                  </div>";
        }
    }
} else {
    echo "<div class='alert alert-danger'>Répertoire /scripts non trouvé.</div>";
}

echo "</div>
<script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
</body>
</html>";
