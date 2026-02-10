<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/db_connect_ecole.php';
$conn = db_connect_ecole();

// Vérification des champs
if (
    empty($_POST['classe_id']) ||
    empty($_POST['montant_scolarite']) ||
    empty($_POST['droit_inscription'])
) {
    die("Erreur : Tous les champs sont obligatoires.");
}

$classe_id          = intval($_POST['classe_id']);
$scolarite          = floatval($_POST['montant_scolarite']);
$droit              = floatval($_POST['droit_inscription']);

// Vérifier si un tarif existe déjà pour cette classe
$check = $conn->prepare("SELECT id FROM tarifs WHERE classe_id = ?");
$check->bind_param("i", $classe_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    die("Un tarif existe déjà pour cette classe.");
}

// Récupérer cycle + filière depuis classes
$sql_info = "
    SELECT c.id AS classe_id, c.niveau, f.id AS filiere_id, cy.id AS cycle_id
    FROM classes c
    INNER JOIN filieres f ON c.filiere_id = f.id
    INNER JOIN cycles cy ON f.cycle_id = cy.id
    WHERE c.id = ?
";
$stmt_info = $conn->prepare($sql_info);
$stmt_info->bind_param("i", $classe_id);
$stmt_info->execute();
$res = $stmt_info->get_result();
$info = $res->fetch_assoc();

if (!$info) {
    die("Erreur : Classe introuvable.");
}

$filiere_id = $info['filiere_id'];
$cycle_id   = $info['cycle_id'];

// Insertion dans la table tarifs
$sql = "INSERT INTO tarifs (classe_id, filiere_id, cycle_id, montant_scolarite, droit_inscription)
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiidd", $classe_id, $filiere_id, $cycle_id, $scolarite, $droit);

if ($stmt->execute()) {
    echo "<h2>✔ Tarif enregistré avec succès !</h2>";
    echo "<a href='ajouter_tarif.php'>Retour</a>";
} else {
    echo "Erreur SQL : " . $stmt->error;
}

?>
