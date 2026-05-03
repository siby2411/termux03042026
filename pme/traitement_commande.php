<?php
include 'includes/db.php';
session_start();

// Vérification de l'ID et de l'action
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($id > 0 && $action === 'facturer') {
    try {
        // 1. Vérifier si la commande existe et est bien en état 'validee'
        $check = $pdo->prepare("SELECT etat FROM commandes WHERE id = ?");
        $check->execute([$id]);
        $commande = $check->fetch();

        if ($commande && $commande['etat'] === 'validee') {
            // 2. Mise à jour de l'état vers 'facturee'
            $stmt = $pdo->prepare("UPDATE commandes SET etat = 'facturee' WHERE id = ?");
            if ($stmt->execute([$id])) {
                // Optionnel : On pourrait ici générer une ligne dans un journal d'audit
                header("Location: comptabilite.php?success=1&msg=Commande_facturée");
                exit();
            }
        } else {
            header("Location: comptabilite.php?error=1&msg=Etat_invalide");
            exit();
        }
    } catch (PDOException $e) {
        die("Erreur base de données : " . $e->getMessage());
    }
} else {
    header("Location: comptabilite.php");
    exit();
}
