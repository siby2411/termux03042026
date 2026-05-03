<?php
/**
 * Fonctions d'authentification
 */

// Inclure la configuration pour avoir accès à $pdo
require_once __DIR__ . '/../config/database.php';

/**
 * Connecte un utilisateur
 */
function login($username, $password) {
    global $pdo;
    
    // Vérifier que la connexion est établie
    if (!isset($pdo)) {
        error_log("PDO non défini dans auth.php");
        return false;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['nom_complet'] ?? $user['username'];
            $_SESSION['user_service'] = $user['service_id'];
            return true;
        }
    } catch (PDOException $e) {
        error_log("Erreur de connexion : " . $e->getMessage());
    }
    return false;
}

/**
 * Déconnecte un utilisateur
 */
function logout() {
    session_destroy();
    return true;
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == $role;
}

/**
 * Vérifie si l'utilisateur a l'un des rôles spécifiés
 */
function hasAnyRole($roles) {
    if (!isset($_SESSION['user_role'])) return false;
    return in_array($_SESSION['user_role'], $roles);
}

/**
 * Récupère l'utilisateur connecté
 */
function getCurrentUser() {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) return null;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération de l'utilisateur : " . $e->getMessage());
        return null;
    }
}
?>
