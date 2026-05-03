<?php
/**
 * Endpoint pour l'exécution des tâches via AJAX
 */

require_once 'ModuleTravauxCloture.php';

header('Content-Type: application/json');

if (isset($_GET['tache'])) {
    $tache = $_GET['tache'];
    $module = new ModuleTravauxCloture(1);
    
    try {
        $resultat = $module->executerTache($tache);
        echo json_encode($resultat);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Aucune tâche spécifiée'
    ]);
}
?>
