<?php
namespace App\Core;
// Questa classe si limita seplicemente a sostituire i placeholder dell' html con i dati presi dal Controller

class Template {
    private $contenuto;

    public function __construct($page) {
        $filePath = __DIR__ . "/../Views/{$page}.html";
        if (file_exists($filePath)) {
            $this->contenuto = file_get_contents($filePath);
        } else {
            throw new \Exception("File template non trovato: $filePath");
        }
    }

    public function set_dati_pagina($array_assoc_dati) {
        if (!is_array($array_assoc_dati)) {
            return;
        }

        foreach ($array_assoc_dati as $chiave => $valore) {
            $this->contenuto = str_replace("##$chiave##", $valore ?? '', $this->contenuto);
        }
    }

    public function get_pagina($keep_placeholder = false) {

        if(!$keep_placeholder){
            return preg_replace('/##.*?##/', '', $this->contenuto);
        }else{
            return $this->contenuto;
        }
    }
}