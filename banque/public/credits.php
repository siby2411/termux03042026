<?php
/**
 * PUBLIC/CREDITS.PHP
 * Module de gestion du portefeuille de crédits (Octroi et Liste).
 * Utilise 'TypeTransaction' pour la traçabilité.
 */

session_start();
require_once '../includes/db.php';
require_once '../includes/header.php'; 
// Assurez-vous d'avoir une fonction get_statut_color dans ../includes/fonctions.php
require_once '../includes/fonctions.php'; 

// Récupération de l'ID du personnel connecté pour la traçabilité des transactions
$PersonnelID = $_SESSION['user_id'] ?? null;

// Vérification minimale que le header a fait son travail
if (!$PersonnelID) {
    header('Location: login.php');
    exit;
}

$action = $_GET['action'] ?? 'list';
$message = '';
$credits = [];
$clients = []; 

// --- 1. RÉCUPÉRATION DES CLIENTS ET LEURS SOLDES ---
// Calcul du SoldeTotal du client en temps réel (pour information avant octroi)
$sql_clients = "
    SELECT 
        c.ClientID, 
        c.Nom, 
        c.Prenoms,
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
    $message .= '<div class="alert alert-danger">Erreur de BD lors de la récupération des clients: ' . $conn->error . '</div>';
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
        
        $conn->begin_transaction(); // Démarrage de la transaction

        try {
            // A. Insertion du crédit
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
            $CreditID = $conn->insert_id; // Récupère l'ID du crédit créé

            // B. Trouver le Compte à créditer (Compte Courant par défaut)
            $sql_find_cc = "SELECT CompteID FROM COMPTES WHERE ClientID = $ClientID AND Statut = 'Ouvert' ORDER BY TypeCompte = 'CompteCourant' DESC LIMIT 1";
            $result_cc = $conn->query($sql_find_cc);
            
            if (!$result_cc || $result_cc->num_rows == 0) {
                 throw new Exception("Compte client pour le versement du prêt non trouvé.");
            }
            $CompteID = $result_cc->fetch_assoc()['CompteID'];

            // C. Créditer le montant principal sur ce compte
            $sql_update_solde = "UPDATE COMPTES SET Solde = Solde + $MontantPrincipal WHERE CompteID = $CompteID";
            if (!$conn->query($sql_update_solde)) {
                 throw new Exception("Échec de la mise à jour du solde du compte.");
            }
            
            // D. ENREGISTRER LA TRANSACTION (Entrée de Fonds)
            $Montant = $MontantPrincipal;
            $Description = "Déblocage Prêt $CodeCredit";
            
            // **CORRECTION: Utilisation de TypeTransaction (Dépot car c'est un crédit sur le compte client)**
            $TypeTransaction = 'Depot'; 
            $DateTransaction = $DateDeblocage; 
            
            $sql_insert_transaction = "
                INSERT INTO TRANSACTIONS (CompteID, CreditID, Montant, TypeTransaction, Description, DateTransaction, PersonnelID)
                VALUES ($CompteID, $CreditID, $Montant, '$TypeTransaction', '$Description', '$DateTransaction', $PersonnelID)
            ";
            if (!$conn->query($sql_insert_transaction)) {
                 throw new Exception("Échec de l'enregistrement de la transaction (Déblocage). Erreur SQL: " . $conn->error);
            }
            
            $conn->commit();
            $message = '<div class="alert alert-success">✅ Le crédit **' . $CodeCredit . '** a été débloqué et le compte client crédité de ' . number_format($MontantPrincipal, 2, ',', ' ') . ' € .</div>';
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
?>

<h1 class="mt-4"><i class="fas fa-hand-holding-usd me-2"></i> Gestion des Crédits</h1>

<?php if ($message) { echo $message; } ?>

<?php if ($action == 'add'): ?>
    <h2><i class="fas fa-plus-circle me-2"></i> Enregistrer un Nouveau Crédit</h2>
    
    <form method="POST" action="credits.php?action=add">
        
        <div class="mb-3">
            <label for="ClientID" class="form-label">Client :</label>
            <select class="form-select" id="ClientID" name="ClientID" required onchange="displayClientSolde()">
                <option value="">Sélectionner un client</option>
                <?php 
                if (!empty($clients)): 
                    foreach ($clients as $client): ?>
                        <option 
                            value="<?= htmlspecialchars($client['ClientID']) ?>"
                            data-solde="<?= number_format($client['SoldeTotal'], 2, ',', ' ') ?> €">
                            <?= htmlspecialchars($client['Nom']) ?> <?= htmlspecialchars($client['Prenoms']) ?>
                        </option>
                    <?php endforeach; 
                else: ?>
                    <option value="" disabled>-- Aucun client actif trouvé --</option>
                <?php endif; ?>
            </select>
        </div>

        <div id="solde-client-display" class="alert alert-info" style="display: none;">
            Solde total actuel du client : <strong id="current-solde">0.00 €</strong>
        </div>
        
        <div class="mb-3">
            <label for="TypeCredit" class="form-label">Type de Crédit :</label>
            <select class="form-select" id="TypeCredit" name="TypeCredit" required>
                <option value="Conso">Consommation</option>
                <option value="Scolaire">Scolaire</option>
                <option value="Agricole">Agricole</option>
                <option value="Habitat">Habitat</option>
            </select>
        </div>

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
        
        document.addEventListener('DOMContentLoaded', displayClientSolde);
    </script>

<?php else: // Affiche la liste des crédits (action=list) 

    // --- 3. RÉCUPÉRATION DE LA LISTE DES CRÉDITS (action=list) ---
    $statuts_credits = ['Approuve', 'EnCours', 'Solde', 'Defaut', 'Tous'];
    $statut_filtre = $_GET['statut'] ?? 'EnCours'; 
    $safe_statut = $conn->real_escape_string($statut_filtre);
    
    $where_clause = ($safe_statut != 'Tous') ? "WHERE c.Statut = '$safe_statut'" : "";
    
    // Requête pour afficher les crédits avec les détails
    $sql = "
        SELECT 
            c.CreditID, c.CodeCredit, c.MontantPrincipal, c.TauxInteretAnnuel,
            c.DureeMois, c.DateDeblocage, c.Statut, c.TypeCredit,
            cl.Nom AS NomClient, cl.Prenoms AS PrenomClient,
            -- Calcul simplifié du solde restant (doit être ajusté si vous avez une table de remboursement)
            CASE 
                WHEN c.Statut = 'EnCours' THEN c.MontantPrincipal * 0.90
                WHEN c.Statut = 'Approuve' THEN c.MontantPrincipal
                ELSE 0.00
            END AS SoldeRestant,
            -- Calcul simplifié du coût total (principal + intérêts)
            (c.MontantPrincipal * (1 + (c.TauxInteretAnnuel / 100) * (c.DureeMois / 12))) AS CoutTotal
        FROM CREDITS c
        JOIN CLIENTS cl ON c.ClientID = cl.ClientID
        $where_clause
        ORDER BY c.DateDeblocage DESC
    ";

    $result = $conn->query($sql);

    if ($result) {
        $credits = [];
        while ($row = $result->fetch_assoc()) {
            $credits[] = $row;
        }
    } else {
        $message .= '<div class="alert alert-danger">Erreur SQL lors de la liste des crédits: ' . $conn->error . '</div>';
    }
?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Portefeuille de Crédits</h2>
        <a href="credits.php?action=add" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Nouveau Crédit</a>
    </div>

    <div class="mb-3">
        <?php foreach ($statuts_credits as $statut): ?>
            <a href="credits.php?statut=<?= $statut ?>" 
               class="btn btn-sm <?= ($statut_filtre == $statut) ? 'btn-dark' : 'btn-outline-dark' ?> me-1">
                <?= htmlspecialchars($statut) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($credits)): ?>
        <div class="alert alert-warning">Aucun crédit trouvé avec le statut **<?= htmlspecialchars($statut_filtre) ?>**.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Montant Prêté (€)</th>
                        <th>Solde Restant (€)</th>
                        <th>Taux Annuel (%)</th>
                        <th>Durée (Mois)</th>
                        <th>Coût Total (€)</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($credits as $credit): ?>
                        <tr>
                            <td><?= htmlspecialchars($credit['CodeCredit']) ?></td>
                            <td><?= htmlspecialchars($credit['PrenomClient'] . ' ' . $credit['NomClient']) ?></td>
                            <td><?= htmlspecialchars($credit['TypeCredit']) ?></td>
                            <td><?= number_format($credit['MontantPrincipal'], 2, ',', ' ') ?></td>
                            <td><strong class="text-danger"><?= number_format($credit['SoldeRestant'], 2, ',', ' ') ?></strong></td>
                            <td><?= number_format($credit['TauxInteretAnnuel'], 2, ',', ' ') ?></td>
                            <td><?= htmlspecialchars($credit['DureeMois']) ?></td>
                            <td><?= number_format($credit['CoutTotal'], 2, ',', ' ') ?></td>
                            <td><span class="badge bg-<?= get_statut_color($credit['Statut']) ?>"><?= htmlspecialchars($credit['Statut']) ?></span></td>
                            <td>
                                <a href="credits.php?action=view&id=<?= $credit['CreditID'] ?>" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
