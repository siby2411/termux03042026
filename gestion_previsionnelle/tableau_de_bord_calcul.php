<?php
// /tableau_de_bord_complet.php
$page_title = "Tableau de Bord Financier & Support de Formation";
include_once 'includes/header.php'; // Inclusion du Header

// -------------------------------------------------------------
// BLOCK DE CALCUL ET DÉFINITION DES VARIABLES (CORPS PHP)
// -------------------------------------------------------------

// --- INCLUSIONS ET CONNEXION DB (Décommenter si nécessaire) ---
// include_once 'config/db.php'; 
// $db = (new Database())->getConnection();

// --- 1. FONCTION DE CALCUL DU SOLDE (Placeholder) ---
// Cette fonction devrait être utilisée pour récupérer les données réelles du GL.
function get_gl_solde($pdo, $code_compte, $date_fin) {
    // Si la DB est connectée, le code réel va ici.
    return (float) 0.00; // Placeholder
}
$N = 360; // Base de jours conventionnelle

// --- 2. AGRÉGATS CLÉS (Résultat des calculs du GL) ---
// Données brutes et agrégées
$ca = 120784.35;         // Ventes
$cdv = 11000.00;         // Coût des Ventes
$charges_fixes = 65000.00; // Total Charges d'Exploitation (641+627+681)
$achats_totaux = 114499.65; // Achats totaux pour le DPO (Stocks + CDV)

// Variables issues des calculs précédents
$resultat_net = 44784.35;
$frng = 275173.35;
$bfr = 63500.00;
$tn = 211673.35;

// Variables pour les ratios de rotation
$stocks_finaux = 103499.65;
$creances_clients = 35000.00;
$dettes_fournisseurs = 60000.00;

// Variables du Flux de Trésorerie
$amortissements_reels = 5000.00;
$ftf = 405389.00;
$fti = -180000.00;

// --- 3. CALCULS DE RENTABILITÉ ET DE FLUX ---
$marge_brute = $ca - $cdv;
$mscv = $marge_brute; 
$taux_mscv = $mscv / $ca;
$sr = $charges_fixes / $taux_mscv;
$point_mort_jours = ($sr / $ca) * $N;
$marge_securite = $ca - $sr;

// Calculs de Rotation
$dio = ($stocks_finaux / $cdv) * $N;
$dso = ($creances_clients / $ca) * $N;
$dpo = ($dettes_fournisseurs / $achats_totaux) * $N;
$ccc = $dio + $dso - $dpo;

// Calculs de Flux
$fte = $resultat_net + $amortissements_reels - $bfr; // FTE par méthode indirecte (simple)
$variation_tresorerie = $fte + $fti + $ftf;

// Données du Bilan (pour affichage)
$bilan_actif = [
    'Immobilisations (Net)' => 175000.00,
    'Stocks' => $stocks_finaux,
    'Créances Clients' => $creances_clients,
    'Trésorerie / Banque' => $tn, // Utilisons TN pour l'affichage cohérent du Bilan
];
$total_actif = array_sum($bilan_actif);

$bilan_passif = [
    'Capitaux Propres (Total)' => 300173.35, // Capital + RN
    'Emprunts LT' => 150000.00,
    'Dettes Fournisseurs' => $dettes_fournisseurs,
    'Dettes Fiscales & Sociales' => 15000.00,
];
$total_passif = array_sum($bilan_passif);
// -------------------------------------------------------------
// FIN DU BLOCK DE CALCUL
// -------------------------------------------------------------

?>




<?php
// /tableau_de_bord_calcul.php
// Contient uniquement la logique PHP pour définir les variables financières.

// --- INCLUSIONS ET CONNEXION DB (Décommenter si nécessaire) ---
// include_once 'config/db.php'; 
// $db = (new Database())->getConnection();

// --- 1. FONCTION DE CALCUL DU SOLDE (Placeholder) ---




$N = 360; // Base de jours conventionnelle

// --- 2. AGRÉGATS CLÉS (Résultat des calculs du GL) ---
// Données brutes et agrégées
$ca = 120784.35;         // Ventes
$cdv = 11000.00;         // Coût des Ventes
$charges_fixes = 65000.00; // Total Charges d'Exploitation (641+627+681)
$achats_totaux = 114499.65; // Achats totaux pour le DPO (Stocks + CDV)

// Variables issues des calculs précédents
$resultat_net = 44784.35;
$frng = 275173.35;
$bfr = 63500.00;
$tn = 211673.35;

// Variables pour les ratios de rotation
$stocks_finaux = 103499.65;
$creances_clients = 35000.00;
$dettes_fournisseurs = 60000.00;

// Variables du Flux de Trésorerie
$amortissements_reels = 5000.00;
$ftf = 405389.00;
$fti = -180000.00;

// --- 3. CALCULS DE RENTABILITÉ ET DE FLUX ---
$marge_brute = $ca - $cdv;
$mscv = $marge_brute; 
$taux_mscv = $mscv / $ca;
$sr = $charges_fixes / $taux_mscv;
$point_mort_jours = ($sr / $ca) * $N;
$marge_securite = $ca - $sr;

// Calculs de Rotation
$dio = ($stocks_finaux / $cdv) * $N;
$dso = ($creances_clients / $ca) * $N;
$dpo = ($dettes_fournisseurs / $achats_totaux) * $N;
$ccc = $dio + $dso - $dpo;

// Calculs de Flux
$fte = $resultat_net + $amortissements_reels - $bfr; 
$variation_tresorerie = $fte + $fti + $ftf;

// Données du Bilan (pour affichage)
$bilan_actif = [
    'Immobilisations (Net)' => 175000.00,
    'Stocks' => $stocks_finaux,
    'Créances Clients' => $creances_clients,
    'Trésorerie / Banque' => $tn, 
];
$total_actif = array_sum($bilan_actif);

$bilan_passif = [
    'Capitaux Propres (Total)' => 300173.35, 
    'Emprunts LT' => 150000.00,
    'Dettes Fournisseurs' => $dettes_fournisseurs,
    'Dettes Fiscales & Sociales' => 15000.00,
];
$total_passif = array_sum($bilan_passif);
?>
