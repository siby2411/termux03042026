<?php
// includes/codes_manager.php

/**
 * Génère un code unique pour un parent
 * @param string $telephone Numéro de téléphone
 * @return string Code unique
 */
function generateParentCode($telephone) {
    $phone_clean = preg_replace('/[^0-9]/', '', $telephone);
    $phone_clean = substr($phone_clean, -9);
    $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    return 'P' . $phone_clean . $random;
}

/**
 * Génère un code unique pour un élève
 * @param int $id_parent ID du parent
 * @param PDO $db Connexion BDD
 * @return string Code unique
 */
function generateEleveCode($id_parent, $db) {
    $stmt = $db->prepare("SELECT telephone FROM parents WHERE id_parent = ?");
    $stmt->execute([$id_parent]);
    $telephone = $stmt->fetchColumn();
    
    $phone_clean = preg_replace('/[^0-9]/', '', $telephone);
    $phone_clean = substr($phone_clean, -9);
    $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    return 'E' . $phone_clean . $random;
}

/**
 * Recherche un parent par son code
 * @param string $code Code parent
 * @param PDO $db Connexion BDD
 * @return array|false
 */
function findParentByCode($code, $db) {
    $stmt = $db->prepare("SELECT * FROM parents WHERE code_parent = ?");
    $stmt->execute([$code]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Recherche un élève par son code
 * @param string $code Code élève
 * @param PDO $db Connexion BDD
 * @return array|false
 */
function findEleveByCode($code, $db) {
    $stmt = $db->prepare("SELECT e.*, p.nom, p.prenom, p.telephone, p.code_parent 
                          FROM eleves e 
                          JOIN parents p ON e.id_parent = p.id_parent 
                          WHERE e.code_eleve = ?");
    $stmt->execute([$code]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Valide un code (vérifie le format)
 */
function validateCode($code) {
    if(preg_match('/^P[0-9]{9}[0-9]{4}$/', $code)) {
        return 'parent';
    } elseif(preg_match('/^E[0-9]{9}[0-9]{4}$/', $code)) {
        return 'eleve';
    }
    return false;
}
?>
