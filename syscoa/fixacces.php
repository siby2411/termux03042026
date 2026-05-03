<?php
// /var/www/syscoa/fix_access.php

// Désactiver l'affichage des erreurs en production
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== CORRECTION DES PROBLÈMES D'ACCÈS SYSCOHADA ===\n\n";

// 1. VÉRIFIER LA CONFIGURATION DE LA BASE
$host = '127.0.0.1';
$dbname = 'sysco_ohada';
$username = 'root';
$password = '123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
                   $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);
    
    echo "✓ Connexion à la base de données réussie\n";
    
    // 2. VÉRIFIER LES VUES SYSCOHADA
    echo "\n=== VÉRIFICATION DES VUES SYSCOHADA ===\n";
    
    $views = [
        'vue_balance_compatible',
        'vue_grand_livre_syscohada', 
        'vue_soldes_comptables',
        'vue_module_comptabilite',
        'vue_etat_tiers',
        'vue_journal_central'
    ];
    
    foreach ($views as $view) {
        try {
            $result = $pdo->query("SELECT 1 FROM $view LIMIT 1");
            echo "✓ $view : Accessible\n";
        } catch (PDOException $e) {
            echo "✗ $view : Erreur - " . $e->getMessage() . "\n";
        }
    }
    
    // 3. VÉRIFIER LES TABLES ESSENTIELLES
    echo "\n=== VÉRIFICATION DES TABLES ===\n";
    
    $tables = [
        'comptes_ohada',
        'exercices_comptables',
        'ecritures',
        'journaux',
        'tiers',
        'societes'
    ];
    
    foreach ($tables as $table) {
        try {
            $result = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $result->fetchColumn();
            echo "✓ $table : $count enregistrement(s)\n";
        } catch (PDOException $e) {
            echo "✗ $table : Erreur - " . $e->getMessage() . "\n";
        }
    }
    
    // 4. CRÉER UN FICHIER DE CONFIGURATION CORRIGÉ
    $configContent = '<?php
// CONFIGURATION SYSCOHADA CORRIGÉE
// Fichier : /var/www/syscoa/config_syscohada.php

// Éviter les redéclarations
if (!function_exists("get_db_connection_syscohada")) {
    function get_db_connection_syscohada() {
        static $conn = null;
        
        if ($conn === null) {
            $host = "127.0.0.1";
            $dbname = "sysco_ohada";
            $username = "root";
            $password = "";
            
            try {
                $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
                               $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);
                $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                error_log("Erreur connexion DB: " . $e->getMessage());
                return null;
            }
        }
        return $conn;
    }
}

// Mappage des vues SYSCOHADA
function get_syscohada_data($view, $params = []) {
    $pdo = get_db_connection_syscohada();
    if (!$pdo) return [];
    
    $views = [
        "balance" => "vue_balance_compatible",
        "grand_livre" => "vue_grand_livre_syscohada",
        "soldes" => "vue_soldes_comptables",
        "operations" => "vue_module_comptabilite",
        "tiers" => "vue_etat_tiers",
        "journal" => "vue_journal_central"
    ];
    
    if (!isset($views[$view])) {
        return ["error" => "Vue non trouvée"];
    }
    
    $table = $views[$view];
    $where = [];
    $values = [];
    
    if (isset($params["exercice"])) {
        $where[] = "id_exercice = ?";
        $values[] = $params["exercice"];
    }
    
    if (isset($params["compte"])) {
        $where[] = "compte_num = ?";
        $values[] = $params["compte"];
    }
    
    if (isset($params["date_debut"]) && isset($params["date_fin"])) {
        $where[] = "date_ecriture BETWEEN ? AND ?";
        $values[] = $params["date_debut"];
        $values[] = $params["date_fin"];
    }
    
    $sql = "SELECT * FROM $table";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    if (isset($params["order"])) {
        $sql .= " ORDER BY " . $params["order"];
    }
    
    if (isset($params["limit"])) {
        $sql .= " LIMIT " . (int)$params["limit"];
    }
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Erreur requête $view: " . $e->getMessage());
        return ["error" => $e->getMessage()];
    }
}

