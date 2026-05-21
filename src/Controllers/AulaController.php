<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;
use App\Models\AulaModel;

class AulaController extends Controller {

    private $Aula;

    public function __construct()
    {
        $this->Aula = new AulaModel();
        //BreadcrumbHelper::reset();
    }

    public function get_aule_by_edificio() {
        $edificio_id = isset($_GET['edificioId']) ? trim($_GET['edificioId']) : null;

        header('Content-Type: application/json');

        if (empty($edificio_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Parametro edificioId mancante o vuoto']);
            exit;
        }

        $aule = $this->Aula->get_aule_by_edificio($edificio_id);
        echo json_encode($aule);
        exit;
    }

}