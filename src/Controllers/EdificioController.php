<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;
use App\Models\EdificioModel;

class EdificioController extends Controller {

    private $Edificio;

    public function __construct()
    {
        $this->Edificio = new EdificioModel();
        //BreadcrumbHelper::reset();
    }

    public function get_edifici_by_dipartimento() {
        $dipartimento_id = isset($_GET['dipartimentoId']) ? trim($_GET['dipartimentoId']) : null;

        header('Content-Type: application/json');

        if (empty($dipartimento_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Parametro dipartimentoId mancante o vuoto']);
            exit;
        }

        $edifici = $this->Edificio->get_edifici_by_dipartimento($dipartimento_id);
        echo json_encode($edifici);
        exit;
    }

}