// Fonction pour les requêtes directes (pour phpMyAdmin)
function get_sql_for_phpmysql($view, $params = []) {
    $views = [
        "balance" => "vue_balance_compatible",
        "grand_livre" => "vue_grand_livre_syscohada",
        "soldes" => "vue_soldes_comptables",
        "operations" => "vue_module_comptabilite",
        "tiers" => "vue_etat_tiers",
        "journal" => "vue_journal_central"
    ];
    
    if (!isset($views[$view])) return "";
    
    $table = $views[$view];
    $sql = "SELECT * FROM $table";
    
    if (isset($params["exercice"])) {
        $sql .= " WHERE id_exercice = " . (int)$params["exercice"];
    }
    
    return $sql . " LIMIT 0, 25";
}
?>';

    file_put_contents('/var/www/syscoa/config_syscohada.php', $configContent);
    echo "\n✓ Fichier de configuration créé: /var/www/syscoa/config_syscohada.php\n";
    
    // 5. CRÉER UN INDEX.PHP CORRIGÉ
    $indexContent = '<?php
// /var/www/syscoa/index.php - VERSION CORRIGÉE
require_once "config_syscohada.php";

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer la connexion
$pdo = get_db_connection_syscohada();

// Page par défaut
$page = $_GET["page"] ?? "dashboard";
$action = $_GET["action"] ?? "list";

// Routes SYSCOHADA
$routes = [
    "dashboard" => "pages/dashboard.php",
    "balance" => "pages/balance.php",
    "grand_livre" => "pages/grand_livre.php",
    "soldes" => "pages/soldes.php",
    "operations" => "pages/operations.php",
    "tiers" => "pages/tiers.php",
    "journal" => "pages/journal.php"
];

// Inclure la page demandée
$pageFile = $routes[$page] ?? "pages/404.php";
if (file_exists($pageFile)) {
    require_once $pageFile;
} else {
    echo "<h2>Page non trouvée</h2>";
    echo "<p>La page demandée n\'existe pas.</p>";
}
?>';

    file_put_contents('/var/www/syscoa/index_corrige.php', $indexContent);
    echo "✓ Fichier index corrigé créé: /var/www/syscoa/index_corrige.php\n";
    
    // 6. CRÉER UN EXEMPLE DE PAGE
    $balancePage = '<?php
// /var/www/syscoa/pages/balance.php
echo "<h2>Balance SYSCOHADA</h2>";

// Récupérer l\'exercice
$exercice = $_GET["exercice"] ?? 1;

// Obtenir les données
$data = get_syscohada_data("balance", ["exercice" => $exercice]);

if (isset($data["error"])) {
    echo "<div class=\"alert alert-danger\">" . $data["error"] . "</div>";
} else {
    echo "<table class=\"table table-striped\">
            <thead>
                <tr>
                    <th>Compte</th>
                    <th>Libellé</th>
                    <th>Débit</th>
                    <th>Crédit</th>
                    <th>Solde</th>
                </tr>
            </thead>
            <tbody>";
    
    foreach ($data as $row) {
        echo "<tr>
                <td>" . htmlspecialchars($row["numero_compte"]) . "</td>
                <td>" . htmlspecialchars($row["nom_compte"]) . "</td>
                <td>" . number_format($row["total_debit"], 2) . "</td>
                <td>" . number_format($row["total_credit"], 2) . "</td>
                <td>" . number_format($row["solde"], 2) . "</td>
              </tr>";
    }
    
    echo "</tbody></table>";
    
    // Lien pour phpMyAdmin
    $sql = get_sql_for_phpmysql("balance", ["exercice" => $exercice]);
    echo "<div class=\"mt-3\">
            <a href=\"http://192.168.1.33:8080/phpmyadmin/index.php?route=/sql&db=sysco_ohada&sql=" . urlencode($sql) . "\" 
               target=\"_blank\" class=\"btn btn-secondary\">
               Ouvrir dans phpMyAdmin
            </a>
          </div>";
}
?>';

    if (!is_dir('/var/www/syscoa/pages')) {
        mkdir('/var/www/syscoa/pages', 0755, true);
    }
    file_put_contents('/var/www/syscoa/pages/balance.php', $balancePage);
    echo "✓ Page exemple créée: /var/www/syscoa/pages/balance.php\n";
    
    // 7. MESSAGE FINAL
    echo "\n=== CORRECTION TERMINÉE ===\n";
    echo "Actions à effectuer:\n";
    echo "1. Renommez votre ancien index.php: mv /var/www/syscoa/index.php /var/www/syscoa/index_backup.php\n";
    echo "2. Utilisez le nouveau: mv /var/www/syscoa/index_corrige.php /var/www/syscoa/index.php\n";
    echo "3. Redémarrez Apache: sudo systemctl restart apache2\n";
    echo "4. Accédez à: http://192.168.1.33:8080/syscoa/\n";
    
} catch (PDOException $e) {
    echo "✗ Erreur de connexion: " . $e->getMessage() . "\n";
    exit(1);
}
?>
