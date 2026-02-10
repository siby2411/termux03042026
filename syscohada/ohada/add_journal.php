<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $date_operation = $_POST['date_operation'];
    $description = $_POST['description'];
    $montant = $_POST['montant'];
    $statut = $_POST['statut'];  // Récupère la valeur Débit/Crédit comme statut
    $type_table = $_POST['type_table']; // Table cible choisie

    // Associer un numéro de compte en fonction de la table choisie
    switch ($type_table) {
        case "journal_achats":
            $numero_compte = "607"; // Achat de marchandises
            break;
        case "journal_ventes":
            $numero_compte = "701"; // Ventes de marchandises
            break;
        case "journal_caisse":
            $numero_compte = "571"; // Caisse
            break;
        case "journal_banque":
            $numero_compte = "512"; // Banque
            break;
        case "journal_fournisseurs":
            $numero_compte = "401"; // Fournisseurs
            break;
        case "journal_clients":  // Cas ajouté pour journal_clients
            $numero_compte = "411"; // Clients
            break;
        default:
            $numero_compte = null;
            break;
    }

    if ($numero_compte) {
        // Connexion à la base de données
        $conn = new mysqli("localhost", "root", "123", "ohada");
        if ($conn->connect_error) {
            die("Erreur de connexion : " . $conn->connect_error);
        }

        // Construire dynamiquement la requête SQL pour la table choisie
        $sql = "INSERT INTO $type_table (date_operation, description, montant, numero_compte, statut) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            die("Erreur de préparation : " . $conn->error);
        }
        
        $stmt->bind_param("sssss", $date_operation, $description, $montant, $numero_compte, $statut);
        
        if ($stmt->execute()) {
            echo "Opération ajoutée avec succès dans la table $type_table.";
        } else {
            echo "Erreur lors de l'ajout de l'opération : " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "Erreur : Table ou numéro de compte invalide.";
    }
}
?>