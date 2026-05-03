<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = intval($_POST['asset_id']);
    $quantity = floatval($_POST['quantity']);
    $trader_id = 1; // On utilise votre compte par défaut

    try {
        $pdo->beginTransaction();

        // 1. Récupérer le prix actuel et le nom de l'actif
        $stmtAsset = $pdo->prepare("SELECT current_price, symbol FROM assets WHERE id = ?");
        $stmtAsset->execute([$asset_id]);
        $asset = $stmtAsset->fetch();

        if (!$asset) throw new Exception("Actif introuvable.");

        $total_cost = $asset['current_price'] * $quantity;

        // 2. Vérifier le solde du trader
        $stmtTrader = $pdo->prepare("SELECT balance FROM traders WHERE id = ? FOR UPDATE");
        $stmtTrader->execute([$trader_id]);
        $trader = $stmtTrader->fetch();

        if ($trader['balance'] < $total_cost) {
            throw new Exception("Solde insuffisant ! Coût: $total_cost, Solde: {$trader['balance']}");
        }

        // 3. Déduire le montant du solde
        $updateBalance = $pdo->prepare("UPDATE traders SET balance = balance - ? WHERE id = ?");
        $updateBalance->execute([$total_cost, $trader_id]);

        // 4. Enregistrer l'ordre dans l'historique
        $insertOrder = $pdo->prepare("INSERT INTO orders (trader_id, asset_id, type, quantity, price_at_order) VALUES (?, ?, 'BUY', ?, ?)");
        $insertOrder->execute([$trader_id, $asset_id, $quantity, $asset['current_price']]);

        $pdo->commit();
        header("Location: index.php?success=Achat de $quantity {$asset['symbol']} réussi !");
        
    } catch (Exception $e) {
        $pdo->rollBack();
        header("Location: index.php?error=" . urlencode($e->getMessage()));
    }
}
?>
