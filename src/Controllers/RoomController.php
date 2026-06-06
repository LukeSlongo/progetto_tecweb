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

    // esegue la ricerca con la funzione searchRoom e carica la pagina con i dati ricercati
    public function viewRoomList()
    {
        $this->requireLogin();
        
        $query = $this->get('search');
        $rooms = $this->searchRoom($query);
        $items_html = ComponentHelper::renderList('roomListItem', $rooms);

        $this->page_title = "Aule - UniFix";
        $this->render('roomListPage', ['ROOM_LIST_ITEMS' => $items_html]);
    }

    public function searchRoom($query)
    {
        $room_model = new RoomModel();
        $roomList = $room_model->searchRoomsWithCount($query);
        return $roomList;
    }


}