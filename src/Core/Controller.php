<?php
namespace App\Core;
use App\Core\Template;
use App\Helpers\ScriptHelper;


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

        $view_file = new Template("pages/{$view}");

        $view_file->set_dati_pagina($data);

        $contenuto_vista = $view_file->get_pagina();

        if ($layout) {
            $layout_data = [
                'CLASSE_PAGINA' => strtolower($view),
                'LINK_UTENTE' => Auth::getHeaderLinks(),
                'LINKS_FOOTER' => Auth::getFooterLinks(),
                'IMPORT_SCRIPTS' => ScriptHelper::import_script($this->scriptPathList),
                'TITOLO_PAGINA' => $this->page_title,
                'DESCRIZIONE_PAGINA' => $this->page_description,
                //'BREADCRUMB' => BreadcrumbHelper::render(),
                'UTENTE_LOGGATO' => (Auth::isUser()) ? "true" : "false"
            ];

            $layout_file = new Template("layouts/{$layout}");
            $layout_file->set_dati_pagina(['CONTENUTO_PRINCIPALE' => $contenuto_vista]);
            $layout_file->set_dati_pagina($layout_data);
            echo $layout_file->get_pagina();

        } else {
            echo $contenuto_vista;
        }

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

    protected function require_login()
    {
        if (!Auth::isLogged()) {
            $_SESSION['flash_error'] = "Non sei loggato. Accedi per visualizzare il profilo!";
            $this->redirect('/login');
            exit;
        }
    }

    protected function require_guest()
    {
        if (Auth::isLogged()) {
            $_SESSION['flash_error'] = "Disconettiti dal tuo account per continuare";
            $this->redirect('/');
            exit;
        }
    }

    protected function require_owner($username)
    {
        $this->require_login();
        if (!Auth::isOwner($username)) {
            $this->abort(403, "Accesso negato! Non hai il permesso di visitare questa pagina");
            exit;
        }
    }



    protected function check_admin()
    {
        $this->require_login();

        if (!Auth::isAdmin()) {
            $_SESSION['flash_error'] = "Non hai il permesso, esegui l'accesso come amministratore!";
            $this->redirect('/login');
            exit;
        }
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
                throw new \Exception($message, 500);
        }
    }
}