<?php
/**
 * PUBLIC/CREDITS.PHP
 * Module de gestion du portefeuille de crédits.
 * Gère l'enregistrement réel des crédits et affiche la liste.
 */

session_start();
require_once '../includes/db.php';
require_once '../includes/header.php'; 
require_once '../includes/fonctions.php'; // Pour get_statut_color

$action = $_GET['action'] ?? 'list';
$message = '';
$credits = [];
$clients = []; 

// --- 1. RÉCUPÉRATION DES CLIENTS ET LEURS SOLDES POUR LE FORMULAIRE ---
// Nous récupérons tous les clients actifs pour la liste déroulante
$sql_clients = "
    SELECT 
        c.ClientID, 
        c.Nom, 
        c.Prenoms,
        -- Jointure et somme pour calculer le solde total du client en temps réel
        COALESCE(SUM(co.Solde), 0.00) AS SoldeTotal
    FROM CLIENTS c
    LEFT JOIN COMPTES co ON c.ClientID = co.ClientID AND co.Statut = 'Ouvert'
    WHERE c.Statut = 'Actif'
    GROUP BY c.ClientID
    ORDER BY c.Nom, c.Prenoms
";
$result_clients = $conn->query($sql_clients);

if ($result_clients) {
    while ($row = $result_clients->fetch_assoc()) {
        $clients[] = $row;
    }
} else {
    $message .= '<div class="alert alert-danger">Erreur lors de la récupération des clients: ' . $conn->error . '</div>';
}

// ------------------------------------------------------------------
// --- 2. LOGIQUE D'INSERTION DE NOUVEAU CRÉDIT (action=add et méthode POST) ---
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'add') {
    $ClientID = (int)$_POST['ClientID'];
    $TypeCredit = $conn->real_escape_string($_POST['TypeCredit']);
    $MontantPrincipal = (float)$_POST['MontantPrincipal'];
    $TauxInteretAnnuel = (float)$_POST['TauxInteretAnnuel'];
    $DureeMois = (int)$_POST['DureeMois'];
    $DateDeblocage = $conn->real_escape_string($_POST['DateDeblocage']);
    $Statut = 'Approuve'; 
    $CodeCredit = 'CREDIT-' . date('Ymd') . '-' . mt_rand(1000, 9999);

    if ($ClientID > 0 && $MontantPrincipal > 0) {
        
        // DÉBUT DE LA TRANSACTION : Déblocage du prêt et mise à jour du solde compte client
        $conn->begin_transaction();
        $success = true;

        try {
            // A. Insertion du crédit dans la table CREDITS
            $sql_insert_credit = "
                INSERT INTO CREDITS (
                    ClientID, CodeCredit, TypeCredit, MontantPrincipal, 
                    TauxInteretAnnuel, DureeMois, DateDeblocage, Statut
                ) VALUES (
                    $ClientID, '$CodeCredit', '$TypeCredit', $MontantPrincipal, 
                    $TauxInteretAnnuel, $DureeMois, '$DateDeblocage', '$Statut'
                )";
            
            if (!$conn->query($sql_insert_credit)) {
                throw new Exception("Erreur d'insertion du crédit: " . $conn->error);
            }

            // B. Mise à jour du solde du compte principal du client (ou du compte courant)
            // On suppose que le prêt est versé sur le Compte Courant (CC)
            
            // 1. Trouver le Compte Courant du client
            $sql_find_cc = "SELECT CompteID FROM COMPTES WHERE ClientID = $ClientID AND TypeCompte = 'CompteCourant' AND Statut = 'Ouvert' LIMIT 1";
            $result_cc = $conn->query($sql_find_cc);
            
            if (!$result_cc || $result_cc->num_rows == 0) {
                // Si le client n'a pas de Compte Courant, on verse sur le premier compte ouvert trouvé
                $sql_find_cc_fallback = "SELECT CompteID FROM COMPTES WHERE ClientID = $ClientID AND Statut = 'Ouvert' LIMIT 1";
                $result_cc = $conn->query($sql_find_cc_fallback);

                if (!$result_cc || $result_cc->num_rows == 0) {
                     throw new Exception("Impossible de trouver un compte ouvert pour verser le prêt.");
                }
            }
            
            $compte_row = $result_cc->fetch_assoc();
            $CompteID = $compte_row['CompteID'];

            // 2. Créditer le montant principal sur ce compte
            $sql_update_solde = "UPDATE COMPTES SET Solde = Solde + $MontantPrincipal WHERE CompteID = $CompteID";
            if (!$conn->query($sql_update_solde)) {
                 throw new Exception("Erreur de mise à jour du solde: " . $conn->error);
            }
            
            // C. Validation de la transaction
            $conn->commit();
            $message = '<div class="alert alert-success">✅ Le crédit **' . $CodeCredit . '** a été enregistré, débloqué et le compte client crédité de ' . number_format($MontantPrincipal, 2, ',', ' ') . ' € .</div>';
            $action = 'list'; 

        } catch (Exception $e) {
            $conn->rollback();
            $message = '<div class="alert alert-danger">❌ Transaction échouée: ' . $e->getMessage() . '</div>';
            $action = 'list'; 
        }

    } else {
        $message = '<div class="alert alert-warning">Veuillez sélectionner un client et entrer un montant valide.</div>';
    }
}

