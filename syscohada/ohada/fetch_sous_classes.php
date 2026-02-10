<?php
include('config.php');

// Vérifier si l'ID de la classe est passé en paramètre
if (isset($_GET['classe_id'])) {
    $classe_id = $_GET['classe_id'];

    // Requête pour récupérer les sous-classes de la classe sélectionnée
    $stmt = $pdo->prepare("SELECT * FROM sous_classes_ohada WHERE classe_id = :classe_id");
    $stmt->bindParam(':classe_id', $classe_id, PDO::PARAM_INT);
    $stmt->execute();
    $sous_classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Générer les options HTML pour les sous-classes
    if ($sous_classes) {
        echo '<option value="">Sélectionnez une Sous-classe</option>';
        foreach ($sous_classes as $sous_classe) {
            echo "<option value='" . htmlspecialchars($sous_classe['id'], ENT_QUOTES) . "'>" . htmlspecialchars($sous_classe['intitule_sous_classe'], ENT_QUOTES) . "</option>";
        }
    } else {
        echo '<option value="">Aucune sous-classe trouvée</option>';
    }
}
?>
