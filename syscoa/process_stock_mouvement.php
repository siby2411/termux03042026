<?php
/**
 * Fichier : process_stock_mouvement.php
 * Rôle : Enregistre un mouvement de stock et met à jour le CMUP de l'article.
 * Rigueur : Application de la méthode CMUP continue pour la valorisation.
 * Impact Comptable : Génère une écriture de Stock (Classe 3) si nécessaire.
 */
require 'config.php';

header('Content-Type: application/json');

try {
    $pdo = connectDB($host, $dbName, $username, $password);
    $pdo->beginTransaction();

    $code_article = $_POST['code_article'] ?? '';
    $type_mouvement = $_POST['type_mouvement'] ?? '';
    $quantite = (float)($_POST['quantite'] ?? 0);
    $cout_unitaire_achat = (float)($_POST['cout_unitaire_achat'] ?? 0); // Uniquement pour les ENTREEs
    $date_mouvement = $_POST['date_mouvement'] ?? date('Y-m-d');
    
    if (empty($code_article) || $quantite <= 0) {
        throw new Exception("Article ou quantité invalide.");
    }
    
    // 1. Récupération des données de l'article (stock_actuel et cmup_actuel)
    $stmt_article = $pdo->prepare("SELECT * FROM stock_articles WHERE code_article = ?");
    $stmt_article->execute([$code_article]);
    $article = $stmt_article->fetch(PDO::FETCH_ASSOC);

    if (!$article) {
        throw new Exception("Article non trouvé.");
    }

    $old_stock = (float)$article['stock_actuel_quantite'];
    $old_cmup = (float)$article['cmup_actuel'];
    $old_valeur = $old_stock * $old_cmup;
    
    $new_stock = $old_stock;
    $new_cmup = $old_cmup;
    $cout_mouvement = 0; // Coût utilisé pour valoriser le mouvement

    // 2. Traitement du Mouvement (Calcul du CMUP)
    if ($type_mouvement === 'ENTREE_ACHAT') {
        $valeur_entree = $quantite * $cout_unitaire_achat;
        
        $new_stock = $old_stock + $quantite;
        // Calcul CMUP continu : (Stock Initial * CMUP Initial + Valeur Entrée) / Nouveau Stock
        if ($new_stock > 0) {
            $new_cmup = ($old_valeur + $valeur_entree) / $new_stock;
        } else {
             $new_cmup = 0;
        }
        $cout_mouvement = $cout_unitaire_achat;
        
    } elseif (in_array($type_mouvement, ['SORTIE_VENTE', 'SORTIE_CONSOMMATION'])) {
        if ($quantite > $old_stock) {
            throw new Exception("Quantité en stock insuffisante pour la sortie.");
        }
        
        // Pour une sortie, on valorise au CMUP actuel
        $cout_mouvement = $old_cmup;
        $new_stock = $old_stock - $quantite;
        $new_cmup = $old_cmup; // Le CMUP ne change pas lors d'une sortie
        
        // --- AUTOMATISATION COMPTABLE : COUT DES VENTES (Classe 6) ---
        // On génère une écriture comptable pour le coût de la sortie (CMV)
        $montant_cmv = $quantite * $cout_mouvement;
        
        $compte_charge = '60300'; // Variation de stock de marchandises (ou équivalent)
        $compte_stock = $article['compte_stock_rattache'];
        
        // L'écriture est : Débit 60300 (Charge) / Crédit 3XX00 (Stock)
        
        // 1. Crédit du compte de stock
        $stmt_stock = $pdo->prepare("INSERT INTO journal_comptable (code_journal, date_ecriture, numero_compte, libelle_ecriture, montant_debit, montant_credit, id_exercice_fk) 
                                     VALUES ('STK', ?, ?, 'Sortie stock - CMV', 0, ?, ?)");
        $stmt_stock->execute([$date_mouvement, $compte_stock, $montant_cmv, $_POST['id_exercice_fk']]);
        $ref_ecriture_stock = $pdo->lastInsertId();

        // 2. Débit du compte de charge (CMV)
        $stmt_cmv = $pdo->prepare("INSERT INTO journal_comptable (code_journal, date_ecriture, numero_compte, libelle_ecriture, montant_debit, montant_credit, id_exercice_fk) 
                                   VALUES ('STK', ?, ?, 'Coût des marchandises vendues', ?, 0, ?)");
        $stmt_cmv->execute([$date_mouvement, $compte_charge, $montant_cmv, $_POST['id_exercice_fk']]);

        
    } else {
        throw new Exception("Type de mouvement non supporté.");
    }
    
    // 3. Mise à jour de la fiche article
    $stmt_update = $pdo->prepare("UPDATE stock_articles SET stock_actuel_quantite = ?, cmup_actuel = ? WHERE code_article = ?");
    $stmt_update->execute([$new_stock, $new_cmup, $code_article]);

    // 4. Enregistrement du mouvement dans l'historique
    $stmt_mouv = $pdo->prepare("INSERT INTO stock_mouvements (code_article_fk, date_mouvement, type_mouvement, quantite, cout_unitaire_mouvement, journal_comptable_ref) 
                                 VALUES (?, ?, ?, ?, ?, ?)");
    // Le 'journal_comptable_ref' est l'ID de la première écriture (crédit stock) pour référence
    $stmt_mouv->execute([$code_article, $date_mouvement, $type_mouvement, $quantite, $cout_mouvement, $ref_ecriture_stock ?? null]);


    $pdo->commit();

    echo json_encode([
        'status' => 'success', 
        'message' => "Mouvement de stock enregistré et CMUP mis à jour. Stock final: {$new_stock}, CMUP: {$new_cmup}",
        'new_cmup' => $new_cmup,
        'new_stock' => $new_stock
    ]);

} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Erreur de traitement du stock: ' . $e->getMessage()]);
}
?>


