<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Template;
use App\Models\IssueModel;
use App\Helpers\ComponentHelper;
use \App\Helpers\BreadcrumbHelper;
use App\Core\Auth;
use \App\Models\BuildingModel;
use \App\Models\RoomModel;

class IssueController extends Controller
{

    private $Issue;

    public function __construct()
    {
        $this->Issue = new IssueModel();
        $this->scriptPathList[] = 'issue';
        BreadcrumbHelper::reset();
        BreadcrumbHelper::add('Home', '/');
    }

    public function viewIssueForm()
    {
        $this->page_title = "Nuova Segnalazione - UniFix";

        $buildingModel = new BuildingModel();
        $roomModel = new RoomModel();

        $buildings = $buildingModel->findAll();
        $rooms = $roomModel->findAll();

        $buildingsHtml = '';
        foreach ($buildings as $building) {
            $buildingsHtml .= '<option value="' . $building['id'] . '">' . htmlspecialchars($building['name'], ENT_QUOTES, 'UTF-8') . '</option>';
        }

        $roomsHtml = '';
        foreach ($buildings as $building) {
            $roomsHtml .= '<optgroup label="' . htmlspecialchars($building['name'], ENT_QUOTES, 'UTF-8') . '" data-building-id="' . $building['id'] . '">';
            foreach ($rooms as $room) {
                if ($room['building_id'] == $building['id']) {
                    $roomsHtml .= '<option value="' . $room['id'] . '">' . htmlspecialchars($room['name'], ENT_QUOTES, 'UTF-8') . '</option>';
                }
            }
            $roomsHtml .= '</optgroup>';
        }
        BreadcrumbHelper::add('Nuovo guasto');
        $this->render('issueFormPage', [
            'BUILDING_OPTIONS' => $buildingsHtml,
            'ROOM_OPTIONS' => $roomsHtml
        ]);
    }

    public function saveIssue()
    {
        $room_id = $this->post('room_id');
        $title = $this->post('issue_title');
        $description = $this->post('issue_description');

        if (empty($room_id) || empty($title) || empty($description)) {
            $_SESSION['flash_error'] = "Tutti i campi (Aula, Titolo, Descrizione) sono obbligatori.";
            $this->redirect('/issues/new');
            return;
        }

        $user = Auth::getUser();
        if (!$user) {
            $_SESSION['flash_error'] = "Sessione scaduta, effettua nuovamente il login.";
            $this->redirect('/login');
            return;
        }

        $issueModel = new IssueModel();

        try {
            $issueModel->registerIssue($user['id'], $room_id, $title, $description);

            $_SESSION['flash_success'] = "Segnalazione inviata con successo!";
            $this->redirect('/');

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = "Errore durante l'invio della segnalazione. Riprova.";
            $this->redirect('/issues/new');
        }
    }

    public function viewIssueList()
    {
        $this->page_title = "Issue List - UniFix";
        $this->scriptPathList = ["issue"];

        $status = $this->get('status');
        $issues = $this->searchIssues($status);

        $items_html = ComponentHelper::renderList('issueListItem', $issues);
        BreadcrumbHelper::add('Guasti', '/issues');
        $this->render('issueListPage', [
            'ISSUE_LIST_ITEMS' => $items_html,
            'CHECKED_ALL' => empty($status) ? 'checked' : '',
            'CHECKED_OPEN' => $status === 'open' ? 'checked' : '',
            'CHECKED_IN_PROGRESS' => $status === 'in_progress' ? 'checked' : '',
            'CHECKED_CLOSED' => $status === 'closed' ? 'checked' : ''
        ]);
    }

    public function searchIssues($status)
    {
        $issue_model = new IssueModel();
        $issueList = $issue_model->getIssuesByStatus($status);
        return $issueList;
    }

    public function viewIssueDetail($id)
    {
        $this->page_title = "Dettaglio Issue - UniFix";
        $issue = $this->Issue->getIssueDetails($id);


        $role = $_SESSION['user']['role'] ?? '';
        $can_see_reporter = ($role === 'admin' || $role === 'technician');
        $deleteIssueButton = (Auth::isAdmin() || (Auth::isLogged() && Auth::isOwner($issue['reporter_id'])))
            ? '<form action="/issues/' . $issue['issue_id'] . '/delete" method="POST" onsubmit="return confirm(\'Vuoi eliminare questa segnalazione?\')">'
            . '<button class="btn btn-primary" type="submit">Elimina segnalazione</button>'
            . '</form>'
            : '';
        BreadcrumbHelper::add('Guasti', '/issues');
        BreadcrumbHelper::add($issue['issue_title']);
        $this->render('issueDetailPage', [
            'ISSUE_TITLE' => $issue['issue_title'],
            'STATUS' => ucfirst(str_replace('_', ' ', $issue['issue_status'])),

            //informazione sulla direzione
            'BUILDING_NAME' => $issue['building_name'],
            'ROOM_NAME' => $issue['room_name'],

            //data di inizio e di fine, con formatto
            'OPEN_DATE' => date('d/m/Y H:i', strtotime($issue['opened_at'])),
            'CLOSE_DATE' => $issue['closed_at'] ? date('d/m/Y H:i', strtotime($issue['closed_at'])) : 'Non ancora chiusa',

            // Controlla se il tecnico è assegnato, altrimenti mostra un messaggio di default, e se il reporter è visibile in base al ruolo dell'utente
            'TECHNICIAN_NAME' => $issue['technician_name'] ?? 'Nessun tecnico assegnato',
            'REPORTER_NAME' => $can_see_reporter ? ($issue['reporter_name'] ?? 'Utente eliminato') : 'Nascosto (Solo Admin/Tecnico)',
            'DELETE_ISSUE_BUTTON' => $deleteIssueButton
        ]);
    }

    public function deleteIssue($id)
    {
        if ($this->Issue->find_issue($id) === false) {
            throw new \App\Exceptions\NotFoundException("Segnalazione non trovata.");
        }
        $this->Issue->delete($id);
        $this->redirect('/issues');
    }
}