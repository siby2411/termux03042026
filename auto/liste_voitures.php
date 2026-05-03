<?php
session_start();
include_once "db_connect.php";
include_once "header.php";

$res = $conn->query("SELECT * FROM voitures ORDER BY id DESC");
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-car me-2 text-primary"></i>Gestion du Parc Automobile</h2>
        <a href="ajouter_voiture.php" class="btn btn-primary shadow-sm"><i class="fas fa-plus me-2"></i>Nouvelle Voiture</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Véhicule</th>
                        <th>Immat</th>
                        <th>Prix/Jour</th>
                        <th class="text-center">Galerie</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?php echo $row['image_url']; ?>" class="rounded-3 me-3" style="width:60px; height:40px; object-fit:cover;">
                                <div>
                                    <span class="fw-bold d-block"><?php echo $row['marque'] . " " . $row['modele']; ?></span>
                                    <small class="text-muted"><?php echo $row['boite_vitesse']; ?> | <?php echo $row['carburant']; ?></small>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?php echo $row['immatriculation']; ?></span></td>
                        <td class="fw-bold text-primary"><?php echo number_format($row['prix_journalier'], 0, ',', ' '); ?> F</td>
                        
                        <td class="text-center">
                            <a href="upload_diaporama.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-info rounded-pill px-3">
                                <i class="fas fa-images me-1"></i> Gérer
                            </a>
                        </td>

                        <td class="text-end">
                            <div class="btn-group">
                                <a href="detail_voiture.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border" title="Voir"><i class="fas fa-eye"></i></a>
                                <a href="modifier_voiture.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border text-warning" title="Modifier"><i class="fas fa-edit"></i></a>
                                <a href="supprimer_voiture.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Supprimer ce véhicule ?')" title="Supprimer"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once "footer.php"; ?>
