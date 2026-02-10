<?php
// insert_matiere_uv.php

// Configuration
$host = "localhost";
$user = "root";
$pass = "123";
$db = "ecole";
$log_file = "/mnt/e/ecole29112026/gestion_ecole/error_log.txt";

// Fonction pour journaliser les erreurs
function log_error($message) {
    global $log_file;
    $msg = "[".date('Y-m-d H:i:s')."] ".$message.PHP_EOL;
    file_put_contents($log_file, $msg, FILE_APPEND);
}

// Vérifier que le formulaire est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classe_id = $_POST['classe_id'] ?? '';
    $matiere_id = $_POST['matiere_id'] ?? '';
    $uv_id = $_POST['uv_id'] ?? '';
    $uv_name = $_POST['uv_name'] ?? '';
    $semestre = $_POST['semestre'] ?? '';
    $coefficient = $_POST['coefficient'] ?? '';

    if (!$classe_id || !$matiere_id || (!$uv_id && !$uv_name) || !$semestre || !$coefficient) {
        log_error("Formulaire incomplet : ".json_encode($_POST));
        die("Veuillez remplir tous les champs du formulaire.");
    }

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        log_error("Erreur connexion DB : ".$conn->connect_error);
        die("Erreur de connexion à la base de données.");
    }

    // Si UV existante sélectionnée, utiliser son ID
    if ($uv_id) {
        $uv_name = '';
    }

    // Vérifier si la même UV existe déjà pour la même matière et semestre
    if ($uv_id || $uv_name) {
        $check_sql = $uv_id
            ? "SELECT id FROM unites_valeur WHERE id = ? AND matiere_id = ? AND semestre = ?"
            : "SELECT id FROM unites_valeur WHERE nom_uv = ? AND matiere_id = ? AND semestre = ?";
        $stmt = $conn->prepare($check_sql);
        if ($uv_id) {
            $stmt->bind_param("iii", $uv_id, $matiere_id, $semestre);
        } else {
            $stmt->bind_param("sii", $uv_name, $matiere_id, $semestre);
        }
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Si existe, mise à jour du coefficient
            if (!$uv_id) {
                $stmt->bind_result($existing_id);
                $stmt->fetch();
                $uv_id = $existing_id;
            }
            $update = $conn->prepare("UPDATE unites_valeur SET coefficient = ? WHERE id = ?");
            $update->bind_param("di", $coefficient, $uv_id);
            if ($update->execute()) {
                echo "Coefficient de l'UV mis à jour avec succès !";
            } else {
                log_error("Erreur mise à jour UV : ".$update->error);
                die("Erreur lors de la mise à jour de l'UV.");
            }
            $update->close();
        } else {
            // Sinon, insertion
            $insert = $conn->prepare("INSERT INTO unites_valeur (nom_uv, matiere_id, semestre, coefficient) VALUES (?, ?, ?, ?)");
            $insert->bind_param("siid", $uv_name, $matiere_id, $semestre, $coefficient);
            if ($insert->execute()) {
                echo "UV ajoutée avec succès !";
            } else {
                log_error("Erreur insertion UV : ".$insert->error);
                die("Erreur lors de l'ajout de l'UV.");
            }
            $insert->close();
        }
        $stmt->close();
    }

    $conn->close();
} else {
    die("Accès direct interdit.");
}
?>

