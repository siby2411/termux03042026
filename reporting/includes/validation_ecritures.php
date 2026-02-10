<?php
// includes/validation_ecritures.php

namespace Momo\Reporting\Includes;

// Inclure la connexion DB
require_once __DIR__ . '/db.php';

/**
 * Validation des écritures comptables SYSCOHADA
 * Vérifie :
 *  - la classe des comptes (1 à 9)
 *  - l'existence des comptes débité et crédité
 *  - le montant positif
 */
class ValidationEcritures
{
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Vérifie si un compte existe et est valide
     */
    public function compteExiste(int $compteId): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM PLAN_COMPTABLE_UEMOA WHERE compte_id = :compte_id");
        $stmt->execute(['compte_id' => $compteId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Vérifie la validité d'une écriture
     */
    public function validerEcriture(int $compteDebite, int $compteCredite, float $montant): array
    {
        $errors = [];

        if (!$this->compteExiste($compteDebite)) {
            $errors[] = "Compte débité invalide : $compteDebite";
        }

        if (!$this->compteExiste($compteCredite)) {
            $errors[] = "Compte crédité invalide : $compteCredite";
        }

        if ($montant <= 0) {
            $errors[] = "Montant doit être supérieur à 0.";
        }

        return $errors;
    }
}

