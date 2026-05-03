<?php
// Connexion à la base de données MySQL
$servername = "127.0.0.1";
$username = "root";
$password = "123";
$dbname = "ohada";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Fonction pour récupérer la somme d'un compte ou groupe de comptes
function getMontantCompte($conn, $compte) {
    $stmt = $conn->prepare("SELECT SUM(montant) as total FROM comptes_comptables WHERE code_compte LIKE ?");
    $compteLike = $compte . '%';
    $stmt->bind_param("s", $compteLike);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Calcul des SIG

// Chiffre d'affaires (701)
$chiffreAffaires = getMontantCompte($conn, '701');

// Production de l'exercice
$productionVendue = $chiffreAffaires;
$variationStocks = getMontantCompte($conn, '603');
$productionExercice = $productionVendue + $variationStocks;

// Valeur ajoutée (VA)
$consommationsBiensServices = getMontantCompte($conn, '60'); // Comptes de consommations de biens
$valeurAjoutee = $productionExercice - $consommationsBiensServices;

// Excédent Brut d'Exploitation (EBE)
$subventionsExploitation = getMontantCompte($conn, '74');
$impotsTaxes = getMontantCompte($conn, '63');
$chargesPersonnel = getMontantCompte($conn, '641');
$EBE = $valeurAjoutee + $subventionsExploitation - $impotsTaxes - $chargesPersonnel;

// Résultat d'exploitation
$reprisesProvisions = getMontantCompte($conn, '791');
$dotationsAmortissements = getMontantCompte($conn, '681');
$resultatExploitation = $EBE + $reprisesProvisions - $dotationsAmortissements;

// Résultat financier
$produitsFinanciers = getMontantCompte($conn, '75');
$chargesFinancieres = getMontantCompte($conn, '661');
$resultatFinancier = $produitsFinanciers - $chargesFinancieres;

// Résultat courant avant impôts
$resultatCourantAvantImpots = $resultatExploitation + $resultatFinancier;

// Affichage des résultats
echo "<h2>Soldes Intermédiaires de Gestion (SIG)</h2>";
echo "<p>Chiffre d'affaires : " . number_format($chiffreAffaires, 2) . " €</p>";
echo "<p>Production de l'exercice : " . number_format($productionExercice, 2) . " €</p>";
echo "<p>Valeur ajoutée : " . number_format($valeurAjoutee, 2) . " €</p>";
echo "<p>Excédent Brut d'Exploitation (EBE) : " . number_format($EBE, 2) . " €</p>";
echo "<p>Résultat d'exploitation : " . number_format($resultatExploitation, 2) . " €</p>";
echo "<p>Résultat financier : " . number_format($resultatFinancier, 2) . " €</p>";
echo "<p>Résultat courant avant impôts : " . number_format($resultatCourantAvantImpots, 2) . " €</p>";

// Fermer la connexion
$conn->close();
?>