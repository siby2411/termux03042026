<?php
namespace App\Services;

class CsvParser {

    private string $filepath;

    public function __construct(string $filepath) {
        $this->filepath = $filepath;
    }

    public function parse(): array {

        $rows = [];
        $handle = fopen($this->filepath, "r");

        if (!$handle) {
            throw new \Exception("Impossible d'ouvrir le fichier.");
        }

        // sauter l'en-tête
        fgetcsv($handle, 0, ';');

        while (($data = fgetcsv($handle, 0, ';')) !== false) {

            $rows[] = [
                "date_operation"   => $data[0],
                "compte_debite_id" => intval($data[1]),
                "compte_credite_id"=> intval($data[2]),
                "montant"          => floatval($data[3])
            ];
        }

        fclose($handle);
        return $rows;
    }
}
