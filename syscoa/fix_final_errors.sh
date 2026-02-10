#!/bin/bash
# fix_final_errors.sh

echo "=== CORRECTION DES ERREURS FINALES SYSCOHADA ==="

echo "1. Correction du fichier header.php..."
if [ -f "/var/www/syscoa/includes/header.php" ]; then
    # Vérifier et corriger l'inclusion
    if grep -q "config_complet.php" /var/www/syscoa/includes/header.php; then
        echo "   → Remplacement de config_complet.php par config.php"
        sudo sed -i "s/config_complet.php/config.php/g" /var/www/syscoa/includes/header.php
    fi
    
    # Vérifier le résultat
    echo "   → Contenu après correction (lignes 1-5):"
    sudo head -5 /var/www/syscoa/includes/header.php
else
    echo "   ❌ Fichier header.php non trouvé"
fi

echo ""
echo "2. Création du lien symbolique config_complet.php..."
if [ ! -f "/var/www/syscoa/config_complet.php" ]; then
    sudo ln -sf /var/www/syscoa/config.php /var/www/syscoa/config_complet.php
    echo "   → config_complet.php créé (lien vers config.php)"
else
    echo "   → config_complet.php existe déjà"
fi

echo ""
echo "3. Correction de la double session_start()..."
if [ -f "/var/www/syscoa/config.php" ]; then
    # Vérifier si session_start() est déjà dans config.php
    if grep -q "session_start()" /var/www/syscoa/config.php; then
        echo "   → session_start() présent dans config.php"
        # Vérifier s'il y a des doublons dans d'autres fichiers
        echo "   → Vérification des doublons dans d'autres fichiers..."
        grep -r "session_start()" /var/www/syscoa/ --include="*.php" | grep -v "config.php"
    fi
fi

echo ""
echo "4. Vérification des inclusions dans index.php..."
if [ -f "/var/www/syscoa/index.php" ]; then
    echo "   → Vérification de index.php"
    # S'assurer que index.php n'inclut pas config.php si header.php le fait déjà
    if grep -q "require_once.*config.php" /var/www/syscoa/index.php && grep -q "require_once.*config.php" /var/www/syscoa/includes/header.php; then
        echo "   ⚠  Attention: config.php inclus à la fois dans index.php et header.php"
    fi
fi

echo ""
echo "5. Test rapide de la syntaxe PHP..."
for file in /var/www/syscoa/index.php /var/www/syscoa/login.php /var/www/syscoa/config.php /var/www/syscoa/includes/header.php; do
    if [ -f "$file" ]; then
        echo -n "   → $(basename $file): "
        php -l "$file" >/dev/null 2>&1 && echo "OK" || echo "ERREUR"
    fi
done

echo ""
echo "6. Redémarrage d'Apache..."
sudo service apache2 restart

echo ""
echo "=== CORRECTION TERMINÉE ==="
echo ""
echo "🎯 ACCÈS À L'APPLICATION:"
echo "   URL: http://192.168.1.33:8080/syscoa/"
echo "   Identifiants: admin / admin123"
echo ""
echo "📋 TESTS À EFFECTUER:"
echo "   1. Accédez à l'URL ci-dessus"
echo "   2. Connectez-vous avec admin/admin123"
echo "   3. Si erreur 500 persiste: sudo tail -f /var/log/apache2/error.log"
