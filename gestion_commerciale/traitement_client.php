<?php
// Fichier : traitement_client.php
// Gère les opérations CREATE, UPDATE et DELETE pour la table 'clients'

session_start();
include_once 'db_connect.php';

// Vérification de la session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

// Récupérer l'action depuis POST ou GET
$action = $_REQUEST['action'] ?? '';
$conn = db_connect();
$message_prefixe = "❌ Erreur : ";
$redirection_url = "crud_clients.php";

// ----------------------------------------------------
// LOGIQUE CREATE (Ajouter un nouveau client)
// ----------------------------------------------------
if ($action === 'ajouter' && $_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Utilisation de filter_input pour la validation et le nettoyage
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $adresse = filter_input(INPUT_POST, 'adresse', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    // On peut utiliser un filtre spécifique pour le téléphone si le format est fixe, sinon simple nettoyage
    $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_NUMBER_INT); 
    
    if ($nom) { // Le nom est le seul champ requis ici (selon le formulaire)
        
        $sql = "INSERT INTO clients (nom, adresse, telephone) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sss", $nom, $adresse, $telephone);

            if ($stmt->execute()) {
                $_SESSION['message'] = "✅ Client **$nom** ajouté avec succès !";
            } else {
                $_SESSION['message'] = $message_prefixe . "Erreur SQL lors de l'ajout: " . $conn->error;
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = $message_prefixe . "Échec de la préparation de la requête (Insertion).";
        }
    } else {
        $_SESSION['message'] = $message_prefixe . "Le nom du client est requis.";
    }

// ----------------------------------------------------
// LOGIQUE UPDATE (Modifier un client existant)
// ----------------------------------------------------
} elseif ($action === 'modifier' && $_SERVER["REQUEST_METHOD"] === "POST") {
    
    $id_client = filter_input(INPUT_POST, 'id_client', FILTER_VALIDATE_INT);
    $nom = filter_input(INPUT_POST, 'nom', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $adresse = filter_input(INPUT_POST, 'adresse', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_NUMBER_INT);
    
    if ($id_client > 0 && $nom) {
        
        $sql = "UPDATE clients SET nom = ?, adresse = ?, telephone = ? WHERE id_client = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sssi", $nom, $adresse, $telephone, $id_client);

            if ($stmt->execute()) {
                $_SESSION['message'] = "🔄 Client **$nom** modifié avec succès !";
            } else {
                $_SESSION['message'] = $message_prefixe . "Erreur SQL lors de la modification: " . $conn->error;
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = $message_prefixe . "Échec de la préparation de la requête (Modification).";
        }
    } else {
        $_SESSION['message'] = $message_prefixe . "ID de client ou Nom invalide/manquant pour la modification.";
    }

// ----------------------------------------------------
// LOGIQUE DELETE (Supprimer un client)
// ----------------------------------------------------
} elseif ($action === 'supprimer' && $_SERVER["REQUEST_METHOD"] === "GET") {
    
    $id_client = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    
    if ($id_client > 0) {
        
        $sql = "DELETE FROM clients WHERE id_client = ?";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("i", $id_client);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $_SESSION['message'] = "🗑️ Client (ID: $id_client) supprimé avec succès.";
                } else {
                    $_SESSION['message'] = $message_prefixe . "Le client (ID: $id_client) n'existe pas ou a déjà été supprimé.";
                }
            } else {
                 // Gère l'erreur de clé étrangère (si le client est lié à une facture)
                if ($conn->errno == 1451) {
                    $_SESSION['message'] = $message_prefixe . "Impossible de supprimer ce client. Il est lié à des **factures existantes** et doit être archivé au lieu d'être supprimé.";
                } else {
                    $_SESSION['message'] = $message_prefixe . "Erreur SQL lors de la suppression: " . $conn->error;
                }
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = $message_prefixe . "Échec de la préparation de la requête (Suppression).";
        }
    } else {
        $_SESSION['message'] = $message_prefixe . "ID de client invalide pour la suppression.";
    }
    
} else {
    // Si l'action n'est pas reconnue ou la méthode est incorrecte
    $_SESSION['message'] = $message_prefixe . "Action invalide ou méthode de requête incorrecte.";
}

$conn->close();
header("Location: " . $redirection_url);
exit();
?>
