<?php
namespace App;

class CsvParser {
    private array $data = [];
    public function __construct(string $filename){
        if(!file_exists($filename)) throw new \Exception("Fichier CSV introuvable");
        if(($handle = fopen($filename,"r")) !== false){
            $header = fgetcsv($handle, 1000, ",");
            while(($row = fgetcsv($handle, 1000, ",")) !== false){
                $this->data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
    }
    public function getData(): array { return $this->data; }
}
