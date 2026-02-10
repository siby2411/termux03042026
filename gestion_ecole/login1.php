<?php
// Active l'affichage des erreurs pour le débogage (à retirer en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'db_connect_ecole.php';

// Rediriger si l'utilisateur est déjà connecté
if (isset($_SESSION['role'])) {
    header("Location: index.php");
    exit();
}

$conn = db_connect_ecole();

if ($conn === null) {
    die("La connexion à la base de données n'a pas pu être établie. Vérifiez db_connect_ecole.php.");
}

$message = ''; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Nettoyage des entrées (utiliser les prepared statements pour éviter l'injection SQL)
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Utilisation des prepared statements
    $stmt = $conn->prepare("SELECT user_id, username, password, role, id_entite_associe, entite_type 
                            FROM utilisateurs_ecole 
                            WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // 1. Vérification du mot de passe (via hachage)
        if (password_verify($password, $user['password'])) {
            
            // 2. Connexion réussie : Initialisation de la session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['entite_id'] = $user['id_entite_associe']; // ID du prof/etu/null
            $_SESSION['entite_type'] = $user['entite_type']; 

            // 3. Redirection basée sur le rôle
            switch ($user['role']) {
                case 'admin':
                    // Redirection par défaut pour l'admin vers le tableau de bord principal
                    header("Location: index.php");
                    break;
                case 'professeur':
                    // Redirection vers la page de gestion des notes du professeur
                    header("Location: prof_gestion_notes.php");
                    break;
                case 'etudiant':
                    // Redirection vers son bulletin/espace personnel
                    header("Location: etudiant_bulletin.php");
                    break;
                default:
                    // Rôle non géré ou par défaut
                    header("Location: index.php");
                    break;
            }
            exit();
            
        } else {
            // Mot de passe incorrect
            $message = "Nom d'utilisateur ou mot de passe incorrect.";
        }

    } else {
        // Utilisateur introuvable
        $message = "Nom d'utilisateur ou mot de passe incorrect.";
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Gestion École</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-6 rounded-lg shadow-xl w-96">
        <h1 class="text-3xl font-extrabold text-blue-600 mb-6 text-center">Connexion Espace École 🔑</h1>
        
        <?php if ($message): ?>
            <p class="bg-red-100 text-red-700 p-3 rounded mb-4 text-center"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 font-semibold mb-2">Nom d'utilisateur</label>
                <input type="text" name="username" id="username" class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div class="mb-6">
                <label for="password" class="block text-gray-700 font-semibold mb-2">Mot de passe</label>
                <input type="password" name="password" id="password" class="w-full border border-gray-300 px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <button type="submit" class="w-full bg-blue-600 text-white font-bold p-3 rounded-lg hover:bg-blue-700 transition duration-200">
                Se Connecter
            </button>
            
        </form>
        <p class="mt-4 text-center text-sm text-gray-500">
            Tests :<br>
            - Admin : **admin / admin123**<br>
            - Prof : **prof.diop / prof123**<br>
            - Étudiant : **ETU-2025-0001 / etu123**
        </p>
    </div>
</body>
</html>
