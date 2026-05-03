<?php
/**
 * PUBLIC/DASHBOARD.PHP
 * Tableau de bord principal pour la banque/mutuelle.
 * Affiche les indicateurs clés et les liens de navigation.
 */

session_start();
// Assurez-vous que les chemins d'accès à vos fichiers d'inclusion sont corrects
require_once '../includes/db.php'; 
require_once '../includes/header.php'; 

// --- Simulation de Données pour les Cartes d'Indicateurs ---
// En production, ces données seraient extraites de la base de données.
$clients_actifs = 452;
$comptes_actifs = 780;
$nouveaux_credits = 12; // Ce mois
$solde_total_caisse = 55000.00; 

// --- Le reste de la logique de vérification de session ou d'extraction de données doit être ici ---

?>

<h1 class="mt-4"><i class="fas fa-home me-2"></i> Tableau de Bord Principal</h1>
<p class="text-muted">Bienvenue dans l'interface de gestion de la Mutuelle.</p>

---

## 📊 Indicateurs Clés de l'Activité

<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow border-left-success h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Clients Actifs</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($clients_actifs) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Comptes Clients</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($comptes_actifs) ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-address-card fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow border-left-info h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Nouveaux Crédits (Mois)</div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><?= $nouveaux_credits ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow border-left-warning h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Solde Trésorerie (Caisse)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($solde_total_caisse, 2, ',', ' ') ?> €</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

---

## 🚀 Navigation et Formulaires

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow border-left-secondary h-100 py-2">
            <a **href="clients.php?action=add"** class="card-body stretched-link text-decoration-none text-dark">
                <i class="fas fa-user-plus fa-3x mb-3 text-secondary"></i>
                <h5 class="card-title">Ajouter un Nouveau Client</h5>
                <p class="card-text text-muted">Accès au formulaire de souscription client.</p>
            </a>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow border-left-secondary h-100 py-2">
            <a **href="epargne.php?action=ouvrir"** class="card-body stretched-link text-decoration-none text-dark">
                <i class="fas fa-folder-open fa-3x mb-3 text-secondary"></i>
                <h5 class="card-title">Ouvrir un Compte (Épargne/Courant)</h5>
                <p class="card-text text-muted">Création de nouveaux comptes pour un client existant.</p>
            </a>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow border-left-secondary h-100 py-2">
            <a **href="comptabilite.php"** class="card-body stretched-link text-decoration-none text-dark">
                <i class="fas fa-chart-bar fa-3x mb-3 text-secondary"></i>
                <h5 class="card-title">Comptabilité & Ratios ALM</h5>
                <p class="card-text text-muted">Vérification de la santé financière (RLI, NPL, Vues).</p>
            </a>
        </div>
    </div>
</div>

<?php 
// Lien vers la page d'administration plus complète (si vous l'avez créée)
// echo '<a href="administration.php" class="btn btn-info btn-lg mt-4"><i class="fas fa-tachometer-alt me-2"></i> Tableau de Bord Admin Complet</a>';
?>

<?php require_once '../includes/footer.php'; ?>
