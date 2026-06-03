<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;
use App\Models\RoomModel;

class RoomController extends Controller {

    private $Room;

    public function __construct()
    {
        $this->Room = new RoomModel();
        //BreadcrumbHelper::reset();
    }

    public function get_aule_by_building() {
        $building_id = isset($_GET['buildingId']) ? trim($_GET['buildingId']) : null;

        header('Content-Type: application/json');

        if (empty($building_id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Parametro buildingId mancante o vuoto']);
            exit;
        }

        $aule = $this->Room->get_aule_by_building($building_id);
        echo json_encode($aule);
        exit;
    }

}