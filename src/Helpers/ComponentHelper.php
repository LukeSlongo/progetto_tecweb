<?php
namespace App\Helpers;

class ComponentHelper
{
    /*Genera una lista HTML a partire da un componente e un array di dati*/
    public static function renderList($componentName, $dataList)
    {
        $path = __DIR__ . "/../Views/components/{$componentName}.html";
        
        if (!file_exists($path) || empty($dataList)) {
            return "";
        }

        $template = file_get_contents($path);
        $output = "";

        foreach ($dataList as $row) {
            $item_html = $template;
            
            // Per ogni colonna del database, cerca il placeholder corrispondente
            // Es: $row['username'] -> sostituisce ##USERNAME##
            foreach ($row as $key => $value) {
                $placeholder = "##" . strtoupper($key) . "##";
                $item_html = str_replace($placeholder, htmlspecialchars((string)$value), $item_html);
            }
            
            $output .= $item_html;
        }

        return $output;
    }
}