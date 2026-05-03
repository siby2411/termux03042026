<?php
require_once 'config/config.php';
$db = getDB();

echo "<h2>Test simple de connexion</h2>";

$test_username = 'fatou';
$test_password = 'password123';

// Récupérer le caissier
$stmt = $db->prepare("SELECT * FROM cashiers WHERE username = ?");
$stmt->execute([$test_username]);
$cashier = $stmt->fetch();

if ($cashier) {
    echo "Caissier trouvé: " . $cashier['full_name'] . "<br>";
    echo "Hash stocké: " . $cashier['password'] . "<br>";
    
    // Tester le mot de passe
    if (password_verify($test_password, $cashier['password'])) {
        echo "<span style='color:green'>✅ Mot de passe valide !</span><br>";
        
        // Créer une session de test
        session_start();
        $_SESSION['cashier_id'] = $cashier['id'];
        $_SESSION['cashier_name'] = $cashier['full_name'];
        $_SESSION['cashier_username'] = $cashier['username'];
        
        echo "<br><a href='pos.php'>Aller au POS</a><br>";
        echo "<a href='cashier_reports.php'>Voir mes rapports</a>";
    } else {
        echo "<span style='color:red'>❌ Mot de passe invalide !</span><br>";
        
        // Générer un nouveau hash correct
        $correct_hash = password_hash($test_password, PASSWORD_DEFAULT);
        echo "Nouveau hash correct: <code>$correct_hash</code><br>";
        
        // Proposer la mise à jour
        echo "<form method='POST' action=''>";
        echo "<input type='hidden' name='update' value='1'>";
        echo "<button type='submit'>Mettre à jour le mot de passe</button>";
        echo "</form>";
        
        if (isset($_POST['update'])) {
            $update = $db->prepare("UPDATE cashiers SET password = ? WHERE username = ?");
            $update->execute([$correct_hash, $test_username]);
            echo "<span style='color:green'>✅ Mot de passe mis à jour ! Rafraîchissez la page.</span>";
        }
    }
} else {
    echo "❌ Caissier non trouvé";
}
?>
