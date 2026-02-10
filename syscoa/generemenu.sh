#!/bin/bash
# fix_generate_menu.sh

echo "=== CORRECTION DE LA FONCTION generate_menu() MANQUANTE ==="

echo "1. Ajout de la fonction generate_menu() dans config.php..."
sudo tee -a /var/www/syscoa/config.php << 'EOF'

// Fonction generate_menu() manquante
function generate_menu($current_module = 'dashboard') {
    $modules = [
        'dashboard' => ['Dashboard', 'fas fa-tachometer-alt'],
        'journaux' => ['Journaux', 'fas fa-book'],
        'grand_livre' => ['Grand Livre', 'fas fa-file-invoice-dollar'],
        'balance' => ['Balance', 'fas fa-scale-balanced'],
        'comptes' => ['Plan Comptable', 'fas fa-list'],
        'tiers' => ['Tiers', 'fas fa-users'],
        'rapports' => ['Rapports', 'fas fa-chart-bar'],
        'etats' => ['États Financiers', 'fas fa-file-contract'],
        'parametres' => ['Paramètres', 'fas fa-cog'],
    ];
    
    $html = '';
    foreach ($modules as $key => $info) {
        $active = ($current_module == $key) ? ' active' : '';
        $html .= '<a class="nav-link' . $active . '" href="?module=' . $key . '">';
        $html .= '<i class="' . $info[1] . '"></i> ' . $info[0];
        $html .= '</a>';
    }
    return $html;
}
EOF

echo "2. Vérification de la ligne 266 dans header.php..."
if [ -f "/var/www/syscoa/includes/header.php" ]; then
    echo "   Ligne 266:"
    sudo sed -n '266p' /var/www/syscoa/includes/header.php
    
    echo ""
    echo "   Contexte (lignes 260-270):"
    sudo sed -n '260,270p' /var/www/syscoa/includes/header.php
fi

echo ""
echo "3. Test de la fonction ajoutée..."
sudo tee /var/www/syscoa/test_generate_menu.php << 'EOF'
<?php
require_once 'config.php';
echo "Test de generate_menu():<br><br>";
echo generate_menu('dashboard');
?>
EOF

echo "4. Redémarrage d'Apache..."
sudo service apache2 restart

echo ""
echo "=== CORRECTION APPLIQUÉE ==="
echo ""
echo "🎯 TESTS À EFFECTUER :"
echo "1. Test de la fonction : http://192.168.1.33:8080/syscoa/test_generate_menu.php"
echo "2. Connexion normale : http://192.168.1.33:8080/syscoa/login.php"
echo "3. Utilisateur : admin / Mot de passe : admin123"
echo ""
echo "📊 SI L'ERREUR PERSISTE :"
echo "   Consultez : sudo tail -f /var/log/apache2/error.log"
