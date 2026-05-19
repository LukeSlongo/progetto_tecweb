<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;
use App\Models\DipartimentoModel;

class DipartimentoController extends Controller {

    private $Dipartimento;

    public function __construct()
    {
        $this->Dipartimento = new DipartimentoModel();
        //BreadcrumbHelper::reset();
    }

    public function get_all_dipartimenti() {
        $dipartimenti = $this->Dipartimento->get_all_dipartimenti();
        header('Content-Type: application/json');
        echo json_encode($dipartimenti);
        exit;
    }

}