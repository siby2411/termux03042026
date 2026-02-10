<?php
include('config.php'); // Assurez-vous que ce fichier contient la configuration de votre connexion à la base de données.

if (isset($_GET['sous_classe_id'])) {
    $sous_classe_id = intval($_GET['sous_classe_id']); // Convertir en entier pour la sécurité
    $stmt = $pdo->prepare("SELECT id, intitule FROM comptes_ohada WHERE sous_classe_id = :sous_classe_id");
    $stmt->execute(['sous_classe_id' => $sous_classe_id]);
    
    $libelles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($libelles) {
        foreach ($libelles as $libelle) {
            echo "<option value='" . htmlspecialchars($libelle['id'], ENT_QUOTES) . "'>" . htmlspecialchars($libelle['intitule'], ENT_QUOTES) . "</option>";
        }
    } else {
        echo "<option value=''>Aucun libellé trouvé</option>";
    }
} else {
    echo "<option value=''>Aucune sous-classe sélectionnée</option>";
}
?>