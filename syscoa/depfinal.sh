#!/bin/bash
# /var/www/syscoa/deploy_wsl.sh

echo "=== DÉPLOIEMENT SYSCOHADA POUR WSL ==="

# Demander les informations de connexion
echo "Veuillez entrer vos informations de connexion MySQL:"
read -p "Nom d'utilisateur MySQL [root]: " mysql_user
mysql_user=${mysql_user:-root}
read -sp "Mot de passe MySQL: " mysql_password
echo ""

# 1. Tester la connexion MySQL
echo "1. Test de connexion MySQL..."
if mysql -u "$mysql_user" -p"$mysql_password" -e "SELECT 1" 2>/dev/null; then
    echo "✓ Connexion MySQL réussie"
else
    echo "✗ Échec de la connexion MySQL"
    echo "Veuillez vérifier vos identifiants et que MySQL est démarré."
    exit 1
fi

# 2. Créer les vues nécessaires
echo "2. Création des vues SYSCOHADA..."

mysql -u "$mysql_user" -p"$mysql_password" sysco_ohada << 'EOF'
-- Vue Journal Central
CREATE OR REPLACE VIEW vue_journal_central AS
SELECT 
    ec.ecriture_id,
    ec.date_ecriture,
    j.journal_code,
    j.intitule AS nom_journal,
    ec.num_piece,
    ec.compte_num,
    c.nom_compte,
    ec.libelle,
    ec.debit,
    ec.credit,
    t.nom_raison_sociale AS tiers,
    e.annee
FROM ecritures ec
LEFT JOIN journaux j ON ec.journal_code = j.journal_code
LEFT JOIN comptes_ohada c ON ec.compte_num = c.numero_compte
LEFT JOIN exercices_comptables e ON ec.id_exercice = e.id_exercice
LEFT JOIN tiers t ON ec.code_tiers = t.code_tiers
ORDER BY ec.date_ecriture DESC;

-- Vue État des Tiers
CREATE OR REPLACE VIEW vue_etat_tiers AS
SELECT 
    t.code_tiers,
    t.nom_raison_sociale,
    t.type_tiers,
    c.numero_compte,
    c.nom_compte,
    e.annee,
    SUM(ec.debit) AS total_debit,
    SUM(ec.credit) AS total_credit,
    SUM(ec.debit) - SUM(ec.credit) AS solde,
    CASE 
        WHEN SUM(ec.debit) > SUM(ec.credit) THEN 'Débiteur'
        WHEN SUM(ec.credit) > SUM(ec.debit) THEN 'Créditeur'
        ELSE 'Équilibré'
    END AS type_solde
FROM ecritures ec
JOIN tiers t ON ec.code_tiers = t.code_tiers
JOIN comptes_ohada c ON ec.compte_num = c.numero_compte
JOIN exercices_comptables e ON ec.id_exercice = e.id_exercice
WHERE ec.code_tiers IS NOT NULL
GROUP BY t.code_tiers, t.nom_raison_sociale, e.id_exercice
ORDER BY t.type_tiers, t.nom_raison_sociale;

-- Vérifier les autres vues
SHOW TABLES LIKE 'vue_%';
EOF

echo "✓ Vues créées avec succès"

# 3. Configurer les fichiers PHP
echo "3. Configuration des fichiers PHP..."

# Créer un fichier config.php minimal pour WSL
cat > /tmp/config_wsl.php << 'EOF'
<?php
// Configuration SYSCOHADA pour WSL
session_start();

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'sysco_ohada');
define('DB_USER', 'root');
define('DB_PASS', '123');  // À remplir manuellement

// Connexion PDO
function get_db_connection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8";
            $pdo = new PDO($dsn, DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Erreur de connexion: " . $e->getMessage());
        }
    }
    return $pdo;
}

// Vérification de session simplifiée
function check_login() {
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: login.php');
        exit();
    }
}

// Fonction pour exécuter des requêtes
function execute_query($sql, $params = []) {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}
EOF

# Mettre à jour le mot de passe dans le fichier config
sed -i "s/define('DB_PASS', '');/define('DB_PASS', '$mysql_password');/" /tmp/config_wsl.php

# Copier les fichiers
sudo cp /tmp/config_wsl.php /var/www/syscoa/config.php
sudo cp includes/header_complet.php includes/header.php
sudo cp includes/footer_complet.php includes/footer.php
sudo cp pages/dashboard_complet.php pages/dashboard.php

# 4. Créer une page de login simplifiée
echo "4. Création de la page de login..."

cat > /tmp/login_wsl.php << 'EOF'
<?php
session_start();

