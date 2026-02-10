<?php
$base_dir = '/var/www/clinique';

// Structure des répertoires à créer
$structure = [
    'modules/personnel',
    'modules/rendezvous', 
    'modules/consultations',
    'modules/finances',
    'modules/analyses',
    'modules/patients',
    'js'
];

// Création des répertoires
foreach ($structure as $dir) {
    $full_path = $base_dir . '/' . $dir;
    if (!is_dir($full_path)) {
        mkdir($full_path, 0755, true);
        echo "✅ Répertoire créé: $full_path\n";
    } else {
        echo "📁 Répertoire existe déjà: $full_path\n";
    }
}

// Fichiers index à créer
$index_files = [
    'index.php' => '<?php header("Location: modules/dashboard.php"); exit(); ?>',
    'modules/personnel/index.php' => '<?php header("Location: list.php"); exit(); ?>',
    'modules/rendezvous/index.php' => '<?php header("Location: list.php"); exit(); ?>',
    'modules/consultations/index.php' => '<?php header("Location: list.php"); exit(); ?>',
    'modules/finances/index.php' => '<?php header("Location: list.php"); exit(); ?>',
    'modules/analyses/index.php' => '<?php header("Location: list.php"); exit(); ?>',
    'modules/patients/index.php' => '<?php header("Location: list.php"); exit(); ?>',
];

// Création des fichiers index
foreach ($index_files as $file => $content) {
    $full_path = $base_dir . '/' . $file;
    if (!file_exists($full_path)) {
        file_put_contents($full_path, $content);
        echo "✅ Fichier créé: $full_path\n";
    } else {
        echo "📄 Fichier existe déjà: $full_path\n";
    }
}

echo "\n🎯 Structure créée avec succès!\n";
?>
