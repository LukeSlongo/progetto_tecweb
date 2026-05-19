<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;
use App\Models\SegnalazioneModel;

class SegnalazioneController extends Controller {

    private $Segnalazione;

    public function __construct()
    {
        $this->Segnalazione = new SegnalazioneModel();
        $this->scriptPathList[] = 'segnalazione';
        //BreadcrumbHelper::reset();
    }

    public function nuova_segnalazione() {
        $this->page_title = "Nuova Segnalazione";
        $this->page_description = "Crea una nuova segnalazione di guasto o problema.";
        $this->render('new_report');
    }

}