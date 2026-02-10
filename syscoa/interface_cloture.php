<?php
/**
 * Interface utilisateur pour les travaux de clôture
 * Page web avec tableau de bord interactif
 */

require_once 'ModuleTravauxCloture.php';

echo '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Module de Clôture - Travaux Fin d\'Exercice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .calendar-item { border-left: 4px solid #0d6efd; margin-bottom: 1rem; }
        .statut-termine { border-left-color: #198754; }
        .statut-en_cours { border-left-color: #ffc107; }
        .statut-en_attente { border-left-color: #6c757d; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">📊 Module de Clôture - Décembre 2024</h1>
        
        <div class="row">
            <div class="col-md-8">';

// Affichage du tableau de bord
$module = new ModuleTravauxCloture(1);
$tableau_bord = $module->getTableauBord();

foreach ($tableau_bord as $tache) {
    $classe_statut = 'statut-' . $tache['statut'];
    echo '<div class="card calendar-item ' . $classe_statut . '">
            <div class="card-body">
                <h5 class="card-title">' . $tache['tache'] . '</h5>
                <p class="card-text">
                    <strong>Période:</strong> ' . $tache['periode_debut'] . ' à ' . $tache['periode_fin'] . '<br>
                    <strong>Statut:</strong> <span class="badge bg-' . $this->getCouleurStatut($tache['statut']) . '">' . $tache['statut'] . '</span>
                </p>';
    
    if ($tache['statut'] == 'en_attente') {
        echo '<button class="btn btn-primary" onclick="executerTache(\'' . $tache['tache'] . '\')">
                Exécuter cette tâche
              </button>';
    }
    
    echo '</div></div>';
}

echo '      </div>
        </div>
    </div>

    <script>
    function executerTache(tache) {
        if(confirm("Voulez-vous exécuter la tâche: " + tache + " ?")) {
            // Appel AJAX pour exécuter la tâche
            fetch("executer_tache.php?tache=" + encodeURIComponent(tache))
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        alert("✅ " + data.message);
                        location.reload();
                    } else {
                        alert("❌ " + data.message);
                    }
                });
        }
    }
    </script>
</body>
</html>';

function getCouleurStatut($statut) {
    switch($statut) {
        case 'termine': return 'success';
        case 'en_cours': return 'warning';
        case 'en_attente': return 'secondary';
        default: return 'dark';
    }
}
?>
