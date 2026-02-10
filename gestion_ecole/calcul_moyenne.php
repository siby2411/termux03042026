<?php
/**
 * Fonction de calcul de la moyenne LMD selon la règle 60% Examen / 40% CC.
 *
 * @param float $note_cc Note de Contrôle Continu (sur 20)
 * @param float $note_exam Note d'Examen (sur 20)
 * @return float La moyenne pondérée finale (sur 20)
 */
function calculer_moyenne_lmd(float $note_cc, float $note_exam): float
{
    // Poids des notes
    $poids_cc = 0.40; // 40%
    $poids_exam = 0.60; // 60%
    
    // Vérification simple des bornes (optionnel mais recommandé)
    if ($note_cc < 0 || $note_cc > 20 || $note_exam < 0 || $note_exam > 20) {
        // Gérer l'erreur ou retourner une valeur spécifique
        error_log("Tentative de calcul de moyenne avec des notes hors limites (0-20).");
        return 0.00; 
    }

    // Calcul de la moyenne pondérée
    $moyenne_finale = ($note_cc * $poids_cc) + ($note_exam * $poids_exam);
    
    // Arrondir à deux décimales
    return round($moyenne_finale, 2);
}


// Exemple d'utilisation:
// $cc = 15;
// $examen = 10;
// $moyenne = calculer_moyenne_lmd($cc, $examen); // Résultat: (15 * 0.4) + (10 * 0.6) = 6 + 6 = 12.00
// echo "Moyenne finale: " . $moyenne;
?>
