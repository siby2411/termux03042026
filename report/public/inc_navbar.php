<?php
// Menu de navigation professionnel - Style Saari
// À inclure dans toutes les pages après session_start()
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'OMEGA ERP - SYSCOHADA UEMOA' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f8;
            overflow-x: hidden;
        }
        .omega-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 280px;
            background: linear-gradient(180deg, #0a2b3e 0%, #0d3550 100%);
            color: white;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
            overflow-y: auto;
        }
        .omega-sidebar .logo-area {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .omega-sidebar .logo-area h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .omega-sidebar .logo-area small {
            font-size: 0.7rem;
            opacity: 0.7;
        }
        .omega-sidebar .nav-item {
            margin: 5px 15px;
        }
        .omega-sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 15px;
            border-radius: 12px;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .omega-sidebar .nav-link i {
            font-size: 1.2rem;
            width: 24px;
        }
        .omega-sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }
        .omega-sidebar .nav-link.active {
            background: linear-gradient(135deg, #1a6f8f, #0f3b52);
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .omega-sidebar .nav-section {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.5;
            padding: 15px 20px 5px 20px;
        }
        .omega-main-content {
            margin-left: 280px;
            min-height: 100vh;
        }
        .omega-topbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        .omega-topbar .page-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1a4a6f;
        }
        .omega-topbar .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .omega-topbar .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #1a6f8f, #0f3b52);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .omega-content {
            padding: 30px;
        }
        .btn-omega {
            background: linear-gradient(135deg, #1a6f8f, #0f3b52);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s;
        }
        .btn-omega:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(26,111,143,0.3);
            color: white;
        }
        .card-stats {
            background: white;
            border-radius: 20px;
            padding: 20px;
            border: none;
            box-shadow: 0 2px 12px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        .card-stats:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        @media (max-width: 768px) {
            .omega-sidebar { transform: translateX(-100%); }
            .omega-sidebar.mobile-open { transform: translateX(0); }
            .omega-main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

<!-- Sidebar Navigation -->
<div class="omega-sidebar" id="sidebar">
    <div class="logo-area text-center">
        <i class="bi bi-journal-bookmark-fill fs-1"></i>
        <h3>OMEGA CONSULTING</h3>
        <small>SYSCOHADA UEMOA</small>
        <small class="d-block mt-1" style="font-size:0.6rem;">© Mohamet Siby</small>
    </div>

    <!-- DASHBOARD -->
    <div class="nav-section">📊 TABLEAU DE BORD</div>
    <div class="nav-item">
        <a href="dashboard_expert.php" class="nav-link"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
    </div>

    <!-- COMPTABILITÉ GÉNÉRALE -->
    <div class="nav-section">📚 COMPTABILITÉ GÉNÉRALE</div>
    <div class="nav-item"><a href="ecriture_controlee.php" class="nav-link"><i class="bi bi-pencil-square"></i> Saisie d'écriture</a></div>
    <div class="nav-item"><a href="ecriture_list.php" class="nav-link"><i class="bi bi-list-ul"></i> Journal des écritures</a></div>
    <div class="nav-item"><a href="grand_livre.php" class="nav-link"><i class="bi bi-book"></i> Grand Livre</a></div>
    <div class="nav-item"><a href="balance.php" class="nav-link"><i class="bi bi-scale"></i> Balance générale</a></div>

    <!-- ÉTATS FINANCIERS -->
    <div class="nav-section">📊 ÉTATS FINANCIERS</div>
    <div class="nav-item"><a href="bilan.php" class="nav-link"><i class="bi bi-pie-chart"></i> Bilan</a></div>
    <div class="nav-item"><a href="compte_resultat.php" class="nav-link"><i class="bi bi-calculator"></i> Compte de résultat (CPC)</a></div>
    <div class="nav-item"><a href="sig.php" class="nav-link"><i class="bi bi-graph-up"></i> Soldes Intermédiaires (SIG)</a></div>
    <div class="nav-item"><a href="flux_tresorerie.php" class="nav-link"><i class="bi bi-cash-stack"></i> Flux de trésorerie (TFT)</a></div>
    <div class="nav-item"><a href="variation_capitaux.php" class="nav-link"><i class="bi bi-arrow-repeat"></i> Variation capitaux (TVCP)</a></div>
    <div class="nav-item"><a href="annexes_etats_financiers.php" class="nav-link"><i class="bi bi-file-text"></i> Annexes (NA)</a></div>

    <!-- ANALYSE FINANCIÈRE -->
    <div class="nav-section">📈 ANALYSE FINANCIÈRE</div>
    <div class="nav-item"><a href="bilan_fonctionnel.php" class="nav-link"><i class="bi bi-pie-chart"></i> Bilan fonctionnel</a></div>
    <div class="nav-item"><a href="tableau_financement.php" class="nav-link"><i class="bi bi-arrow-left-right"></i> Tableau financement</a></div>
    <div class="nav-item"><a href="ratios_liquidite.php" class="nav-link"><i class="bi bi-calculator"></i> Ratios liquidité</a></div>

    <!-- ANALYSE FINANCIÈRE AVANCÉE -->
    <div class="nav-section">🎯 ANALYSE AVANCÉE</div>
    <div class="nav-item"><a href="score_rentabilite.php" class="nav-link"><i class="bi bi-graph-up"></i> Score rentabilité (Altman)</a></div>
    <div class="nav-item"><a href="effet_levier.php" class="nav-link"><i class="bi bi-arrow-up-down"></i> Effet de levier</a></div>
    <div class="nav-item"><a href="plan_financement.php" class="nav-link"><i class="bi bi-calendar-check"></i> Plan de financement</a></div>

    <!-- BFR & INVESTISSEMENTS -->
    <div class="nav-section">💰 BFR & INVESTISSEMENTS</div>
    <div class="nav-item"><a href="bfr_previsionnel.php" class="nav-link"><i class="bi bi-graph-up"></i> BFR prévisionnel</a></div>
    <div class="nav-item"><a href="etude_projets.php" class="nav-link"><i class="bi bi-briefcase"></i> Étude projets (VAN/TRI)</a></div>
    <div class="nav-item"><a href="cout_capital.php" class="nav-link"><i class="bi bi-percent"></i> Coût du capital (WACC)</a></div>

    <!-- INVESTISSEMENT & RISQUE -->
    <div class="nav-section">🎲 INVESTISSEMENT & RISQUE</div>
    <div class="nav-item"><a href="investissement_aleatoire.php" class="nav-link"><i class="bi bi-graph-up"></i> Investissement aléatoire</a></div>
    <div class="nav-item"><a href="budget_tresorerie.php" class="nav-link"><i class="bi bi-cash-stack"></i> Budget trésorerie</a></div>
    <div class="nav-item"><a href="evaluation_entreprise.php" class="nav-link"><i class="bi bi-building"></i> Évaluation entreprise</a></div>
    <div class="nav-item"><a href="analyse_sensibilite.php" class="nav-link"><i class="bi bi-graph-up"></i> Analyse sensibilité</a></div>

    <!-- GESTION COMMERCIALE -->
    <div class="nav-section">🏢 GESTION COMMERCIALE</div>
    <div class="nav-item"><a href="gestion_tiers.php" class="nav-link"><i class="bi bi-people"></i> Clients & Fournisseurs</a></div>
    <div class="nav-item"><a href="facturation_complete.php" class="nav-link"><i class="bi bi-file-invoice"></i> Facturation</a></div>
    <div class="nav-item"><a href="effets_commerce.php" class="nav-link"><i class="bi bi-file-text"></i> Effets de commerce</a></div>

    <!-- GESTION DES STOCKS -->
    <div class="nav-section">📦 GESTION DES STOCKS</div>
    <div class="nav-item"><a href="gestion_stocks_complet.php" class="nav-link"><i class="bi bi-box-seam"></i> Gestion des stocks</a></div>
    <div class="nav-item"><a href="inventaire_physique.php" class="nav-link"><i class="bi bi-clipboard-data"></i> Inventaire physique</a></div>
    <div class="nav-item"><a href="gestion_articles.php" class="nav-link"><i class="bi bi-box"></i> Articles & prix</a></div>

    <!-- COMPTABILITÉ ANALYTIQUE -->
    <div class="nav-section">📊 COMPTABILITÉ ANALYTIQUE</div>
    <div class="nav-item"><a href="saisie_analytique.php" class="nav-link"><i class="bi bi-pie-chart"></i> Saisie analytique</a></div>
    <div class="nav-item"><a href="centres_analytiques.php" class="nav-link"><i class="bi bi-pie-chart"></i> Centres coûts/profits</a></div>
    <div class="nav-item"><a href="analyse_cae.php" class="nav-link"><i class="bi bi-graph-up"></i> Analyse CG → CAE</a></div>

    <!-- GESTION FINANCIÈRE -->
    <div class="nav-section">💰 GESTION FINANCIÈRE</div>
    <div class="nav-item"><a href="gestion_titres.php" class="nav-link"><i class="bi bi-graph-up"></i> Portefeuille-titres</a></div>
    <div class="nav-item"><a href="rapprochement_bancaire_complet.php" class="nav-link"><i class="bi bi-arrow-left-right"></i> Rapprochement bancaire</a></div>
    <div class="nav-item"><a href="operations_etrangeres.php" class="nav-link"><i class="bi bi-currency-exchange"></i> Devises & écarts</a></div>

    <!-- RESSOURCES HUMAINES -->
    <div class="nav-section">👥 RESSOURCES HUMAINES</div>
    <div class="nav-item"><a href="gestion_salaires.php" class="nav-link"><i class="bi bi-people"></i> Gestion des salaires</a></div>
    <div class="nav-item"><a href="charges_personnel_impots.php" class="nav-link"><i class="bi bi-calculator"></i> Salaires & Impôts</a></div>

    <!-- PROVISIONS & RISQUES -->
    <div class="nav-section">🛡️ PROVISIONS & RISQUES</div>
    <div class="nav-item"><a href="gestion_provisions.php" class="nav-link"><i class="bi bi-shield"></i> Provisions & dépréciations</a></div>
    <div class="nav-item"><a href="engagements_hors_bilan_complet.php" class="nav-link"><i class="bi bi-shield"></i> Engagements hors bilan</a></div>

    <!-- IMMOBILISATIONS -->
    <div class="nav-section">🏗️ IMMOBILISATIONS</div>
    <div class="nav-item"><a href="immobilisations.php" class="nav-link"><i class="bi bi-building"></i> Immobilisations</a></div>
    <div class="nav-item"><a href="amortissements_complet.php" class="nav-link"><i class="bi bi-calculator"></i> Amortissements</a></div>
    <div class="nav-item"><a href="dotations_amortissements.php" class="nav-link"><i class="bi bi-calculator"></i> Dotations</a></div>
    <div class="nav-item"><a href="ecarts_reevaluation.php" class="nav-link"><i class="bi bi-arrow-repeat"></i> Écarts réévaluation</a></div>

    <!-- RÉGULARISATIONS & CLÔTURE -->
    <div class="nav-section">🔄 RÉGULARISATIONS & CLÔTURE</div>
    <div class="nav-item"><a href="regularisations.php" class="nav-link"><i class="bi bi-arrow-repeat"></i> Régularisations</a></div>
    <div class="nav-item"><a href="report_nouveau.php" class="nav-link"><i class="bi bi-arrow-right-circle"></i> Report à nouveau</a></div>
    <div class="nav-item"><a href="travaux_fin_exercice.php" class="nav-link"><i class="bi bi-calendar-check"></i> Travaux fin d'exercice</a></div>
    <div class="nav-item"><a href="evenements_posterieurs.php" class="nav-link"><i class="bi bi-calendar"></i> Événements postérieurs</a></div>

    <!-- OUTILS AVANCÉS -->
    <div class="nav-section">🚀 OUTILS AVANCÉS</div>
    <div class="nav-item"><a href="gestion_journaux.php" class="nav-link"><i class="bi bi-journal"></i> Multi-journaux</a></div>
    <div class="nav-item"><a href="lettrage_comptable.php" class="nav-link"><i class="bi bi-link"></i> Lettrage comptable</a></div>
    <div class="nav-item"><a href="modeles_saisie.php" class="nav-link"><i class="bi bi-speedometer2"></i> Modèles de saisie</a></div>
    <div class="nav-item"><a href="import_releve_auto.php" class="nav-link"><i class="bi bi-cloud-upload"></i> Import relevé auto</a></div>
    <div class="nav-item"><a href="export_pdf.php?type=bilan" class="nav-link"><i class="bi bi-file-pdf"></i> Export PDF</a></div>

    <!-- FORMATION & DOCUMENTATION -->
    <div class="nav-section">🎓 FORMATION & DOCUMENTATION</div>
    <div class="nav-item"><a href="didactiel/index.php" class="nav-link"><i class="bi bi-mortarboard"></i> Didacticiel complet</a></div>
    <div class="nav-item"><a href="formations/index.php" class="nav-link"><i class="bi bi-book"></i> Centre de formation</a></div>
    <div class="nav-item"><a href="manuel_formation.php" class="nav-link"><i class="bi bi-book"></i> Manuel utilisateur</a></div>
    <div class="nav-item"><a href="manuel_cae.php" class="nav-link"><i class="bi bi-book"></i> Manuel CAE</a></div>
    <div class="nav-item"><a href="manuel_analyse_financiere.php" class="nav-link"><i class="bi bi-book"></i> Manuel analyse financière</a></div>
    <div class="nav-item"><a href="manuel_analyse_avancee.php" class="nav-link"><i class="bi bi-book"></i> Manuel analyse avancée</a></div>

    <!-- ADMINISTRATION -->
    <div class="nav-section">🏦 ADMINISTRATION</div>
    <div class="nav-item"><a href="ajouter_compte_controle.php" class="nav-link"><i class="bi bi-plus-circle"></i> Ajouter un compte</a></div>
    <div class="nav-item"><a href="audit_trail.php" class="nav-link"><i class="bi bi-eye"></i> Audit Trail</a></div>
    <div class="nav-item"><a href="liasses_fiscales.php" class="nav-link"><i class="bi bi-file-text"></i> Déclarations fiscales</a></div>
    <div class="nav-item"><a href="augmentation_capital.php" class="nav-link"><i class="bi bi-bank"></i> Augmentation capital</a></div>

    <!-- DÉCONNEXION -->
    <div class="nav-section">🔐 COMPTE</div>
    <div class="nav-item"><a href="logout.php" class="nav-link"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></div>
</div>

<div class="omega-main-content">
    <div class="omega-topbar">
        <div class="page-title">
            <i class="bi bi-<?= $page_icon ?? 'layout-text-window' ?>"></i> <?= $page_title ?? 'OMEGA ERP' ?>
        </div>
        <div class="user-info">
            <span><i class="bi bi-calendar3"></i> <?= date('d/m/Y') ?></span>
            <div class="user-avatar">
                <?= strtoupper(substr($_SESSION['email'] ?? 'U', 0, 1)) ?>
            </div>
            <span class="d-none d-md-block"><?= htmlspecialchars($_SESSION['email'] ?? 'Utilisateur') ?></span>
        </div>
    </div>
    <div class="omega-content">

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('mobile-open');
}
</script>

    <div class="nav-section">📊 MODÈLES FINANCIERS</div>
    <div class="nav-item">
        <a href="modigliani_miller.php" class="nav-link">
            <i class="bi bi-graph-up"></i> Modigliani-Miller
        </a>
    </div>
    <div class="nav-item">
        <a href="gordon_shapiro.php" class="nav-link">
            <i class="bi bi-graph-up"></i> Gordon-Shapiro
        </a>
    </div>
    <div class="nav-item">
        <a href="manuel_modeles_financiers.php" class="nav-link">
            <i class="bi bi-book"></i> Manuel modèles financiers
        </a>
    </div>

    <div class="nav-section">📅 CLÔTURE & OUVERTURE</div>
    <div class="nav-item">
        <a href="cloture_ouverture_exercice.php" class="nav-link">
            <i class="bi bi-calendar-check"></i> Clôture/ouverture exercice
        </a>
    </div>
    <div class="nav-item">
        <a href="manuel_ouverture_bilan.php" class="nav-link">
            <i class="bi bi-book"></i> Manuel ouverture bilan
        </a>
    </div>

    <div class="nav-section">🏢 ÉVALUATION D'ENTREPRISE</div>
    <div class="nav-item">
        <a href="taux_actualisation.php" class="nav-link">
            <i class="bi bi-percent"></i> Taux d'actualisation
        </a>
    </div>
    <div class="nav-item">
        <a href="cout_moyen_capital.php" class="nav-link">
            <i class="bi bi-calculator"></i> Coût du capital (WACC)
        </a>
    </div>
    <div class="nav-item">
        <a href="evaluation_globale.php" class="nav-link">
            <i class="bi bi-building"></i> Évaluation globale
        </a>
    </div>
    <div class="nav-item">
        <a href="manuel_evaluation_entreprise.php" class="nav-link">
            <i class="bi bi-book"></i> Manuel évaluation
        </a>
    </div>
