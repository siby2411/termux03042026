<?php
/**
 * PUBLIC/CAPITALISATION.PHP
 * Module de calcul et de versement des intérêts sur les comptes d'épargne.
 * CORRIGE: Utilise TypeTransaction et Interet_Crediteur.
 */

session_start();
require_once '../includes/db.php';
require_once '../includes/header.php'; // Ce fichier doit contenir la vérification de session!

$message = '';
$PersonnelID = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'capitaliser' && $PersonnelID) {
    
    $date_capitalisation = date('Y-m-d H:i:s');
    $nombre_comptes_capitalises = 0;
    
    // 1. Récupérer tous les comptes d'Épargne/DAT ouverts avec un solde > 0
    $sql_comptes = "
        SELECT CompteID, Solde, TauxInteret 
        FROM COMPTES 
        WHERE TypeCompte IN ('Epargne', 'DepotATerme') AND Statut = 'Ouvert' AND Solde > 0
    ";
    $result_comptes = $conn->query($sql_comptes);

    if ($result_comptes && $result_comptes->num_rows > 0) {
        
        $conn->begin_transaction();
        
        try {
            while ($compte = $result_comptes->fetch_assoc()) {
                $CompteID = $compte['CompteID'];
                $taux = $compte['TauxInteret'] / 100;
                $solde = $compte['Solde'];
                
                // Calcul des intérêts annuels (Simplifié pour la démo)
                $interets = round($solde * $taux, 2); 
                
                if ($interets > 0) {
                    // A. Mise à jour du Solde du Compte
                    $sql_update_solde = "UPDATE COMPTES SET Solde = Solde + $interets WHERE CompteID = $CompteID";
                    if (!$conn->query($sql_update_solde)) {
                        throw new Exception("Échec de la mise à jour du solde du compte $CompteID.");
                    }

                    // B. Enregistrement de la Transaction 
                    $Description = "Versement intérêts annuels (Taux: " . $taux * 100 . "%)";
                    
                    // **CORRECTION : Utilisation de TypeTransaction et Interet_Crediteur**
                    $TypeTransaction = 'Interet_Crediteur';
                    
                    $sql_insert_transaction = "
                        INSERT INTO TRANSACTIONS (CompteID, Montant, TypeTransaction, Description, DateTransaction, PersonnelID)
                        VALUES ($CompteID, $interets, '$TypeTransaction', '$Description', '$date_capitalisation', $PersonnelID)
                    ";
                    if (!$conn->query($sql_insert_transaction)) {
                        throw new Exception("Échec de l'enregistrement de la transaction (Intérêts) pour le compte $CompteID. Erreur SQL: " . $conn->error);
                    }
                    $nombre_comptes_capitalises++;
                }
            }
            
            $conn->commit();
            $message = '<div class="alert alert-success">✅ Capitalisation réussie ! Intérêts versés sur **' . $nombre_comptes_capitalises . '** comptes.</div>';
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = '<div class="alert alert-danger">❌ Transaction échouée: ' . $e->getMessage() . '</div>';
        }
    } else {
        $message = '<div class="alert alert-info">Aucun compte éligible à la capitalisation trouvé.</div>';
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$PersonnelID) {
     $message = '<div class="alert alert-danger">Vous devez être connecté pour effectuer cette action.</div>';
}
?>

<h1 class="mt-4"><i class="fas fa-calculator me-2"></i> Capitalisation des Intérêts</h1>
<p class="text-muted">Processus manuel pour simuler le versement périodique des intérêts sur les comptes d'épargne et dépôts.</p>

<?php if ($message) { echo $message; } ?>

<div class="card p-4 shadow">
    <h3>Déclencher la Capitalisation</h3>
    <p>Cette action créditera les intérêts sur les comptes d'épargne et enregistrera les transactions correspondantes.</p>
    <form method="POST" action="capitalisation.php">
        <input type="hidden" name="action" value="capitaliser">
        <button type="submit" class="btn btn-primary" onclick="return confirm('Êtes-vous sûr de vouloir déclencher la capitalisation des intérêts ?');">
            <i class="fas fa-hand-holding-usd me-2"></i> Capitaliser Maintenant (<?= date('Y-m-d') ?>)
        </button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
