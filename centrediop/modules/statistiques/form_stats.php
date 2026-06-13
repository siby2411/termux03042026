<div class="card p-4 shadow-sm">
    <h5><i class="fas fa-filter text-primary"></i> Filtres Financiers</h5>
    <form action="/modules/statistiques/index.php" method="GET" class="row g-3">
        <div class="col-md-3">
            <select name="service_id" class="form-select">
                <option value="">Tous les Services</option>
                <?php 
                $services = $db->query("SELECT id, name FROM services");
                foreach($services as $s) echo "<option value='{$s['id']}'>{$s['name']}</option>";
                ?>
            </select>
        </div>
        <div class="col-md-3"><input type="date" name="date_debut" class="form-control" placeholder="Date début"></div>
        <div class="col-md-3"><input type="date" name="date_fin" class="form-control" placeholder="Date fin"></div>
        <div class="col-md-3"><button type="submit" class="btn btn-primary w-100">Générer État</button></div>
    </form>
</div>
