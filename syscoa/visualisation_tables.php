<?php
// visualisation_tables.php
require_once 'config/database.php';
require_once 'includes/functions.php';
check_auth();

// Récupérer la liste des tables
$sql_tables = "SHOW TABLES";
$stmt_tables = $pdo->query($sql_tables);
$tables = $stmt_tables->fetchAll(PDO::FETCH_COLUMN);

// Table sélectionnée
$table_selected = isset($_GET['table']) ? secure_input($_GET['table']) : '';
$data = [];
$structure = [];
$total_rows = 0;

if ($table_selected && in_array($table_selected, $tables)) {
    // Récupérer la structure de la table
    $sql_structure = "DESCRIBE $table_selected";
    $stmt_structure = $pdo->query($sql_structure);
    $structure = $stmt_structure->fetchAll();
    
    // Compter les lignes
    $sql_count = "SELECT COUNT(*) as total FROM $table_selected";
    $stmt_count = $pdo->query($sql_count);
    $total_rows = $stmt_count->fetch()['total'];
    
    // Récupérer les données (avec pagination)
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = 100;
    $offset = ($page - 1) * $limit;
    
    $sql_data = "SELECT * FROM $table_selected LIMIT $limit OFFSET $offset";
    $stmt_data = $pdo->query($sql_data);
    $data = $stmt_data->fetchAll();
    
    $total_pages = ceil($total_rows / $limit);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualisation des Tables - SYSCO OHADA</title>
    <style>
        :root {
            --primary: #1a365d;
            --secondary: #2d3748;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7fafc;
            color: var(--secondary);
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary), #2b6cb0);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .main-layout {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }
        
        .sidebar {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            height: fit-content;
        }
        
        .table-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .table-item {
            padding: 12px 15px;
            margin-bottom: 8px;
            background: #f7fafc;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .table-item:hover {
            background: #edf2f7;
            border-left-color: var(--primary);
        }
        
        .table-item.active {
            background: #ebf8ff;
            border-left-color: var(--primary);
            font-weight: bold;
        }
        
        .table-icon {
            margin-right: 10px;
            opacity: 0.7;
        }
        
        .content {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .table-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .table-name {
            font-size: 24px;
            color: var(--primary);
            font-weight: bold;
        }
        
        .table-stats {
            color: #718096;
            font-size: 14px;
        }
        
        .structure-table, .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .structure-table th,
        .data-table th {
            background: var(--primary);
            color: white;
            padding: 12px 15px;
            text-align: left;
        }
        
        .structure-table td,
        .data-table td {
            padding: 10px 15px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        
        .structure-table tr:hover,
        .data-table tr:hover {
            background: #f7fafc;
        }
        
        .data-table {
            overflow-x: auto;
            display: block;
        }
        
        .pagination {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .page-link {
            padding: 8px 15px;
            background: #edf2f7;
            border-radius: 6px;
            color: var(--secondary);
            text-decoration: none;
        }
        
        .page-link.active {
            background: var(--primary);
            color: white;
        }
        
        .search-box {
            margin-bottom: 20px;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .sql-query {
            background: #2d3748;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            margin-bottom: 20px;
            overflow-x: auto;
        }
        
        .btn {
            padding: 10px 20px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
        
        .btn:hover {
            background: #2c5282;
        }
        
        .export-options {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        @media (max-width: 1024px) {
            .main-layout {
                grid-template-columns: 1fr;
            }
            
            .sidebar {
                order: 2;
            }
            
            .content {
                order: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <h1>🔍 Visualisation des Tables</h1>
            <p style="opacity: 0.9; margin-top: 10px;">Exploration des données de la base SYSCO OHADA</p>
        </div>
        
        <div class="main-layout">
            <!-- Sidebar avec liste des tables -->
            <div class="sidebar">
                <div class="search-box">
                    <input type="text" id="searchTable" placeholder="Rechercher une table...">
                </div>
                
                <h3 style="color: var(--primary); margin-bottom: 15px;">📋 Tables de la base</h3>
                <ul class="table-list">
                    <?php foreach ($tables as $table): ?>
                        <li class="table-item <?php echo $table == $table_selected ? 'active' : ''; ?>"
                            onclick="window.location.href='?table=<?php echo urlencode($table); ?>'">
                            <span class="table-icon">📄</span>
                            <?php echo $table; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                    <p style="color: #718096; font-size: 13px;">
                        <strong><?php echo count($tables); ?></strong> tables disponibles
                    </p>
                </div>
            </div>
            
            <!-- Contenu principal -->
            <div class="content">
                <?php if ($table_selected): ?>
                    <div class="table-info">
                        <div>
                            <div class="table-name"><?php echo $table_selected; ?></div>
                            <div class="table-stats">
                                <?php echo $total_rows; ?> lignes • 
                                <?php echo count($structure); ?> colonnes
                            </div>
                        </div>
                        
                        <div class="export-options">
                            <button class="btn" onclick="exportToCSV()">📥 Export CSV</button>
                            <button class="btn" onclick="window.print()">🖨️ Imprimer</button>
                        </div>
                    </div>
                    
                    <!-- Structure de la table -->
                    <h3 style="color: var(--primary); margin-bottom: 15px;">🏗️ Structure</h3>
                    <table class="structure-table">
                        <thead>
                            <tr>
                                <th width="200">Colonne</th>
                                <th width="150">Type</th>
                                <th width="100">Null</th>
                                <th width="150">Clé</th>
                                <th>Valeur par défaut</th>
                                <th>Extra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($structure as $col): ?>
                                <tr>
                                    <td><strong><?php echo $col['Field']; ?></strong></td>
                                    <td><code><?php echo $col['Type']; ?></code></td>
                                    <td><?php echo $col['Null']; ?></td>
                                    <td><?php echo $col['Key']; ?></td>
                                    <td><?php echo $col['Default']; ?></td>
                                    <td><?php echo $col['Extra']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <!-- Données -->
                    <h3 style="color: var(--primary); margin-bottom: 15px;">📊 Données (<?php echo count($data); ?> lignes)</h3>
                    
                    <div class="sql-query">
                        SELECT * FROM <?php echo $table_selected; ?> LIMIT <?php echo $limit; ?> OFFSET <?php echo $offset; ?>;
                    </div>
                    
                    <?php if (!empty($data)): ?>
                        <div class="data-table">
                            <table>
                                <thead>
                                    <tr>
                                        <?php foreach (array_keys($data[0]) as $colonne): ?>
                                            <th><?php echo $colonne; ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data as $ligne): ?>
                                        <tr>
                                            <?php foreach ($ligne as $valeur): ?>
                                                <td>
                                                    <?php 
                                                    if ($valeur === null) {
                                                        echo '<span style="color: #a0aec0; font-style: italic;">NULL</span>';
                                                    } elseif (is_numeric($valeur)) {
                                                        echo number_format($valeur, 2, ',', ' ');
                                                    } else {
                                                        echo htmlspecialchars(substr(strval($valeur), 0, 100));
                                                        if (strlen(strval($valeur)) > 100) echo '...';
                                                    }
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?table=<?php echo urlencode($table_selected); ?>&page=<?php echo $page - 1; ?>" 
                                       class="page-link">‹ Précédent</a>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= min(5, $total_pages); $i++): ?>
                                    <a href="?table=<?php echo urlencode($table_selected); ?>&page=<?php echo $i; ?>" 
                                       class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($total_pages > 5): ?>
                                    <span>...</span>
                                    <a href="?table=<?php echo urlencode($table_selected); ?>&page=<?php echo $total_pages; ?>" 
                                       class="page-link <?php echo $total_pages == $page ? 'active' : ''; ?>">
                                        <?php echo $total_pages; ?>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?table=<?php echo urlencode($table_selected); ?>&page=<?php echo $page + 1; ?>" 
                                       class="page-link">Suivant ›</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div style="text-align: center; padding: 40px; color: #718096;">
                            Aucune donnée dans cette table
                        </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div style="text-align: center; padding: 60px; color: #718096;">
                        <div style="font-size: 48px; margin-bottom: 20px;">📊</div>
                        <h3 style="color: var(--primary); margin-bottom: 15px;">Sélectionnez une table</h3>
                        <p>Choisissez une table dans la liste à gauche pour afficher sa structure et ses données.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Recherche de tables
        document.getElementById('searchTable').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const tableItems = document.querySelectorAll('.table-item');
            
            tableItems.forEach(item => {
                const tableName = item.textContent.toLowerCase();
                if (tableName.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
        
        // Export CSV
        function exportToCSV() {
            const table = '<?php echo $table_selected; ?>';
            if (!table) return;
            
            // Simuler un téléchargement
            const csvContent = "data:text/csv;charset=utf-8,";
            window.open(`export_table.php?table=${table}&format=csv`, '_blank');
        }
    </script>
</body>
</html>
