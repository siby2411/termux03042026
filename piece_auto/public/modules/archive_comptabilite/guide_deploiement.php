<?php
$page_title = "Guide de déploiement";
require_once 'inc_navbar.php';
?>
<div class="card">
    <div class="card-header bg-success text-white">
        <h5><i class="bi bi-rocket"></i> Guide de déploiement - Mise en production</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <strong>📋 Prérequis serveur :</strong>
            <ul class="mt-2">
                <li>PHP 8.0 ou supérieur</li>
                <li>MariaDB 10.5 ou supérieur</li>
                <li>Extensions PHP : PDO, MySQLi, GD, ZIP</li>
                <li>Serveur web : Apache/Nginx (recommandé)</li>
            </ul>
        </div>
        
        <h6>🔧 Installation simplifiée :</h6>
        <pre class="bg-dark text-white p-3 rounded">
# 1. Cloner l'application
git clone [repository] /var/www/omega-erp

# 2. Configurer la base de données
mysql -u root -p < sql/init_database.sql

# 3. Configurer Apache
sudo cp config/omega-erp.conf /etc/apache2/sites-available/
sudo a2ensite omega-erp.conf
sudo systemctl reload apache2

# 4. Définir les permissions
sudo chown -R www-data:www-data /var/www/omega-erp/
sudo chmod -R 755 /var/www/omega-erp/

# 5. Accéder à l'application
# http://votre-domaine.com
        </pre>
        
        <div class="alert alert-warning mt-3">
            <i class="bi bi-shield-lock"></i>
            <strong>Sécurité recommandée :</strong>
            <ul>
                <li>Modifier le mot de passe admin par défaut</li>
                <li>Configurer HTTPS (certificat SSL)</li>
                <li>Restreindre l'accès à l'API avec une clé forte</li>
                <li>Effectuer des sauvegardes quotidiennes de la base</li>
            </ul>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>
