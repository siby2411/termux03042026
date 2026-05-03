<?php
// Fichier : traitement_client.php

session_start();
include_once 'db_connect.php';

// Redirection si l'action n'est pas spécifiée
if (!isset($_REQUEST['action'])) {
    header("Location: crud_clients.php");
    exit();
}

// Vérification de la session
if (!isset($_SESSION['id_vendeur'])) {
    header("Location: login.php");
    exit();
}

$action = $_REQUEST['action'];
$conn = db_connect();

// ----------------------------------------------------
// LOGIQUE CREATE (Ajouter un nouveau client)
// ----------------------------------------------------
if ($action === 'ajouter' && $_SERVER["REQUEST_METHOD"] === "POST") {
    
    $nom = $conn->real_escape_string($_POST['nom']);
    $adresse = $conn->real_escape_string($_POST['adresse']);
    $telephone = $conn->real_escape_string($_POST['telephone']);
    
    $sql = "INSERT INTO clients (nom, adresse, telephone) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nom, $adresse, $telephone);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Client ajouté avec succès !";
    } else {
        $_SESSION['message'] = "Erreur lors de l'ajout du client: " . $conn->error;
    }

// ----------------------------------------------------
// LOGIQUE UPDATE (Modifier un client existant)
// ----------------------------------------------------
} elseif ($action === 'modifier' && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_client'])) {
    
    $id_client = intval($_POST['id_client']);
    $nom = $conn->real_escape_string($_POST['nom']);
    $adresse = $conn->real_escape_string($_POST['adresse']);
    $telephone = $conn->real_escape_string($_POST['telephone']);
    
    $sql = "UPDATE clients SET nom = ?, adresse = ?, telephone = ? WHERE id_client = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nom, $adresse, $telephone, $id_client);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Client modifié avec succès !";
    } else {
        $_SESSION['message'] = "Erreur lors de la modification du client: " . $conn->error;
    }

// ----------------------------------------------------
// LOGIQUE DELETE (Supprimer un client)
// ----------------------------------------------------
} elseif ($action === 'supprimer' && isset($_GET['id'])) {
    
    $id_client = intval($_GET['id']);
    
    // Remarque : Si ce client est lié à une facture, la suppression échouera 
    // à cause de la clé étrangère (protection d'intégrité).
    $sql = "DELETE FROM clients WHERE id_client = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_client);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Client supprimé avec succès.";
    } else {
        $_SESSION['message'] = "Erreur lors de la suppression du client: " . $conn->error;
    }
    
} else {
    $_SESSION['message'] = "Action ou méthode invalide.";
}

$conn->close();
header("Location: crud_clients.php");
exit();
?>
