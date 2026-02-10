<?php
// /var/www/piece_auto/modules/gestion_achats.php

include '../config/Database.php';
include '../includes/header.php';
$page_title = "Gestion des Achats et Fournisseurs";

$database = new Database();
$db = $database->getConnection(); 
$message_status = "";

// --- 1. LOGIQUE : GESTION DE L'AJOUT DE FOURNISSEUR ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_fournisseur') {
    // Nettoyage des données
    $nom_fournisseur = htmlspecialchars($_POST['nom_fournisseur']);
    $contact_tel = htmlspecialchars($_POST['contact_tel']);
    $email = htmlspecialchars($_POST['email']);
    
    // Requête d'insertion
    $query = "INSERT INTO FOURNISSEURS (nom_fournisseur, contact_tel, email) 
              VALUES (:nom, :tel, :email)";
    
    $stmt = $db->prepare($query);

    try {
        $stmt->execute([
            ':nom' => $nom_fournisseur,
            ':tel' => $contact_tel,
            ':email' => $email
        ]);
        $message_status = "<div class='alert alert-success'>Fournisseur **" . $nom_fournisseur . "** ajouté avec succès !</div>";
    } catch (PDOException $e) {
        $message_status = "<div class='alert alert-danger'>Erreur lors de l'ajout : " . $e->getMessage() . "</div>";
    }
}

// --- 2. LOGIQUE : RÉCUPÉRATION DE LA LISTE DES FOURNISSEURS ---
$fournisseurs = [];
$query_fournisseurs = "SELECT * FROM FOURNISSEURS ORDER BY nom_fournisseur LIMIT 50";
$stmt_fournisseurs = $db->query($query_fournisseurs);
if ($stmt_fournisseurs) {
    $fournisseurs = $stmt_fournisseurs->fetchAll(PDO::FETCH_ASSOC);
}
?>

<div class="row">
    <div class="col-12">
        <h2 class="mb-4"><i class="fas fa-truck"></i> Gestion des Achats & Fournisseurs</h2>
        
        <div class="d-flex justify-content-between mb-4">
            <a href="#formulaire_fournisseur" class="btn btn-info" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="formulaire_fournisseur">
                <i class="fas fa-user-tie"></i> Ajouter un Fournisseur
            </a>
            <a href="#formulaire_achat" class="btn btn-auto" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="formulaire_achat">
                <i class="fas fa-file-invoice-dollar"></i> Enregistrer une Commande
            </a>
        </div>
        
        <?= $message_status ?>

        <div class="collapse mb-5" id="formulaire_fournisseur">
            <div class="card p-4">
                <h4 class="card-title mb-4">Fiche Nouveau Fournisseur</h4>
                <form method="POST" action="gestion_achats.php">
                    <input type="hidden" name="action" value="add_fournisseur">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nom_fournisseur" class="form-label">Nom du Fournisseur</label>
                            <input type="text" class="form-control" name="nom_fournisseur" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="col-md-6">
                            <label for="contact_tel" class="form-label">Téléphone</label>
                            <input type="tel" class="form-control" name="contact_tel">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success mt-4"><i class="fas fa-save"></i> Enregistrer le Fournisseur</button>
                </form>
            </div>
        </div>
        
        <div class="collapse mb-5" id="formulaire_achat">
            <div class="card p-4 bg-light">
                <h4 class="card-title mb-4">Nouvelle Commande d'Achat</h4>
                <p class="text-danger small">NOTE: Ce formulaire n'est qu'un squelette. Le vrai module nécessite une logique complexe (ajout de lignes d'achat).</p>
                <form>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Fournisseur</label>
                            <select class="form-select" required>
                                <option value="">Choisir...</option>
                                <?php foreach ($fournisseurs as $f): ?>
                                    <option value="<?= $f['id_fournisseur'] ?>"><?= $f['nom_fournisseur'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date de Commande</label>
                            <input type="date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Montant Total HT</label>
                            <input type="number" step="0.01" min="0" class="form-control" placeholder="0.00" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning mt-4"><i class="fas fa-plus"></i> Enregistrer l'Achat (Simple)</button>
                </form>
            </div>
        </div>

        <div class="card p-4">
            <h4 class="card-title mb-4">Liste des Fournisseurs (<?= count($fournisseurs) ?>)</h4>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nom Fournisseur</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($fournisseurs)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Aucun fournisseur enregistré.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($fournisseurs as $f): ?>
                                <tr>
                                    <td class="fw-bold"><?= $f['nom_fournisseur'] ?></td>
                                    <td><?= $f['email'] ?></td>
                                    <td><?= $f['contact_tel'] ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-success" title="Nouvelle Commande"><i class="fas fa-plus"></i></a>
                                        <a href="#" class="btn btn-sm btn-outline-primary" title="Modifier"><i class="fas fa-edit"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php 
include '../includes/footer.php'; 
?>
