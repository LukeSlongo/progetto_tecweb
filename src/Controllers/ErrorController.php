<?php
namespace App\Controllers;
use App\Core\Controller;

Class ErrorController extends Controller {

    public function index($code, $view, $err_message = ""){
        http_response_code($code);
        $this->page_title = "Errore $code";
        $this->page_description = "Si è verificato un errore nel sistema.";
        $this->render("errors/" . $view , ['ERROR_MESSAGE' => $err_message]);
    }
}