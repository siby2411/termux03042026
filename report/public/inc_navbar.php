<?php
// Menu de navigation professionnel - Style Saari
// À inclure dans toutes les pages après session_start()
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'OMEGA INFORMATIQUE CONSULTING ERP - SYSCOHADA UEMOA - Copyright Mohamet Siby' ?></title>
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
        
        /* Sidebar Navigation Style Saari */
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
        
        @media (max-width: 768px) {
            .omega-sidebar {
                transform: translateX(-100%);
            }
            .omega-sidebar.mobile-open {
                transform: translateX(0);
            }
            .omega-main-content {
                margin-left: 0;
            }
            .mobile-toggle {
                display: block;
            }
        }
        
        .mobile-toggle {
            display: none;
            background: #1a4a6f;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
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
    </style>
</head>
<body>

<!-- Sidebar Navigation -->
<div class="omega-sidebar" id="sidebar">
    <div class="logo-area text-center">
        <i class="bi bi-journal-bookmark-fill fs-1"></i>
        <h3>OMEGA CONSULTING</h3>
        <small>SYSCOHADA UEMOA - Copyright Mohamet Siby</small>
    </div>
    
    <div class="nav-section">COMPTABILITÉ</div>
    <div class="nav-item">
        <a href="dashboard_expert.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard_expert.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Tableau de bord
        </a>
    </div>
    <div class="nav-item">
        <a href="ecriture.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'ecriture.php' ? 'active' : '' ?>">
            <i class="bi bi-pencil-square"></i> Saisie d'écriture
        </a>
    </div>
    <div class="nav-item">
        <a href="ecriture_list.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'ecriture_list.php' ? 'active' : '' ?>">
            <i class="bi bi-list-ul"></i> Journal des écritures
        </a>
    </div>
    <div class="nav-item">
        <a href="grand_livre.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'grand_livre.php' ? 'active' : '' ?>">
            <i class="bi bi-book"></i> Grand Livre
        </a>
    </div>
    <div class="nav-item">
        <a href="balance.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'balance.php' ? 'active' : '' ?>">
            <i class="bi bi-scale"></i> Balance générale
        </a>
    </div>
    
    <div class="nav-section">GESTION</div>
    <div class="nav-item">
        <a href="immobilisations.php" class="nav-link">
            <i class="bi bi-building"></i> Immobilisations
        </a>
    </div>
    <div class="nav-item">
        <a href="stock.php" class="nav-link">
            <i class="bi bi-box-seam"></i> Gestion des stocks
        </a>
    </div>
    <div class="nav-item">
        <a href="rapprochement.php" class="nav-link">
            <i class="bi bi-arrow-left-right"></i> Rapprochement bancaire
        </a>
    </div>
    
    <div class="nav-section">ÉTATS FINANCIERS</div>
    <div class="nav-item">
        <a href="bilan.php" class="nav-link">
            <i class="bi bi-pie-chart"></i> Bilan
        </a>
    </div>
    <div class="nav-item">
        <a href="sig.php" class="nav-link">
            <i class="bi bi-graph-up"></i> Tableau SIG
        </a>
    </div>
    <div class="nav-item">
        <a href="compte_resultat.php" class="nav-link">
            <i class="bi bi-calculator"></i> Compte de résultat
        </a>
    </div>
    <div class="nav-item">
        <a href="flux_tresorerie.php" class="nav-link">
            <i class="bi bi-cash-stack"></i> Flux de trésorerie
        </a>
    </div>
    
    <div class="nav-section">ADMINISTRATION</div>
    <div class="nav-section">FORMATION</div>
    <div class="nav-item">
        <a href="manuel_formation.php" class="nav-link">
            <i class="bi bi-book"></i> 📚 Manuel de formation
        </a>
    </div>
    <div class="nav-item">
        <a href="admin_dashboard.php" class="nav-link">
            <i class="bi bi-shield-lock"></i> Administration
        </a>
    </div>
    <div class="nav-item">
        <a href="logout.php" class="nav-link">
            <i class="bi bi-box-arrow-right"></i> Déconnexion
        </a>
    </div>
</div>

<div class="omega-main-content">
    <div class="omega-topbar">
        <button class="mobile-toggle" id="mobileToggle" onclick="toggleSidebar()">
            <i class="bi bi-list"></i> Menu
        </button>
        <div class="page-title">
            <i class="bi bi-<?= $page_icon ?? 'layout-text-window' ?>"></i> <?= $page_title ?? 'OMEGA INFORMATIQUE CONSULTING ERP' ?>
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

    <div class="nav-section">RÉGULARISATIONS & CLÔTURE</div>
    <div class="nav-item">
        <a href="regularisations.php" class="nav-link">
            <i class="bi bi-arrow-repeat"></i> Régularisations
        </a>
    </div>
    <div class="nav-item">
        <a href="amortissements_complet.php" class="nav-link">
            <i class="bi bi-calculator"></i> Amortissements
        </a>
    </div>
    <div class="nav-item">
        <a href="report_nouveau.php" class="nav-link">
            <i class="bi bi-arrow-right-circle"></i> Report à nouveau
        </a>
    </div>

    <div class="nav-section">OUTILS AVANCÉS</div>
    <div class="nav-item">
        <a href="dashboard_graphiques.php" class="nav-link">
            <i class="bi bi-graph-up"></i> Dashboard graphique
        </a>
    </div>
    <div class="nav-item">
        <a href="import_releve.php" class="nav-link">
            <i class="bi bi-cloud-upload"></i> Import relevé bancaire
        </a>
    </div>
    <div class="nav-item">
        <a href="export_pdf.php?type=bilan" class="nav-link">
            <i class="bi bi-file-pdf"></i> Export PDF
        </a>
    </div>

    <div class="nav-item">
        <a href="../didactiel/index.php" class="nav-link">
            <i class="bi bi-mortarboard"></i> Didacticiel SYSCOHADA
        </a>
    </div>

    <div class="nav-section">🏢 GESTION COMMERCIALE</div>
    <div class="nav-item">
        <a href="tiers.php" class="nav-link">
            <i class="bi bi-people"></i> Tiers (Clients/Fournisseurs)
        </a>
    </div>
    <div class="nav-item">
        <a href="facturation.php" class="nav-link">
            <i class="bi bi-file-invoice"></i> Facturation
        </a>
    </div>
    
    <div class="nav-section">⚠️ ENGAGEMENTS & CONTRÔLE</div>
    <div class="nav-item">
        <a href="engagements_hors_bilan.php" class="nav-link">
            <i class="bi bi-shield"></i> Engagements Hors Bilan
        </a>
    </div>
    <div class="nav-item">
        <a href="audit_trail.php" class="nav-link">
            <i class="bi bi-eye"></i> Audit Trail
        </a>
    </div>
    <div class="nav-item">
        <a href="declarations_fiscales.php" class="nav-link">
            <i class="bi bi-file-text"></i> Déclarations fiscales
        </a>
    </div>

    <div class="nav-section">🎓 FORMATION & SUPPORT SAV</div>
    <div class="nav-item">
        <a href="formations/index.php" class="nav-link">
            <i class="bi bi-mortarboard"></i> Centre de formation
        </a>
    </div>
    <div class="nav-item">
        <a href="manuel_formation.php" class="nav-link">
            <i class="bi bi-book"></i> Manuel utilisateur
        </a>
    </div>
    <div class="nav-item">
        <a href="support_technique.php" class="nav-link">
            <i class="bi bi-headset"></i> Support technique
        </a>
    </div>

    <div class="nav-section">📊 FINANCES AVANCÉES</div>
    <div class="nav-item">
        <a href="dotations_amortissements.php" class="nav-link">
            <i class="bi bi-calculator"></i> Dotations amortissements
        </a>
    </div>
    <div class="nav-item">
        <a href="evaluation_financiere.php" class="nav-link">
            <i class="bi bi-graph-up"></i> VAN / TRI / DCF
        </a>
    </div>
    <div class="nav-item">
        <a href="ecarts_reevaluation.php" class="nav-link">
            <i class="bi bi-arrow-repeat"></i> Écarts de réévaluation
        </a>
    </div>
    <div class="nav-item">
        <a href="augmentation_capital.php" class="nav-link">
            <i class="bi bi-bank"></i> Augmentation capital
        </a>
    </div>

    <div class="nav-section">📊 CONTRÔLE DE GESTION</div>
    <div class="nav-item">
        <a href="controle_budgetaire.php" class="nav-link">
            <i class="bi bi-graph-up"></i> Contrôle budgétaire
        </a>
    </div>
    <div class="nav-item">
        <a href="report_impots.php" class="nav-link">
            <i class="bi bi-file-text"></i> Report à nouveau & Impôts
        </a>
    </div>
