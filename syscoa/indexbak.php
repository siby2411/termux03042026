

<?php
// Fichier: index.php
// Rôle: Tableau de bord central et menu principal de l'application comptable OHADA.

// Configuration de base et inclusion de Bootstrap
// ÉTAPE SUIVANTE : Pour assurer la cohérence visuelle et fonctionnelle au sein de chaque module, 
// l'en-tête (DOCTYPE, <head>, <nav>) sera externalisé vers 'includes/header.php' 
// et le pied de page vers 'includes/footer.php'.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord | Logiciel Comptable OHADA</title>
    <!-- Intégration de Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        /* Style personnalisé pour un design épuré et professionnel */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f7f6;
        }
        .navbar-ohada {
            background-color: #007a4d; /* Vert foncé, couleur institutionnelle */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .module-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 12px;
            border-left: 5px solid #007a4d;
        }
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
        }
        .icon-box {
            color: #007a4d;
            background-color: #e6f5ef;
            padding: 15px;
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <!-- Barre de Navigation Supérieure -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-ohada sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-chart-line me-2"></i> COMPTA PRO-OHADA
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php"><i class="fas fa-home me-1"></i> Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-user-circle me-1"></i> Utilisateur Connecté</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenu Principal / Tableau de Bord -->
    <div class="container my-5">
        <header class="mb-5 p-4 bg-white rounded-lg shadow-sm">
            <h1 class="display-5 fw-bold text-dark">Espace de Travail Comptable</h1>
            <p class="lead text-muted">
                Accès rapide aux modules conformes au Plan Comptable OHADA Révisé.
            </p>
        </header>

        <!-- Grille des Modules (Utilisation des Cards Bootstrap) -->
        <div class="row g-4">
            
            <!-- Module 1: Saisie Comptable & Grand Livre -->
            <div class="col-lg-4 col-md-6">
                <a href="./modules/saisie_comptable/journal_saisie.php" class="text-decoration-none">
                    <div class="card module-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="icon-box mb-3 align-self-start"><i class="fas fa-file-invoice-dollar fa-2x"></i></div>
                            <h5 class="card-title fw-bold text-success">Saisie des Écritures & Grand Livre</h5>
                            <p class="card-text text-muted">Enregistrement des pièces comptables et consultation du Grand Livre (Classes 1 à 7).</p>
                            <span class="mt-auto text-success fw-bold">Accéder <i class="fas fa-arrow-right ms-2"></i></span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Module 2: Gestion des Tiers (Clients & Fournisseurs) -->
            <div class="col-lg-4 col-md-6">
                <a href="./modules/tiers/fiche_client.php" class="text-decoration-none">
                    <div class="card module-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="icon-box mb-3 align-self-start"><i class="fas fa-handshake fa-2x"></i></div>
                            <h5 class="card-title fw-bold text-success">Gestion des Tiers (Clients/Fournisseurs)</h5>
                            <p class="card-text text-muted">Suivi des comptes 40 et 41, lettrage automatique et balances âgées.</p>
                            <span class="mt-auto text-success fw-bold">Accéder <i class="fas fa-arrow-right ms-2"></i></span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Module 3: Immobilisations & Amortissements -->
            <div class="col-lg-4 col-md-6">
                <a href="./modules/immobilisations/gestion_immobilisations.html" class="text-decoration-none">
                    <div class="card module-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="icon-box mb-3 align-self-start"><i class="fas fa-truck-moving fa-2x"></i></div>
                            <h5 class="card-title fw-bold text-success">Immobilisations & Amortissements</h5>
                            <p class="card-text text-muted">Gestion du cycle de vie des actifs (Classe 2) et automatisation des dotations (Classe 68).</p>
                            <span class="mt-auto text-success fw-bold">Accéder <i class="fas fa-arrow-right ms-2"></i></span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Module 4: Gestion des Stocks & CMUP -->
            <div class="col-lg-4 col-md-6">
                <a href="./modules/stock/saisie_stock.html" class="text-decoration-none">
                    <div class="card module-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="icon-box mb-3 align-self-start"><i class="fas fa-warehouse fa-2x"></i></div>
                            <h5 class="card-title fw-bold text-success">Gestion des Stocks & CMUP</h5>
                            <p class="card-text text-muted">Valorisation des entrées/sorties (Classe 3) et calcul automatique du Coût des Ventes (CMV).</p>
                            <span class="mt-auto text-success fw-bold">Accéder <i class="fas fa-arrow-right ms-2"></i></span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Module 5: États Financiers & Clôture -->
            <div class="col-lg-4 col-md-6">
                <a href="./modules/reporting/balance_affichage.php" class="text-decoration-none">
                    <div class="card module-card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <div class="icon-box mb-3 align-self-start"><i class="fas fa-balance-scale-right fa-2x"></i></div>
                            <h5 class="card-title fw-bold text-success">États Financiers & Clôture</h5>
                            <p class="card-text text-muted">Génération du Bilan, CPC, TAFIRE et préparation à la clôture de l'exercice (OHADA).</p>
                            <span class="mt-auto text-success fw-bold">Accéder <i class="fas fa-arrow-right ms-2"></i></span>
                        </div>
                    </div>
                </a>
            </div>

        </div> <!-- Fin row -->

        <hr class="my-5">
        
        <!-- Section d'information OHADA (Rappel de conformité) -->
        <div class="p-4 bg-light rounded-lg shadow-sm text-center">
            <h3 class="fw-bold text-dark">Conformité SYSCOHADA (Révisé)</h3>
            <p class="text-muted mb-0">
                Ce logiciel est conçu pour satisfaire les exigences des administrations ouest-africaines et simplifier le travail des cabinets.
            </p>
        </div>

    </div> <!-- Fin container -->

    <!-- Pied de Page (Footer) -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        &copy; <?php echo date('Y'); ?> Logiciel COMPTA PRO-OHADA - Conçu par un Expert-Comptable Développeur
    </footer>

    <!-- Scripts Bootstrap JS (Nécessaire pour le menu déroulant mobile) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>




