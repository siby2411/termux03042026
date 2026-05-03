<?php
// export_flux_tresorerie_simple.php
require_once 'config/database.php';
require_once 'includes/functions.php';
check_auth();

$id_exercice = isset($_GET['id_exercice']) ? intval($_GET['id_exercice']) : $_SESSION['id_exercice'];

// Forcer le téléchargement HTML comme document
header('Content-Type: text/html; charset=utf-8');
header('Content-Disposition: attachment; filename="flux_tresorerie_' . date('Y') . '.html"');

// Inclure le même contenu que tableau_flux_tresorerie.php mais sans les contrôles d'impression
ob_start();
include 'tableau_flux_tresorerie.php';
$content = ob_get_clean();

// Supprimer les contrôles d'impression
$content = preg_replace('/<div class="print-controls.*?<\/div>/s', '', $content);

echo $content;
