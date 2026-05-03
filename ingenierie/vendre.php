<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = intval($_POST['asset_id']);
    $quantity = floatval($_POST['quantity']);
    $trader_id = 1;

    try {
        $pdo->beginTransaction();

        // 1. Vérifier la possession de l'actif (somme des achats - somme des ventes)
        $stmtStock = $pdo->prepare("
            SELECT 
                (SELECT IFNULL(SUM(quantity), 0) FROM orders WHERE trader_id = ? AND asset_id = ? AND type = 'BUY') - 
                (SELECT IFNULL(SUM(quantity), 0) FROM orders WHERE trader_id = ? AND asset_id = ? AND type = 'SELL') as total_owned
        ");
        $stmtStock->execute([$trader_id, $asset_id, $trader_id, $asset_id]);
        $owned = $stmtStock->fetch();

        if ($owned['total_owned'] < $quantity) {
            throw new Exception("Vous ne possédez pas assez de cet actif ! (Possédé: {$owned['total_owned']})");
        }

        // 2. Récupérer le prix actuel
        $stmtAsset = $pdo->prepare("SELECT current_price, symbol FROM assets WHERE id = ?");
        $stmtAsset->execute([$asset_id]);
        $asset = $stmtAsset->fetch();

        $total_gain = $asset['current_price'] * $quantity;

        // 3. Ajouter le montant au solde
        $updateBalance = $pdo->prepare("UPDATE traders SET balance = balance + ? WHERE id = ?");
        $updateBalance->execute([$total_gain, $trader_id]);

        // 4. Enregistrer la vente
        $insertOrder = $pdo->prepare("INSERT INTO orders (trader_id, asset_id, type, quantity, price_at_order) VALUES (?, ?, 'SELL', ?, ?)");
        $insertOrder->execute([$trader_id, $asset_id, $quantity, $asset['current_price']]);

        $pdo->commit();
        header("Location: index.php?success=Vente de $quantity {$asset['symbol']} effectuée !");
        
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: index.php?error=" . urlencode($e->getMessage()));
    }
}
?>
