<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../includes/auth_check.php';
// =================================================================================
// 1. INCLUSION ET CONNEXION BDD
// =================================================================================

// Chemin corrigé : remonte d'un niveau (..) puis va dans /config/Database.php
require_once __DIR__ . '/../../config/Database.php'; 

$pdo = null; // Initialisation de la variable de connexion
try {
    // CORRECTION : Instanciation de la classe Database pour récupérer la connexion PDO
    $db = new Database(); 
    $pdo = $db->getConnection(); 
    
} catch (Exception $e) {
    // Gestion de l'échec de la connexion 
    $erreur_bdd = "Erreur de connexion à la base de données.";
}

// =================================================================================
// 2. LOGIQUE DE RÉCUPÉRATION DES DONNÉES
// =================================================================================

$ventes = [];
if ($pdo) {
    try {
        // Requête SQL corrigée : 
        // 1. Utilisation des noms de tables en majuscules (VENTES, CLIENTS).
        // 2. Utilisation de la colonne réelle v.total_ttc.
        // 3. CORRECTION DE LA COLONNE CLIENT : c.nom AS nom_client (NOM_CLIENT est probablement 'nom')
        $sql = "
            SELECT 
                v.id_vente, 
                v.date_vente, 
                v.total_ttc,
                c.nom AS nom_client  /* C'EST CETTE LIGNE QUI DOIT ETRE VERIFIEE */
            FROM 
                VENTES v
            INNER JOIN
                CLIENTS c ON v.id_client = c.id_client
            ORDER BY 
                v.date_vente DESC
        ";
        
        $stmt = $pdo->query($sql);
        $ventes = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        // En cas d'erreur dans la requête SQL elle-même
        $erreur_bdd = "Erreur de base de données : SQLSTATE[" . $e->getCode() . "] " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des Ventes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .montant { text-align: right; }
    </style>
</head>
<body>

    <h1>Liste des Ventes</h1>
    
    <p>
        <a href="creation_vente.php">Ajouter une nouvelle vente</a> 
        | <a href="<?= $app_root ?? '../index.php' ?>">Retour à l'accueil</a> 
    </p>

    <?php if (isset($erreur_bdd)): ?>
        <div style="color: white; background-color: darkred; padding: 15px; border-radius: 5px;">
            <p><strong>Une erreur est survenue lors du chargement des données.</strong></p>
            <p><small><?= $erreur_bdd ?></small></p>
        </div>
    <?php elseif (empty($ventes)): ?>
        <p>Aucune vente enregistrée pour le moment.</p>
    <?php else: ?>
        
        <table>
            <thead>
                <tr>
                    <th>ID Vente</th>
                    <th>Date</th>
                    <th>Client</th>
                    <th class="montant">Montant Total (TTC)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ventes as $vente): ?>
                <tr>
                    <td><?= htmlspecialchars($vente['id_vente']) ?></td>
                    <td><?= htmlspecialchars($vente['date_vente']) ?></td>
                    <td><?= htmlspecialchars($vente['nom_client']) ?></td> 
                    <td class="montant"><?= number_format($vente['total_ttc'], 2, ',', ' ') ?> €</td>
                    <td>
                        <a href="detail_vente.php?id=<?= $vente['id_vente'] ?>">Voir Détails</a> |
                        <a href="modifier_vente.php?id=<?= $vente['id_vente'] ?>">Modifier</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    <?php endif; ?>

</body>
</html>
