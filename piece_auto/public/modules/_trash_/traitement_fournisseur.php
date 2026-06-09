<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = (new Database())->getConnection();
    
    $nom = $_POST['nom_fournisseur'];
    $contact = $_POST['contact_nom'];
    $tel = $_POST['telephone'];
    $email = $_POST['email'];

    $sql = "INSERT INTO FOURNISSEURS (nom_fournisseur, contact_nom, telephone, email) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    
    try {
        $stmt->execute([$nom, $contact, $tel, $email]);
        header("Location: gestion_fournisseurs.php?success=1");
    } catch (PDOException $e) {
        die("Erreur : " . $e->getMessage());
    }
}
