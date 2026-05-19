<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;

class ReportController extends Controller {
    public function nuova_segnalazione() {
        $this->page_title = "Nuova Segnalazione";
        $this->page_description = "Crea una nuova segnalazione di guasto o problema.";
        $this->render('new_report');
    }
}