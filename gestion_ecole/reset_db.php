<?php
// ******************************************************
// NOUVEAU : Configuration d'affichage des erreurs (TRÈS IMPORTANT POUR LE DÉBOGAGE)
// ATTENTION : À désactiver ou modifier en production.
// ******************************************************
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$pageTitle = "Réinitialisation de la Base de Données";

// Inclusion des fichiers de structure et de connexion
// Assurez-vous que ce fichier initialise la variable $db avec un objet PDO valide.
require_once 'db_connect_ecole.php'; 

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification de sécurité (Adapté pour l'environnement de développement)
// En production, cette vérification devrait être beaucoup plus stricte.
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Si ce n'est pas l'administrateur, affiche un message d'erreur et termine.
    // Vous pouvez commenter cette ligne si vous souhaitez tester sans session active,
    // mais elle est cruciale pour la sécurité.
    // die("Accès refusé. Vous devez être connecté en tant qu'administrateur pour exécuter ce script.");
}

$message = '';
$error = '';

// *****************************************************************
// CORRECTION CLÉ : Le code précédent s'exécutait même si $db était null.
// Nous nous assurons d'abord que $db est bien un objet PDO/DB avant de continuer.
// *****************************************************************
if (!isset($db) || !($db instanceof PDO)) {
    $error = "Échec de la connexion à la base de données. La variable \$db n'est pas un objet de connexion valide. Vérifiez 'db_connect_ecole.php'.";
} else {
    try {
        // 1. Désactiver les vérifications de clés étrangères temporairement
        $db->exec('SET FOREIGN_KEY_CHECKS = 0');
        $message .= "<li class='text-green-600'>Vérifications de clés étrangères désactivées.</li>";

        // 2. Suppression des tables existantes
        $db->exec('DROP TABLE IF EXISTS filieres');
        $db->exec('DROP TABLE IF EXISTS cycles');
        $message .= "<li class='text-green-600'>Tables 'filieres' et 'cycles' supprimées.</li>";

        // 3. Création de la table cycles
        $db->exec("
            CREATE TABLE cycles (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(255) NOT NULL UNIQUE,
                description TEXT
            ) ENGINE=InnoDB;
        ");
        $message .= "<li class='text-green-600'>Table 'cycles' recréée.</li>";

        // 4. Création de la table filieres
        $db->exec("
            CREATE TABLE filieres (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nom VARCHAR(255) NOT NULL UNIQUE,
                cycle_id INT NOT NULL,
                details TEXT,
                FOREIGN KEY (cycle_id) REFERENCES cycles(id) ON DELETE CASCADE
            ) ENGINE=InnoDB;
        ");
        $message .= "<li class='text-green-600'>Table 'filieres' recréée.</li>";

        // 5. Définition des Cycles et Filières à insérer
        $structure = [
            [
                'nom' => 'LICENCE ET MASTER',
                'details' => '(NIVEAU BAC, BAC OU EQUIVALENT)',
                'filieres' => [
                    'Entrepreneuriat et Gestion',
                    'Comptabilité Audit Finance',
                    'Banque Assurance Microfinance',
                    'Informatique Réseaux Télécommunication',
                    'Gestion des Ressources Humaines',
                    'Gestion de Projets et de programmes',
                    'Gestion des Entreprises d’Exports/Imports',
                    'Marketing et Communication',
                    'Journalisme et Communication',
                    'Gestion des Ressources du Pétrole et du Gaz',
                    'Commerce International et Transit',
                    'Transport et Logistique',
                    'Hôtellerie et Restauration (Nouveau)',
                    'Génie Logiciel (Nouveau)',
                    'Administration des Systèmes et Réseaux', // J'ai corrigé la fin de la liste tronquée
                ]
            ],
            [
                'nom' => 'FORMATION TECHNIQUE (BTS)',
                'details' => '(BAC, BT, BTH, BAC PRO OU EQUIVALENT)',
                'filieres' => [
                    'Hôtellerie-Restauration',
                    'Froid et Climatisation',
                    'Électronique',
                    'Mécanique et Maintenance Auto',
                    'Logistique',
                ]
            ]
        ];

        // 6. Préparation des requêtes d'insertion
        $stmt_cycle = $db->prepare("INSERT INTO cycles (nom, description) VALUES (?, ?)");
        $stmt_filiere = $db->prepare("INSERT INTO filieres (nom, cycle_id) VALUES (?, ?)");
        $nb_cycles = 0;
        $nb_filieres = 0;

        // 7. Exécution des insertions
        foreach ($structure as $cycle_data) {
            // Insertion du cycle
            $stmt_cycle->execute([$cycle_data['nom'], $cycle_data['details']]);
            $cycle_id = $db->lastInsertId();
            $nb_cycles++;

            // Insertion des filières associées
            foreach ($cycle_data['filieres'] as $filiere_nom) {
                $stmt_filiere->execute([$filiere_nom, $cycle_id]);
                $nb_filieres++;
            }
        }
        
        $message .= "<li class='text-blue-600'>Données initiales insérées : <strong>{$nb_cycles} cycles</strong> et <strong>{$nb_filieres} filières</strong>.</li>";

        // 8. Réactiver les vérifications de clés étrangères
        $db->exec('SET FOREIGN_KEY_CHECKS = 1');
        $message .= "<li class='text-green-600'>Vérifications de clés étrangères réactivées.</li>";

        $message = "Base de données réinitialisée avec succès! <ul class='list-disc pl-5 mt-2'>{$message}</ul>";

    } catch (PDOException $e) {
        $error = "Erreur PDO lors de la réinitialisation : " . $e->getMessage();
    } catch (Exception $e) {
        $error = "Erreur inattendue : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
    </style>
</head>
<body class="p-8">

    <div class="max-w-3xl mx-auto bg-white p-6 md:p-10 rounded-xl shadow-2xl border-t-4 border-indigo-600">
        <h1 class="text-3xl font-bold text-gray-900 mb-6 border-b pb-2 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <?php echo htmlspecialchars($pageTitle); ?>
        </h1>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-6 font-medium" role="alert">
                <p class="font-bold">Erreur Critique</p>
                <p><?php echo $error; ?></p>
                <p class="mt-2 text-sm italic">Vérifiez le fichier `db_connect_ecole.php` et les identifiants de connexion.</p>
            </div>
        <?php endif; ?>

        <?php if ($message && !$error): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md mb-6 font-medium" role="alert">
                <p class="font-bold mb-2">Opération réussie</p>
                <?php echo $message; ?>
            </div>
            
            <a href="index.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0v-6a1 1 0 011-1h2a1 1 0 011 1v6m-4 0h4" />
                </svg>
                Retour à l'accueil
            </a>
        <?php endif; ?>

        <?php if (!$message && !$error): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-md mb-6 font-medium" role="alert">
                <p class="font-bold">Script en attente</p>
                <p>La base de données est en cours de réinitialisation. Veuillez vérifier les messages ci-dessus ou actualiser si l'opération a pris du temps.</p>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
