<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'ADMIN') { header("Location: login.php"); exit(); }

require_once dirname(__DIR__) . '/config/config.php';

try {
    $exercice = date('Y');
    
    // Calcul du résultat
    $produits = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799 AND YEAR(date_ecriture) = ?");
    $produits->execute([$exercice]);
    $total_produits = $produits->fetchColumn();
    
    $charges = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699 AND YEAR(date_ecriture) = ?");
    $charges->execute([$exercice]);
    $total_charges = $charges->fetchColumn();
    
    $resultat = $total_produits - $total_charges;
    
    // Écriture de clôture
    $sql = "INSERT INTO ECRITURES_COMPTABLES (date_ecriture, libelle, compte_debite_id, compte_credite_id, montant, reference_piece, type_ecriture) VALUES (?, ?, ?, ?, ?, ?, 'CLOTURE')";
    $stmt = $pdo->prepare($sql);
    
    if ($resultat > 0) {
        $stmt->execute([date('Y-m-d'), "Clôture exercice $exercice - Bénéfice", 112, 120, $resultat, "CLOT-$exercice", 'CLOTURE']);
    } else {
        $stmt->execute([date('Y-m-d'), "Clôture exercice $exercice - Perte", 120, 112, abs($resultat), "CLOT-$exercice", 'CLOTURE']);
    }
    
    $_SESSION['message'] = "✅ Clôture de l'exercice $exercice effectuée - Résultat : " . number_format($resultat, 0, ',', ' ') . " FCFA";
} catch(Exception $e) {
    $_SESSION['error'] = "Erreur : " . $e->getMessage();
}

header("Location: travaux_fin_exercice.php");
exit();
