<?php
require_once '../../includes/classes/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Récupération des données du formulaire avec protection contre les champs vides
        $designation = $_POST['designation'] ?? null;
        $prix_achat = $_POST['prix_achat'] ?? 0;
        $prix_v = $_POST['prix_vente'] ?? 0;
        $qte = $_POST['qte_initiale'] ?? 0;
        $seuil = $_POST['seuil'] ?? 5;

        // VALIDATION : Si la désignation est vide ou le prix de vente est invalide
        if (!$designation || $prix_v <= 0) {
            header("Location: ../../index.php?status=error&msg=data_missing");
            exit();
        }

        // INSERTION : On ne mentionne pas 'reference_interne' car le TRIGGER s'en occupe
        $sql = "INSERT INTO pieces_detachees (nom_piece, prix_achat, prix_vente, stock_actuel, seuil_alerte) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $designation,
            $prix_achat,
            $prix_v,
            $qte,
            $seuil
        ]);

        if ($result) {
            // Succès : Redirection vers l'index avec notification
            header("Location: ../../index.php?status=success&msg=piece_ajoutee");
        } else {
            header("Location: ../../index.php?status=error&msg=insertion_failed");
        }
        exit();
    }
} catch (Exception $e) {
    // En cas d'erreur fatale (ex: table manquante), on affiche l'erreur proprement
    die("Erreur Omega Tech : " . $e->getMessage());
}
?>
