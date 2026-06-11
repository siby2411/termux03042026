<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "🔧 CRÉATION DE PATIENTS DE TEST\n";
echo "===============================\n\n";

// Fonction pour générer un code patient unique
function generatePatientCode() {
    return 'PAT-' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Vérifier si la table traitements existe, sinon la créer
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS traitements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            medicament VARCHAR(255) NOT NULL,
            posologie TEXT,
            duree VARCHAR(100),
            date_prescription DATE,
            medecin_prescripteur VARCHAR(100),
            instructions TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "✅ Table 'traitements' vérifiée/créée\n\n";
} catch (Exception $e) {
    echo "⚠️  Note: " . $e->getMessage() . "\n";
}

// Récupérer le médecin
$stmt = $db->query("SELECT id, nom, prenom FROM users WHERE role = 'medecin' LIMIT 1");
$medecin = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$medecin) {
    // Si pas de médecin, créer un médecin par défaut
    echo "⚠️  Aucun médecin trouvé, création d'un médecin par défaut...\n";
    $insert = $db->prepare("
        INSERT INTO users (nom, prenom, username, password, role, service_id, created_at)
        VALUES ('Fall', 'Aminata', 'dr.fall', ?, 'medecin', 1, NOW())
    ");
    $hashed = password_hash('password123', PASSWORD_DEFAULT);
    $insert->execute([$hashed]);
    
    $medecin_id = $db->lastInsertId();
    $medecin = ['id' => $medecin_id, 'nom' => 'Fall', 'prenom' => 'Aminata'];
    echo "✅ Médecin créé: Dr. Aminata Fall\n\n";
} else {
    $medecin_id = $medecin['id'];
    echo "✅ Médecin trouvé: Dr. " . $medecin['prenom'] . " " . $medecin['nom'] . " (ID: $medecin_id)\n\n";
}

// Patient 1: Siby Momo
$patient1_code = generatePatientCode();
$patient1 = [
    'code_patient_unique' => $patient1_code,
    'numero_patient' => $patient1_code,
    'nom' => 'Siby',
    'prenom' => 'Momo',
    'date_naissance' => '1985-06-15',
    'lieu_naissance' => 'Dakar',
    'sexe' => 'M',
    'groupe_sanguin' => 'O+',
    'allergie' => 'Aucune',
    'antecedent_medicaux' => 'Hypertension',
    'telephone' => '77 654 28 03',
    'adresse' => 'Dakar, Sénégal',
    'email' => 'momo.siby@email.com',
    'created_by' => $medecin_id
];

// Patient 2: Fall Aminata
$patient2_code = generatePatientCode();
$patient2 = [
    'code_patient_unique' => $patient2_code,
    'numero_patient' => $patient2_code,
    'nom' => 'Fall',
    'prenom' => 'Aminata',
    'date_naissance' => '1990-03-22',
    'lieu_naissance' => 'Thiès',
    'sexe' => 'F',
    'groupe_sanguin' => 'A+',
    'allergie' => 'Pénicilline',
    'antecedent_medicaux' => 'Aucun',
    'telephone' => '78 123 45 67',
    'adresse' => 'Thiès, Sénégal',
    'email' => 'aminata.fall@email.com',
    'created_by' => $medecin_id
];

// Insérer Patient 1
$sql = "INSERT INTO patients (
    code_patient_unique, numero_patient, nom, prenom, date_naissance, 
    lieu_naissance, sexe, groupe_sanguin, allergie, antecedent_medicaux,
    telephone, adresse, email, created_by, created_at
) VALUES (
    :code_patient_unique, :numero_patient, :nom, :prenom, :date_naissance,
    :lieu_naissance, :sexe, :groupe_sanguin, :allergie, :antecedent_medicaux,
    :telephone, :adresse, :email, :created_by, NOW()
)";

$stmt = $db->prepare($sql);

try {
    $stmt->execute($patient1);
    $patient1_id = $db->lastInsertId();
    echo "✅ Patient 1 créé: Siby Momo (Code: $patient1_code, ID: $patient1_id)\n";
} catch (Exception $e) {
    echo "❌ Erreur Patient 1: " . $e->getMessage() . "\n";
    $patient1_id = null;
}

// Insérer Patient 2
try {
    $stmt->execute($patient2);
    $patient2_id = $db->lastInsertId();
    echo "✅ Patient 2 créé: Fall Aminata (Code: $patient2_code, ID: $patient2_id)\n\n";
} catch (Exception $e) {
    echo "❌ Erreur Patient 2: " . $e->getMessage() . "\n\n";
    $patient2_id = null;
}

