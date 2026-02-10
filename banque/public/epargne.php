<?php
/**
 * PUBLIC/EPARGNE.PHP
 * Module de gestion des comptes d'épargne et courants.
 */

session_start();
require_once '../includes/db.php';
require_once '../includes/header.php'; 
require_once '../includes/fonctions.php'; // Nécessite la fonction get_statut_color

$action = $_GET['action'] ?? 'list';
$message = '';
$clients = [];
$comptes = [];

// --- RÉCUPÉRATION DES CLIENTS POUR LES FORMULAIRES ---
$sql_clients = "
    SELECT 
        ClientID, 
        Nom, 
        Prenoms 
    FROM CLIENTS 
    WHERE Statut = 'Actif'
    ORDER BY Nom, Prenoms
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
// --- LOGIQUE D'INSERTION DE COMPTE (action=ouvrir et méthode POST) ---
// ------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $action == 'ouvrir') {
    $ClientID = (int)$_POST['ClientID'];
    $TypeCompte = $conn->real_escape_string($_POST['TypeCompte']);
    $SoldeInitial = (float)$_POST['SoldeInitial'];
    $DateOuverture = $conn->real_escape_string($_POST['DateOuverture']);
    $Statut = 'Ouvert'; 
    
    $taux = 0.00;
    if ($TypeCompte == 'Epargne') {
        $taux = 3.00; 
    } elseif ($TypeCompte == 'DepotATerme') {
        $taux = 4.50; 
    }
    
    $CodeCompte = 'CPT-' . $ClientID . '-' . mt_rand(100, 999);
    
    if ($ClientID > 0) {
        $sql_insert = "
            INSERT INTO COMPTES (
                ClientID, CodeCompte, TypeCompte, Solde, Statut, 
                TauxInteret, DateOuverture
            ) VALUES (
                $ClientID, '$CodeCompte', '$TypeCompte', $SoldeInitial, '$Statut',
                $taux, '$DateOuverture'
            )";
        
        if ($conn->query($sql_insert)) {
            $message = '<div class="alert alert-success">✅ Le compte **' . $CodeCompte . '** (' . $TypeCompte . ') a été créé avec succès et crédité de ' . number_format($SoldeInitial, 2, ',', ' ') . ' €.</div>';
            $action = 'list';
        } else {
            $message = '<div class="alert alert-danger">❌ Erreur lors de l\'enregistrement du compte: ' . $conn->error . '</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">Veuillez sélectionner un client valide et réessayer.</div>';
    }
}

// ------------------------------------------------------------------
// --- RÉCUPÉRATION DE LA LISTE DES COMPTES (action=list) ---
// ------------------------------------------------------------------
if ($action == 'list') {
    $sql_list = "
        SELECT 
            co.CompteID, co.CodeCompte, co.TypeCompte, co.Solde, co.Statut, 
            co.TauxInteret, co.DateOuverture, 
            cl.Nom AS NomClient, cl.Prenoms AS PrenomClient
        FROM COMPTES co
        JOIN CLIENTS cl ON co.ClientID = cl.ClientID
        ORDER BY co.CompteID DESC
    ";

    $result_list = $conn->query($sql_list);

    if ($result_list) {
        while ($row = $result_list->fetch_assoc()) {
            $comptes[] = $row;
        }
    } else {
        $message = '<div class="alert alert-danger">Erreur lors de la récupération des comptes: ' . $conn->error . '</div>';
    }
}
?>

<h1 class="mt-4"><i class="fas fa-wallet me-2"></i> Gestion des Comptes & Épargne</h1>

<?php if ($message) { echo $message; } ?>

<?php if ($action == 'ouvrir'): ?>
    <h2><i class="fas fa-folder-open me-2"></i> Ouvrir un Nouveau Compte</h2>
    <p class="text-muted">Veuillez sélectionner le client et les détails du compte.</p>

    <form method="POST" action="epargne.php?action=ouvrir">
        
        <div class="mb-3">
            <label for="ClientID" class="form-label">Client :</label>
            <select class="form-select" id="ClientID" name="ClientID" required>
                <option value="">Sélectionner un client</option>
                <?php 
                if (!empty($clients)): 
                    foreach ($clients as $client): ?>
                        <option value="<?= htmlspecialchars($client['ClientID']) ?>">
                            <?= htmlspecialchars($client['Nom']) ?> <?= htmlspecialchars($client['Prenoms']) ?> (ID: <?= $client['ClientID'] ?>)
                        </option>
                    <?php endforeach; 
                else: ?>
                    <option value="" disabled>-- Aucun client actif trouvé --</option>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="TypeCompte" class="form-label">Type de Compte :</label>
            <select class="form-select" id="TypeCompte" name="TypeCompte" required>
                <option value="CompteCourant">Compte Courant</option>
                <option value="Epargne">Épargne (Taux: 3.00%)</option>
                <option value="DepotATerme">Dépôt à Terme (Taux: 4.50%)</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="SoldeInitial" class="form-label">Solde Initial (€) :</label>
            <input type="number" class="form-control" id="SoldeInitial" name="SoldeInitial" step="0.01" min="0" value="0.00" required>
        </div>
        
        <div class="mb-3">
            <label for="DateOuverture" class="form-label">Date d'Ouverture :</label>
            <input type="date" class="form-control" id="DateOuverture" name="DateOuverture" value="<?= date('Y-m-d') ?>" required>
        </div>
        
        <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i> Ouvrir le Compte</button>
        <a href="epargne.php" class="btn btn-secondary ms-2">Retour à la Liste</a>
    </form>

<?php else: ?>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Liste des Comptes</h2>
        <a href="epargne.php?action=ouvrir" class="btn btn-primary"><i class="fas fa-plus me-2"></i> Ouvrir un Compte</a>
    </div>

    <?php if (empty($comptes)): ?>
        <div class="alert alert-warning">Aucun compte trouvé dans la base de données.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Client</th>
                        <th>Type</th>
                        <th>Solde (€)</th>
                        <th>Taux (%)</th>
                        <th>Ouverture</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comptes as $compte): ?>
                        <tr>
                            <td><?= htmlspecialchars($compte['CodeCompte']) ?></td>
                            <td><?= htmlspecialchars($compte['PrenomClient'] . ' ' . $compte['NomClient']) ?></td>
                            <td><?= htmlspecialchars($compte['TypeCompte']) ?></td>
                            <td><strong class="text-primary"><?= number_format($compte['Solde'], 2, ',', ' ') ?></strong></td>
                            <td><?= number_format($compte['TauxInteret'], 2, ',', ' ') ?></td>
                            <td><?= htmlspecialchars($compte['DateOuverture']) ?></td>
                            <td><span class="badge bg-<?= get_statut_color($compte['Statut']) ?>"><?= htmlspecialchars($compte['Statut']) ?></span></td>
                            <td>
                                <a href="operations.php?id=<?= $compte['CompteID'] ?>" class="btn btn-sm btn-info"><i class="fas fa-history"></i></a>
                                <a href="epargne.php?action=close&id=<?= $compte['CompteID'] ?>" class="btn btn-sm btn-danger"><i class="fas fa-lock"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
