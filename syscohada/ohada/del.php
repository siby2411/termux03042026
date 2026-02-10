<?php
// Connexion à la base de données
$conn = new mysqli("localhost", "root", "123", "ohada");
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Liste des tables à vider
$tables = [
    'journal_achats',
    'journal_banque',
    'journal_caisse',
    'journal_fournisseurs',
    'journal_operations_diverses',
    'journal_ventes'
];

// Fonction pour vider une table
function viderTable($conn, $table_name) {
    $sql = "DELETE FROM $table_name";
    if ($conn->query($sql) === TRUE) {
        echo "La table $table_name a été vidée avec succès.<br>";
    } else {
        echo "Erreur lors du vidage de la table $table_name : " . $conn->error . "<br>";
    }
}

// Fonction pour supprimer un enregistrement spécifique du journal client
function supprimerJournalClient($conn, $id) {
    $sql = "DELETE FROM journal_client WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); // 'i' indique que le paramètre est un entier

    if ($stmt->execute()) {
        echo "L'enregistrement avec ID $id a été supprimé du journal client avec succès.<br>";
    } else {
        echo "Erreur lors de la suppression de l'enregistrement avec ID $id : " . $stmt->error . "<br>";
    }
    $stmt->close();
}

// Vider chaque table dans la liste
foreach ($tables as $table_name) {
    viderTable($conn, $table_name);
}

// Suppression d'un enregistrement du journal client (par exemple, avec ID 1)
$id_a_supprimer = 1; // Remplacez par l'ID de l'enregistrement que vous souhaitez supprimer
supprimerJournalClient($conn, $id_a_supprimer);

// Fermer la connexion
$conn->close();
?>