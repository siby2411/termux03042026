
<?php
// Fichier : traitement_produit.php
// Gère les opérations CREATE, UPDATE et DELETE pour la table 'produits'

session_start();
include_once 'db_connect.php';

// Redirection si l'action n'est pas spécifiée
if (!isset($_REQUEST['action'])) {
    header("Location: crud_produits.php");
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
// LOGIQUE CREATE (Ajouter un nouveau produit)
// ----------------------------------------------------
if ($action === 'ajouter' && $_SERVER["REQUEST_METHOD"] === "POST") {
    
    $designation = $conn->real_escape_string($_POST['designation']);
    $prix_unitaire = floatval($_POST['prix_unitaire']);
    $stock_initial = intval($_POST['stock_initial']);
    
    // Le champ code_produit est laissé à NULL/vide.
    // Le TRIGGER 'trig_code_produit' va s'activer pour le générer.
    $sql = "INSERT INTO produits (designation, prix_unitaire, stock_actuel) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    // 'sdi' -> string, double (decimal), integer
    $stmt->bind_param("sdi", $designation, $prix_unitaire, $stock_initial);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Produit ajouté avec succès ! Code Produit généré automatiquement.";
    } else {
        $_SESSION['message'] = "Erreur lors de l'ajout du produit: " . $conn->error;
    }

// ----------------------------------------------------
// LOGIQUE UPDATE (Modifier un produit existant)
// ----------------------------------------------------
} elseif ($action === 'modifier' && $_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id_produit'])) {
    
    $id_produit = intval($_POST['id_produit']);
    $designation = $conn->real_escape_string($_POST['designation']);
    $prix_unitaire = floatval($_POST['prix_unitaire']);
    $stock_actuel = intval($_POST['stock_actuel']); // Permet la correction manuelle du stock
    
    $sql = "UPDATE produits SET designation = ?, prix_unitaire = ?, stock_actuel = ? WHERE id_produit = ?";
    $stmt = $conn->prepare($sql);
    // 'sddi' -> string, double (decimal), double (decimal), integer
    $stmt->bind_param("sddi", $designation, $prix_unitaire, $stock_actuel, $id_produit);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Produit modifié avec succès !";
    } else {
        $_SESSION['message'] = "Erreur lors de la modification du produit: " . $conn->error;
    }

// ----------------------------------------------------
// LOGIQUE DELETE (Supprimer un produit)
// ----------------------------------------------------
} elseif ($action === 'supprimer' && isset($_GET['id'])) {
    
    $id_produit = intval($_GET['id']);
    
    // Remarque : Si ce produit est référencé dans 'details_facture', 
    // la suppression sera bloquée (par défaut des FK), sauf si CASCADE est configuré.
    $sql = "DELETE FROM produits WHERE id_produit = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_produit);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Produit supprimé avec succès.";
    } else {
        $_SESSION['message'] = "Erreur lors de la suppression: " . $conn->error;
    }
    
} else {
    // Si l'action n'est pas reconnue ou la méthode est incorrecte
    $_SESSION['message'] = "Action ou méthode invalide.";
}

$conn->close();
header("Location: crud_produits.php");
exit();
?>


