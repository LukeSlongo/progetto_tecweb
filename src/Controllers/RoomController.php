<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\IssueModel;
use App\Models\RoomModel;
use App\Helpers\ComponentHelper;
use \App\Helpers\BreadcrumbHelper;

use App\Core\Auth;

class RoomController extends Controller
{

    private $Room;

    public function __construct()
    {
        $this->Room = new RoomModel();
        BreadcrumbHelper::reset();
        BreadcrumbHelper::add('Home', '/');
    }

    // esegue la ricerca con la funzione searchRoom e carica la pagina con i dati ricercati
    public function viewRoomList()
    {
        $query = $this->get('search');
        $rooms = $this->searchRoom($query);
        if (empty($rooms)) {
            $messaggio = !empty($query) 
                ? "Nessuna corrispondenza trovata per la ricerca: <strong>" . htmlspecialchars($query) . "</strong>" 
                : "Nessuna aula disponibile al momento.";

            $items_html = '<tr><td colspan="4" style="text-align: center; padding: 2.5rem; color: var(--text-gray);">' . $messaggio . '</td></tr>';
        } else {
            $items_html = ComponentHelper::renderList('roomListItem', $rooms);
        }

        $this->page_title = "Aule - UniFix";
        $this->scriptPathList[] = 'room';
        BreadcrumbHelper::add('Aule', '/rooms');
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

        $status_translations = [
            'open' => 'Aperto',
            'in_progress' => 'In riparazione',
            'closed' => 'Chiuso',
            'resolved' => 'Risolto'
        ];

        foreach ($issues_of_room as &$issue) {
            $status_en = $issue['issue_status'] ?? ''; 
            
            if (isset($status_translations[$status_en])) {
                $issue['issue_status'] = $status_translations[$status_en];
            }
        }
        unset($issue);

        if (empty($issues_of_room)) {
            $messaggio = "Nessuna segnalazione presente per questa aula. Tutto funziona correttamente!";
            $issues_html = '<tr><td colspan="4" style="text-align: center; padding: 2.5rem; color: var(--success-green); font-weight: 600;">' . $messaggio . '</td></tr>';
        } else {
            $issues_html = ComponentHelper::renderList('issueListItemRoomPage', $issues_of_room);
        }
        $this->scriptPathList[] = 'room';
        BreadcrumbHelper::add('Aule', '/rooms');
        BreadcrumbHelper::add($room_data['room_name']);
        $this->render('roomDetailPage', [
            'ROOM_NAME' => $room_data['room_name'],
            'ROOM_ID' => $room_data['room_id'],
            'BUILDING_NAME' => $room_data['building_name'],
            'BUILDING_ADDRESS' => $room_data['building_address'],
            'ISSUES_LIST' => $issues_html
        ]);
    }


}