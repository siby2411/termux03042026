<?php
require_once '../../includes/classes/Database.php';
$dbObj = new Database();
$pdo = $dbObj->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'terminer') {
            $id = $_POST['id_lavage'];
            
            // On récupère les infos pour WhatsApp
            $stmt = $pdo->prepare("SELECT l.*, c.nom, c.telephone FROM lavage_operations l 
                                   JOIN clients c ON l.immatriculation = c.immatriculation_principale 
                                   WHERE l.id_lavage = ?");
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            $update = $pdo->prepare("UPDATE lavage_operations SET statut = 'Terminé', heure_sortie = NOW() WHERE id_lavage = ?");
            $update->execute([$id]);

            // Message WhatsApp formaté
            $msg = "Bonjour " . $data['nom'] . ", votre vehicule " . $data['immatriculation'] . " est pret chez OMEGA TECH.";
            $url = "https://wa.me/221" . str_replace(' ', '', $data['telephone']) . "?text=" . urlencode($msg);

            // JS pour ouvrir WhatsApp et rediriger pour éviter la page blanche
            echo "<html><body><script>
                window.open('$url', '_blank');
                window.location.href='../../index.php?status=success';
            </script></body></html>";
            exit();
        }
    } catch (Exception $e) { die($e->getMessage()); }
}
header('Location: ../../index.php');