// Créer des rendez-vous pour aujourd'hui
if ($patient1_id || $patient2_id) {
    echo "📅 CRÉATION DE RENDEZ-VOUS POUR AUJOURD'HUI\n";
    echo "===========================================\n\n";
    
    $today = date('Y-m-d');
    $times = ['09:00:00', '10:30:00'];
    
    // Vérifier si la table rendez_vous existe
    try {
        $db->query("SELECT 1 FROM rendez_vous LIMIT 1");
        
        $rdv_sql = "INSERT INTO rendez_vous (
            patient_id, service_id, medecin_id, date_rdv, heure_rdv, motif, statut, cree_le
        ) VALUES (
            :patient_id, :service_id, :medecin_id, :date_rdv, :heure_rdv, :motif, :statut, NOW()
        )";
        
        $rdv_stmt = $db->prepare($rdv_sql);
        
        if ($patient1_id) {
            try {
                $rdv_stmt->execute([
                    'patient_id' => $patient1_id,
                    'service_id' => 1,
                    'medecin_id' => $medecin_id,
                    'date_rdv' => $today,
                    'heure_rdv' => $times[0],
                    'motif' => 'Consultation de suivi',
                    'statut' => 'confirme'
                ]);
                echo "✅ Rendez-vous pour Siby Momo à 09:00 créé\n";
            } catch (Exception $e) {
                echo "❌ Erreur RDV Patient 1: " . $e->getMessage() . "\n";
            }
        }
        
        if ($patient2_id) {
            try {
                $rdv_stmt->execute([
                    'patient_id' => $patient2_id,
                    'service_id' => 1,
                    'medecin_id' => $medecin_id,
                    'date_rdv' => $today,
                    'heure_rdv' => $times[1],
                    'motif' => 'Première consultation',
                    'statut' => 'programme'
                ]);
                echo "✅ Rendez-vous pour Fall Aminata à 10:30 créé\n\n";
            } catch (Exception $e) {
                echo "❌ Erreur RDV Patient 2: " . $e->getMessage() . "\n\n";
            }
        }
    } catch (Exception $e) {
        echo "⚠️  Table rendez_vous non trouvée, ignore les RDV\n\n";
    }
    
    // Ajouter à la file d'attente
    echo "🔄 AJOUT À LA FILE D'ATTENTE\n";
    echo "===========================\n\n";
    
    function generateToken($patient_id) {
        return 'TK' . date('Ymd') . str_pad($patient_id, 4, '0', STR_PAD_LEFT);
    }
    
    // Essayer d'abord avec file_attente
    try {
        $db->query("SELECT 1 FROM file_attente LIMIT 1");
        
        $queue_sql = "INSERT INTO file_attente (
            patient_id, service_id, token, statut, priorite, cree_a
        ) VALUES (
            :patient_id, :service_id, :token, 'en_attente', 'normal', NOW()
        )";
        
        $queue_stmt = $db->prepare($queue_sql);
        
        if ($patient1_id) {
            try {
                $queue_stmt->execute([
                    'patient_id' => $patient1_id,
                    'service_id' => 1,
                    'token' => generateToken($patient1_id)
                ]);
                echo "✅ Patient 1 ajouté à file_attente\n";
            } catch (Exception $e) {
                echo "❌ Erreur file_attente Patient 1: " . $e->getMessage() . "\n";
            }
        }
        
        if ($patient2_id) {
            try {
                $queue_stmt->execute([
                    'patient_id' => $patient2_id,
                    'service_id' => 1,
                    'token' => generateToken($patient2_id)
                ]);
                echo "✅ Patient 2 ajouté à file_attente\n\n";
            } catch (Exception $e) {
                echo "❌ Erreur file_attente Patient 2: " . $e->getMessage() . "\n\n";
            }
        }
    } catch (Exception $e) {
        echo "⚠️  Table file_attente non trouvée\n\n";
    }
}

// Vérification finale
echo "📊 VÉRIFICATION FINALE\n";
echo "=====================\n\n";

try {
    $total_patients = $db->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    echo "Total patients: " . $total_patients . "\n";
    
    try {
        $rdv_ajd = $db->query("SELECT COUNT(*) FROM rendez_vous WHERE date_rdv = CURDATE()")->fetchColumn();
        echo "Rendez-vous aujourd'hui: " . $rdv_ajd . "\n";
    } catch (Exception $e) {
        echo "Rendez-vous aujourd'hui: Table non disponible\n";
    }
    
    try {
        $file_attente = $db->query("SELECT COUNT(*) FROM file_attente WHERE statut = 'en_attente'")->fetchColumn();
        echo "File d'attente: " . $file_attente . "\n";
    } catch (Exception $e) {
        echo "File d'attente: Table non disponible\n";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur vérification: " . $e->getMessage() . "\n";
}

echo "\n✅ Initialisation terminée !\n";
echo "Connectez-vous en tant que médecin pour voir les patients.\n";
?>
