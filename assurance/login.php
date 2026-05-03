<?php
session_start();
require_once 'config/db.php';

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Vérification dans la base de données
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM utilisateurs WHERE nom_utilisateur = :username AND actif = 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Vérification du mot de passe
    if($user) {
        // Vérifier le mot de passe (hashé ou en clair pour admin123)
        if(password_verify($password, $user['mot_de_passe']) || ($username == 'admin' && $password == 'admin123')) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = ($user['prenom'] ? $user['prenom'] . ' ' : '') . $user['nom'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['username'] = $user['nom_utilisateur'];
            
            // Mettre à jour la dernière connexion (si la colonne existe)
            try {
                $update = $db->prepare("UPDATE utilisateurs SET dernier_connexion = NOW() WHERE id = :id");
                $update->execute([':id' => $user['id']]);
            } catch(PDOException $e) {
                // Ignorer si la colonne n'existe pas
            }
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Mot de passe incorrect";
        }
    } else {
        // Créer l'utilisateur admin s'il n'existe pas
        if($username == 'admin' && $password == 'admin123') {
            $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
            $insert = $db->prepare("INSERT INTO utilisateurs (nom_utilisateur, mot_de_passe, nom, prenom, role, actif) 
                                    VALUES (:username, :password, 'Administrateur', 'Système', 'admin', 1)");
            $insert->execute([
                ':username' => 'admin',
                ':password' => $password_hash
            ]);
            
            $_SESSION['user_id'] = $db->lastInsertId();
            $_SESSION['user_name'] = 'Administrateur Système';
            $_SESSION['user_role'] = 'admin';
            $_SESSION['username'] = 'admin';
            
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Utilisateur non trouvé";
        }
    }
    
    if($error) {
        header('Location: index.php?error=' . urlencode($error));
        exit;
    }
}

// Si accès direct, rediriger
header('Location: index.php');
?>
