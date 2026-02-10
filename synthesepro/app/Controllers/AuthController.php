

<?php
// app/Controllers/AuthController.php
require_once 'app/Models/User.php';

class AuthController {

    public function showLoginForm() {
        // Afficher le formulaire de connexion
        require_once 'app/Views/login_form.php'; 
    }

    public function loginAction() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
            $userModel = new User();
            $user = $userModel->authenticate($_POST['email'], $_POST['password']);
            
            if ($user) {
                // Démarrer la session et stocker les infos clés
                session_start();
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_role'] = $user['role']; // Clé essentielle !
                
                // Redirection basée sur le rôle
                $this->redirectToDashboard($user['role']);
            } else {
                $message = "Identifiants invalides.";
                // Afficher le formulaire de connexion avec l'erreur
                require_once 'app/Views/login_form.php'; 
            }
        }
    }

    /**
     * Logique de redirection vers le tableau de bord spécifique.
     */
    private function redirectToDashboard(string $role) {
        switch ($role) {
            case 'ADMIN':
                header('Location: index.php?action=admin_dashboard');
                break;
            case 'COMPTABLE':
                header('Location: index.php?action=comptable_upload'); // Page d'import par défaut
                break;
            case 'LECTEUR':
                header('Location: index.php?action=lecteur_reports'); // Page de consultation
                break;
            default:
                header('Location: index.php?action=logout'); // Rôle inconnu
                break;
        }
        exit();
    }
    
    // ... (Ajouter une méthode logoutAction pour déconnecter)
}



?>

