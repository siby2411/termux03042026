<?php
/**
 * Fichier : insert_operation.php
 * Rôle : Traite le formulaire de saisie simplifiée (reqcompte.html)
 * et insère les écritures comptables (débit et crédit) dans la table journal_comptable.
 * Contexte : Développeur OHADA, automatisation de la saisie.
 */

// 1. Inclure la configuration de la base de données
// Assurez-vous que ce fichier initialise $host, $dbName, $username, $password
require 'config.php';

// Fonction de nettoyage et de validation
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Vérifier si la requête est POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 2. Récupérer et nettoyer les données du formulaire
    $date_ecriture = sanitize_input($_POST['date_ecriture']);
    $journal_code = sanitize_input($_POST['journal_code']);
    $libelle_ecriture = sanitize_input($_POST['libelle_ecriture']);
    
    $compte_debit = sanitize_input($_POST['compte_debit']);
    $montant_debit = (float) $_POST['montant_debit'];
    
    $compte_credit = sanitize_input($_POST['compte_credit']);
    $montant_credit = (float) $_POST['montant_credit'];

    // Récupérer l'ID de l'exercice en cours (hypothèse simplifiée)
    // Dans une application réelle, ceci serait dynamique, mais nous utilisons 1 pour l'exemple.
    $id_exercice = 1; 

    // 3. Validation de l'équilibre
    if (abs($montant_debit - $montant_credit) > 0.01) {
        $message = "Erreur: L'écriture n'est pas équilibrée (Débit ≠ Crédit).";
        $success = false;
    } elseif ($montant_debit <= 0) {
        $message = "Erreur: Le montant doit être strictement positif.";
        $success = false;
    } else {
        $success = true;
    }

    // 4. Exécution des insertions si la validation est réussie
    if ($success) {
        try {
            // Connexion à la base de données
            $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Démarrer une transaction pour garantir l'atomicité (les deux lignes sont insérées ou aucune)
            $pdo->beginTransaction();

            $sql = "INSERT INTO journal_comptable 
                    (date_ecriture, numero_piece, libelle_ecriture, compte_general, montant_debit, montant_credit, journal_code, id_exercice) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            // Générer un numéro de pièce unique (Simplifié pour l'exemple)
            $numero_piece = $journal_code . '-' . time();

            // --- Insertion de la ligne de DÉBIT ---
            $stmt_debit = $pdo->prepare($sql);
            $stmt_debit->execute([
                $date_ecriture, 
                $numero_piece, 
                $libelle_ecriture, 
                $compte_debit, 
                $montant_debit, 
                0.00, // Crédit à zéro
                $journal_code,
                $id_exercice
            ]);

            // --- Insertion de la ligne de CRÉDIT ---
            $stmt_credit = $pdo->prepare($sql);
            $stmt_credit->execute([
                $date_ecriture, 
                $numero_piece, 
                $libelle_ecriture, 
                $compte_credit, 
                0.00, // Débit à zéro
                $montant_credit, 
                $journal_code,
                $id_exercice
            ]);

            // Valider la transaction
            $pdo->commit();
            $message = "Succès ! L'écriture comptable a été enregistrée avec le N° Pièce : {$numero_piece}.";
            
        } catch (PDOException $e) {
            // Annuler la transaction en cas d'erreur
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("DB Error during insertion: " . $e->getMessage());
            $message = "Erreur de base de données lors de l'enregistrement de l'écriture. Veuillez contacter l'administrateur.";
            $success = false;
        }
    }
    
    // 5. Redirection vers la page de saisie avec le message de résultat
    // (Nous utilisons une redirection HTTP simple ici, mais AJAX serait plus moderne)
    $status_param = $success ? 'success' : 'error';
    $msg_param = urlencode($message);
    
    // Normalement on redirige vers l'interface de saisie pour afficher le message
    // header("Location: reqcompte.html?status={$status_param}&message={$msg_param}");
    // exit();

    // Pour l'environnement de développement, affichons juste le résultat
    echo json_encode(['status' => $status_param, 'message' => $message]);

} else {
    // Accès direct au fichier
    echo json_encode(['status' => 'error', 'message' => 'Accès non autorisé.']);
}

?>



