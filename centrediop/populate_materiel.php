<?php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();
    
    // 1. Ajouter des fournisseurs
    $fournisseurs = [
        ['Siemens Healthineers', 'contact@siemens.sn', '+221 33 849 30 00', 'Dakar, Sénégal', 'Imagerie médicale, Équipement de diagnostic'],
        ['GE Healthcare', 'info@gehealthcare.sn', '+221 33 859 40 00', 'Dakar, Sénégal', 'Équipement médical, Imagerie'],
        ['Philips Medical Systems', 'senegal@philips.com', '+221 33 869 50 00', 'Dakar, Sénégal', 'Équipement hospitalier'],
        ['Dell Technologies', 'ventes@dell.sn', '+221 33 879 60 00', 'Dakar, Sénégal', 'Informatique, serveurs'],
        ['HP Inc.', 'info@hp.sn', '+221 33 889 70 00', 'Dakar, Sénégal', 'Ordinateurs, imprimantes'],
        ['Fresenius Medical Care', 'info@fresenius.sn', '+221 33 899 80 00', 'Dakar, Sénégal', 'Dialyse, équipement médical'],
        ['Becton Dickinson', 'bd@bd.sn', '+221 33 909 90 00', 'Dakar, Sénégal', 'Matériel de laboratoire'],
        ['Roche Diagnostics', 'roche@roche.sn', '+221 33 919 00 11', 'Dakar, Sénégal', 'Diagnostic, réactifs'],
        ['Medtronic', 'medtronic@medtronic.sn', '+221 33 929 11 22', 'Dakar, Sénégal', 'Équipement chirurgical'],
        ['Stryker', 'stryker@stryker.sn', '+221 33 939 22 33', 'Dakar, Sénégal', 'Matériel orthopédique'],
        ['Canon Medical', 'canon@canon.sn', '+221 33 949 33 44', 'Dakar, Sénégal', 'Imagerie médicale'],
        ['Microsoft Senegal', 'ms@microsoft.sn', '+221 33 959 44 55', 'Dakar, Sénégal', 'Logiciels, licences']
    ];

    $fournisseur_ids = [];
    foreach ($fournisseurs as $f) {
        $stmt = $conn->prepare("INSERT INTO fournisseurs (nom, email, telephone, adresse, specialite) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute($f);
        $fournisseur_ids[] = $conn->lastInsertId();
    }
    echo "✅ Fournisseurs ajoutés\n";

    // 2. Récupérer tous les services
    $services = $conn->query("SELECT id, name FROM services")->fetchAll();
    $salles = $conn->query("SELECT id, service_id, batiment_id, etage, numero_salle FROM salles")->fetchAll();
    
    // 3. Catégories de matériel
    $categories = [
        ['Imagerie médicale', 'Équipements d\'imagerie (radiologie, échographie, IRM, scanner)'],
        ['Équipement de laboratoire', 'Analyseurs, microscopes, centrifugeuses'],
        ['Matériel chirurgical', 'Instruments pour blocs opératoires'],
        ['Mobilier médical', 'Lits, fauteuils, armoires, tables d\'examen'],
        ['Monitoring', 'Moniteurs de signes vitaux, ECG'],
        ['Réanimation', 'Respirateurs, défibrillateurs, pompes'],
        ['Informatique médicale', 'Ordinateurs, serveurs, écrans'],
        ['Périphériques', 'Imprimantes, scanners, lecteurs codes-barres'],
        ['Réseau', 'Switchs, routeurs, câblage'],
        ['Consommables', 'Gants, seringues, compresses'],
        ['Matériel de diagnostic', 'Stéthoscopes, tensiomètres, otoscopes'],
        ['Mobilier de bureau', 'Bureaux, chaises, armoires de rangement']
    ];

    foreach ($categories as $cat) {
        $stmt = $conn->prepare("INSERT INTO categories_materiel (nom, description) VALUES (?, ?)");
        $stmt->execute($cat);
    }
    echo "✅ Catégories ajoutées\n";

    // 4. Générer du matériel pour chaque service
    $materiel_data = [
        // Cardiologie
        ['ECG 12 dérivations', 'Électrocardiographe numérique', 1, 3500000, 'Siemens Healthineers', 'ECG-2025-001'],
        ['Holter ECG', 'Enregistreur ECG 24h', 1, 2800000, 'GE Healthcare', 'HOL-2025-002'],
        ['Échocardiographe', 'Échographe cardiaque portable', 1, 8500000, 'Philips Medical Systems', 'ECHO-2025-003'],
        ['Tensiomètre automatique', 'Tensiomètre brassard', 2, 450000, 'Fresenius', 'TENS-2025-004'],
        ['Défibrillateur', 'Défibrillateur externe automatique', 1, 4200000, 'Medtronic', 'DEF-2025-005'],
        
        // Radiologie
        ['Scanner 64 barrettes', 'Scanner multi-coupes', 1, 45000000, 'Siemens Healthineers', 'SCAN-2025-006'],
        ['IRM 1.5 Tesla', 'Imageur par résonance magnétique', 1, 85000000, 'GE Healthcare', 'IRM-2025-007'],
        ['Radiologie numérique', 'Système de radiographie numérique', 2, 25000000, 'Canon Medical', 'RAD-2025-008'],
        ['Échographe Doppler', 'Échographe avec doppler couleur', 2, 12000000, 'Philips', 'ECHO-2025-009'],
        ['Mammographe', 'Mammographe numérique', 1, 32000000, 'Siemens', 'MAM-2025-010'],
        
        // Laboratoire
        ['Analyseur biochimie', 'Analyseur automatique', 2, 18000000, 'Roche Diagnostics', 'BIO-2025-011'],
        ['Hématologie automate', 'Analyseur de formule sanguine', 2, 15000000, 'Beckman Coulter', 'HEMA-2025-012'],
        ['Microscope binoculaire', 'Microscope de laboratoire', 5, 850000, 'Olympus', 'MIC-2025-013'],
        ['Centrifugeuse', 'Centrifugeuse de laboratoire', 3, 1200000, 'Thermo Fisher', 'CENT-2025-014'],
        ['Réfrigérateur médical', 'Réfrigérateur pour échantillons', 3, 2200000, 'Liebherr', 'FRIG-2025-015'],
        
        // Bloc opératoire
        ['Table d\'opération', 'Table opératoire électrique', 2, 8500000, 'Maquet', 'TABLE-2025-016'],
        ['Scialytique', 'Lampe scialytique LED', 2, 4500000, 'Dräger', 'LAMP-2025-017'],
        ['Respirateur', 'Ventilateur de réanimation', 3, 12500000, 'Hamilton Medical', 'VENT-2025-018'],
        ['Monitoring multiparamétrique', 'Moniteur de signes vitaux', 5, 3500000, 'Philips', 'MON-2025-019'],
        ['Pompe à perfusion', 'Pompe volumétrique', 8, 1200000, 'Fresenius', 'POMP-2025-020'],
        
        // Informatique
        ['Serveur HP DL380', 'Serveur de données médicales', 2, 4500000, 'HP Inc.', 'SRV-2025-021'],
        ['Station de travail', 'PC médical', 15, 850000, 'Dell Technologies', 'PC-2025-022'],
        ['Écran médical', 'Écran 24" diagnostic', 20, 350000, 'Eizo', 'ECR-2025-023'],
        ['Imprimante laser', 'Imprimante réseau', 10, 250000, 'HP Inc.', 'IMP-2025-024'],
        ['Switch Cisco', 'Switch réseau 48 ports', 5, 450000, 'Cisco', 'SW-2025-025'],
        ['Routeur', 'Routeur professionnel', 3, 350000, 'MikroTik', 'ROUT-2025-026'],
        ['NAS Synology', 'Serveur de stockage', 2, 850000, 'Synology', 'NAS-2025-027'],
        ['Lecteur codes-barres', 'Scanner de codes patients', 25, 45000, 'Zebra', 'SCAN-2025-028'],
        
        // Mobilier
        ['Bureau médical', 'Bureau avec rangements', 30, 250000, 'Steelcase', 'BUR-2025-029'],
        ['Chaise de bureau', 'Chaise ergonomique', 50, 85000, 'Herman Miller', 'CHAISE-2025-030'],
        ['Lit médicalisé', 'Lit électrique', 40, 950000, 'Hill-Rom', 'LIT-2025-031'],
        ['Table d\'examen', 'Table d\'auscultation', 25, 450000, 'Stryker', 'TAB-2025-032'],
        ['Armoire médicale', 'Armoire de rangement', 30, 180000, 'Lista', 'ARM-2025-033'],
        ['Paravent', 'Paravent 3 vantaux', 20, 95000, 'Probed', 'PARA-2025-034'],
        
        // Urgences
        ['Brancard', 'Brancard de transport', 8, 350000, 'Stryker', 'BRAN-2025-035'],
        ['Fauteuil roulant', 'Fauteuil roulant standard', 15, 125000, 'Invacare', 'FAUT-2025-036'],
        ['Chariot d\'urgence', 'Chariot de soins d\'urgence', 5, 450000, 'Probed', 'CHARIOT-2025-037'],
        ['Aspirateur médical', 'Aspirateur de mucosités', 6, 350000, 'Dräger', 'ASP-2025-038'],
        ['Bouteille oxygène', 'Bouteille d\'oxygène 5L', 20, 85000, 'Air Liquide', 'O2-2025-039'],
        
        // Pédiatrie
        ['Balance bébé', 'Balance électronique pour nourrissons', 4, 125000, 'Seca', 'BAL-2025-040'],
        ['Incubateur', 'Incubateur néonatal', 3, 8500000, 'Dräger', 'INC-2025-041'],
        ['Photothérapie', 'Lampe de photothérapie', 2, 1200000, 'Medela', 'PHOTO-2025-042'],
        ['Matériel de réanimation pédiatrique', 'Kit pédiatrique', 5, 350000, 'Laerdal', 'KIT-2025-043'],
        
        // Gynécologie
        ['Table gynécologique', 'Table d\'examen gynécologique', 4, 950000, 'Stryker', 'TABG-2025-044'],
        ['Échographe obstétrical', 'Échographe avec doppler fœtal', 3, 12000000, 'GE Healthcare', 'ECHOOB-2025-045'],
        ['Monitorage fœtal', 'Moniteur fœtal', 4, 3500000, 'Philips', 'FŒT-2025-046'],
        ['Colposcope', 'Colposcope de gynécologie', 2, 4500000, 'Leisegang', 'COLPO-2025-047']
    ];

    // Assigner le matériel aux services
    $service_map = [
        1 => 'Accueil/Triage',
        2 => 'Caisse',
        3 => 'Cardiologie',
        4 => 'Odontologie',
        5 => 'Maternité',
        6 => 'Médecine générale',
        7 => 'Pharmacie',
        8 => 'Radiologie',
        9 => 'Laboratoire',
        10 => 'Ophtalmologie',
        11 => 'ORL',
        12 => 'Gynécologie',
        13 => 'Pédiatrie',
        14 => 'Urgences',
        15 => 'Bloc opératoire'
    ];

    foreach ($materiel_data as $index => $item) {
        list($nom, $description, $quantite, $prix, $fournisseur_nom, $serie) = $item;
        
        // Assigner à un service de façon logique
        $service_id = null;
        if (strpos($nom, 'ECG') !== false || strpos($nom, 'Holter') !== false) $service_id = 3; // Cardiologie
        elseif (strpos($nom, 'Scanner') !== false || strpos($nom, 'IRM') !== false || strpos($nom, 'Radiologie') !== false) $service_id = 8; // Radiologie
        elseif (strpos($nom, 'Analyseur') !== false || strpos($nom, 'Microscope') !== false) $service_id = 9; // Laboratoire
        elseif (strpos($nom, 'Table') !== false && strpos($nom, 'opération') !== false) $service_id = 15; // Bloc
        elseif (strpos($nom, 'Incubateur') !== false || strpos($nom, 'Balance bébé') !== false) $service_id = 13; // Pédiatrie
        elseif (strpos($nom, 'Table gynécologique') !== false || strpos($nom, 'Colposcope') !== false) $service_id = 12; // Gynécologie
        elseif (strpos($nom, 'Brancard') !== false || strpos($nom, 'Fauteuil') !== false) $service_id = 14; // Urgences
        elseif (strpos($nom, 'Serveur') !== false || strpos($nom, 'Station') !== false) $service_id = 2; // Caisse/Admin
        else $service_id = 6; // Médecine générale par défaut
        
        // Trouver une salle pour ce service
        $salle_id = null;
        foreach ($salles as $salle) {
            if ($salle['service_id'] == $service_id) {
                $salle_id = $salle['id'];
                break;
            }
        }
        
        // Déterminer la catégorie
        $categorie_id = 1; // Par défaut
        if (strpos($nom, 'Scanner') !== false || strpos($nom, 'IRM') !== false) $categorie_id = 1; // Imagerie
        elseif (strpos($nom, 'Analyseur') !== false || strpos($nom, 'Microscope') !== false) $categorie_id = 2; // Laboratoire
        elseif (strpos($nom, 'Table') !== false && strpos($nom, 'opération') !== false) $categorie_id = 3; // Chirurgical
        elseif (strpos($nom, 'Lit') !== false || strpos($nom, 'Bureau') !== false) $categorie_id = 4; // Mobilier
        elseif (strpos($nom, 'Monitor') !== false) $categorie_id = 5; // Monitoring
        elseif (strpos($nom, 'Respirateur') !== false) $categorie_id = 6; // Réanimation
        elseif (strpos($nom, 'Serveur') !== false || strpos($nom, 'Station') !== false) $categorie_id = 7; // Informatique
        elseif (strpos($nom, 'Imprimante') !== false) $categorie_id = 8; // Périphériques
        
        // Insérer le matériel
        $code_materiel = 'MAT-' . date('Y') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
        
        $stmt = $conn->prepare("INSERT INTO materiel 
            (code_materiel, nom, description, categorie_id, service_id, salle_id, 
             date_acquisition, valeur_achat, fournisseur, numero_serie, statut, quantite, observations) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $code_materiel,
            $nom,
            $description,
            $categorie_id,
            $service_id,
            $salle_id,
            date('Y-m-d', strtotime('-'.rand(1, 365).' days')),
            $prix,
            $fournisseur_nom,
            $serie,
            rand(0, 10) > 2 ? 'actif' : (rand(0, 10) > 5 ? 'maintenance' : 'hors_service'),
            $quantite,
            'Équipement de qualité hospitalière'
        ]);
    }
    
    echo "✅ " . count($materiel_data) . " équipements ajoutés\n";
    
    // Ajouter quelques mouvements de matériel
    $materiel_ids = $conn->query("SELECT id FROM materiel")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($materiel_ids as $mid) {
        if (rand(0, 10) > 5) {
            $stmt = $conn->prepare("INSERT INTO mouvements_materiel 
                (materiel_id, type_mouvement, service_destination, salle_destination, quantite, motif, utilisateur_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $service_dest = rand(1, 12);
            $salle_dest = null;
            foreach ($salles as $salle) {
                if ($salle['service_id'] == $service_dest) {
                    $salle_dest = $salle['id'];
                    break;
                }
            }
            
            $stmt->execute([
                $mid,
                ['entree', 'transfert', 'maintenance'][rand(0, 2)],
                $service_dest,
                $salle_dest,
                rand(1, 3),
                'Mouvement standard',
                2 // caissier_id
            ]);
        }
    }
    
    echo "✅ Mouvements ajoutés\n";
    
    $conn->commit();
    echo "\n🎉 Base de données peuplée avec succès !\n";
    
} catch (Exception $e) {
    $conn->rollBack();
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
?>
