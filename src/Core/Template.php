<?php
namespace App\Core;
// Questa classe si limita seplicemente a sostituire i placeholder dell' html con i dati presi dal Controller

class Template
{
    private $content;

    public function __construct($page)
    {
        $file_path = __DIR__ . "/../Views/{$page}.html";
        if (file_exists($file_path)) {
            $this->content = file_get_contents($file_path);
        } else {
            throw new \Exception("File template non trovato: $file_path");
        }
    }

    public function setPageData($data_array)
    {
        if (!is_array($data_array)) {
            return;
        }

        foreach ($data_array as $key => $value) {
            $this->content = str_replace("##$key##", $value ?? '', $this->content);
        }
    }

    public function getPage($keep_placeholder = false)
    {
        if (!$keep_placeholder) {
            return preg_replace('/##.*?##/', '', $this->content);
        } else {
            return $this->content;
        }
    }
}