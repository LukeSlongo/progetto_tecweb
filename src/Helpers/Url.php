<?php
namespace App\Helpers;

// Helper per costruire URL coerenti con il prefisso su cui è montata l'app (BASE_PATH).
// La guardia su defined() serve perché nei test (bootstrap PHPUnit) config.php non è caricato.
class Url
{
    public static function base()
    {
        return defined('BASE_PATH') ? BASE_PATH : '';
    }

    public static function to($path = '')
    {
        return self::base() . $path;
    }

    // Prepende BASE_PATH agli URL "root-relative" (che iniziano con un singolo /) negli
    // attributi href/src/action dell'HTML generato. Lascia intatti gli URL assoluti
    // (http://, //), le ancore (#...) e i path relativi. In questo modo nel codice si
    // scrivono link normali (es. /login) e il prefisso viene aggiunto in automatico.
    public static function rewriteHtml($html)
    {
        $base = self::base();
        if ($base === '' || $html === null || $html === '') {
            return $html;
        }
        return preg_replace(
            '/(\b(?:href|src|action)\s*=\s*)(["\'])\/(?!\/)/i',
            '$1$2' . $base . '/',
            $html
        );
    }
}
