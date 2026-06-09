<?php
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../config/Database.php';
$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_client = intval($_POST['id_client']);
    $montant = floatval($_POST['montant']);
    $mode = $_POST['mode_paiement'];
    $notes = htmlspecialchars($_POST['notes']); // Récupération du commentaire/référence
    
    // Insertion avec le champ notes
    $stmt = $db->prepare("INSERT INTO PAIEMENTS (type_tiers, id_tiers, montant, mode_paiement, notes, date_paiement) VALUES ('CLIENT', ?, ?, ?, ?, NOW())");
    $stmt->execute([$id_client, $montant, $mode, $notes]);
    
    header("Location: etat_financier_clients.php?msg=Paiement_Enregistre");
    exit();
}

$id_client = isset($_GET['id_client']) ? intval($_GET['id_client']) : 0;
$client = $db->prepare("SELECT * FROM CLIENTS WHERE id_client = ?");
$client->execute([$id_client]);
$c = $client->fetch(PDO::FETCH_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<div class="card shadow-sm border-0 col-md-6 mx-auto">
    <div class="card-header bg-white py-3"><h5><i class="fas fa-hand-holding-usd me-2"></i> Encaisser un paiement</h5></div>
    <div class="card-body">
        <form method="POST">
            <input type="hidden" name="id_client" value="<?= $id_client ?>">
            
            <div class="mb-3">
                <label class="form-label">Client</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($c['nom'] . ' ' . $c['prenom']) ?>" readonly>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Montant (F)</label>
                    <input type="number" name="montant" class="form-control" step="0.01" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mode de paiement</label>
                    <select name="mode_paiement" class="form-select">
                        <option value="Espèce">Espèce</option>
                        <option value="Mobile Money">Mobile Money</option>
                        <option value="Virement">Virement</option>
                        <option value="Chèque">Chèque</option>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Réf. transaction / Commentaire</label>
                <input type="text" name="notes" class="form-control" placeholder="Ex: Wave #123456789 ou Chèque #987">
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Enregistrer l'encaissement</button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
