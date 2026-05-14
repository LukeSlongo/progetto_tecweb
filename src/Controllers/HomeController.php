<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;

class HomeController extends Controller {
    public function visualizza_home() {
        $this->page_title = "HomePage";
        $this->page_description = "Benvenuto nel sito di segnalazione di guasti o problemi.";
        $this->render('home');
    }
}
?>