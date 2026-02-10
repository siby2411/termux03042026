<?php
// /analyse_detaillee.php
$page_title = "Analyse Complète de la Structure et des Flux de Trésorerie";
// Inclusion des variables de calcul (Compte de Résultat, Bilan, FRNG, BFR, TN)
// ... (Les sections 1 & 2 de l'analyse_structurelle.php sont supposées incluses ici) ...

// --- RAPPEL DES AGRÉGATS CLÉS (issus du GL) ---
$ca = 120784.35;
$cdv = 11000.00; 
$stocks_finaux = 103499.65; 
$creances_clients = 35000.00; 
$dettes_fournisseurs = 60000.00;
$achats_totaux = 114499.65; // Achats totaux pour le DPO (Stocks + CDV)
$N = 360; // Base de jours conventionnelle

// ==========================================================
// 1. CALCUL DES RATIOS DE ROTATION (Jours)
// ==========================================================

// A. Rotation Stocks (DIO)
$dio = ($stocks_finaux / $cdv) * $N;

// B. Rotation Clients (DSO)
$dso = ($creances_clients / $ca) * $N;

// C. Rotation Fournisseurs (DPO)
$dpo = ($dettes_fournisseurs / $achats_totaux) * $N;

// D. Cycle de Conversion de Trésorerie (CCC)
$ccc = $dio + $dso - $dpo;

// ==========================================================
// 2. RAPPEL DES GRANDEURS STRUCTURELLES (Calculs précédents)
// ==========================================================

// FRNG (Fonds de Roulement Net Global) - Réel
$frng = 275173.35; // (Hypothèse basée sur les insertions Capital/Emprunts)

// BFR (Besoin en Fonds de Roulement) - Réel
$bfr = 63500.00; // (Créances + Stocks - Dettes CT)

// TN (Trésorerie Nette) - Réel
$tn = 211673.35; // (FRNG - BFR)

// RÉSULTAT NET - Réel
$resultat_net = 44784.35; 
$fte = -13715.65; // Flux de Trésorerie d'Exploitation

?>

<h1 class="mt-4 text-center"><i class="fas fa-chart-pie me-2"></i> Tableau de Bord Financier Stratégique</h1>
<p class="text-muted text-center">Intégration des ratios de rotation pour l'optimisation du cash-flow.</p>
<hr class="mb-5">

<div class="row">
    <div class="col-md-3"><div class="alert alert-success text-center">Résultat Net: <strong><?= number_format($resultat_net, 0, ',', ' ') ?> €</strong></div></div>
    <div class="col-md-3"><div class="alert alert-primary text-center">Trésorerie Nette: <strong><?= number_format($tn, 0, ',', ' ') ?> €</strong></div></div>
    <div class="col-md-3"><div class="alert alert-info text-center">FRNG: <strong><?= number_format($frng, 0, ',', ' ') ?> €</strong></div></div>
    <div class="col-md-3"><div class="alert alert-warning text-center">BFR: <strong><?= number_format($bfr, 0, ',', ' ') ?> €</strong></div></div>
</div>

