<?php
namespace App\Core;
use App\Core\Template;
use App\Helpers\ScriptHelper;
use App\Exceptions\NotFoundException;
use App\Exceptions\ForbiddenException;

use Exception;


abstract class Controller
{
    protected $page_title = "";
    protected $page_description = "";

    protected $scriptPathList = [];

    public function render($view, $data = [], $layout = 'main')
    {

        if (isset($_SESSION['flash_error'])) {
            $data['FLASH_ERROR'] = $_SESSION['flash_error'];
            unset($_SESSION['flash_error']);
        }

        if (isset($_SESSION['flash_success'])) {
            $data['FLASH_SUCCESS'] = $_SESSION['flash_success'];
            unset($_SESSION['flash_success']);
        }

        $view_file = new Template("pages/{$view}");
        $view_file->setPageData($data);
        $content_view = $view_file->getPage();

        $layout_file = new Template("layouts/{$layout}");

        if ($layout === 'main') {

            $current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

            $nav_home = ($current_uri === '/' || $current_uri === '/home')
                ? '<span class="active-page" aria-current="page">Home</span>'
                : '<a href="/">Home</a>';

            $nav_issue = ($current_uri === '/issues/new')
                ? '<span class="active-page" aria-current="page">Nuova issue</span>'
                : '<a href="/issues/new">Nuova issue</a>';



            $layout_data = [
                'CLASSE_PAGINA' => strtolower($view),
                'LINK_UTENTE' => Auth::getHeaderLinks(),
                'LINKS_FOOTER' => Auth::getFooterLinks(),
                'IMPORT_SCRIPTS' => ScriptHelper::import_script($this->scriptPathList),
                'TITOLO_PAGINA' => $this->page_title,
                'DESCRIZIONE_PAGINA' => $this->page_description,
                'UTENTE_LOGGATO' => (Auth::isLogged()) ? "true" : "false",
                'NAV_HOME' => $nav_home,
                'NAV_NUOVA_ISSUE' => $nav_issue,
            ];
            $layout_file->setPageData($layout_data);
        } else {
            $layout_file->setPageData(['TITOLO_PAGINA' => $this->page_title]);
        }

        $layout_file->setPageData(['CONTENUTO_PRINCIPALE' => $content_view]);
        echo $layout_file->getPage();
    }

    public function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }

    protected function post($key, $default = null)
    {
        if (isset($_POST[$key])) {
            return trim($_POST[$key]);
        }
        return $default;
    }

    protected function get($key, $default = null)
    {
        if (isset($_GET[$key])) {
            return trim($_GET[$key]);
        }
        return $default;
    }

    protected function abort($code = 404, $message = "")
    {
        switch ($code) {
            case 404:
                throw new NotFoundException($message);
            case 403:
                throw new ForbiddenException($message);
            case 500:
            default:
                throw new Exception($message, 500);
        }
    }
}