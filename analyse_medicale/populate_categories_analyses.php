<?php
require_once 'includes/db.php';

$pdo = getPDO();

// Catégories d'analyses
$categories = [
    ['nom' => 'Hématologie', 'description' => 'Analyses du sang, numération formule sanguine, coagulation'],
    ['nom' => 'Biochimie', 'description' => 'Analyses biochimiques : glycémie, cholestérol, enzymes hépatiques'],
    ['nom' => 'Microbiologie', 'description' => 'Analyses bactériologiques, parasitologiques, mycologiques'],
    ['nom' => 'Immunologie', 'description' => 'Sérologie, marqueurs tumoraux, auto-immunité'],
    ['nom' => 'Hormonologie', 'description' => 'Dosages hormonaux : TSH, T3, T4, hormones sexuelles'],
    ['nom' => 'Toxicologie', 'description' => 'Recherche de toxiques, alcoolémie, stupéfiants'],
    ['nom' => 'Anatomopathologie', 'description' => 'Analyses cytologiques et histologiques'],
    ['nom' => 'Urines', 'description' => 'Analyses d\'urine : ECBU, protéinurie, etc.'],
    ['nom' => 'Biologie moléculaire', 'description' => 'PCR, séquençage, tests génétiques'],
];

foreach ($categories as $cat) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO categories_analyse (nom, description) VALUES (?, ?)");
    $stmt->execute([$cat['nom'], $cat['description']]);
    echo "Catégorie ajoutée : " . $cat['nom'] . "\n";
}

// Analyses de base
$analyses = [
    ['code' => 'NFS', 'nom' => 'Numération Formule Sanguine', 'categorie' => 'Hématologie', 'type' => 'sang', 'prix' => 5000, 'delai' => 4, 'description' => 'Numération des globules rouges, blancs, plaquettes et formule leucocytaire'],
    ['code' => 'GLY', 'nom' => 'Glycémie à jeun', 'categorie' => 'Biochimie', 'type' => 'sang', 'prix' => 3000, 'delai' => 2, 'description' => 'Dosage du glucose sanguin'],
    ['code' => 'CHOL', 'nom' => 'Cholestérol total', 'categorie' => 'Biochimie', 'type' => 'sang', 'prix' => 3500, 'delai' => 2, 'description' => 'Dosage du cholestérol'],
    ['code' => 'HDL', 'nom' => 'HDL-Cholestérol', 'categorie' => 'Biochimie', 'type' => 'sang', 'prix' => 4000, 'delai' => 2, 'description' => 'Dosage du bon cholestérol'],
    ['code' => 'LDL', 'nom' => 'LDL-Cholestérol', 'categorie' => 'Biochimie', 'type' => 'sang', 'prix' => 4000, 'delai' => 2, 'description' => 'Dosage du mauvais cholestérol'],
    ['code' => 'TSH', 'nom' => 'TSH us', 'categorie' => 'Hormonologie', 'type' => 'sang', 'prix' => 6000, 'delai' => 6, 'description' => 'Dosage de la thyréostimuline'],
    ['code' => 'ECBU', 'nom' => 'Examen Cytobactériologique des Urines', 'categorie' => 'Urines', 'type' => 'urine', 'prix' => 4500, 'delai' => 24, 'description' => 'Recherche d\'infection urinaire'],
    ['code' => 'CRP', 'nom' => 'Protéine C réactive', 'categorie' => 'Biochimie', 'type' => 'sang', 'prix' => 5000, 'delai' => 2, 'description' => 'Marqueur inflammatoire'],
    ['code' => 'FIB', 'nom' => 'Fibrinogène', 'categorie' => 'Hématologie', 'type' => 'sang', 'prix' => 5500, 'delai' => 4, 'description' => 'Facteur de coagulation'],
    ['code' => 'ASAT', 'nom' => 'ASAT (SGOT)', 'categorie' => 'Biochimie', 'type' => 'sang', 'prix' => 3500, 'delai' => 2, 'description' => 'Enzyme hépatique'],
    ['code' => 'ALAT', 'nom' => 'ALAT (SGPT)', 'categorie' => 'Biochimie', 'type' => 'sang', 'prix' => 3500, 'delai' => 2, 'description' => 'Enzyme hépatique'],
];

foreach ($analyses as $a) {
    $stmt = $pdo->prepare("SELECT id FROM categories_analyse WHERE nom = ?");
    $stmt->execute([$a['categorie']]);
    $cat_id = $stmt->fetchColumn();
    if ($cat_id) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO analyses (code_analyse, nom, categorie_id, type_prelevement, prix, delai_resultat, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$a['code'], $a['nom'], $cat_id, $a['type'], $a['prix'], $a['delai'], $a['description']]);
        echo "Analyse ajoutée : " . $a['nom'] . "\n";
    } else {
        echo "Catégorie non trouvée pour " . $a['categorie'] . "\n";
    }
}

// Paramètres pour NFS
$analyse_id = $pdo->query("SELECT id FROM analyses WHERE code_analyse = 'NFS'")->fetchColumn();
if ($analyse_id) {
    $params = [
        ['nom' => 'Globules rouges', 'unite' => '10^6/mm3', 'valeur_min' => 4.5, 'valeur_max' => 5.9, 'ordre' => 1],
        ['nom' => 'Hémoglobine', 'unite' => 'g/dL', 'valeur_min' => 13, 'valeur_max' => 17, 'ordre' => 2],
        ['nom' => 'Hématocrite', 'unite' => '%', 'valeur_min' => 40, 'valeur_max' => 52, 'ordre' => 3],
        ['nom' => 'Globules blancs', 'unite' => '10^3/mm3', 'valeur_min' => 4, 'valeur_max' => 10, 'ordre' => 4],
        ['nom' => 'Plaquettes', 'unite' => '10^3/mm3', 'valeur_min' => 150, 'valeur_max' => 400, 'ordre' => 5],
    ];
    foreach ($params as $p) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO parametres_analyse (analyse_id, nom, unite, valeur_min, valeur_max, ordre) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$analyse_id, $p['nom'], $p['unite'], $p['valeur_min'], $p['valeur_max'], $p['ordre']]);
        echo "Paramètre ajouté : " . $p['nom'] . "\n";
    }
}

echo "\nPeuplement terminé.\n";
