#!/bin/bash

echo "🔍 VÉRIFICATION DES DONNÉES DANS MARIADB"
echo "========================================"

mariadb -u root -p centrediop << 'SQL'
-- Patients
SELECT '📋 LISTE DES PATIENTS' as '';
SELECT id, code_patient_unique, nom, prenom, telephone FROM patients;

-- Rendez-vous aujourd'hui
SELECT '📅 RENDEZ-VOUS AUJOURD\'HUI' as '';
SELECT r.id, p.nom, p.prenom, r.date_rdv, r.heure_rdv, r.statut 
FROM rendez_vous r
JOIN patients p ON r.patient_id = p.id
WHERE r.date_rdv = CURDATE();

-- File d'attente
SELECT '🔄 FILE D\'ATTENTE' as '';
SELECT f.id, p.nom, p.prenom, f.token, f.priorite 
FROM file_attente f
JOIN patients p ON f.patient_id = p.id
WHERE f.statut = 'en_attente';

-- Vérification des tokens
SELECT '🔑 INFORMATIONS POUR TOKEN' as '';
SELECT 
    p.id,
    p.code_patient_unique as code,
    p.nom,
    p.prenom,
    p.telephone
FROM patients p
ORDER BY p.id;
SQL
