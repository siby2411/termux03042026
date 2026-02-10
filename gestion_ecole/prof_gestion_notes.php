<?php
// Fichier : prof_gestion_notes.php - Page d'accueil Professeur (Placeholder)
session_start();
require_once 'db_connect_ecole.php';
require_once 'header_ecole.php';

// Vérification du rôle
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'professeur') {
    $_SESSION['message'] = "Accès non autorisé.";
    header("Location: login.php");
    exit();
}
?>

<h1 class="mb-4">Espace Professeur : Mes Matières</h1>

<div class="alert alert-info">
    Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> ! Vous êtes connecté en tant que Professeur.
    <p>Cette page affichera bientôt les matières que vous devez noter.</p>
</div>

<?php 
// FUTURE ÉTAPE : Afficher la liste des matières liées à $_SESSION['entite_id'] (id_professeur)
?>

<?php require_once 'footer_ecole.php'; ?>
