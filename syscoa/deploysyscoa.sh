#!/bin/bash
# /var/www/syscoa/deploy_syscohada.sh

echo "=== DÉPLOIEMENT SYSTÈME SYSCOHADA COMPLET ==="

# 1. Créer la vue manquante
echo "1. Création de la vue vue_journal_central..."
mysql -u root -p sysco_ohada << EOF
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
EOF

# 2. Copier les fichiers de configuration
echo "2. Installation des fichiers de configuration..."
cp config_complet.php config.php
cp includes/header_complet.php includes/header.php
cp includes/footer_complet.php includes/footer.php
cp pages/dashboard_complet.php pages/dashboard.php

# 3. Créer les dossiers nécessaires
echo "3. Création des dossiers..."
mkdir -p modules/api
mkdir -p modules/sig
mkdir -p modules/exports
mkdir -p modules/reports

# 4. Déployer tous les modules existants
echo "4. Déploiement des modules existants..."

# Comptabilité
ln -sf ../balance.php modules/comptabilite/
ln -sf ../grand_livre.php modules/comptabilite/
ln -sf ../journal_comptable.php modules/comptabilite/
ln -sf ../saisie_ecriture.php modules/comptabilite/
ln -sf ../plan_comptable.php modules/comptabilite/

# États financiers
ln -sf ../bilan-comptable.php modules/etats_financiers/
ln -sf ../compte_resultat.php modules/etats_financiers/
ln -sf ../tableau_flux_tresorerie.php modules/etats_financiers/
ln -sf ../soldes_gestion.php modules/etats_financiers/

# Gestion des tiers
ln -sf ../tiers_gestion_form.php modules/tiers/

# Immobilisations
ln -sf ../immo.html modules/immobilisations/
ln -sf ../process_dotation_amortissement.php modules/immobilisations/

# Gestion de stock
ln -sf ../gestion_articles.php modules/stock/
ln -sf ../process_stock_mouvement.php modules/stock/
ln -sf ../module_gestion_inventaire_final.php modules/stock/

# Fiscalité
ln -sf ../module_fiscalite_tva.php modules/fiscalite/
ln -sf ../module_impots_liasse.php modules/fiscalite/
ln -sf ../gestion_fiscale_uemoa.php modules/fiscalite/

# Clôture
ln -sf ../travaux_cloture.php modules/cloture/
ln -sf ../rapprochement_bancaire.php modules/cloture/
ln -sf ../controle_coherence_fiscale.php modules/cloture/

# Rapports & SIG
ln -sf ../analyse_financiere.php modules/rapports/
ln -sf ../dashboard_financier.php modules/rapports/
ln -sf ../export_flux_tresorerie_pdf.php modules/rapports/

# Administration
ln -sf ../admin/ modules/administration/
ln -sf ../backup.php modules/administration/
ln -sf ../createuser.php modules/administration/

# 5. Mettre les permissions
echo "5. Attribution des permissions..."
chown -R www-data:www-data /var/www/syscoa
chmod -R 755 /var/www/syscoa
chmod -R 777 /var/www/syscoa/uploads
chmod -R 777 /var/www/syscoa/exports

# 6. Redémarrer Apache
echo "6. Redémarrage d'Apache..."
systemctl restart apache2

# 7. Message final
echo "=== DÉPLOIEMENT TERMINÉ ==="
echo "URL: http://192.168.1.33:8080/syscoa/"
echo "Utilisateur: admin"
echo "Mot de passe: admin"
echo ""
echo "Modules déployés:"
echo "• Comptabilité (Balance, Grand Livre, Journal, Saisie, Plan Comptable)"
echo "• États Financiers (Bilan, Compte Résultat, Flux Trésorerie, Soldes)"
echo "• Gestion des Tiers (Clients, Fournisseurs)"
echo "• Immobilisations (Liste, Amortissements)"
echo "• Gestion de Stock (Articles, Mouvements, Inventaire)"
echo "• Fiscalité (TVA, Impôts, Déclarations)"
echo "• Clôture (Travaux, Rapprochement, Contrôle)"
echo "• Rapports & SIG (Analyse, Ratios, Dashboard, Export)"
echo "• Administration (Utilisateurs, Sauvegarde, Paramètres)"
