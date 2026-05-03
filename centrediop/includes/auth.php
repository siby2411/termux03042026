<?php
/**
 * Fonctions d'authentification
 */

function getDB() {
    static $db = null;
    if ($db === null) {
        require_once __DIR__ . '/../config/database.php';
        $database = new Database();
        $db = $database->getConnection();
    }
    return $db;
}

function login($username, $password) {
    $db = getDB();
    
    if (!$db) {
        error_log("Erreur: Connexion BDD échouée dans login()");
        return false;
    }
    
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_service'] = $user['service_id'];
            
            // Créer un token simple avec les infos de base
            $token_data = [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'],
                'nom' => $user['nom'],
                'prenom' => $user['prenom']
            ];
            
            // Stocker le token en session
            $_SESSION['user_token'] = base64_encode(json_encode($token_data));
            
            return true;
        }
    } catch (Exception $e) {
        error_log("Erreur login: " . $e->getMessage());
    }
    
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_destroy();
    header('Location: /index.php');
    exit();
}

function getUserToken() {
    if (isset($_SESSION['user_token'])) {
        return json_decode(base64_decode($_SESSION['user_token']), true);
    }
    return null;
}

function getPatientFromToken() {
    $token = getUserToken();
    return isset($token['patient']) ? $token['patient'] : null;
}

function updateTokenPatient($patient_id) {
    $db = getDB();
    
    if (!$db) {
        error_log("updateTokenPatient: DB non disponible");
        return false;
    }
    
    if (!isset($_SESSION['user_id'])) {
        error_log("updateTokenPatient: Utilisateur non connecté");
        return false;
    }
    
    try {
        // Récupérer les infos du patient
        $stmt = $db->prepare("SELECT id, nom, prenom, code_patient_unique, telephone FROM patients WHERE id = ?");
        $stmt->execute([$patient_id]);
        $patient = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$patient) {
            error_log("updateTokenPatient: Patient $patient_id non trouvé");
            return false;
        }
        
        // Récupérer le token actuel ou en créer un nouveau
        $token = getUserToken();
        if (!$token) {
            $token = [
                'user_id' => $_SESSION['user_id'],
                'role' => $_SESSION['user_role'],
                'nom' => $_SESSION['user_nom'],
                'prenom' => $_SESSION['user_prenom']
            ];
        }
        
        // Ajouter/mettre à jour les infos patient
        $token['patient'] = [
            'id' => $patient['id'],
            'code' => $patient['code_patient_unique'] ?? '',
            'nom' => $patient['nom'],
            'prenom' => $patient['prenom'],
            'telephone' => $patient['telephone'] ?? ''
        ];
        
        // Sauvegarder
        $_SESSION['user_token'] = base64_encode(json_encode($token));
        
        error_log("updateTokenPatient: Patient {$patient['prenom']} {$patient['nom']} chargé avec succès");
        return true;
        
    } catch (Exception $e) {
        error_log("updateTokenPatient Erreur: " . $e->getMessage());
        return false;
    }
}
