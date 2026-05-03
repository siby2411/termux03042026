<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    public static function check() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login.php');
            exit();
        }
    }

    public static function login($username, $password) {
        require_once __DIR__ . '/Database.php';
        
        $username = trim($username);
        $password = trim($password);
        
        // On utilise 'mot_de_passe' comme vu dans le DESC
        $results = Database::query("SELECT * FROM utilisateurs WHERE login = ? AND actif = 1", [$username]);

        if ($results && count($results) > 0) {
            $user = $results[0];
            
            // Comparaison avec la colonne correcte
            if ($password === trim($user['mot_de_passe'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['prenom'] . ' ' . $user['nom'];
                $_SESSION['user_role'] = $user['role'];
                return true;
            }
        }
        return false;
    }

    public static function getUser() {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'nom' => $_SESSION['user_nom'] ?? 'Utilisateur',
            'role' => $_SESSION['user_role'] ?? 'caissier'
        ];
    }
}
