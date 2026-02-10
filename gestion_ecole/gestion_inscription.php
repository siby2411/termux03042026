<?php
// Fichier : gestion_inscription.php - Espace Inscription
session_start();
require_once 'db_connect_ecole.php';
require_once 'header_ecole.php';

// Vérification du rôle
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'inscription' && $_SESSION['role'] !== 'admin')) {
    $_SESSION['message'] = "Accès non autorisé.";
    header("Location: login.php");
    exit();
}

$conn = db_connect_ecole();
$annee_actuelle = date('Y') . '-' . (date('y') + 1); // Ex: 2024-2025
$etudiant_code = ''; // Pour la recherche
$etudiant_data = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'rechercher' && !empty($_POST['code_etudiant'])) {
        $etudiant_code = $conn->real_escape_string($_POST['code_etudiant']);
        
        // 1. Récupérer les informations de l'étudiant, sa filière et son cycle
        $stmt = $conn->prepare("
            SELECT 
                e.code_etudiant, e.nom, e.prenom, f.id_filiere, f.nom_filiere, e.cycle
            FROM etudiants e
            JOIN filieres f ON e.id_filiere = f.id_filiere
            WHERE e.code_etudiant = ?
        ");
        $stmt->bind_param("s", $etudiant_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $etudiant_data = $result->fetch_assoc();

            // 2. Récupérer les coûts standards
            $stmt_cout = $conn->prepare("
                SELECT droit_inscription, frais_scolarite_annuel 
                FROM couts_standards 
                WHERE id_filiere = ? AND cycle = ?
            ");
            $stmt_cout->bind_param("is", $etudiant_data['id_filiere'], $etudiant_data['cycle']);
            $stmt_cout->execute();
            $cout_result = $stmt_cout->get_result();
            $couts = $cout_result->fetch_assoc();
            
            $etudiant_data['couts'] = $couts;

            // 3. Récupérer l'état du paiement de l'inscription pour l'année en cours
            $stmt_paiement = $conn->prepare("
                SELECT montant_paye 
                FROM paiements_etudiants 
                WHERE code_etudiant = ? AND annee_academique = ? AND type_paiement = 'inscription'
            ");
            $stmt_paiement->bind_param("ss", $etudiant_code, $annee_actuelle);
            $stmt_paiement->execute();
            $paiement_result = $stmt_paiement->get_result();
            $etudiant_data['inscription_payee'] = $paiement_result->num_rows > 0;
            $etudiant_data['montant_deja_paye'] = $paiement_result->fetch_assoc()['montant_paye'] ?? 0;
            
        } else {
            $message = "Aucun étudiant trouvé avec le code **" . htmlspecialchars($etudiant_code) . "**.";
        }
    } elseif ($_POST['action'] === 'valider_inscription' && isset($_POST['code_etudiant_valider'])) {
        // Logique de validation/paiement de l'inscription
        $code_etudiant_valider = $conn->real_escape_string($_POST['code_etudiant_valider']);
        $montant_paye = (float)$_POST['montant_inscription'];
        
        // Sécurité : On s'assure que le droit d'inscription n'est pas déjà payé ou que le montant est > 0
        if ($montant_paye > 0) {
            // Utiliser REPLACE INTO pour insérer ou mettre à jour (si le UNIQUE KEY est défini)
            // Dans ce cas, nous allons utiliser INSERT IGNORE ou UPDATE si existe.
            
            $stmt = $conn->prepare("
                INSERT INTO paiements_etudiants 
                (code_etudiant, annee_academique, type_paiement, montant_paye, date_paiement)
                VALUES (?, ?, 'inscription', ?, CURDATE())
                ON DUPLICATE KEY UPDATE 
                montant_paye = montant_paye + VALUES(montant_paye),
                date_paiement = CURDATE()
            ");
            $stmt->bind_param("ssd", $code_etudiant_valider, $annee_actuelle, $montant_paye);
            
            if ($stmt->execute()) {
                $message = "Paiement d'inscription de **{$montant_paye} €** validé pour l'étudiant **{$code_etudiant_valider}** pour l'année **{$annee_actuelle}**.";
            } else {
                $message = "Erreur lors de la validation du paiement : " . $conn->error;
            }
        } else {
            $message = "Le montant payé doit être supérieur à zéro.";
        }
    }
}
?>

<h1 class="text-4xl font-extrabold text-green-600 mb-6"><i class="fas fa-user-plus mr-3"></i> Gestion des Inscriptions (<?= htmlspecialchars($annee_actuelle) ?>)</h1>

<?php if ($message): ?>
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 rounded-lg" role="alert">
        <?= nl2br(htmlspecialchars($message)) ?>
    </div>
<?php endif; ?>

<!-- Formulaire de Recherche Étudiant -->
<div class="bg-white p-6 rounded-xl shadow-lg mb-8">
    <h2 class="text-2xl font-semibold text-gray-700 mb-4">Rechercher un Étudiant</h2>
    <form method="POST" action="gestion_inscription.php" class="flex space-x-4">
        <input type="hidden" name="action" value="rechercher">
        <input type="text" name="code_etudiant" placeholder="Code Étudiant (Ex: ETU-2025-0001)" value="<?= htmlspecialchars($etudiant_code) ?>" class="flex-grow border border-gray-300 rounded-lg p-3 focus:ring-green-500 focus:border-green-500" required>
        <button type="submit" class="bg-green-500 text-white font-bold px-6 py-3 rounded-lg hover:bg-green-600 transition duration-200">
            Rechercher <i class="fas fa-search ml-2"></i>
        </button>
    </form>
</div>

<!-- Affichage des Détails et Validation -->
<?php if ($etudiant_data): ?>
<div class="bg-white p-8 rounded-xl shadow-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Détails de l'Étudiant</h2>
    
    <div class="grid grid-cols-2 gap-4 mb-6 text-lg">
        <div><span class="font-semibold text-gray-600">Nom Complet:</span> <?= htmlspecialchars($etudiant_data['prenom'] . ' ' . $etudiant_data['nom']) ?></div>
        <div><span class="font-semibold text-gray-600">Code:</span> <?= htmlspecialchars($etudiant_data['code_etudiant']) ?></div>
        <div><span class="font-semibold text-gray-600">Cycle:</span> <?= htmlspecialchars($etudiant_data['cycle']) ?></div>
        <div><span class="font-semibold text-gray-600">Filière:</span> <?= htmlspecialchars($etudiant_data['nom_filiere']) ?></div>
    </div>
    
    <h3 class="text-xl font-bold text-gray-800 mb-3 border-t pt-4">Statut Inscription (Année <?= htmlspecialchars($annee_actuelle) ?>)</h3>
    
    <?php
        $droit_inscription = $etudiant_data['couts']['droit_inscription'] ?? 0;
        $montant_deja_paye = $etudiant_data['montant_deja_paye'] ?? 0;
        $solde_restant = max(0, $droit_inscription - $montant_deja_paye);
        $statut_couleur = $solde_restant > 0 ? 'bg-red-100 text-red-700 border-red-500' : 'bg-green-100 text-green-700 border-green-500';
    ?>
    
    <div class="p-4 rounded-lg border-l-4 <?= $statut_couleur ?> mb-6">
        <p class="font-bold text-lg">Montant Droit d'Inscription : <?= number_format($droit_inscription, 2, ',', ' ') ?> €</p>
        <p>Montant Déjà Payé : <?= number_format($montant_deja_paye, 2, ',', ' ') ?> €</p>
        <p class="font-extrabold text-xl mt-2">Solde Restant : <?= number_format($solde_restant, 2, ',', ' ') ?> €</p>
    </div>
    
    <?php if ($solde_restant > 0): ?>
        <h3 class="text-xl font-bold text-gray-800 mb-3 border-t pt-4">Valider un Paiement</h3>
        <form method="POST" action="gestion_inscription.php" class="flex space-x-4">
            <input type="hidden" name="action" value="valider_inscription">
            <input type="hidden" name="code_etudiant_valider" value="<?= htmlspecialchars($etudiant_data['code_etudiant']) ?>">
            
            <input type="number" step="0.01" min="0.01" max="<?= $solde_restant ?>" name="montant_inscription" placeholder="Montant du paiement (max <?= $solde_restant ?> €)" class="flex-grow border border-gray-300 rounded-lg p-3 focus:ring-green-500 focus:border-green-500" required>
            
            <button type="submit" class="bg-blue-600 text-white font-bold px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                Enregistrer le Paiement <i class="fas fa-check-circle ml-2"></i>
            </button>
        </form>
    <?php else: ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mt-6">
            <i class="fas fa-check-circle mr-2"></i> Les droits d'inscription sont entièrement payés pour cette année académique.
        </div>
    <?php endif; ?>

</div>
<?php endif; ?>

<?php 
// Fermer la connexion
if ($conn) $conn->close();
require_once 'footer_ecole.php'; 
?>
