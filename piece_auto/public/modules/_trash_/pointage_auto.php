<?php
require_once "../includes/db.php";
session_start();

$match_count = 0;
$marge_jours = 5; // Sécurité : l'écart de date max entre compta et banque

try {
    // 1. Récupérer les écritures bancaires (Compte 521) non pointées
    // Nous récupérons la date de l'écriture pour la comparaison
    $ecritures = $pdo->query("SELECT id, date_ecriture, montant, compte_debite_id 
                              FROM ECRITURES_COMPTABLES 
                              WHERE (compte_debite_id = 521 OR compte_credite_id = 521)")->fetchAll();

    foreach ($ecritures as $ecr) {
        $montant = $ecr['montant'];
        $date_compta = $ecr['date_ecriture'];
        
        // 2. Préparation de la requête avec filtre sur le montant ET la date
        // ABS(DATEDIFF(...)) permet de vérifier l'écart dans les deux sens (avant ou après)
        if ($ecr['compte_debite_id'] == 521) {
            // Débit compta (entrée) -> Crédit banque (entrée)
            $sql = "SELECT id FROM releves_bancaires 
                    WHERE credit = :montant 
                    AND pointe = 0 
                    AND ABS(DATEDIFF(date_operation, :date_c)) <= :marge 
                    LIMIT 1";
        } else {
            // Crédit compta (sortie) -> Débit banque (sortie)
            $sql = "SELECT id FROM releves_bancaires 
                    WHERE debit = :montant 
                    AND pointe = 0 
                    AND ABS(DATEDIFF(date_operation, :date_c)) <= :marge 
                    LIMIT 1";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':montant' => $montant,
            ':date_c'   => $date_compta,
            ':marge'    => $marge_jours
        ]);
        
        $banque_ligne = $stmt->fetch();

        if ($banque_ligne) {
            // 3. Match sécurisé trouvé !
            $pdo->prepare("UPDATE releves_bancaires SET pointe = 1 WHERE id = ?")
                ->execute([$banque_ligne['id']]);
            
            $match_count++;
        }
    }

    $_SESSION['message'] = "Rapprochement terminé : $match_count opérations pointées avec succès (marge de $marge_jours jours).";
    header("Location: rapprochement.php");
    exit();

} catch (Exception $e) {
    die("Erreur lors du pointage sécurisé : " . $e->getMessage());
}
