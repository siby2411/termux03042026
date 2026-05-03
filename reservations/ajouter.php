<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$error = '';
$success = '';

$clients = $pdo->query("SELECT id, nom, prenom FROM clients ORDER BY nom")->fetchAll();
$chambres = $pdo->query("SELECT id, numero, type, prix_nuit FROM chambres WHERE statut = 'disponible' OR statut = 'reserve' ORDER BY numero")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = $_POST['client_id'] ?? 0;
    $chambre_id = $_POST['chambre_id'] ?? 0;
    $date_arrivee = $_POST['date_arrivee'] ?? '';
    $date_depart = $_POST['date_depart'] ?? '';
    $statut = $_POST['statut'] ?? 'Confirmée';
    $mode_paiement = $_POST['mode_paiement'] ?? 'Espèces';
    $notes = trim($_POST['notes'] ?? '');

    if (!$client_id || !$chambre_id || !$date_arrivee || !$date_depart) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $nb_nuits = calculerNuits($date_arrivee, $date_depart);
        if ($nb_nuits <= 0) {
            $error = "La date de départ doit être postérieure à la date d'arrivée.";
        } else {
            $prix_total = calculerPrixTotal($chambre_id, $nb_nuits);
            try {
                $stmt = $pdo->prepare("INSERT INTO reservations (client_id, chambre_id, date_arrivee, date_depart, nb_nuits, prix_total, statut, mode_paiement, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$client_id, $chambre_id, $date_arrivee, $date_depart, $nb_nuits, $prix_total, $statut, $mode_paiement, $notes]);
                
                // Mettre à jour le statut de la chambre
                $pdo->prepare("UPDATE chambres SET statut = 'reserve' WHERE id = ?")->execute([$chambre_id]);
                
                $success = "Réservation ajoutée avec succès. Total: " . formatMoney($prix_total) . " pour $nb_nuits nuits.";
            } catch (PDOException $e) {
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Nouvelle réservation</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
function calculerPrix() {
    const chambre = document.getElementById('chambre_id');
    const prixNuit = chambre.options[chambre.selectedIndex]?.getAttribute('data-prix') || 0;
    const arrivee = new Date(document.getElementById('date_arrivee').value);
    const depart = new Date(document.getElementById('date_depart').value);
    if (arrivee && depart && depart > arrivee) {
        const nuits = Math.ceil((depart - arrivee) / (1000 * 60 * 60 * 24));
        const total = nuits * prixNuit;
        document.getElementById('total_preview').innerHTML = total.toLocaleString() + ' FCFA';
        document.getElementById('nuits_preview').innerHTML = nuits + ' nuit(s)';
    } else {
        document.getElementById('total_preview').innerHTML = '-';
        document.getElementById('nuits_preview').innerHTML = '-';
    }
}
</script>
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2><i class="fas fa-plus me-2"></i>Nouvelle réservation</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post" onchange="calculerPrix()" onkeyup="calculerPrix()">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Client *</label>
                    <select name="client_id" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($clients as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= escape($c['prenom'] . ' ' . $c['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Chambre *</label>
                    <select name="chambre_id" id="chambre_id" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($chambres as $ch): ?>
                        <option value="<?= $ch['id'] ?>" data-prix="<?= $ch['prix_nuit'] ?>"><?= escape($ch['numero']) ?> - <?= escape($ch['type']) ?> (<?= formatMoney($ch['prix_nuit']) ?>/nuit)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Date d'arrivée *</label>
                    <input type="date" name="date_arrivee" id="date_arrivee" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Date de départ *</label>
                    <input type="date" name="date_depart" id="date_depart" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Statut</label>
                    <select name="statut" class="form-control">
                        <option value="Confirmée">Confirmée</option>
                        <option value="En cours">En cours</option>
                        <option value="Terminée">Terminée</option>
                        <option value="Annulée">Annulée</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Mode de paiement</label>
                    <select name="mode_paiement" class="form-control">
                        <option value="Espèces">Espèces</option>
                        <option value="Carte">Carte bancaire</option>
                        <option value="Virement">Virement</option>
                        <option value="Mobile Money">Mobile Money</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>&nbsp;</label>
                    <div class="alert alert-info">
                        <strong>Détails :</strong><br>
                        <span id="nuits_preview">-</span><br>
                        <strong>Total : <span id="total_preview">-</span></strong>
                    </div>
                </div>
                <div class="col-12 mb-3">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
