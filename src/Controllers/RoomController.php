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
            $role = $_SESSION['user']['role'] ?? '';

            foreach ($rooms as &$room) {
                if ($role === 'student') {
                    $room['ADD_TO_FAVORITES_BUTTON'] = <<<HTML
                        <form action="/api/favorites/{$room['room_id']}/add"
                            method="POST"
                            class="favorite-form"
                            data-room-id="{$room['room_id']}"
                            data-room-name="{$room['room_name']}"
                            data-is-favorite="false">
                            <button type="submit"
                                    id="btn-favorite-{$room['room_id']}"
                                    class="btn-favorite"
                                    title="Aggiungi ai preferiti"
                                    aria-label="Azione preferiti aula: {$room['room_name']}">
                                Preferiti
                            </button>
                        </form>
                        HTML;
                } else {
                    $room['ADD_TO_FAVORITES_BUTTON'] = '<span class="solo-studenti">Solo Studenti</span>';
                }
            }
            unset($room);
            $items_html = ComponentHelper::renderList('roomListItem', $rooms);
        }

        $this->page_title = "Aule - UniFix";
        $this->page_description = "Visualizza l'elenco delle aule disponibili all'Università di Padova e il loro stato attuale.";
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

        $room = $this->Room->getRoomWithBuilding($room_id);
        $clean_title = htmlspecialchars(strip_tags($room['room_name']), ENT_QUOTES, 'UTF-8');
        $this->page_description = "Visualizza i dettagli dell'aula " . $clean_title . " comprese le segnalazioni attive e lo stato dell'aula.- UniFix";

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

        $role = $_SESSION['user']['role'] ?? '';
        $add_to_favorites_button = ($role === 'student') ? 
            '<form action="/api/favorites/##ROOM_ID##/add" method="POST" class="favorite-form" data-room-id="##ROOM_ID##" data-is-favorite="false">
                <button type="submit" id="btn-favorite-##ROOM_ID##" class="btn-favorite" title="Aggiungi ai preferiti" aria-label="Azione preferiti aula: ##ROOM_NAME##">
                    Preferiti
                </button>
            </form>' : 
        '';

        $this->scriptPathList[] = 'room';
        BreadcrumbHelper::add('Aule', '/rooms');
        BreadcrumbHelper::add($room_data['room_name']);
        $this->render('roomDetailPage', [
            'ROOM_NAME' => $room_data['room_name'],
            'ROOM_ID' => $room_data['room_id'],
            'BUILDING_NAME' => $room_data['building_name'],
            'BUILDING_ADDRESS' => $room_data['building_address'],
            'ISSUES_LIST' => $issues_html,
            'ADD_TO_FAVORITES_BUTTON' => $add_to_favorites_button
        ]);
    }


}