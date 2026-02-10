<?php
/**
 * Script d'autodiagnostic pour vérifier la connexion PDO.
 */

// ******************************************************
// Configuration d'affichage des erreurs
// ******************************************************
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$host = 'localhost';
$dbname = 'ecole'; 
$user = 'root';
$pass = '123';     // <--- VÉRIFIEZ CE MOT DE PASSE EN PARTICULIER

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$message = '';
$error = '';

try {
    // 1. TENTATIVE DE CONNEXION
    $db = new PDO($dsn, $user, $pass, $options);
    
    // 2. TENTATIVE DE VÉRIFICATION
    $db->query('SELECT 1');

    $message = "
        <h2 class='text-2xl font-bold text-green-700 mb-4'>✅ Connexion Réussie !</h2>
        <p class='text-gray-700'>Le serveur de base de données est accessible avec les identifiants suivants. Vous pouvez supprimer ce fichier.</p>
        <ul class='list-disc list-inside mt-4 text-sm text-green-600 font-mono bg-green-50 p-3 rounded'>
            <li>Hôte: <code>$host</code></li>
            <li>Base de données: <code>$dbname</code></li>
            <li>Utilisateur: <code>$user</code></li>
        </ul>
        <p class='mt-4 text-sm'>Vous pouvez maintenant relancer <code>reset_db.php</code>.</p>
    ";

} catch (\PDOException $e) {
    $error_message = $e->getMessage();
    $error = "
        <h2 class='text-2xl font-bold text-red-700 mb-4'>❌ Échec de la Connexion</h2>
        <p class='text-gray-700'>Impossible d'établir la connexion à la base de données. Veuillez vérifier les points suivants :</p>
        <ol class='list-decimal list-inside mt-4 text-sm space-y-2'>
            <li><strong>Le serveur MySQL est-il démarré ?</strong> (WAMP, XAMPP, etc.)</li>
            <li><strong>Le mot de passe '123' est-il correct pour l'utilisateur 'root' ?</strong></li>
            <li><strong>La base de données 'ecole' existe-t-elle ?</strong></li>
        </ol>
        <h3 class='text-lg font-semibold mt-6 text-gray-800'>Détails Techniques :</h3>
        <code class='block bg-red-50 p-3 rounded-lg text-red-600 font-mono mt-2 overflow-x-auto'>$error_message</code>
    ";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic de Connexion PDO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-4">
    <div class="max-w-xl mx-auto bg-white p-8 rounded-xl shadow-2xl border-t-4 <?php echo $error ? 'border-red-600' : 'border-green-600'; ?>">
        <?php echo $error ? $error : $message; ?>
    </div>
</body>
</html>
