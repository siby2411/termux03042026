<?php
/**
 * Fonctions utilitaires pour l'application
 */

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /index.php');
        exit();
    }
}

/**
 * Récupère la file d'attente pour un service
 */
function getQueueForService($service_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT q.*, 
               p.id as patient_id, 
               p.name as patient_name,
               p.birthdate as birth_date,
               p.phone,
               p.address,
               p.gender
        FROM queue q
        JOIN patients p ON q.patient_id = p.id
        WHERE q.service_id = ? AND q.status = 'waiting'
        ORDER BY FIELD(q.priority, 'senior', 'normal'), q.created_at ASC
    ");
    $stmt->execute([$service_id]);
    return $stmt->fetchAll();
}

/**
 * Récupère les consultations d'un médecin
 */
function getDoctorConsultations($doctor_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT c.*, 
               p.name as patient_name,
               p.birthdate as birth_date,
               p.phone,
               p.gender,
               s.name as service_name
        FROM consultations c
        JOIN patients p ON c.patient_id = p.id
        JOIN services s ON c.service_id = s.id
        WHERE c.doctor_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$doctor_id]);
    return $stmt->fetchAll();
}

/**
 * Récupère les patients récents
 */
function getRecentPatients($limit = 10) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT id, name, birthdate, phone, address, gender, created_at
        FROM patients
        ORDER BY created_at DESC
        LIMIT :limit
    ");
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Récupère les statistiques du tableau de bord
 */
function getDashboardStats($service_id = null) {
    global $pdo;
    
    $stats = [];
    
    try {
        if ($service_id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM queue WHERE service_id = ? AND DATE(created_at) = CURDATE()");
            $stmt->execute([$service_id]);
        } else {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM queue WHERE DATE(created_at) = CURDATE()");
        }
        $result = $stmt->fetch();
        $stats['today_patients'] = $result['count'] ?? 0;
    } catch (Exception $e) {
        $stats['today_patients'] = 0;
    }
    
    try {
        if ($service_id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM queue WHERE service_id = ? AND status = 'waiting'");
            $stmt->execute([$service_id]);
        } else {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM queue WHERE status = 'waiting'");
        }
        $result = $stmt->fetch();
        $stats['waiting'] = $result['count'] ?? 0;
    } catch (Exception $e) {
        $stats['waiting'] = 0;
    }
    
    try {
        if ($service_id) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM consultations WHERE service_id = ? AND DATE(created_at) = CURDATE()");
            $stmt->execute([$service_id]);
        } else {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM consultations WHERE DATE(created_at) = CURDATE()");
        }
        $result = $stmt->fetch();
        $stats['today_consultations'] = $result['count'] ?? 0;
    } catch (Exception $e) {
        $stats['today_consultations'] = 0;
    }
    
    return $stats;
}

/**
 * Ajoute un patient à la file d'attente
 */
function addToQueue($patient_id, $service_id, $priority = 'normal') {
    global $pdo;
    
    // Générer un token unique
    $token = 'TKN' . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $pdo->prepare("
        INSERT INTO queue (patient_id, service_id, priority, status, token)
        VALUES (?, ?, ?, 'waiting', ?)
    ");
    return $stmt->execute([$patient_id, $service_id, $priority, $token]);
}

/**
 * Récupère les services
 */
function getServices() {
    global $pdo;
    
    $stmt = $pdo->query("SELECT id, name, description FROM services ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Récupère un utilisateur par son ID
 */
function getUserById($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Récupère le service d'un utilisateur
 */
function getUserService($user_id) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT s.* 
        FROM services s
        JOIN users u ON u.service_id = s.id
        WHERE u.id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Récupère la file d'attente complète
 */
function getAllQueue() {
    global $pdo;
    
    $stmt = $pdo->query("
        SELECT q.*, 
               p.name as patient_name,
               s.name as service_name
        FROM queue q
        JOIN patients p ON q.patient_id = p.id
        JOIN services s ON q.service_id = s.id
        WHERE q.status = 'waiting'
        ORDER BY FIELD(q.priority, 'senior', 'normal'), q.created_at ASC
    ");
    return $stmt->fetchAll();
}
?>
