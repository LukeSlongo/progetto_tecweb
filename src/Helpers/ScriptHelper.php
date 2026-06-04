<?php
namespace App\Helpers;

class ScriptHelper
{

    static public function import_script($scriptPathList) {
        $output = '';
        foreach ($scriptPathList as $path) {
            $output .= '<script src="/js/' . $path . '.js"></script>
            ';
        }
        return $output;
    }
}