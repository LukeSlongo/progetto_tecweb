<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\IssueModel;
use App\Models\RoomModel;
use App\Helpers\ComponentHelper;
use App\Core\Auth;

class RoomController extends Controller
{

    private $Room;

    public function __construct()
    {
        $this->Room = new RoomModel();
        //BreadcrumbHelper::reset();
    }

    // esegue la ricerca con la funzione searchRoom e carica la pagina con i dati ricercati
    public function viewRoomList()
    {
        $query = $this->get('search');
        $rooms = $this->searchRoom($query);
        $items_html = ComponentHelper::renderList('roomListItem', $rooms);

        $this->page_title = "Aule - UniFix";
        $this->scriptPathList[] = 'room';
        $this->render('roomListPage', ['ROOM_LIST_ITEMS' => $items_html]);
    }

    public function searchRoom($query)
    {
        $room_model = new RoomModel();
        $roomList = $room_model->searchRoomsWithCount($query);
        return $roomList;
    }

    public function viewRoomDetail($room_id)
    {
        $this->page_title = "Dettaglio aula - UniFix";

        $room_data = $this->Room->getRoomWithBuilding($room_id);
        if (!$room_data) {
            $this->abort(404, "L'aula richiesta non esiste.");
        }

        $issue_model = new IssueModel();
        $issues_of_room = $issue_model->getIssuesByRoom($room_id);
        $issues_html = ComponentHelper::renderList('issueListItem', $issues_of_room);
        $this->scriptPathList[] = 'room';

        $this->render('roomDetailPage', [
            'ROOM_NAME' => $room_data['room_name'],
            'ROOM_ID' => $room_data['room_id'],
            'BUILDING_NAME' => $room_data['building_name'],
            'BUILDING_ADDRESS' => $room_data['building_address'],
            'ISSUES_LIST' => $issues_html
        ]);
    }


}