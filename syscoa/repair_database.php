<?php
// repair_database.php - Script de réparation

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Réparation de la base de données</h2>";

// Connexion
$host = 'localhost';
$dbname = 'sysco_ohada';
$username = 'root';
$password = '123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Connexion réussie</p>";
    
    // Désactiver les contraintes
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    echo "<p>✅ Contraintes désactivées</p>";
    
    // 1. Vérifier et corriger la table exercices_comptables
    $sql = "CREATE TABLE IF NOT EXISTS exercices_comptables (
                id_exercice INT PRIMARY KEY AUTO_INCREMENT,
                annee YEAR NOT NULL,
                date_debut DATE NOT NULL,
                date_fin DATE NOT NULL,
                statut ENUM('ouvert', 'ferme', 'cloture') DEFAULT 'ouvert',
                description TEXT,
                UNIQUE KEY unique_annee (annee)
            )";
    $pdo->exec($sql);
    echo "<p>✅ Table exercices_comptables vérifiée</p>";
    
    // 2. Vérifier et corriger la table ecritures
    $sql = "CREATE TABLE IF NOT EXISTS ecritures (
                ecriture_id INT PRIMARY KEY AUTO_INCREMENT,
                id_exercice INT NOT NULL,
                date_ecriture DATE NOT NULL,
                journal_code CHAR(2) NOT NULL,
                num_piece VARCHAR(50) NOT NULL,
                compte_num CHAR(8) NOT NULL,
                libelle VARCHAR(255) NOT NULL,
                debit DECIMAL(15,2) DEFAULT 0.00,
                credit DECIMAL(15,2) DEFAULT 0.00,
                code_tiers VARCHAR(50),
                ref_lettrage CHAR(2),
                statut ENUM('saisie', 'valide', 'comptabilise') DEFAULT 'saisie',
                INDEX idx_exercice (id_exercice),
                INDEX idx_date (date_ecriture),
                INDEX idx_compte (compte_num),
                FOREIGN KEY (id_exercice) REFERENCES exercices_comptables(id_exercice) ON DELETE CASCADE
            )";
    $pdo->exec($sql);
    echo "<p>✅ Table ecritures vérifiée</p>";
    
    // 3. Ajouter des données de test si nécessaire
    $count = $pdo->query("SELECT COUNT(*) FROM exercices_comptables")->fetchColumn();
    if ($count == 0) {
        $sql = "INSERT INTO exercices_comptables (annee, date_debut, date_fin, statut) VALUES 
                (2024, '2024-01-01', '2024-12-31', 'ouvert'),
                (2025, '2025-01-01', '2025-12-31', 'ferme')";
        $pdo->exec($sql);
        echo "<p>✅ Données d'exercices ajoutées</p>";
    }
    
    // 4. Vérifier les écritures existantes
    $count_ecritures = $pdo->query("SELECT COUNT(*) FROM ecritures")->fetchColumn();
    if ($count_ecritures == 0) {
        // Ajouter des écritures de test
        $sql = "INSERT INTO ecritures (id_exercice, date_ecriture, journal_code, num_piece, compte_num, libelle, debit, credit) VALUES
                (1, '2024-01-15', 'VT', 'VT/24-001', '41100000', 'Facture Vente n°001', 120000.00, 0),
                (1, '2024-01-15', 'VT', 'VT/24-001', '70100000', 'Vente Alpha Commerce', 0, 100000.00),
                (1, '2024-01-15', 'VT', 'VT/24-001', '44520000', 'TVA Facturée', 0, 20000.00),
                (1, '2024-01-20', 'AC', 'AC/24-005', '62100000', 'Achat fournitures', 50000.00, 0),
                (1, '2024-01-20', 'AC', 'AC/24-005', '44540000', 'TVA Récupérable', 10000.00, 0),
                (1, '2024-01-20', 'AC', 'AC/24-005', '40100000', 'Facture Fourniture', 0, 60000.00)";
        $pdo->exec($sql);
        echo "<p>✅ Écritures de test ajoutées</p>";
    }
    
    // 5. Réactiver les contraintes
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "<p>✅ Contraintes réactivées</p>";
    
    echo "<h3>✅ Réparation terminée avec succès!</h3>";
    
    // Afficher le résumé
    $exercices = $pdo->query("SELECT COUNT(*) as nb FROM exercices_comptables")->fetch();
    $ecritures = $pdo->query("SELECT COUNT(*) as nb FROM ecritures")->fetch();
    
    echo "<div class='alert alert-success'>
            <h4>Résumé</h4>
            <p>Exercices: {$exercices['nb']}</p>
            <p>Écritures: {$ecritures['nb']}</p>
            <p><a href='index.php'>Retour à l'accueil</a></p>
          </div>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'><strong>Erreur:</strong> " . $e->getMessage() . "</div>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Réparation Base de Données</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h2>Réparation de la base de données SYSCOA</h2>
        </div>
        <div class="card-body">
            <?php
            // Le code PHP ci-dessus s'exécute ici
            ?>
        </div>
    </div>
</body>
</html>
