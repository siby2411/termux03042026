
<?php
// Fichier: modules/tiers/tiers_gestion_form.php
// Rôle: CRUD (Création, Lecture, Modification) des fiches Clients et Fournisseurs.

$page_title = "Gestion des Tiers (Clients & Fournisseurs)";
require_once '../../includes/header.php'; 
// Le fichier config.php doit contenir la logique de connexion à la base de données ($db)
// require_once '../../includes/config.php'; 

// SIMULATION DE LA CONNEXION À LA BASE DE DONNÉES ($db)
class DatabaseSimulator {
    private $db;
    public function __construct() {
        // En réalité, ici on initierait la connexion (PDO ou MySQLi)
        // $this->db = new PDO(...);
    }

    // Récupère les comptes collectifs (401 et 411) pour le formulaire
    public function getCollectiveAccounts() {
        // SQL: SELECT compte_num, intitule FROM comptes_pcgo WHERE compte_num LIKE '401%' OR compte_num LIKE '411%';
        return [
            ['num' => '41100000', 'intitule' => '411 - Clients'],
            ['num' => '40100000', 'intitule' => '401 - Fournisseurs'],
        ];
    }
    
    // Récupère la liste de tous les tiers
    public function getTiers() {
        // SQL: SELECT * FROM tiers ORDER BY type_tiers, code_tiers;
        global $tiers_list; // Utilisation de la liste de démo pour simuler le fetch
        return $tiers_list;
    }

    // Fonction de simulation pour l'insertion/mise à jour
    public function saveTiers($data) {
        // Logique de sanitisation et de validation...
        $action = isset($data['code_tiers']) && !empty($data['code_tiers']) ? 'Mis à jour' : 'Créé';
        
        // SQL: INSERT INTO tiers (...) VALUES (...) ON DUPLICATE KEY UPDATE ...
        
        // En réalité, on exécuterait la requête d'insertion ou de mise à jour ici.
        
        return "Tiers '{$data['nom_raison_sociale']}' {$action} avec succès. Type: {$data['type_tiers']}.";
    }
}

$db_sim = new DatabaseSimulator();
$comptes_collectifs = $db_sim->getCollectiveAccounts();
$tiers_list = $db_sim->getTiers();

// DÉMO DATA (sera remplacé par $db_sim->getTiers() en production)
$tiers_list = [
    ['code_tiers' => 'CL001', 'nom_raison_sociale' => 'SARL Alpha Commerce', 'type_tiers' => 'CLIENT', 'compte_collectif' => '41100000', 'email' => 'alpha.c@test.com', 'telephone' => '77 123 45 67'],
    ['code_tiers' => 'FR001', 'nom_raison_sociale' => 'Fournitures BTP Ouest-Africain', 'type_tiers' => 'FOURNISSEUR', 'compte_collectif' => '40100000', 'email' => 'btp@test.com', 'telephone' => '33 800 11 22'],
];


$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collecte des données
    $data = [
        'code_tiers' => trim($_POST['code_tiers']),
        'nom_raison_sociale' => trim($_POST['nom_raison_sociale']),
        'type_tiers' => $_POST['type_tiers'],
        'compte_collectif' => $_POST['compte_collectif'],
        'telephone' => trim($_POST['telephone']),
        'email' => trim($_POST['email']),
        'adresse' => trim($_POST['adresse']),
    ];
    
    // Exécuter la sauvegarde
    $message = $db_sim->saveTiers($data);
}
?>

<h1 class="text-3xl font-bold text-gray-800 mb-8 border-b pb-2">Gestion des Partenaires Commerciaux (Tiers)</h1>

<!-- Affichage des messages de succès -->
<?php if (!empty($message)): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg shadow" role="alert">
        <p class="font-bold">Opération Réussie :</p>
        <p><?php echo htmlspecialchars($message); ?></p>
    </div>
<?php endif; ?>