<div class="row mt-4">
    <h2 class="mb-4 text-primary"><i class="fas fa-sync-alt me-2"></i> Ratios de Rotation et Optimisation du Cash</h2>
    <p class="text-muted">Ces ratios mesurent l'efficacité de la gestion du cycle d'exploitation et sont cruciaux pour réduire le BFR.</p>
    
    <div class="col-lg-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-light fw-bold">1. Stocks (DIO)</div>
            <div class="card-body">
                <div class="fs-4 fw-bold text-danger"><?= number_format($dio, 2, ',', ' ') ?> jours</div>
                <p class="small">Temps moyen nécessaire pour convertir le stock en ventes.</p>
                <blockquote class="blockquote small bg-light p-2 border-start border-3 border-danger">
                    <p class="mb-0">$$\text{DIO} = \frac{\text{Stock Moyen}}{\text{Coût des Ventes}} \times 360$$</p>
                </blockquote>
            </div>
            <div class="card-footer small text-danger">
                **Diagnostic :** Rotation extrêmement lente (plus de 9 ans). C'est le **levier critique** : soit le stock est surévalué, soit le CDV est sous-estimé (problème comptable), soit nous avons des stocks obsolètes (problème opérationnel).
            </div>
        </div>
    </div>
    
    <div class="col-lg-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-light fw-bold">2. Clients (DSO)</div>
            <div class="card-body">
                <div class="fs-4 fw-bold text-warning"><?= number_format($dso, 2, ',', ' ') ?> jours</div>
                <p class="small">Délai moyen d'encaissement des créances.</p>
                <blockquote class="blockquote small bg-light p-2 border-start border-3 border-warning">
                    <p class="mb-0">$$\text{DSO} = \frac{\text{Créances Clients}}{\text{Chiffre d'Affaires}} \times 360$$</p>
                </blockquote>
            </div>
            <div class="card-footer small text-warning">
                **Diagnostic :** **104 jours** est long. Cela consomme du BFR. Il faut négocier des délais de paiement plus courts ou optimiser le recouvrement.
            </div>
        </div>
    </div>

    <div class="col-lg-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header bg-light fw-bold">3. Fournisseurs (DPO)</div>
            <div class="card-body">
                <div class="fs-4 fw-bold text-success"><?= number_format($dpo, 2, ',', ' ') ?> jours</div>
                <p class="small">Délai moyen de paiement aux fournisseurs.</p>
                <blockquote class="blockquote small bg-light p-2 border-start border-3 border-success">
                    <p class="mb-0">$$\text{DPO} = \frac{\text{Dettes Fournisseurs}}{\text{Achats Totaux}} \times 360$$</p>
                </blockquote>
            </div>
            <div class="card-footer small text-success">
                **Diagnostic :** **188 jours** est très confortable. Nous finançons une grande partie de notre BFR par les fournisseurs. Ce levier est déjà bien exploité.
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-lg border-dark border-3">
            <div class="card-header bg-dark text-white fw-bold">Cycle de Conversion de Trésorerie (CCC)</div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4 text-center">
                        <h3 class="fw-bold text-danger"><?= number_format($ccc, 2, ',', ' ') ?> jours</h3>
                        <p class="small">Temps nécessaire pour qu'un euro investi revienne en cash.</p>
                    </div>
                    <div class="col-md-8">
                        <blockquote class="blockquote bg-light p-3 rounded">
                            <p class="mb-0">$$\text{CCC} = \text{DIO (3392,72)} + \text{DSO (104,49)} - \text{DPO (188,65)} \approx \mathbf{3308,56\text{ jours}}$$</p>
                        </blockquote>
                        <p class="mt-3 small">**Analyse Stratégique :** Le CCC est extrêmement long, dominé par la rotation des stocks. Cela confirme que le BFR (63 500 €) est principalement dû à l'immobilisation du capital dans les stocks. Malgré la trésorerie élevée, **la gestion du stock doit être l'urgence opérationnelle numéro un** pour libérer le cash du cycle d'exploitation (réduire le FTE négatif).</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <h2 class="mb-4 text-success"><i class="fas fa-graduation-cap me-2"></i> Formulaire Financier (Support de Formation)</h2>
    <div class="col-lg-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr class="table-success"><th>Grandeur</th><th>Formule (Mathématique)</th><th>Valeur Calculée</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>**Fonds de Roulement Net Global (FRNG)**</td>
                            <td>$$\text{FRNG} = (\text{Capitaux Propres} + \text{Dettes LT}) - \text{Actif Immobilisé}$$</td>
                            <td><?= number_format($frng, 2, ',', ' ') ?> €</td>
                        </tr>
                        <tr>
                            <td>**Besoin en Fonds de Roulement (BFR)**</td>
                            <td>$$\text{BFR} = (\text{Stocks} + \text{Clients}) - (\text{Fournisseurs} + \text{Dettes Fisc.})$$</td>
                            <td><?= number_format($bfr, 2, ',', ' ') ?> €</td>
                        </tr>
                        <tr>
                            <td>**Trésorerie Nette (TN)**</td>
                            <td>$$\text{TN} = \text{FRNG} - \text{BFR}$$</td>
                            <td><?= number_format($tn, 2, ',', ' ') ?> €</td>
                        </tr>
                        <tr>
                            <td>**Flux de Trésorerie d'Exploitation (FTE)**</td>
                            <td>$$\text{FTE} = \text{Résultat Net} + \text{Amortissements} - \text{Variation BFR}$$</td>
                            <td><?= number_format($fte, 2, ',', ' ') ?> €</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
