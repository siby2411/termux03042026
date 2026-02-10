<?php
// backup.php
require_once 'config/database.php';
check_auth();

// Vérifier les permissions admin
$user = get_current_user();
if ($user['role'] !== 'admin') {
    die('Accès refusé.');
}

// Créer une sauvegarde
$backup_file = 'backup/sysco_ohada_' . date('Y-m-d_H-i-s') . '.sql';
$command = "mysqldump --user=" . DB_USER . " --password=" . DB_PASS . 
           " --host=" . DB_HOST . " " . DB_NAME . " > " . $backup_file;

exec($command, $output, $return_var);

if ($return_var === 0) {
    $message = "Sauvegarde créée avec succès: " . basename($backup_file);
} else {
    $message = "Erreur lors de la sauvegarde";
}

// Rediriger vers l'administration
header('Location: administration.php?message=' . urlencode($message));
exit();
