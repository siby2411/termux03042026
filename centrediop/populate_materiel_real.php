<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();
    
    // 1. Récupérer les IDs réels des services
    $services = $conn->query("SELECT id, name FROM services ORDER BY id")->fetchAll(PDO::FETCH_KEY_PAIR);
    echo "✅ Services trouvés :\n";
    foreach ($services as $id => $name) {
        echo "   ID $id : $name\n";
    }
    
    // 2. Récupérer les salles
    $salles = $conn->query("SELECT id, service_id, batiment_id, etage, numero_salle FROM salles")->fetchAll();
    echo "✅ " . count($salles) . " salles trouvées\n";
    
    // 3. Vérifier/Créer les catégories
    $cat_count = $conn->query("SELECT COUNT(*) FROM categories_materiel")->fetchColumn();
    if ($cat_count == 0) {
        $categories = [
            ['Imagerie médicale', 'Équipements d\'imagerie (radiologie, échographie)'],
            ['Équipement de laboratoire', 'Analyseurs, microscopes, centrifugeuses'],
            ['Matériel chirurgical', 'Instruments pour blocs opératoires'],
            ['Mobilier médical', 'Lits, fauteuils, armoires, tables d\'examen'],
            ['Monitoring', 'Moniteurs de signes vitaux, ECG'],
            ['Réanimation', 'Respirateurs, défibrillateurs, pompes'],
            ['Informatique médicale', 'Ordinateurs, serveurs, écrans'],
            ['Périphériques', 'Imprimantes, scanners, lecteurs codes-barres'],
            ['Matériel de diagnostic', 'Stéthoscopes, tensiomètres, otoscopes'],
            ['Mobilier de bureau', 'Bureaux, chaises, armoires de rangement']
        ];
        
        foreach ($categories as $cat) {
            $stmt = $conn->prepare("INSERT INTO categories_materiel (nom, description) VALUES (?, ?)");
            $stmt->execute($cat);
        }
        echo "✅ Catégories ajoutées\n";
    } else {
        echo "✅ Catégories existantes\n";
    }
    
    // 4. Récupérer les IDs des catégories
    $categories = $conn->query("SELECT id, nom FROM categories_materiel")->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // 5. Ajouter des fournisseurs s'ils n'existent pas
    $fournisseurs = [
        ['Siemens Healthineers', 'M. Diallo', '+221 33 849 30 00', 'contact@siemens.sn', 'Dakar', 'Imagerie médicale'],
        ['GE Healthcare', 'Mme Diop', '+221 33 859 40 00', 'info@gehealthcare.sn', 'Dakar', 'Équipement médical'],
        ['Philips Medical Systems', 'M. Fall', '+221 33 869 50 00', 'senegal@philips.com', 'Dakar', 'Équipement hospitalier'],
        ['Dell Technologies', 'M. Ndiaye', '+221 33 879 60 00', 'ventes@dell.sn', 'Dakar', 'Informatique'],
        ['HP Inc.', 'Mme Ba', '+221 33 889 70 00', 'info@hp.sn', 'Dakar', 'Ordinateurs, imprimantes'],
        ['Fresenius', 'M. Sow', '+221 33 899 80 00', 'info@fresenius.sn', 'Dakar', 'Dialyse, réanimation'],
        ['Roche Diagnostics', 'Mme Ndiaye', '+221 33 919 00 11', 'roche@roche.sn', 'Dakar', 'Diagnostic, laboratoire'],
        ['Medtronic', 'M. Diouf', '+221 33 929 11 22', 'medtronic@medtronic.sn', 'Dakar', 'Équipement chirurgical'],
        ['Stryker', 'M. Thiam', '+221 33 939 22 33', 'stryker@stryker.sn', 'Dakar', 'Matériel orthopédique'],
        ['Dräger', 'M. Faye', '+221 33 959 44 55', 'draege@draeger.sn', 'Dakar', 'Réanimation, anesthésie']
    ];

    foreach ($fournisseurs as $f) {
        $stmt = $conn->prepare("INSERT IGNORE INTO fournisseurs (nom, contact, telephone, email, adresse, specialite) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute($f);
    }
    echo "✅ Fournisseurs ajoutés/vérifiés\n";

    // 6. Générer du matériel pour chaque service (adapté aux IDs réels)
    $materiel_data = [
        // Cardiologie (ID 8)
        [8, 'ECG 12 dérivations', 'Électrocardiographe numérique', 'Monitoring', 3500000, 'GE Healthcare', 'ECG-2025-001', 2, 'actif'],
        [8, 'Holter ECG', 'Enregistreur ECG 24h', 'Monitoring', 2800000, 'Philips Medical Systems', 'HOL-2025-002', 1, 'actif'],
        [8, 'Échocardiographe', 'Échographe cardiaque portable', 'Imagerie médicale', 8500000, 'Siemens Healthineers', 'ECHO-2025-003', 1, 'actif'],
        [8, 'Tensiomètre automatique', 'Tensiomètre brassard électronique', 'Matériel de diagnostic', 450000, 'Fresenius', 'TENS-2025-004', 5, 'actif'],
        [8, 'Défibrillateur', 'Défibrillateur avec monitoring', 'Réanimation', 4200000, 'Medtronic', 'DEF-2025-005', 1, 'actif'],
        
        // Pédiatrie (ID 3)
        [3, 'Balance bébé', 'Balance électronique pour nourrissons', 'Matériel de diagnostic', 125000, 'Seca', 'BAL-2025-006', 3, 'actif'],
        [3, 'Incubateur', 'Incubateur néonatal', 'Réanimation', 8500000, 'Dräger', 'INC-2025-007', 1, 'actif'],
        [3, 'Table à langer', 'Table d\'examen pour bébés', 'Mobilier médical', 250000, 'Stryker', 'TAB-2025-008', 2, 'actif'],
        [3, 'Nébuliseur pédiatrique', 'Nébuliseur pour enfants', 'Matériel de diagnostic', 85000, 'Philips Medical Systems', 'NEB-2025-009', 4, 'actif'],
        [3, 'Stéthoscope pédiatrique', 'Stéthoscope pour enfants', 'Matériel de diagnostic', 75000, 'Medtronic', 'STET-2025-010', 3, 'actif'],
        
        // Gynécologie (ID 5)
        [5, 'Table gynécologique', 'Table d\'examen gynécologique électrique', 'Mobilier médical', 950000, 'Stryker', 'TABG-2025-011', 2, 'actif'],
        [5, 'Échographe obstétrical', 'Échographe avec doppler fœtal', 'Imagerie médicale', 12000000, 'GE Healthcare', 'ECHO-2025-012', 1, 'actif'],
        [5, 'Colposcope', 'Colposcope de gynécologie', 'Matériel de diagnostic', 4500000, 'Leisegang', 'COLPO-2025-013', 1, 'actif'],
        [5, 'Monitorage fœtal', 'Moniteur fœtal', 'Monitoring', 3500000, 'Philips Medical Systems', 'FOET-2025-014', 1, 'actif'],
        [5, 'Spéculum set', 'Set de spéculums stériles', 'Matériel chirurgical', 25000, 'Medtronic', 'SPEC-2025-015', 20, 'actif'],
        
        // Odontologie (ID 4)
        [4, 'Fauteuil dentaire', 'Fauteuil de consultation dentaire électrique', 'Mobilier médical', 3500000, 'Stryker', 'FAUT-2025-016', 2, 'actif'],
        [4, 'Radiologie dentaire', 'Radio panoramique dentaire', 'Imagerie médicale', 8500000, 'Siemens Healthineers', 'RAD-2025-017', 1, 'actif'],
        [4, 'Instrumentation dentaire', 'Kit d\'instruments dentaires complet', 'Matériel chirurgical', 450000, 'Medtronic', 'KIT-2025-018', 5, 'actif'],
        [4, 'Autoclave dentaire', 'Stérilisateur pour instruments dentaires', 'Équipement de laboratoire', 1200000, 'Tuttnauer', 'AUTO-2025-019', 1, 'actif'],
        [4, 'Lampe photopolymériseuse', 'Lampe à polymériser', 'Matériel de diagnostic', 250000, '3M', 'LAMP-2025-020', 3, 'actif'],
        
        // Médecine générale (ID 6)
        [6, 'Table d\'examen', 'Table d\'auscultation', 'Mobilier médical', 450000, 'Stryker', 'TAB-2025-021', 5, 'actif'],
        [6, 'Armoire médicale', 'Armoire de rangement', 'Mobilier médical', 180000, 'Lista', 'ARM-2025-022', 4, 'actif'],
        [6, 'Stéthoscope', 'Stéthoscope professionnel', 'Matériel de diagnostic', 65000, 'Medtronic', 'STET-2025-023', 8, 'actif'],
        [6, 'Otoscope', 'Otoscope LED', 'Matériel de diagnostic', 85000, 'Welch Allyn', 'OTO-2025-024', 3, 'actif'],
        [6, 'Tensiomètre', 'Tensiomètre manuel', 'Matériel de diagnostic', 35000, 'Omron', 'TENS-2025-025', 6, 'actif'],
        
        // Pharmacie (ID 7)
        [7, 'Réfrigérateur pharmaceutique', 'Réfrigérateur pour médicaments', 'Équipement de laboratoire', 2200000, 'Liebherr', 'FRIG-2025-026', 2, 'actif'],
        [7, 'Comptoir de pharmacie', 'Comptoir de dispensation', 'Mobilier médical', 550000, 'Steelcase', 'COMP-2025-027', 1, 'actif'],
        [7, 'Armoire sécurisée', 'Armoire pour produits contrôlés', 'Mobilier médical', 350000, 'Lista', 'ARMS-2025-028', 1, 'actif'],
        
        // Ophtalmologie (ID 9)
        [9, 'Lampe à fente', 'Lampe à fente pour examen oculaire', 'Matériel de diagnostic', 5500000, 'Haag-Streit', 'LAMP-2025-029', 1, 'actif'],
        [9, 'Ophtalmoscope', 'Ophtalmoscope LED', 'Matériel de diagnostic', 250000, 'Welch Allyn', 'OPHT-2025-030', 2, 'actif'],
        [9, 'Réfractomètre', 'Auto-réfractomètre', 'Matériel de diagnostic', 4500000, 'Topcon', 'REF-2025-031', 1, 'actif'],
        
        // Diabétologie (ID 10)
        [10, 'Glucomètre', 'Lecteur de glycémie', 'Matériel de diagnostic', 25000, 'Roche Diagnostics', 'GLUC-2025-032', 10, 'actif'],
        [10, 'Pompe à insuline', 'Pompe à insuline programmable', 'Réanimation', 450000, 'Medtronic', 'POMP-2025-033', 2, 'actif'],
        [10, 'Pèse-personne', 'Balance médicale', 'Mobilier médical', 85000, 'Seca', 'BAL-2025-034', 2, 'actif'],
        
        // Neurologie (ID 11)
        [11, 'Marteau réflexes', 'Marteau neurologique', 'Matériel de diagnostic', 15000, 'Medtronic', 'MART-2025-035', 5, 'actif'],
        [11, 'Diapason', 'Diapason neurologique', 'Matériel de diagnostic', 25000, 'Ryvac', 'DIAP-2025-036', 3, 'actif'],
        [11, 'EEG portable', 'Électroencéphalographe', 'Monitoring', 12500000, 'Natus', 'EEG-2025-037', 1, 'actif'],
        
        // Dermatologie (ID 12)
        [12, 'Dermatoscope', 'Dermatoscope LED', 'Matériel de diagnostic', 350000, 'Heine', 'DERM-2025-038', 1, 'actif'],
        [12, 'Lampe UV', 'Lampe de photothérapie', 'Réanimation', 850000, 'Philips Medical Systems', 'UV-2025-039', 1, 'actif'],
        [12, 'Cryothérapie', 'Appareil de cryothérapie', 'Matériel chirurgical', 1200000, 'CryoAlpha', 'CRYO-2025-040', 1, 'actif'],
        
        // Caisse (ID 2)
        [2, 'Ordinateur', 'PC de bureau', 'Informatique médicale', 450000, 'Dell Technologies', 'PC-2025-041', 3, 'actif'],
        [2, 'Imprimante', 'Imprimante multifonction', 'Périphériques', 250000, 'HP Inc.', 'IMP-2025-042', 2, 'actif'],
        [2, 'Scanner codes-barres', 'Lecteur de codes patients', 'Périphériques', 45000, 'Zebra', 'SCAN-2025-043', 2, 'actif'],
        [2, 'Terminal de paiement', 'TPE bancaire', 'Périphériques', 150000, 'Ingenico', 'TPE-2025-044', 1, 'actif'],
        
        // Accueil/Triage (ID 1)
        [1, 'Bureau accueil', 'Bureau de réception', 'Mobilier de bureau', 250000, 'Steelcase', 'BUR-2025-045', 1, 'actif'],
        [1, 'Chaise', 'Chaise d\'attente', 'Mobilier de bureau', 85000, 'Herman Miller', 'CHAISE-2025-046', 10, 'actif'],
        [1, 'Écran d\'affichage', 'TV pour file d\'attente', 'Informatique médicale', 350000, 'Samsung', 'TV-2025-047', 1, 'actif']
    ];

    $compteur = 0;
    foreach ($materiel_data as $item) {
        list($service_id, $nom, $description, $categorie_nom, $prix, $fournisseur, $serie, $quantite, $statut) = $item;
        
        // Trouver l'ID de la catégorie
        $categorie_id = array_search($categorie_nom, $categories);
        if ($categorie_id === false) {
            $categorie_id = 1; // Par défaut
        }
        
        // Trouver une salle pour ce service
        $salle_id = null;
        foreach ($salles as $salle) {
            if ($salle['service_id'] == $service_id) {
                $salle_id = $salle['id'];
                break;
            }
        }
        
        // Générer un code matériel unique
        $code_materiel = 'MAT-' . date('Y') . '-' . str_pad($compteur + 1, 4, '0', STR_PAD_LEFT);
        
        // Insérer le matériel
        $stmt = $conn->prepare("INSERT INTO materiel 
            (code_materiel, nom, description, categorie_id, service_id, salle_id, 
             date_acquisition, valeur_achat, fournisseur, numero_serie, statut, quantite) 
            VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $code_materiel,
            $nom,
            $description,
            $categorie_id,
            $service_id,
            $salle_id,
            $prix,
            $fournisseur,
            $serie,
            $statut,
            $quantite
        ]);
        
        $compteur++;
    }
    
    echo "✅ $compteur équipements ajoutés\n";
    
    // Ajouter quelques mouvements aléatoires
    $materiel_ids = $conn->query("SELECT id FROM materiel WHERE id <= 20")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($materiel_ids as $mid) {
        if (rand(0, 10) > 7) {
            $service_dest = array_rand($services);
            $type = ['entree', 'transfert', 'maintenance'][rand(0, 2)];
            
            $stmt = $conn->prepare("INSERT INTO mouvements_materiel 
                (materiel_id, type_mouvement, service_destination, quantite, motif, utilisateur_id) 
                VALUES (?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $mid,
                $type,
                $service_dest,
                rand(1, 2),
                "Mouvement de " . ($type == 'entree' ? 'nouvel équipement' : ($type == 'maintenance' ? 'maintenance programmée' : 'transfert de service')),
                2
            ]);
        }
    }
    
    echo "✅ Mouvements aléatoires ajoutés\n";
    
    $conn->commit();
    
    // Afficher les statistiques
    $total = $conn->query("SELECT COUNT(*) FROM materiel")->fetchColumn();
    $valeur = $conn->query("SELECT SUM(valeur_achat * quantite) FROM materiel")->fetchColumn();
    $actif = $conn->query("SELECT COUNT(*) FROM materiel WHERE statut = 'actif'")->fetchColumn();
    $maintenance = $conn->query("SELECT COUNT(*) FROM materiel WHERE statut = 'maintenance'")->fetchColumn();
    
    echo "\n🎉 Base de données peuplée avec succès !\n";
    echo "\n📊 Statistiques finales :\n";
    echo "   Total équipements : $total\n";
    echo "   Équipements actifs : $actif\n";
    echo "   En maintenance : $maintenance\n";
    echo "   Valeur totale : " . number_format($valeur, 0, ',', ' ') . " FCFA\n";
    
} catch (Exception $e) {
    $conn->rollBack();
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
?>
