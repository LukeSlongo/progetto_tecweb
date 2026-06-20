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
        if (empty(self::$items)) {
            return '';
        }

        $html = '<nav class="breadcrumb" aria-label="breadcrumb">';
        $html .= '<ol>';

        $last = array_key_last(self::$items);

        foreach (self::$items as $i => $item) {
            $label = htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8');
            
            $langAttr = ($item['label'] === 'Home') ? ' lang="en"' : '';

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