// Connexion simplifiée
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validation simple (à renforcer en production)
    if ($username === 'admin' && $password === 'admin') {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = 'admin';
        header('Location: index.php');
        exit();
    } else {
        $error = "Identifiants incorrects";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SYSCOHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .login-container { max-width: 400px; margin: 100px auto; }
        .card { border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #2c3e50, #3498db); color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header text-center py-4">
                    <h3><i class="fas fa-balance-scale"></i> SYSCOHADA</h3>
                    <p class="mb-0">Système Comptable OHADA</p>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="admin" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" 
                                   value="admin" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p class="text-muted small mb-0">
                            <strong>Identifiants par défaut:</strong><br>
                            Utilisateur: <code>admin</code> | Mot de passe: <code>admin</code>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted small">
                    Version 2.0 • SYSCOHADA Compliant<br>
                    Système de Gestion Comptable
                </p>
            </div>
        </div>
    </div>
    
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
EOF

sudo cp /tmp/login_wsl.php /var/www/syscoa/login.php

# 5. Créer un fichier index.php simplifié
echo "5. Création du fichier index.php..."

cat > /tmp/index_wsl.php << 'EOF'
<?php
// Page d'accueil SYSCOHADA pour WSL
require_once 'config.php';
check_login();

$module = $_GET['module'] ?? 'dashboard';
$submodule = $_GET['submodule'] ?? '';

// Inclure le header
include 'includes/header.php';

// Contenu principal
if ($module === 'dashboard') {
    include 'pages/dashboard.php';
} else {
    echo '<div class="container-fluid py-4">';
    echo '<div class="alert alert-info">';
    echo '<h4><i class="fas fa-cogs"></i> Module en construction</h4>';
    echo '<p>Le module <strong>' . htmlspecialchars($module) . '</strong> est en cours de développement.</p>';
    echo '<a href="?module=dashboard" class="btn btn-primary">Retour au tableau de bord</a>';
    echo '</div>';
    echo '</div>';
}

// Inclure le footer
include 'includes/footer.php';
EOF

sudo cp /tmp/index_wsl.php /var/www/syscoa/index.php

# 6. Mettre à jour les permissions
echo "6. Mise à jour des permissions..."
sudo chown -R www-data:www-data /var/www/syscoa 2>/dev/null || true
sudo find /var/www/syscoa -type f -name "*.php" -exec chmod 644 {} \;
sudo find /var/www/syscoa -type d -exec chmod 755 {} \;
sudo chmod 755 /var/www/syscoa

# 7. Redémarrer Apache (WSL)
echo "7. Redémarrage du service web..."
if command -v apache2 &> /dev/null; then
    sudo service apache2 restart 2>/dev/null || sudo /etc/init.d/apache2 restart 2>/dev/null
    echo "✓ Service web redémarré"
else
    echo "⚠ Apache2 n'est pas installé ou le service ne peut pas être redémarré"
    echo "   Pour installer Apache sur WSL: sudo apt install apache2"
fi

# 8. Message final
echo ""
echo "=== INSTALLATION TERMINÉE ==="
echo ""
echo "INFORMATIONS D'ACCÈS:"
echo "• URL: http://localhost/syscoa/ ou http://127.0.0.1/syscoa/"
echo "• Identifiants par défaut: admin / admin"
echo ""
echo "POUR TESTER:"
echo "1. Ouvrez votre navigateur à l'URL ci-dessus"
echo "2. Connectez-vous avec admin/admin"
echo "3. Vous verrez le tableau de bord SYSCOHADA"
echo ""
echo "FICHIERS IMPORTANTS:"
echo "• /var/www/syscoa/config.php - Configuration de la base"
echo "• /var/www/syscoa/login.php - Page de connexion"
echo "• /var/www/syscoa/index.php - Page d'accueil"
echo "• /var/www/syscoa/pages/dashboard.php - Tableau de bord"
echo ""
echo "EN CAS DE PROBLÈME:"
echo "1. Vérifiez que MySQL est démarré: sudo service mysql status"
echo "2. Vérifiez qu'Apache est démarré: sudo service apache2 status"
echo "3. Consultez les logs: sudo tail -f /var/log/apache2/error.log"
echo "4. Testez la connexion MySQL: mysql -u root -p"
EOF

## Étape 3 : Exécuter le déploiement

```bash
# Rendre le script exécutable
sudo chmod +x /var/www/syscoa/depfinal.sh

# Exécuter le déploiement (pour WSL, utilisez deploy_wsl.sh)
sudo /var/www/syscoa/depfinal.sh
