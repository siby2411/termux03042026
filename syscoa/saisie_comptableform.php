<?php
// Fichier: modules/saisie_comptable/saisie_form.php
// Rôle: Formulaire pour enregistrer une nouvelle écriture comptable (Ligne de journal).

// Définir le titre pour le header
$page_title = "Saisie d'Écriture Comptable";

// Inclusion de l'en-tête (Navigation, styles, etc.)
require_once '../../includes/header.php'; 
// Inclusion de la connexion à la base de données (simulée ici pour l'exemple)
// require_once '../../includes/config.php'; 

// SIMULATION DE CONNEXION ET DONNÉES (À REMPLACER PAR DE VRAIES REQUÊTES SQL)
function get_journaux() {
    // SELECT journal_code, intitule FROM journaux;
    return [
        ['code' => 'AC', 'intitule' => 'Achats (AC)'],
        ['code' => 'VT', 'intitule' => 'Ventes (VT)'],
        ['code' => 'BK', 'intitule' => 'Banque (BK)'],
        ['code' => 'OD', 'intitule' => 'Opérations Diverses (OD)']
    ];
}

function get_comptes_principaux() {
    // SELECT compte_num, intitule FROM comptes_pcgo WHERE CHAR_LENGTH(compte_num) <= 4;
    return [
        ['num' => '41100000', 'intitule' => 'Clients'],
        ['num' => '60100000', 'intitule' => 'Achats de marchandises'],
        ['num' => '70100000', 'intitule' => 'Ventes de produits finis'],
        ['num' => '52100000', 'intitule' => 'Banque']
    ];
}

$journaux = get_journaux();
$comptes = get_comptes_principaux();

// LOGIQUE DE TRAITEMENT (SIMULATION DE L'INSERTION DANS LA TABLE ecritures)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valider les données...
    $date = $_POST['date_ecriture'];
    $journal = $_POST['journal_code'];
    $compte = $_POST['compte_num'];
    $libelle = $_POST['libelle'];
    $debit = $_POST['debit'] ?? 0;
    $credit = $_POST['credit'] ?? 0;
    
    // Si $debit > 0 et $credit > 0 sur la même ligne, c'est une erreur de saisie.
    // Il faudrait une table temporaire pour les lignes ou une boucle ici.
    
    // Exemple de requête INSERT (À IMPLÉMENTER AVEC PDO/MySQLi)
    /*
    $sql = "INSERT INTO ecritures (date_ecriture, journal_code, compte_num, libelle, debit, credit) 
            VALUES (?, ?, ?, ?, ?, ?)";
    // Exécuter l'insertion...
    */

    $message = "Écriture enregistrée (simulée) : Compte {$compte} | Débit: {$debit} | Crédit: {$credit}";
}
?>

<h1 class="text-3xl font-semibold text-gray-800 mb-6">Saisie d'une Nouvelle Écriture</h1>

<?php if (isset($message)): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
        <p class="font-bold">Succès :</p>
        <p><?php echo $message; ?></p>
    </div>
<?php endif; ?>

<form method="POST" action="saisie_form.php" class="bg-white p-6 rounded-lg shadow-md max-w-4xl">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="date_ecriture" class="block text-sm font-medium text-gray-700 mb-1">Date de l'Écriture</label>
            <input type="date" name="date_ecriture" id="date_ecriture" value="<?php echo date('Y-m-d'); ?>" required 
                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
        </div>
        
        <div>
            <label for="journal_code" class="block text-sm font-medium text-gray-700 mb-1">Journal</label>
            <select name="journal_code" id="journal_code" required 
                    class="w-full p-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                <?php foreach ($journaux as $journal): ?>
                    <option value="<?php echo $journal['code']; ?>"><?php echo $journal['intitule']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Section Ligne Comptable (Débit/Crédit) -->
    <h3 class="text-xl font-medium text-gray-800 mb-4 border-b pb-2">Détail de la Ligne</h3>

    <div class="grid grid-cols-1 gap-6">
        <div>
            <label for="compte_num" class="block text-sm font-medium text-gray-700 mb-1">Compte Général (PCGO)</label>
            <select name="compte_num" id="compte_num" required 
                    class="w-full p-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
                <option value="">-- Sélectionner un compte --</option>
                <?php foreach ($comptes as $compte): ?>
                    <option value="<?php echo $compte['num']; ?>"><?php echo $compte['num'] . ' - ' . $compte['intitule']; ?></option>
                <?php endforeach; ?>
            </select>
            <small class="text-gray-500">Utilisez le compte collectif pour les tiers (411, 401).</small>
        </div>

        <div>
            <label for="libelle" class="block text-sm font-medium text-gray-700 mb-1">Libellé</label>
            <input type="text" name="libelle" id="libelle" required 
                   class="w-full p-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="Ex: Facture 2025/001 ou Virement client">
        </div>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="debit" class="block text-sm font-medium text-gray-700 mb-1">Montant au Débit</label>
                <input type="number" step="0.01" name="debit" id="debit" value="" 
                       class="w-full p-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="0.00">
            </div>
            
            <div>
                <label for="credit" class="block text-sm font-medium text-gray-700 mb-1">Montant au Crédit</label>
                <input type="number" step="0.01" name="credit" id="credit" value="" 
                       class="w-full p-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500" placeholder="0.00">
            </div>
            <small class="text-red-500 col-span-2">Attention : Seul le Débit OU le Crédit doit être rempli par ligne.</small>
        </div>
        
    </div>

    <div class="mt-6">
        <button type="submit" class="btn-primary-ohada text-white font-bold py-2 px-4 rounded-md shadow-lg hover:shadow-xl">
            <i class="fas fa-save mr-2"></i> Enregistrer l'Écriture
        </button>
    </div>
</form>

<?php
// Inclusion du pied de page
require_once '../../includes/footer.php';
?>



