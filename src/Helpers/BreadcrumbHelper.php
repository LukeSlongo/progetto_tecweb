<?php
namespace App\Helpers;

class BreadcrumbHelper
{
    private static array $items = [];

    public static function reset(): void
    {
        self::$items = [];
    }

    public static function add(string $label, string $url = null): void
    {
        self::$items[] = [
            'label' => $label,
            'url'   => $url
        ];
    }

    public static function render(): string
    {
        // Se c'è solo "Home", non stampiamo nulla per non essere ridondanti
        if (empty(self::$items) || count(self::$items) === 1) {
            return '';
        }

        $html = '<nav class="breadcrumb-nav" aria-label="Percorso di navigazione">';
        $html .= '<ol>';

        $last = array_key_last(self::$items);

        foreach (self::$items as $i => $item) {
            $label = htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8');
            
            $english_words = ['Home'];
            $langAttr = in_array($item['label'], $english_words) ? ' lang="en"' : '';

            // Se è l'ultimo elemento o non ha URL, niente link circolare e aggiungiamo aria-current
            if ($i === $last || empty($item['url'])) {
                $html .= '<li aria-current="page"' . $langAttr . '>' . $label . '</li>';
            } else {
                $url = htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8');
                $html .= '<li><a href="' . $url . '"' . $langAttr . '>' . $label . '</a></li>';
            }
        }

        $html .= '</ol>';
        $html .= '</nav>';

        return $html;
    }
}