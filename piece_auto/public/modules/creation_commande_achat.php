<?php
// /var/www/piece_auto/public/modules/creation_commande_achat.php
$page_title = "Création Commande d'Achat";
require_once __DIR__ . '/../../config/Database.php';
include '../../includes/header.php';

$database = new Database();
$db = $database->getConnection();
$message = '';

// 1. Logique d'enregistrement de la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enregistrer_commande'])) {
    try {
        $db->beginTransaction();

        $id_fournisseur = $_POST['id_fournisseur'];
        $date_commande = date('Y-m-d');
        
        // Création de l'entête de commande d'achat
        $query_cmd = "INSERT INTO COMMANDES_ACHAT (id_fournisseur, date_commande, statut) VALUES (:id_f, :dt, 'En attente')";
        $stmt_cmd = $db->prepare($query_cmd);
        $stmt_cmd->execute([':id_f' => $id_fournisseur, ':dt' => $date_commande]);
        $id_commande = $db->lastInsertId();

        // Ajout des lignes (Exemple simplifié avec une ligne, extensible en JS pour plusieurs)
        if (isset($_POST['id_piece']) && !empty($_POST['id_piece'])) {
            $query_ligne = "INSERT INTO LIGNES_COMMANDE_ACHAT (id_commande_achat, id_piece, quantite_commandee, prix_achat_unitaire) 
                            VALUES (:id_c, :id_p, :qty, :px)";
            $stmt_ligne = $db->prepare($query_ligne);
            $stmt_ligne->execute([
                ':id_c' => $id_commande,
                ':id_p' => $_POST['id_piece'],
                ':qty' => $_POST['quantite'],
                ':px'  => $_POST['prix_achat']
            ]);
        }

        $db->commit();
        $message = '<div class="alert alert-success">Commande d\'achat #' . $id_commande . ' enregistrée avec succès.</div>';
    } catch (Exception $e) {
        $db->rollBack();
        $message = '<div class="alert alert-danger">Erreur : ' . $e->getMessage() . '</div>';
    }
}

// 2. Récupération des données pour le formulaire
$fournisseurs = $db->query("SELECT id_fournisseur, nom_fournisseur FROM FOURNISSEURS")->fetchAll(PDO::FETCH_ASSOC);
$pieces = $db->query("SELECT id_piece, reference, nom_piece FROM PIECES")->fetchAll(PDO::FETCH_ASSOC);
?>

<h1><i class="fas fa-cart-arrow-down"></i> Nouvelle Commande d'Achat</h1>
<p class="lead">Sélectionnez un fournisseur et les pièces à réapprovisionner.</p>
<hr>

<?= $message ?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-dark text-white">Formulaire de commande</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Fournisseur</label>
                        <select name="id_fournisseur" class="form-select" required>
                            <option value="">-- Choisir un fournisseur --</option>
                            <?php foreach ($fournisseurs as $f): ?>
                                <option value="<?= $f['id_fournisseur'] ?>"><?= htmlspecialchars($f['nom_fournisseur']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <h5 class="mt-4">Pièce à commander</h5>
                    <div class="row g-3 border p-3 rounded bg-light">
                        <div class="col-md-6">
                            <label class="form-label">Référence / Nom</label>
                            <select name="id_piece" class="form-select" required>
                                <option value="">-- Choisir une pièce --</option>
                                <?php foreach ($pieces as $p): ?>
                                    <option value="<?= $p['id_piece'] ?>"><?= $p['reference'] ?> - <?= $p['nom_piece'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Quantité</label>
                            <input type="number" name="quantite" class="form-control" min="1" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Prix Achat (€)</label>
                            <input type="number" step="0.01" name="prix_achat" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" name="enregistrer_commande" class="btn btn-primary mt-4">
                        <i class="fas fa-save"></i> Enregistrer la commande
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="alert alert-warning">
            <h5><i class="fas fa-info-circle"></i> Info Flux</h5>
            L'enregistrement d'une commande d'achat ne modifie pas encore le stock. Le stock sera impacté lors de la <strong>Réception</strong>.
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
