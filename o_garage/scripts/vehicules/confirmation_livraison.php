<?php 
include '../../includes/header.php'; 
require_once '../../includes/classes/Database.php';
$db = (new Database())->getConnection();

if(isset($_POST['livrer'])) {
    $db->prepare("UPDATE vehicules SET statut_presence='Sorti', date_livraison=NOW() WHERE id_vehicule=?")->execute([$_POST['id_v']]);
    echo "<div class='alert alert-success'>Véhicule livré avec succès !</div>";
}
?>
<h3>Confirmation de Livraison</h3>
<table class="table table-striped">
    <thead><tr><th>Véhicule</th><th>Client</th><th>Action</th></tr></thead>
    <tbody>
    <?php
    $list = $db->query("SELECT v.*, c.nom FROM vehicules v JOIN clients c ON v.id_client = c.id_client WHERE v.statut_presence='Atelier'");
    while($v = $list->fetch()) {
        echo "<tr>
            <td>{$v['marque']} {$v['modele']} ({$v['immatriculation']})</td>
            <td>{$v['nom']}</td>
            <td>
                <form method='POST'>
                    <input type='hidden' name='id_v' value='{$v['id_vehicule']}'>
                    <button name='livrer' class='btn btn-sm btn-success'>Confirmer le retrait</button>
                </form>
            </td>
        </tr>";
    }
    ?>
    </tbody>
</table>
<?php include '../../includes/footer.php'; ?>
