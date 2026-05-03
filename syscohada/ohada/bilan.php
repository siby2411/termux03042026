<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saisie Comptable</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2 class="mt-5">Saisie des opérations comptables</h2>
    <form action="ajouter_ecriture.php" method="POST">
        <div class="form-group">
            <label for="date_operation">Date de l'opération :</label>
            <input type="date" class="form-control" id="date_operation" name="date_operation" required>
        </div>
        <div class="form-group">
            <label for="numero_compte">Numéro de compte :</label>
            <input type="text" class="form-control" id="numero_compte" name="numero_compte" required>
        </div>
        <div class="form-group">
            <label for="description">Description :</label>
            <input type="text" class="form-control" id="description" name="description" required>
        </div>
        <div class="form-group">
            <label for="debit">Débit :</label>
            <input type="number" class="form-control" id="debit" name="debit">
        </div>
        <div class="form-group">
            <label for="credit">Crédit :</label>
            <input type="number" class="form-control" id="credit" name="credit">
        </div>
        <button type="submit" class="btn btn-primary">Ajouter l'écriture</button>
    </form>
</div>
</body>
</html>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saisie Comptable</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2 class="mt-5">Saisie des opérations comptables</h2>
    <form action="ajouter_ecriture.php" method="POST">
        <div class="form-group">
            <label for="date_operation">Date de l'opération :</label>
            <input type="date" class="form-control" id="date_operation" name="date_operation" required>
        </div>
        <div class="form-group">
            <label for="numero_compte">Numéro de compte :</label>
            <input type="text" class="form-control" id="numero_compte" name="numero_compte" required>
        </div>
        <div class="form-group">
            <label for="description">Description :</label>
            <input type="text" class="form-control" id="description" name="description" required>
        </div>
        <div class="form-group">
            <label for="debit">Débit :</label>
            <input type="number" class="form-control" id="debit" name="debit">
        </div>
        <div class="form-group">
            <label for="credit">Crédit :</label>
            <input type="number" class="form-control" id="credit" name="credit">
        </div>
        <button type="submit" class="btn btn-primary">Ajouter l'écriture</button>
    </form>
</div>
</body>
</html>


<?php
// Connexion à la base de données
$host = '127.0.0.1';
$dbname = 'comptabilite';
$user = 'root';
$password = '123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les données du formulaire
    $date_operation = $_POST['date_operation'];
    $numero_compte = $_POST['numero_compte'];
    $description = $_POST['description'];
    $debit = $_POST['debit'] ?? 0;
    $credit = $_POST['credit'] ?? 0;
    
    // Insertion dans la table des écritures
    $sql = "INSERT INTO ecritures (date_operation, numero_compte, description, debit, credit) 
            VALUES (:date_operation, :numero_compte, :description, :debit, :credit)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':date_operation' => $date_operation,
        ':numero_compte' => $numero_compte,
        ':description' => $description,
        ':debit' => $debit,
        ':credit' => $credit
    ]);

    echo "Écriture comptable ajoutée avec succès !";
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>


<?php
// Connexion à la base de données
$host = '127.0.0.1';
$dbname = 'comptabilite';
$user = 'root';
$password = '123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Récupérer les actifs
    $sql_actifs = "SELECT c.intitule, SUM(e.debit - e.credit) AS solde 
                   FROM comptes c 
                   JOIN ecritures e ON c.numero_compte = e.numero_compte 
                   WHERE c.type_compte = 'Actif' 
                   GROUP BY c.intitule";
    $actifs = $pdo->query($sql_actifs)->fetchAll(PDO::FETCH_ASSOC);

    // Récupérer les passifs
    $sql_passifs = "SELECT c.intitule, SUM(e.credit - e.debit) AS solde 
                    FROM comptes c 
                    JOIN ecritures e ON c.numero_compte = e.numero_compte 
                    WHERE c.type_compte = 'Passif' 
                    GROUP BY c.intitule";
    $passifs = $pdo->query($sql_passifs)->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Bilan Comptable</h2>";
    
    // Affichage des actifs
    echo "<h3>Actifs</h3>";
    foreach ($actifs as $actif) {
        echo "<p>{$actif['intitule']} : {$actif['solde']} €</p>";
    }

    // Affichage des passifs
    echo "<h3>Passifs</h3>";
    foreach ($passifs as $passif) {
        echo "<p>{$passif['intitule']} : {$passif['solde']} €</p>";
    }

} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>


CREATE TABLE comptes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_compte VARCHAR(20) NOT NULL,
    intitule VARCHAR(255) NOT NULL,
    type_compte ENUM('Actif', 'Passif', 'Produit', 'Charge') NOT NULL
);

CREATE TABLE ecritures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_operation DATE NOT NULL,
    numero_compte VARCHAR(20),
    description VARCHAR(255),
    debit DECIMAL(10, 2) DEFAULT 0,
    credit DECIMAL(10, 2) DEFAULT 0,
    FOREIGN KEY (numero_compte) REFERENCES comptes(numero_compte)
);


CREATE TABLE bilans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_bilan DATE NOT NULL,
    total_actif DECIMAL(10, 2) DEFAULT 0,
    total_passif DECIMAL(10, 2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
-- Insertion de comptes
INSERT INTO comptes (numero_compte, intitule, type_compte) VALUES
('1000', 'Caisse', 'Actif'),
('1010', 'Banque', 'Actif'),
('2000', 'Fournisseurs', 'Passif'),
('2100', 'Banque', 'Passif');

-- Insertion d'écritures
INSERT INTO ecritures (date_operation, numero_compte, description, debit, credit) VALUES
('2024-09-01', '1000', 'Vente de produits', 1000, 0),
('2024-09-01', '2000', 'Achat de marchandises', 0, 500),
('2024-09-15', '1010', 'Virement bancaire', 200, 0),
('2024-09-15', '2100', 'Remboursement fournisseur', 0, 200);
