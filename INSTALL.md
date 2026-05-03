# 📦 GUIDE D'INSTALLATION — PharmaSen & MedEquip Pro
## Environnement : Termux → proot-distro Ubuntu → MariaDB + PHP + Apache

---

## 1. PRÉREQUIS (depuis Termux)
```bash
pkg update && pkg upgrade -y
pkg install proot-distro curl
proot-distro install ubuntu
proot-distro login ubuntu
```

## 2. DANS PROOT-DISTRO UBUNTU
```bash
apt update && apt upgrade -y
apt install -y apache2 php8.2 php8.2-mysql php8.2-mbstring \
               php8.2-curl php8.2-gd php8.2-zip \
               mariadb-server mariadb-client \
               libapache2-mod-php8.2 nano tree curl wget
```

## 3. DÉMARRAGE DES SERVICES
```bash
service mariadb start
service apache2 start
# Ou via Termux avec les aliases :
# mysqld_safe --user=root &
# apachectl start
```

## 4. SÉCURISATION MARIADB
```bash
mysql_secure_installation
# Suivre les instructions
# Créer un utilisateur dédié :
mysql -u root -p << 'SQL'
CREATE USER 'pharma_user'@'localhost' IDENTIFIED BY 'MotDePasseForte2026!';
GRANT ALL PRIVILEGES ON pharmacie.*         TO 'pharma_user'@'localhost';
GRANT ALL PRIVILEGES ON revendeur_medical.* TO 'pharma_user'@'localhost';
FLUSH PRIVILEGES;
SQL
```

## 5. IMPORT DES BASES DE DONNÉES
```bash
mysql -u root -p < 02_pharmacie_schema.sql
mysql -u root -p < 03_revendeur_medical_schema.sql
```

## 6. STRUCTURE DES RÉPERTOIRES
```
~/shared/htdocs/apachewsl2026/
├── pharmacie/
│   ├── config/
│   │   └── config.php              # Configuration DB + constantes
│   ├── core/
│   │   ├── Database.php            # Singleton PDO MariaDB
│   │   ├── Auth.php                # Authentification sessions
│   │   └── Helper.php              # Utilitaires FCFA, logs, JSON
│   ├── modules/
│   │   ├── medicaments/
│   │   │   ├── MedicamentModel.php # CRUD médicaments
│   │   │   ├── medicaments_api.php # API REST
│   │   │   └── index.php           # Interface liste
│   │   ├── ventes/
│   │   │   ├── VenteModel.php      # Modèle ventes POS
│   │   │   ├── ventes_api.php      # API REST ventes
│   │   │   └── ticket.php          # Impression ticket
│   │   ├── caisse/
│   │   │   └── pos.php             # Interface POS tactile
│   │   ├── ordonnances/
│   │   ├── stock/
│   │   ├── clients/
│   │   ├── fournisseurs/
│   │   ├── rapports/
│   │   └── utilisateurs/
│   ├── templates/
│   │   ├── layouts/base.php        # Layout principal sidebar
│   │   └── partials/               # Header, footer, modals
│   ├── assets/
│   │   ├── css/main.css
│   │   └── js/main.js
│   ├── uploads/
│   │   └── ordonnances/
│   ├── logs/
│   └── index.php
│
└── revendeur_medical/
    ├── config/config.php
    ├── core/                       # Même structure que pharmacie
    ├── modules/
    │   ├── produits/
    │   │   └── ProduitModel.php    # CRUD matériel médical
    │   ├── devis/
    │   │   └── DevisModel.php      # Devis → Commande → Facture
    │   ├── commandes/
    │   ├── facturation/
    │   ├── sav/
    │   ├── clients/
    │   ├── fournisseurs/
    │   └── rapports/
    └── templates/
```

## 7. EXÉCUTION DES SCRIPTS SHELL
```bash
cd ~/shared/htdocs/apachewsl2026
chmod +x 01_arborescence.sh 04_php_core.sh 05_php_modules_crud.sh 06_pos_devis_templates.sh
bash 01_arborescence.sh
bash 04_php_core.sh
bash 05_php_modules_crud.sh
bash 06_pos_devis_templates.sh
```

