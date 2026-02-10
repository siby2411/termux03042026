<?php
// Fichier : bulletin_edit.php - Affichage détaillé du Bulletin avec design Bootstrap

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect_ecole.php'; 

// Vérification de la session
if (!isset($_SESSION['role'])) {
    $_SESSION['message'] = "Veuillez vous connecter pour accéder au bulletin.";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php");
    exit();
}

// Connexion à la base
$conn = db_connect_ecole();
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données: " . $conn->connect_error);
}

// Récupération du code étudiant
$code_etudiant = $_GET['code_etudiant'] ?? null;
if (!$code_etudiant) {
    $_SESSION['message'] = "Code étudiant manquant pour l'affichage du bulletin.";
    $_SESSION['msg_type'] = "danger";
    header("Location: crud_etudiants.php");
    exit();
}

// Étudiant ne peut voir que son bulletin
if ($_SESSION['role'] == 'etudiant' && $code_etudiant !== $_SESSION['code_etudiant']) {
    $_SESSION['message'] = "Accès refusé. Vous ne pouvez consulter que votre propre bulletin.";
    $_SESSION['msg_type'] = "danger";
    header("Location: index.php");
    exit();
}

// --- Récupération des informations générales et bulletins ---
$query_info = "
    SELECT 
        e.nom, e.prenom, e.date_naissance, e.code_etudiant,
        c.nom_classe, c.annee_academique, 
        f.nom AS nom_filiere, 
        b.moyenne_semestre1, b.moyenne_semestre2, b.moyenne_annuelle, b.statut_final
    FROM etudiants e
    JOIN classes c ON e.id_classe = c.id_classe
    JOIN filieres f ON c.id_filiere = f.id
    JOIN bulletins b ON e.code_etudiant = b.code_etudiant
    WHERE e.code_etudiant = ?
    ORDER BY b.annee_academique DESC
    LIMIT 1
";

$stmt_info = $conn->prepare($query_info);
$stmt_info->bind_param("s", $code_etudiant);
$stmt_info->execute();
$bulletin_data = $stmt_info->get_result()->fetch_assoc();
$stmt_info->close();

if (!$bulletin_data) {
    $_SESSION['message'] = "Bulletin non trouvé ou informations incomplètes.";
    $_SESSION['msg_type'] = "danger";
    header("Location: crud_etudiants.php");
    exit();
}

// --- Récupération des notes ---
$query_notes = "
    SELECT 
        n.semestre, m.nom_matiere, m.coefficient, 
        n.note_cc1, n.note_cc2, n.note_examen, n.moyenne_matiere
    FROM notes n
    JOIN matieres m ON n.id_matiere = m.id_matiere
    WHERE n.code_etudiant = ?
    ORDER BY n.semestre, m.nom_matiere
";

$stmt_notes = $conn->prepare($query_notes);
$stmt_notes->bind_param("s", $code_etudiant);
$stmt_notes->execute();
$notes_result = $stmt_notes->get_result();

$notes_par_semestre = ['S1' => [], 'S2' => []];
while ($row = $notes_result->fetch_assoc()) {
    $notes_par_semestre[$row['semestre']][] = $row;
}
$stmt_notes->close();
$conn->close();

include 'header_ecole.php';
?>

<h1 class="mb-4 text-primary">📋 Bulletin Scolaire Détaillé</h1>

<div class="card shadow-lg mb-5">
    <div class="card-header bg-info text-white">
        <h3 class="mb-0">Informations Générales de l'Étudiant</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Nom et Prénom :</strong> <?php echo htmlspecialchars($bulletin_data['prenom'] . ' ' . $bulletin_data['nom']); ?></p>
                <p><strong>Code Étudiant :</strong> <?php echo htmlspecialchars($bulletin_data['code_etudiant']); ?></p>
                <p><strong>Date de Naissance :</strong> <?php echo htmlspecialchars($bulletin_data['date_naissance']); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Filière :</strong> <?php echo htmlspecialchars($bulletin_data['nom_filiere']); ?></p>
                <p><strong>Classe :</strong> <?php echo htmlspecialchars($bulletin_data['nom_classe']); ?></p>
                <p><strong>Année Académique :</strong> <?php echo htmlspecialchars($bulletin_data['annee_academique']); ?></p>
            </div>
        </div>

        <hr>

        <h4 class="mt-4">Résultats Récapitulatifs</h4>
        <div class="row text-center font-weight-bold">
            <div class="col-md-4 mb-2">
                <div class="p-3 border rounded <?php echo ($bulletin_data['moyenne_semestre1'] >= 10) ? 'bg-success text-white' :_]()_

