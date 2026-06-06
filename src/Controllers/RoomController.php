<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\RoomModel;
use App\Helpers\ComponentHelper;

class RoomController extends Controller {

    private $Room;

    public function __construct()
    {
        $this->Room = new RoomModel();
        //BreadcrumbHelper::reset();
    }

    public function viewRoomList()
    {
        $this->requireLogin();
        $room_model = new RoomModel();
        $rooms = $room_model->searchRoomsWithCount();

        $items_html = ComponentHelper::renderList('roomListItem', $rooms);


        $this->page_title = "Aule - UniFix";
        $this->render('roomListPage', ['ROOM_LIST_ITEMS' => $items_html]);
    }


}