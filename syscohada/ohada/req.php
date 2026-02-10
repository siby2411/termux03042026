<?php
// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ohada', 'root', '123');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération des données POST
$classe = isset($_POST['classe']) ? $_POST['classe'] : null;
$sous_classe = isset($_POST['sous_classe']) ? $_POST['sous_classe'] : null;
$num_compte = isset($_POST['num_compte']) ? $_POST['num_compte'] : null;

// Construction de la requête SQL avec la clause WHERE dynamique
$query = "SELECT c.num_compte, c.intitule, sc.intitule_sous_classe as sous_classe_libelle, cl.intitule_classe as classe_libelle 
          FROM comptes_ohada c
          JOIN sous_classes_ohada sc ON c.sous_classe_id = sc.id
          JOIN classes_ohada cl ON sc.classe_id = cl.id
          WHERE 1=1"; // 1=1 permet d'ajouter dynamiquement des conditions

// Ajout de conditions dynamiques
if ($classe) {
    $query .= " AND cl.id = :classe";
}
if ($sous_classe) {
    $query .= " AND sc.id = :sous_classe";
}
if ($num_compte) {
    $query .= " AND c.num_compte LIKE :num_compte";
}

// Préparation de la requête
$stmt = $pdo->prepare($query);

// Liaison des paramètres
if ($classe) {
    $stmt->bindParam(':classe', $classe, PDO::PARAM_INT);
}
if ($sous_classe) {
    $stmt->bindParam(':sous_classe', $sous_classe, PDO::PARAM_INT);
}
if ($num_compte) {
    $num_compte_like = "%" . $num_compte . "%"; // Pour une recherche partielle
    $stmt->bindParam(':num_compte', $num_compte_like, PDO::PARAM_STR);
}

// Exécution de la requête
$stmt->execute();

// Récupération des résultats
$comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Affichage des résultats
if ($comptes) {
    echo "<table border='1'>
            <tr>
                <th>Numéro de Compte</th>
                <th>Libellé</th>
                <th>Sous Classe</th>
                <th>Classe</th>
            </tr>";
    foreach ($comptes as $compte) {
        echo "<tr>
                <td>{$compte['num_compte']}</td>
                <td>{$compte['intitule']}</td>
                <td>{$compte['sous_classe_libelle']}</td>
                <td>{$compte['classe_libelle']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "Aucun compte trouvé.";
}
?>

<form method="POST" action="recherche_compte.php">
    <label for="classe">Classe:</label>
    <select name="classe" id="classe">
        <option value="">Sélectionner une classe</option>
        <?php
        // Récupération des classes
        $classes = $pdo->query("SELECT id, intitule_classe FROM classes_ohada")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($classes as $class) {
            echo "<option value=\"{$class['id']}\">{$class['intitule_classe']}</option>";
        }
        ?>
    </select>

    <label for="sous_classe">Sous-classe:</label>
    <select name="sous_classe" id="sous_classe">
        <option value="">Sélectionner une sous-classe</option>
        <?php
        // Récupération des sous-classes
        $sous_classes = $pdo->query("SELECT id, intitule_sous_classe FROM sous_classes_ohada")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($sous_classes as $sous_class) {
            echo "<option value=\"{$sous_class['id']}\">{$sous_class['intitule_sous_classe']}</option>";
        }
        ?>
    </select>

    <label for="num_compte">Numéro de Compte:</label>
    <input type="text" name="num_compte" id="num_compte">

    <input type="submit" value="Rechercher">
</form>