<?php
/**
 * PUBLIC/LOGOUT.PHP
 * Termine la session utilisateur de manière sécurisée et redirige vers la page de connexion.
 */

// 1. Démarrer la session (nécessaire pour accéder aux données de session existantes)
session_start();

// 2. Détruire toutes les variables de session
// Ceci est crucial pour effacer l'identifiant de l'utilisateur, le rôle, et le statut 'logged_in'.
$_SESSION = array();

// 3. Si l'utilisateur utilise les cookies de session (méthode par défaut),
// invalider le cookie de session en définissant une durée d'expiration passée.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Détruire la session elle-même du côté serveur
session_destroy();

// 5. Redirection vers la page de connexion
header('Location: login.php');
exit;
?>
