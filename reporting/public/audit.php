 Parfait. On va refondre complètement l’expérience utilisateur pour ton application comptable SYSCOHADA,
 avec un design professionnel, complet et fonctionnel,
 même si les fichiers sidebar.php, topbar.php, footer.php ne sont pas présents.
Voici la stratégie complète :

1️⃣ Objectifs


Créer un menu dropdown dynamique pour les codes item (VENTILATION_ITEMS)

 et pour toutes les tables concernées (PLAN_COMPTABLE_UEMOA, SOCIETES, ECRITURES_COMPTABLES).


Créer des formulaires Bootstrap 5 épurés, intégrant :



Sidebar / topbar / footer (ou alternative inline si les vues manquent)



Validation côté navigateur (HTML5 + JS léger)



Insertion sécurisée dans la base MariaDB via PDO




Assurer que chaque formulaire peut s’auto-suffire :


Même si views/ n’existe pas, le formulaire s’affiche proprement


Bootstrap 5 intégré via CDN


Alertes et notifications pour succès ou erreur





2️⃣ Exemple de formulaire complet pour VENTILATION_ITEMS

<?php
// Connexion à la base
require_once __DIR__ . '/../config/database.php'; // $conn PDO

// Insertion sécurisée
$message = '';
if(isset($_POST['submit'])){
    $item_code = $_POST['item_code'];
    $item_label = $_POST['item_label'];
    $item_type = $_POST['item_type'];
    $criteria = $_POST['criteria'];
    $display_order = $_POST['display_order'];

    $stmt = $conn->prepare("INSERT INTO VENTILATION_ITEMS (item_code, item_label, item_type, criteria, display_order) VALUES (?, ?, ?, ?, ?)");
    if($stmt->execute([$item_code, $item_label, $item_type, $criteria, $display_order])){
        $message = '<div class="alert alert-success">Item ajouté avec succès !</div>';
    } else {
        $message = '<div class="alert alert-danger">Erreur lors de l\'insertion.</div>';
    }
}

// Récupération des codes items pour le dropdown
$codes_items = $conn->query("SELECT item_code FROM VENTILATION_ITEMS ORDER BY item_code")->fetchAll(PDO::FETCH_COLUMN);

?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion Ventilation SYSCOHADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid">
    <div class="row bg-dark text-white p-3 mb-4">
        <div class="col"><h3>SYSCOHADA - Ventilation</h3></div>
        <div class="col text-end"><a href="dashboard.php" class="btn btn-light btn-sm">Retour Dashboard</a></div>
    </div>

    <?php echo $message; ?>

    <form method="post" class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Code Item</label>
            <select class="form-select" name="item_code" required>
                <option value="">-- Choisir un code --</option>
                <?php foreach($codes_items as $code): ?>
                    <option value="<?php echo $code; ?>"><?php echo $code; ?></option>
                <?php endforeach; ?>
                <option value="new">Nouveau code...</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Libellé</label>
            <input type="text" class="form-control" name="item_label" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Type</label>
            <select class="form-select" name="item_type" required>
                <option value="BILAN_ACTIF">BILAN_ACTIF</option>
                <option value="BILAN_PASSIF">BILAN_PASSIF</option>
                <option value="CR_CHARGES">CR_CHARGES</option>
                <option value="CR_PRODUITS">CR_PRODUITS</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Critères</label>
            <textarea class="form-control" name="criteria"></textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label">Ordre affichage</label>
            <input type="number" class="form-control" name="display_order" value="0">
        </div>
        <div class="col-12">
            <button type="submit" name="submit" class="btn btn-primary">Ajouter</button>
        </div>
    </form>

    <hr>
    <h5>Liste des items existants</h5>
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Libellé</th>
                <th>Type</th>
                <th>Critères</th>
                <th>Ordre</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $items = $conn->query("SELECT * FROM VENTILATION_ITEMS ORDER BY display_order")->fetchAll(PDO::FETCH_ASSOC);
        foreach($items as $i):
        ?>
            <tr>
                <td><?= $i['item_id'] ?></td>
                <td><?= $i['item_code'] ?></td>
                <td><?= $i['item_label'] ?></td>
                <td><?= $i['item_type'] ?></td>
                <td><?= $i['criteria'] ?></td>
                <td><?= $i['display_order'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

