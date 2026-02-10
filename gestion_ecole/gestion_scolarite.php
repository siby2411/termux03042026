<?php
// Fichier : gestion_scolarite.php - Espace Scolarité
session_start();
require_once 'db_connect_ecole.php';
require_once 'header_ecole.php';

// Vérification du rôle
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'scolarite' && $_SESSION['role'] !== 'admin')) {
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
                SELECT frais_scolarite_annuel 
                FROM couts_standards 
                WHERE id_filiere = ? AND cycle = ?
            ");
            $stmt_cout->bind_param("is", $etudiant_data['id_filiere'], $etudiant_data['cycle']);
            $stmt_cout->execute();
            $cout_result = $stmt_cout->get_result();
            $couts = $cout_result->fetch_assoc();
            
            $etudiant_data['couts'] = $couts;

            // 3. Récupérer le montant total payé pour la scolarité pour l'année en cours
            $stmt_paiement = $conn->prepare("
                SELECT SUM(montant_paye) as total_paye
                FROM paiements_etudiants 
                WHERE code_etudiant = ? AND annee_academique = ? AND type_paiement = 'scolarite'
            ");
            $stmt_paiement->bind_param("ss", $etudiant_code, $annee_actuelle);
            $stmt_paiement->execute();
            $paiement_result = $stmt_paiement->get_result();
            $etudiant_data['montant_deja_paye'] = $paiement_result->fetch_assoc()['total_paye'] ?? 0;

        } else {
            $message = "Aucun étudiant trouvé avec le code **" . htmlspecialchars($etudiant_code) . "**.";
        }
    } elseif ($_POST['action'] === 'enregistrer_paiement' && isset($_POST['code_etudiant_valider'])) {
        // Logique d'enregistrement d'un versement de scolarité
        $code_etudiant_valider = $conn->real_escape_string($_POST['code_etudiant_valider']);
        $montant_paye = (float)$_POST['montant_scolarite'];
        
        if ($montant_paye > 0) {
            // Un versement est une nouvelle ligne dans la table paiements_etudiants
            $stmt = $conn->prepare("
                INSERT INTO paiements_etudiants 
                (code_etudiant, annee_academique, type_paiement, montant_paye, date_paiement)
                VALUES (?, ?, 'scolarite', ?, CURDATE())
            ");
            $stmt->bind_param("ssd", $code_etudiant_valider, $annee_actuelle, $montant_paye);
            
            if ($stmt->execute()) {
                $message = "Versement de scolarité de **{$montant_paye} €** enregistré pour l'étudiant **{$code_etudiant_valider}** pour l'année **{$annee_actuelle}**.";
            } else {
                $message = "Erreur lors de l'enregistrement du paiement : " . $conn->error;
            }
        } else {
            $message = "Le montant payé doit être supérieur à zéro.";
        }
    }
}
?>

<h1 class="text-4xl font-extrabold text-yellow-600 mb-6"><i class="fas fa-credit-card mr-3"></i> Gestion des Scolarités (<?= htmlspecialchars($annee_actuelle) ?>)</h1>

<?php if ($message): ?>
    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 rounded-lg" role="alert">
        <?= nl2br(htmlspecialchars($message)) ?>
    </div>
<?php endif; ?>

<!-- Formulaire de Recherche Étudiant -->
<div class="bg-white p-6 rounded-xl shadow-lg mb-8">
    <h2 class="text-2xl font-semibold text-gray-700 mb-4">Rechercher un Étudiant</h2>
    <form method="POST" action="gestion_scolarite.php" class="flex space-x-4">
        <input type="hidden" name="action" value="rechercher">
        <input type="text" name="code_etudiant" placeholder="Code Étudiant (Ex: ETU-2025-0001)" value="<?= htmlspecialchars($etudiant_code) ?>" class="flex-grow border border-gray-300 rounded-lg p-3 focus:ring-yellow-500 focus:border-yellow-500" required>
        <button type="submit" class="bg-yellow-500 text-white font-bold px-6 py-3 rounded-lg hover:bg-yellow-600 transition duration-200">
            Rechercher <i class="fas fa-search ml-2"></i>
        </button>
    </form>
</div>

<!-- Affichage des Détails et Enregistrement du Paiement -->
<?php if ($etudiant_data): ?>
<div class="bg-white p-8 rounded-xl shadow-2xl">
    <h2 class="text-2xl font-bold text-gray-800 mb-4 border-b pb-2">Détails de la Scolarité</h2>
    
    <div class="grid grid-cols-2 gap-4 mb-6 text-lg">
        <div><span class="font-semibold text-gray-600">Nom Complet:</span> <?= htmlspecialchars($etudiant_data['prenom'] . ' ' . $etudiant_data['nom']) ?></div>
        <div><span class="font-semibold text-gray-600">Code:</span> <?= htmlspecialchars($etudiant_data['code_etudiant']) ?></div>
        <div><span class="font-semibold text-gray-600">Cycle:</span> <?= htmlspecialchars($etudiant_data['cycle']) ?></div>
        <div><span class="font-semibold text-gray-600">Filière:</span> <?= htmlspecialchars($etudiant_data['nom_filiere']) ?></div>
    </div>
    
    <h3 class="text-xl font-bold text-gray-800 mb-3 border-t pt-4">Suivi Paiement (Année <?= htmlspecialchars($annee_actuelle) ?>)</h3>
    
    <?php
        $frais_annuel = $etudiant_data['couts']['frais_scolarite_annuel'] ?? 0;
        $montant_deja_paye = $etudiant_data['montant_deja_paye'] ?? 0;
        $solde_restant = max(0, $frais_annuel - $montant_deja_paye);
        $statut_couleur = $solde_restant > 0 ? 'bg-red-100 text-red-700 border-red-500' : 'bg-green-100 text-green-700 border-green-500';
    ?>
    
    <div class="p-4 rounded-lg border-l-4 <?= $statut_couleur ?> mb-6">
        <p class="font-bold text-lg">Frais de Scolarité Annuel : <?= number_format($frais_annuel, 2, ',', ' ') ?> €</p>
        <p>Montant Total des Versements : <?= number_format($montant_deja_paye, 2, ',', ' ') ?> €</p>
        <p class="font-extrabold text-xl mt-2">Solde Restant : <?= number_format($solde_restant, 2, ',', ' ') ?> €</p>
    </div>
    
    <?php if ($solde_restant > 0): ?>
        <h3 class="text-xl font-bold text-gray-800 mb-3 border-t pt-4">Enregistrer un Versement</h3>
        <form method="POST" action="gestion_scolarite.php" class="flex space-x-4">
            <input type="hidden" name="action" value="enregistrer_paiement">
            <input type="hidden" name="code_etudiant_valider" value="<?= htmlspecialchars($etudiant_data['code_etudiant']) ?>">
            
            <input type="number" step="0.01" min="0.01" name="montant_scolarite" placeholder="Montant du versement" class="flex-grow border border-gray-300 rounded-lg p-3 focus:ring-yellow-500 focus:border-yellow-500" required>
            
            <button type="submit" class="bg-blue-600 text-white font-bold px-6 py-3 rounded-lg hover:bg-blue-700 transition duration-200">
                Enregistrer le Versement <i class="fas fa-money-bill-wave ml-2"></i>
            </button>
        </form>
    <?php else: ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg mt-6">
            <i class="fas fa-check-circle mr-2"></i> Les frais de scolarité sont entièrement payés pour cette année académique.
        </div>
    <?php endif; ?>

</div>
<?php endif; ?>

<?php 
// Fermer la connexion
if ($conn) $conn->close();
require_once 'footer_ecole.php'; 
?>
