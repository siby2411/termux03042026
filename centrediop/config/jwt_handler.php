<?php
/**
 * Gestionnaire JWT pour l'ajout des informations patient
 * À intégrer sans casser le code existant
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTHandler {
    private $secret_key;
    private $algorithm;
    
    public function __construct() {
        $this->secret_key = "votre_cle_secrete_tres_longue_et_secure_2024";
        $this->algorithm = "HS256";
    }
    
    /**
     * Génère un token avec les informations patient
     * Compatible avec l'ancien système
     */
    public function generateToken($user, $patientInfo = null) {
        $issued_at = time();
        $expiration = $issued_at + (24 * 60 * 60); // 24h
        
        // Structure de base du token (compatible avec l'existant)
        $payload = [
            'iat' => $issued_at,
            'exp' => $expiration,
            'user_id' => $user['id'],
            'username' => $user['username'] ?? $user['email'] ?? '',
            'role' => $user['role'] ?? 'user',
            'nom' => $user['nom'] ?? '',
            'prenom' => $user['prenom'] ?? ''
        ];
        
        // AJOUT: Informations patient pour le dashboard médecin
        if ($patientInfo) {
            $payload['patient'] = [
                'id' => $patientInfo['id'] ?? null,
                'code_patient' => $patientInfo['code_patient_unique'] ?? $patientInfo['numero_patient'] ?? '',
                'nom' => $patientInfo['nom'] ?? '',
                'prenom' => $patientInfo['prenom'] ?? '',
                'telephone' => $patientInfo['telephone'] ?? '',
                'date_naissance' => $patientInfo['date_naissance'] ?? '',
                'groupe_sanguin' => $patientInfo['groupe_sanguin'] ?? '',
                'allergie' => $patientInfo['allergie'] ?? '',
                'antecedents' => $patientInfo['antecedent_medicaux'] ?? ''
            ];
        }
        
        // AJOUT: Informations du dernier rendez-vous si disponible
        if (isset($patientInfo['dernier_rdv'])) {
            $payload['patient']['dernier_rdv'] = $patientInfo['dernier_rdv'];
        }
        
        return JWT::encode($payload, $this->secret_key, $this->algorithm);
    }
    
    /**
     * Vérifie et décode le token
     */
    public function verifyToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secret_key, $this->algorithm));
            return (array) $decoded;
        } catch (Exception $e) {
            error_log("Erreur JWT: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les informations patient du token
     */
    public function getPatientFromToken($token) {
        $decoded = $this->verifyToken($token);
        if ($decoded && isset($decoded['patient'])) {
            return $decoded['patient'];
        }
        return null;
    }
    
    /**
     * Ajoute les informations patient à un token existant
     */
    public function addPatientToToken($existingToken, $patientId, $db) {
        try {
            // Récupérer les infos patient
            $stmt = $db->prepare("
                SELECT p.*, 
                       (SELECT date_rdv FROM rendez_vous 
                        WHERE patient_id = p.id 
                        ORDER BY date_rdv DESC, heure_rdv DESC 
                        LIMIT 1) as dernier_rdv
                FROM patients p 
                WHERE p.id = ?
            ");
            $stmt->execute([$patientId]);
            $patient = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$patient) {
                return $existingToken;
            }
            
            // Décoder le token existant
            $decoded = $this->verifyToken($existingToken);
            if (!$decoded) {
                return $existingToken;
            }
            
            // Ajouter les infos patient
            $decoded['patient'] = [
                'id' => $patient['id'],
                'code_patient' => $patient['code_patient_unique'],
                'numero_patient' => $patient['numero_patient'],
                'nom' => $patient['nom'],
                'prenom' => $patient['prenom'],
                'telephone' => $patient['telephone'],
                'date_naissance' => $patient['date_naissance'],
                'groupe_sanguin' => $patient['groupe_sanguin'],
                'allergie' => $patient['allergie']
            ];
            
            // Regénérer le token
            return JWT::encode($decoded, $this->secret_key, $this->algorithm);
            
        } catch (Exception $e) {
            error_log("Erreur ajout patient au token: " . $e->getMessage());
            return $existingToken;
        }
    }
}
