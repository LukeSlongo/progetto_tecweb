<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;
use App\Models\BuildingModel;

class BuildingController extends Controller {

    private $Building;

    public function __construct()
    {
        $this->Building = new BuildingModel();
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

        $edifici = $this->Building->get_edifici_by_dipartimento($dipartimento_id);
        echo json_encode($edifici);
        exit;
    }

}