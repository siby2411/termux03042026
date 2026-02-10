<?php
// Connexion à la base de données
try {
    $pdo = new PDO('mysql:host=localhost;dbname=ohada', 'root', '123');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération des données GET (puisque le formulaire envoie les données via GET)
$classe = isset($_GET['classe']) ? $_GET['classe'] : null;
$sous_classe = isset($_GET['sous_classe']) ? $_GET['sous_classe'] : null;
$num_compte = isset($_GET['num_compte']) ? $_GET['num_compte'] : null;

// Affichage des valeurs reçues pour débogage
echo "Classe : " . ($classe ? $classe : 'Aucune') . "<br>";
echo "Sous-classe : " . ($sous_classe ? $sous_classe : 'Aucune') . "<br>";
echo "Numéro de compte : " . ($num_compte ? $num_compte : 'Aucun') . "<br>";

// Construction de la requête SQL avec la clause WHERE dynamique
$query = "SELECT c.num_compte, c.intitule, sc.intitule_sous_classe AS sous_classe_libelle, cl.intitule_classe AS classe_libelle 
          FROM comptes_ohada c
          JOIN sous_classes_ohada sc ON c.sous_classe_id = sc.id
          JOIN classes_ohada cl ON sc.classe_id = cl.id
          WHERE 1=1"; // 1=1 permet d'ajouter dynamiquement des conditions

// Ajout de conditions dynamiques selon les valeurs reçues
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
    echo "<table class='table table-bordered'>
            <thead>
                <tr>
                    <th>Numéro de Compte</th>
                    <th>Libellé</th>
                    <th>Sous Classe</th>
                    <th>Classe</th>
                </tr>
            </thead>
            <tbody>";
    foreach ($comptes as $compte) {
        echo "<tr>
                <td>{$compte['num_compte']}</td>
                <td>{$compte['intitule']}</td>
                <td>{$compte['sous_classe_libelle']}</td>
                <td>{$compte['classe_libelle']}</td>
              </tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<div class='alert alert-warning'>Aucun compte trouvé.</div>";
}
?>