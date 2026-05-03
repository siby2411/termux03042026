<?php
require_once 'db_connect_ecole.php';
require_once 'functions.php'; // Contient genererCode($type)
$conn = db_connect_ecole();

// Exemple pour Étudiant
if (isset($_POST['creer_etudiant'])) {
    $code = genererCode('ETUDIANT');
    $nom = $_POST['nom'];
    $prenom = $_POST['prenom'];
    $classe = $_POST['classe_id'];

    $stmt = $conn->prepare("INSERT INTO etudiants (code_etudiant, nom, prenom, classe_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $code, $nom, $prenom, $classe);
    $stmt->execute();
    header("Location: crud_etudiants.php?msg=Etudiant créé avec le code $code");
}

// Exemple pour Professeur
if (isset($_POST['creer_professeur'])) {
    $code = genererCode('PROFESSEUR');
    $nom = $_POST['nom'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("INSERT INTO professeurs (id_prof_code, nom, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $code, $nom, $email);
    $stmt->execute();
    header("Location: crud_professeurs.php?msg=Professeur créé avec le code $code");
}