// ------------------------------------------------------------------
// --- 3. RÉCUPÉRATION DE LA LISTE DES CRÉDITS (action=list) ---
// ------------------------------------------------------------------
if ($action == 'list') {
    // ... Logique pour afficher la liste des crédits ...
    // (Non incluse ici pour la concision, mais devrait contenir votre requête SELECT)
    $credits = []; // Remplir le tableau $credits
}
?>

<h1 class="mt-4"><i class="fas fa-hand-holding-usd me-2"></i> Gestion des Crédits</h1>

<?php if ($message) { echo $message; } ?>

<?php if ($action == 'add'): ?>
    <h2><i class="fas fa-plus-circle me-2"></i> Enregistrer un Nouveau Crédit</h2>
    



<form method="POST" action="credits.php?action=add">
    
    <div class="mb-3">
        <label for="MontantPrincipal" class="form-label">Montant du Prêt (€) :</label>
        <input type="number" class="form-control" id="MontantPrincipal" name="MontantPrincipal" step="100.00" min="100" required>
        <div class="form-text">Entrez le montant principal qui sera débloqué.</div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="TauxInteretAnnuel" class="form-label">Taux d'Intérêt Annuel (%) :</label>
            <input type="number" class="form-control" id="TauxInteretAnnuel" name="TauxInteretAnnuel" step="0.01" min="1" max="50" value="6.50" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="DureeMois" class="form-label">Durée (Mois) :</label>
            <input type="number" class="form-control" id="DureeMois" name="DureeMois" min="12" max="120" value="36" required>
        </div>
    </div>
    
    <div class="mb-3">
        <label for="DateDeblocage" class="form-label">Date de Déblocage :</label>
        <input type="date" class="form-control" id="DateDeblocage" name="DateDeblocage" value="<?= date('Y-m-d') ?>" required>
    </div>

    <button type="submit" class="btn btn-success"><i class="fas fa-check-circle me-2"></i> Enregistrer et Débloquer le Crédit</button>
    <a href="credits.php" class="btn btn-secondary ms-2">Annuler</a>
</form>


    
    <script>
        function displayClientSolde() {
            const select = document.getElementById('ClientID');
            const selectedOption = select.options[select.selectedIndex];


            const soldeDisplay = document.getElementById('solde-client-display');
            const currentSolde = document.getElementById('current-solde');

            if (selectedOption.value) {
                const solde = selectedOption.getAttribute('data-solde');
                currentSolde.textContent = solde;
                soldeDisplay.style.display = 'block';
            } else {
                soldeDisplay.style.display = 'none';
            }
        }
        
        // Appel initial pour cacher si rien n'est sélectionné
        document.addEventListener('DOMContentLoaded', displayClientSolde);
    </script>

<?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Portefeuille de Crédits</h2>
        <a href="credits.php?action=add" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Nouveau Crédit</a>
    </div>

    <div class="alert alert-warning">Veuillez implémenter la liste des crédits ici.</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
