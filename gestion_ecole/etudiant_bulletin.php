<?php
// Fichier : etudiant_bulletin.php - Page d'accueil Étudiant (Placeholder)
session_start();
require_once 'db_connect_ecole.php';
require_once 'header_ecole.php';

// Vérification du rôle
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'etudiant') {
    $_SESSION['message'] = "Accès non autorisé.";
    header("Location: login.php");
    exit();
}

$code_etudiant = htmlspecialchars($_SESSION['username']); 
?>

<h1 class="mb-4">Espace Étudiant : Mon Bulletin</h1>

<div class="alert alert-info">
    Bienvenue, <?php echo htmlspecialchars($code_etudiant); ?> ! Vous êtes connecté en tant qu'Étudiant.
    <p>Votre bulletin de notes pour l'année en cours sera affiché ici.</p>
</div>

<?php 
// FUTURE ÉTAPE : Inclure le contenu de bulletin_edit.php ici, en utilisant $code_etudiant
?>

<?php require_once 'footer_ecole.php'; ?>
