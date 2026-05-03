<?php
require_once '../../includes/classes/Database.php';

// Initialisation de la connexion
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $nom = htmlspecialchars(trim($_POST['nom']));
    $telephone = htmlspecialchars(trim($_POST['telephone']));
    $email = htmlspecialchars(trim($_POST['email'] ?? ''));
    $adresse = htmlspecialchars(trim($_POST['adresse'] ?? ''));
    
    // Le pivot de traçabilité (Immatriculation)
    // On force la mise en majuscules pour éviter les doublons (ex: dk11 vs DK11)
    $immat = strtoupper(htmlspecialchars(trim($_POST['immatriculation'])));

    try {
        // 1. Vérifier si l'immatriculation existe déjà pour éviter les doublons
        $check = $db->prepare("SELECT id_client FROM clients WHERE immatriculation = ?");
        $check->execute([$immat]);
        
        if ($check->rowCount() > 0) {
            header("Location: ../../index.php?status=error&msg=Vehicule_deja_enregistre");
            exit();
        }

        // 2. Préparation de l'insertion
        // On remplit TOUTES les colonnes d'immatriculation par sécurité
        $sql = "INSERT INTO clients (
                    prenom, 
                    nom, 
                    telephone, 
                    email, 
                    adresse, 
                    immatriculation, 
                    immatriculation_principale, 
                    immatriculation_vehicule,
                    date_creation
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $db->prepare($sql);
        
        // On passe $immat pour les trois colonnes de plaques
        $result = $stmt->execute([
            $prenom, 
            $nom, 
            $telephone, 
            $email, 
            $adresse, 
            $immat, 
            $immat, 
            $immat
        ]);

        if ($result) {
            $new_id = $db->lastInsertId();
            // Redirection vers le profil du client fraîchement créé
            header("Location: profil.php?id=" . $new_id . "&status=success&msg=Client_cree");
        } else {
            header("Location: ../../index.php?status=error&msg=Echec_insertion");
        }

    } catch (PDOException $e) {
        // En cas d'erreur SQL, on affiche le message pour le débogage (à retirer en production)
        die("Erreur lors de la création du dossier : " . $e->getMessage());
    }
} else {
    // Si on tente d'accéder au script sans POST
    header("Location: ../../index.php");
    exit();
}