## 8. CONFIGURATION APACHE — VirtualHost
```apache
# /etc/apache2/sites-available/pharma.conf
<VirtualHost *:8080>
    ServerName localhost
    DocumentRoot /root/shared/htdocs/apachewsl2026

    <Directory /root/shared/htdocs/apachewsl2026>
        AllowOverride All
        Require all granted
        Options -Indexes
    </Directory>

    ErrorLog  /root/shared/htdocs/apachewsl2026/pharmacie/logs/apache_error.log
    CustomLog /root/shared/htdocs/apachewsl2026/pharmacie/logs/apache_access.log combined
</VirtualHost>
```
```bash
a2ensite pharma.conf
a2enmod rewrite
service apache2 reload
```

## 9. ACCÈS AUX APPLICATIONS
- **Pharmacie POS** : http://localhost:8080/pharmacie/modules/caisse/pos.php
- **Pharmacie Dashboard** : http://localhost:8080/pharmacie/
- **Revendeur Médical** : http://localhost:8080/revendeur_medical/
- **Login par défaut** : `admin` / `password` *(À CHANGER IMMÉDIATEMENT)*

---

## 📋 RÉSUMÉ DES FONCTIONNALITÉS

### 💊 PHARMACIE (PharmaSen)
| Module | Fonctionnalités |
|--------|----------------|
| Médicaments | CRUD, DCI, formes, dosages, ordonnance obligatoire |
| Stock | Lots, péremptions, alertes rupture, mouvements |
| POS Caisse | Vente tactile, code-barre, remise, mobile money |
| Ordonnances | Scan, dispatch, suivi partial/total |
| Clients | IPM/IPRES/CSS mutuelles, crédit |
| Achats | Réception lots, péremptions, MAJ stock auto |
| Rapports | CA jour/mois, top produits, stock critique |

### 🏥 REVENDEUR MÉDICAL (MedEquip Pro)
| Module | Fonctionnalités |
|--------|----------------|
| Catalogue | Matériel médical, specs techniques, marquage CE/OMS |
| Devis | Pipeline commercial, validité, conversion commande |
| Commandes | Cycle de vie, livraisons partielles |
| Facturation | FAC auto-référencée, TVA 18%, échéances |
| Paiements | Multi-mode, suivi encours, relances |
| SAV | Interventions, garanties, techniciens |
| Rapports | CA commercial, factures en retard, pipeline devis |

### 🔧 ARCHITECTURE TECHNIQUE
| Composant | Détail |
|-----------|--------|
| Base de données | MariaDB — triggers, vues, colonnes calculées |
| Backend PHP | PDO, MVC simplifié, API JSON REST |
| Frontend POS | HTML5/CSS3/JS Vanilla — responsive tactile |
| Sécurité | bcrypt passwords, sessions, RBAC par rôle |
| Contexte Sénégal | FCFA, TVA 0% médicaments / 18% matériel, NINEA, mutuelles |

### 📊 TRIGGERS MARIADB
| Trigger | Application | Rôle |
|---------|-------------|------|
| trg_vente_update_stock | Pharmacie | Stock − à chaque vente ligne |
| trg_achat_update_stock | Pharmacie | Stock + à la réception |
| trg_alerte_stock_min | Pharmacie | Log alerte rupture stock |
| trg_vente_reference | Pharmacie | Référence VNT-YYYYMM-XXXXX |
| trg_vente_calcul_totaux | Pharmacie | Calcul HT/TVA/net auto |
| trg_facture_reference | Revendeur | Référence FAC-YYYY-XXXXX |
| trg_devis_reference | Revendeur | Référence DEV-YYYY-XXXXX |
| trg_livraison_stock | Revendeur | Stock − à la livraison |
| trg_paiement_update_facture | Revendeur | Statut facture après paiement |
| trg_facture_encours_client | Revendeur | Encours crédit client |

### 👁 VUES MARIADB
| Vue | Description |
|-----|-------------|
| v_stock_critique | Médicaments en rupture ou seuil critique |
| v_peremptions_proches | Lots expirant dans 60 jours |
| v_ca_journalier | CA par jour + ventilation par mode paiement |
| v_top_medicaments | Classement par volume vendu |
| v_ordonnances_en_attente | Ordonnances à servir |
| v_tableau_bord_commercial | Perf commerciaux revendeur |
| v_factures_en_retard | Impayés + jours de retard |
| v_stock_produits | Stock matériel médical + statut |
| v_sav_en_cours | Interventions SAV actives |
| v_pipeline_devis | Devis commerciaux en cours |