<div class="flex flex-col lg:flex-row gap-8">
    
    <!-- Formulaire de Saisie/Modification -->
    <div class="lg:w-1/3 bg-white p-6 rounded-xl shadow-lg h-fit">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Créer / Modifier un Tiers</h2>
        
        <form method="POST" action="tiers_gestion_form.php" class="space-y-4">
            
            <!-- Type de Tiers -->
            <div>
                <label for="type_tiers" class="block text-sm font-medium text-gray-700">Type de Tiers</label>
                <select name="type_tiers" id="type_tiers" required class="w-full mt-1 p-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                    <option value="CLIENT">CLIENT (Compte 411)</option>
                    <option value="FOURNISSEUR">FOURNISSEUR (Compte 401)</option>
                    <option value="AUTRE">AUTRE (Ex: Personnel)</option>
                </select>
            </div>
            
            <!-- Compte Collectif OHADA -->
            <div>
                <label for="compte_collectif" class="block text-sm font-medium text-gray-700">Compte Collectif (PCGO)</label>
                <select name="compte_collectif" id="compte_collectif" required class="w-full mt-1 p-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                    <?php foreach ($comptes_collectifs as $compte): ?>
                        <option value="<?php echo $compte['num']; ?>"><?php echo $compte['intitule']; ?></option>
                    <?php endforeach; ?>
                </select>
                <small class="text-gray-500">Le compte collectif est rattaché automatiquement au type de tiers sélectionné.</small>
            </div>

            <!-- Code Tiers (Code Auxiliaire) -->
            <div>
                <label for="code_tiers" class="block text-sm font-medium text-gray-700">Code du Tiers (Ex: CL003)</label>
                <input type="text" name="code_tiers" id="code_tiers" required placeholder="Ex: CL003 ou FR003"
                       class="w-full mt-1 p-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
            </div>

            <!-- Nom / Raison Sociale -->
            <div>
                <label for="nom_raison_sociale" class="block text-sm font-medium text-gray-700">Nom / Raison Sociale</label>
                <input type="text" name="nom_raison_sociale" id="nom_raison_sociale" required placeholder="Nom de l'entreprise ou du particulier"
                       class="w-full mt-1 p-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
            </div>
            
            <!-- Téléphone -->
            <div>
                <label for="telephone" class="block text-sm font-medium text-gray-700">Téléphone</label>
                <input type="tel" name="telephone" id="telephone" placeholder="Format: 77 123 45 67"
                       class="w-full mt-1 p-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" placeholder="contact@example.com"
                       class="w-full mt-1 p-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
            </div>

            <!-- Adresse -->
            <div>
                <label for="adresse" class="block text-sm font-medium text-gray-700">Adresse Complète</label>
                <textarea name="adresse" id="adresse" rows="3" placeholder="Rue, Ville, Pays"
                          class="w-full mt-1 p-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"></textarea>
            </div>

            <!-- Bouton de Soumission -->
            <button type="submit" class="w-full mt-4 btn-primary-ohada text-white font-bold py-2 px-4 rounded-md shadow-md hover:shadow-xl">
                <i class="fas fa-user-plus mr-2"></i> Enregistrer le Tiers
            </button>
        </form>
    </div>

    <!-- Tableau de la Liste des Tiers Existants -->
    <div class="lg:w-2/3">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Liste des Tiers Enregistrés (<?php echo count($tiers_list); ?>)</h2>
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code / Nom</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Compte Col.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($tiers_list as $tiers): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($tiers['code_tiers']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($tiers['nom_raison_sociale']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                <?php echo $tiers['type_tiers'] === 'CLIENT' ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800'; ?>">
                                <?php echo htmlspecialchars($tiers['type_tiers']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo htmlspecialchars($tiers['compte_collectif']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <i class="fas fa-phone-alt mr-1"></i> <?php echo htmlspecialchars($tiers['telephone']); ?><br>
                            <i class="fas fa-envelope mr-1"></i> <?php echo htmlspecialchars($tiers['email']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="#" class="text-indigo-600 hover:text-indigo-900 mr-4">Modifier</a>
                            <a href="#" class="text-red-600 hover:text-red-900">Supprimer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="mt-4 text-sm text-gray-600">
            <i class="fas fa-info-circle mr-1"></i> L'ajout d'un tiers crée un compte auxiliaire prêt à être utilisé dans la saisie comptable.
        </p>
    </div>
</div>

<?php
require_once '../../includes/footer.php';


