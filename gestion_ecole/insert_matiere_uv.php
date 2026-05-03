<?php
require_once 'db_connect_ecole.php';
$conn = db_connect_ecole();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classe_id = intval($_POST['classe_id']);
    $nom_matiere = trim($_POST['nom_matiere']);
    $nom_uv = trim($_POST['uv']);
    $semestre = intval($_POST['semestre']);
    $coefficient = floatval($_POST['coefficient']);

    // 1. Vérifier/Insérer la matière globale
    $stmt = $conn->prepare("SELECT id FROM matieres WHERE nom_matiere = ?");
    $stmt->bind_param("s", $nom_matiere);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $matiere_id = $res->fetch_assoc()['id'];
    } else {
        $stmt_ins = $conn->prepare("INSERT INTO matieres (nom_matiere) VALUES (?)");
        $stmt_ins->bind_param("s", $nom_matiere);
        $stmt_ins->execute();
        $matiere_id = $stmt_ins->insert_id;
    }

    // 2. Insérer l'UV liée à la classe
    $stmt_uv = $conn->prepare("INSERT INTO unites_valeur (nom_uv, matiere_id, classe_id, semestre, coefficient) VALUES (?, ?, ?, ?, ?)");
    $stmt_uv->bind_param("siiid", $nom_uv, $matiere_id, $classe_id, $semestre, $coefficient);

    if ($stmt_uv->execute()) {
        header("Location: ajouter_matiere_uv.php?status=success");
    } else {
        echo "Erreur : " . $stmt_uv->error;
    }
}
